<?php

namespace App\Support;

use App\Models\Administration;
use App\Models\Role;
use App\Models\Statussdm;
use App\Models\Task;
use App\Models\TaskDifficulty;
use App\Models\User;

class StatusSdmManager
{
    /**
     * Nilai status task yang berarti masih ada pekerjaan aktif (selaras dengan seeder + variasi casing lama).
     *
     * @return list<string>
     */
    private static function activeWorkTaskStatuses(): array
    {
        return ['To Do', 'To do', 'In progress', 'Review', 'Revision'];
    }

    /**
     * Reset harian: semua SDM operasional (staff / director) — kolom aktivitas kembali ke Not Ready.
     * id_status_sdm (kepegawaian Tetap/Kontrak/Magang) tidak diubah.
     * Cuti disetujui hari ini dikecualikan.
     */
    public static function dailyOperationalReset(): int
    {
        $notReadyStatus = Statussdm::firstOrCreate(['status_sdm' => 'Not Ready']);
        $resetIds = Statussdm::dailyActivityResetSourceIds();

        $roleIds = Role::query()->whereIn('role', ['staff', 'director'])->pluck('id');
        if ($roleIds->isEmpty()) {
            return 0;
        }

        $today = now()->toDateString();

        $updated = User::query()
            ->whereIn('id_role', $roleIds)
            ->whereDoesntHave('administrations', function ($q) use ($today) {
                $q->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->whereHas('status', function ($q2) {
                        $q2->where('name', 'accept');
                    });
            })
            ->where(function ($q) use ($resetIds) {
                $q->whereIn('id_activity_status_sdm', $resetIds)
                    ->orWhereNull('id_activity_status_sdm');
            })
            ->update(['id_activity_status_sdm' => $notReadyStatus->id]);

        User::query()
            ->whereIn('id_role', $roleIds)
            ->chunk(150, function ($users): void {
                foreach ($users as $user) {
                    self::syncForUser($user);
                }
            });

        return $updated;
    }

    public static function syncForUser(User $user): void
    {
        $user->loadMissing(['role', 'activityStatussdm']);
        if (!$user->role || !in_array($user->role->role, ['staff', 'director'], true)) {
            return;
        }

        $today = now()->toDateString();
        $isAbsent = Administration::query()
            ->where('id_user', $user->id)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->whereHas('status', function ($q) {
                $q->where('name', 'accept');
            })
            ->exists();

        if ($isAbsent) {
            self::applyActivityStatus($user, 'Absent');

            return;
        }

        $standbyDifficultyId = TaskDifficulty::query()->where('difficulty', 'Stand By')->value('id');

        $workTasksQuery = Task::query()->where('id_user', $user->id);
        if ($standbyDifficultyId) {
            $workTasksQuery->where('id_difficulty', '!=', $standbyDifficultyId);
        }

        $hasActiveWorkTask = (clone $workTasksQuery)
            ->whereHas('status', function ($q) {
                $q->whereIn('status', self::activeWorkTaskStatuses());
            })
            ->exists();

        if ($hasActiveWorkTask) {
            self::applyActivityStatus($user, 'Ready');

            return;
        }

        // Stand By bersifat harian: hanya task Stand By yang dibuat hari ini
        // yang boleh mengubah activity status ke "Stand By".
        // Jika user sudah punya task kerja aktif, status harus tetap "Ready" (lihat return di atas).
        $hasStandbyTask = $standbyDifficultyId
            && Task::query()
                ->where('id_user', $user->id)
                ->where('id_difficulty', $standbyDifficultyId)
                ->whereDate('created_at', $today)
                ->whereHas('status', function ($q) {
                    $q->where('status', '!=', 'Complete');
                })
                ->exists();

        if ($hasStandbyTask) {
            self::applyActivityStatus($user, 'Stand By');

            return;
        }

        if ($workTasksQuery->exists()) {
            $hasIncompleteWork = (clone $workTasksQuery)
                ->whereHas('status', function ($q) {
                    $q->where('status', '!=', 'Complete');
                })
                ->exists();

            if (!$hasIncompleteWork) {
                self::applyActivityStatus($user, 'Stand By');

                return;
            }
        }

        self::applyActivityStatus($user, 'Not Ready');
    }

    private static function applyActivityStatus(User $user, string $statusSdmName): void
    {
        $status = Statussdm::firstOrCreate(['status_sdm' => $statusSdmName]);
        if ($user->id_activity_status_sdm !== $status->id) {
            $user->id_activity_status_sdm = $status->id;
            $user->save();
        }
    }
}
