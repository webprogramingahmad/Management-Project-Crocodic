<?php

namespace App\Support;

use App\Models\Task;
use App\Models\TaskPhoto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Mengelola upload & hapus foto bukti hasil kerja (Work Evidence) pada task.
 * Hanya pemilik task (id_user) yang boleh meng-upload / menghapus foto.
 */
class TaskPhotoManager
{
    public const DISK = 'public';
    public const DIRECTORY = 'task-photos';

    public static function isOwner(User $user, Task $task): bool
    {
        return (string) $task->id_user === (string) $user->id;
    }

    /**
     * Simpan foto-foto yang ada di request untuk task tertentu.
     * Tidak melakukan apa-apa jika tidak ada file yang dikirim.
     */
    public static function storeFromRequest(Request $request, Task $task, User $user): void
    {
        if (! $request->hasFile('photos')) {
            return;
        }

        abort_unless(self::isOwner($user, $task), 403);

        foreach ($request->file('photos') as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $path = $file->store(self::DIRECTORY, self::DISK);

            TaskPhoto::create([
                'id_task' => $task->id,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by' => $user->id,
            ]);
        }
    }

    /**
     * Hapus satu foto (file fisik + record). Hanya pemilik task.
     */
    public static function delete(TaskPhoto $photo, User $user): void
    {
        $photo->loadMissing('task');
        $task = $photo->task;

        abort_if(! $task, 404);
        abort_unless(self::isOwner($user, $task), 403);

        if ($photo->path && Storage::disk(self::DISK)->exists($photo->path)) {
            Storage::disk(self::DISK)->delete($photo->path);
        }

        $photo->delete();
    }
}
