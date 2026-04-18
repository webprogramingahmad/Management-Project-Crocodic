<?php

namespace App\Http\Controllers\User\Profile;

use App\Http\Controllers\Concerns\ValidatesWithEditRedirect;
use App\Http\Controllers\Controller;
use App\Models\Statussdm;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                'required',
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
            'tgl_masuk' => ['required', 'date'],
            'id_graduate' => ['required'],
        ], 'staff.profile.edit', $id);

        if ($validate instanceof RedirectResponse) {
            return $validate;
        }

        if ($request->filled('password')) {
            $validate['password'] = Hash::make($request->password);
        } else {
            unset($validate['password']);
        }

        Statussdm::finalizeEmploymentStatusValidated($validate, $user, $request);

        $user->update($validate);

        return redirect()->route('staff.profile.index')->with('success', 'Data user berhasil diperbarui.');
    }
}
