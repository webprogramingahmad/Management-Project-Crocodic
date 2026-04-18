<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DestroypictAdminController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (! $user->avatar) {
            return response()->json(['message' => 'User tidak memiliki avatar.'], 404);
        }

        try {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        } catch (\Throwable $e) {
        }

        $user->avatar = null;
        $user->save();

        return redirect()->back()->with('success', 'User berhasil dihapus.');;
    }
}
