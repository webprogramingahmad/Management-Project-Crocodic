<?php

namespace App\Http\Controllers\Admin\Profile;

use App\Http\Controllers\Concerns\ValidatesWithEditRedirect;
use App\Http\Controllers\Controller;
use App\Models\Statussdm;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UpdateProfileController extends Controller
{
    use ValidatesWithEditRedirect;

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $user = User::findOrFail($id);
        abort_unless((string) Auth::id() === (string) $user->id, 403);

        if ($request->input('id_status_sdm') === '') {
            $request->merge(['id_status_sdm' => null]);
        }

        $validate = $this->validateWithEditRedirect($request, [
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id, 'id'),
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
            ],
            'nik' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'nik')->ignore($user->id, 'id'),
            ],
            'link_tele' => ['required', 'string', 'max:255'],
            'id_status_sdm' => ['nullable', 'uuid', Rule::in(Statussdm::employmentTypeIds())],
            'alamat' => ['required', 'string'],
            'no_telp' => [
                'required',
                'string',
                'min:11',
                'max:13',
                Rule::unique('users', 'no_telp')->ignore($user->id, 'id'),
            ],
            'tgl_lahir' => ['required', 'date'],
            'tgl_masuk' => ['nullable', 'date'],
            'id_graduate' => ['required'],
        ], 'executive.profile.edit', $id);

        if ($validate instanceof RedirectResponse) {
            return $validate;
        }

        if ($request->filled('password')) {
            $validate['password'] = Hash::make($request->password);
        } else {
            unset($validate['password']);
        }

        // Profile edit (self service): core HR fields remain immutable.
        $validate['nik'] = $user->nik;
        $validate['id_status_sdm'] = $user->id_status_sdm;
        $validate['tgl_masuk'] = $user->tgl_masuk;

        Statussdm::finalizeEmploymentStatusValidated($validate, $user, $request);

        $user->update($validate);

        return redirect()->route('executive.profile.index')->with('success', 'Data user berhasil diperbarui.');
    }
}
