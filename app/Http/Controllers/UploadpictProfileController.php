<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadpictProfileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'avatar' => 'required|image|max:5120',
        ]);

        if (! $request->hasFile('avatar')) {
            return response()->json(['message' => 'File avatar tidak ditemukan'], 422);
        }

        $file = $request->file('avatar');

        if (! $file->isValid()) {
            return response()->json(['message' => 'File upload tidak valid'], 422);
        }

        $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';
        $filename = (string) Str::uuid() . '.' . $ext;

        try {
            $path = $file->storeAs('avatars', $filename, 'public');
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal menyimpan file: ' . $e->getMessage()], 500);
        }

        if (! $path) {
            return response()->json(['message' => 'Gagal menyimpan file'], 500);
        }

        if ($user->avatar) {
            try {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            } catch (\Throwable $e) {
            }
        }

        $user->avatar = $filename;
        $user->save();

        return response()->json([
            'message' => 'Avatar berhasil diupload',
            'filename' => $filename,
            'url' => asset('storage/avatars/' . $filename),
        ], 200);
    }
}
