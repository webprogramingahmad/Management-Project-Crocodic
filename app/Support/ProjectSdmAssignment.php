<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProjectSdmAssignment
{
    /**
     * @return list<string> Unique user IDs for attach/sync.
     */
    public static function validatedIds(Request $request): array
    {
        $sdms = $request->input('sdms');

        if ($sdms === null || $sdms === []) {
            return [];
        }

        if (!is_array($sdms)) {
            throw ValidationException::withMessages([
                'sdms' => ['Format penugasan SDM tidak valid.'],
            ]);
        }

        $all = [];

        foreach ($sdms as $divisionId => $slots) {
            $divisionId = (string) $divisionId;

            if (!is_array($slots)) {
                throw ValidationException::withMessages([
                    'sdms' => ['Format penugasan SDM tidak valid.'],
                ]);
            }

            $picked = array_values(array_filter($slots, fn ($v) => $v !== null && $v !== ''));

            if (count($picked) > 2) {
                throw ValidationException::withMessages([
                    'sdms' => ['Maksimal 2 SDM per divisi.'],
                ]);
            }

            if (count($picked) !== count(array_unique($picked))) {
                throw ValidationException::withMessages([
                    'sdms' => ['Kedua SDM dalam satu divisi harus berbeda.'],
                ]);
            }

            foreach ($picked as $userId) {
                if (!Str::isUuid($userId)) {
                    throw ValidationException::withMessages([
                        'sdms' => ['ID SDM tidak valid.'],
                    ]);
                }

                $user = User::query()->find($userId);
                if (!$user || (string) $user->id_divisi !== $divisionId) {
                    throw ValidationException::withMessages([
                        'sdms' => ['Setiap SDM harus berasal dari divisi yang dipilih.'],
                    ]);
                }

                $all[] = $userId;
            }
        }

        if (count($all) !== count(array_unique($all))) {
            throw ValidationException::withMessages([
                'sdms' => ['Satu SDM tidak boleh dipilih lebih dari satu kali pada project ini.'],
            ]);
        }

        return array_values(array_unique($all));
    }
}
