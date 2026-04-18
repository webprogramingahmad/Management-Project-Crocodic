<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Statussdm extends Model
{
    use HasFactory, Notifiable;

    /** Status operasional harian (bukan jenis kepegawaian). */
    public const OPERATIONAL_WORKFLOW = ['Ready', 'Stand By', 'Not Ready'];

    /** Jenis kepegawaian + Absent (boleh diatur manual admin). */
    public const ADMIN_ASSIGNABLE = ['Tetap', 'Kontrak', 'Magang', 'Absent'];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'status_sdm',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public static function isOperationalWorkflow(?self $row): bool
    {
        return $row !== null && in_array($row->status_sdm, self::OPERATIONAL_WORKFLOW, true);
    }

    public static function forEmploymentProfileSelect()
    {
        $rows = self::query()
            ->whereIn('status_sdm', ['Tetap', 'Kontrak', 'Magang'])
            ->get();

        $order = ['Tetap' => 0, 'Magang' => 1, 'Kontrak' => 2];

        return $rows->sortBy(fn ($row) => $order[$row->status_sdm] ?? 99)->values();
    }

    public static function forAdminUserEditSelect()
    {
        return self::query()
            ->whereIn('status_sdm', self::ADMIN_ASSIGNABLE)
            ->orderBy('status_sdm')
            ->get();
    }

    /** @return list<string> */
    public static function employmentTypeIds(): array
    {
        return self::query()
            ->whereIn('status_sdm', ['Tetap', 'Kontrak', 'Magang'])
            ->pluck('id')
            ->all();
    }

    /** Apakah UUID ini salah satu baris Tetap/Kontrak/Magang (bukan operasional/Absent). */
    public static function isEmploymentStatusId(?string $id): bool
    {
        return $id !== null && in_array($id, self::employmentTypeIds(), true);
    }

    /**
     * Setelah validasi form (edit profil mandiri atau edit user oleh admin): jenis kepegawaian boleh kosong.
     * Jangan mengubah id_status_sdm jika nilai saat ini bukan Tetap/Kontrak/Magang.
     *
     * @param  array<string, mixed>  $validated
     */
    public static function finalizeEmploymentStatusValidated(array &$validated, User $user, Request $request): void
    {
        $user->loadMissing('statussdm');
        if ($request->filled('id_status_sdm')) {
            return;
        }
        if (self::isEmploymentStatusId($user->id_status_sdm)) {
            $validated['id_status_sdm'] = null;
        } else {
            unset($validated['id_status_sdm']);
        }
    }

    /** @return list<string> */
    public static function adminAssignableIds(): array
    {
        return self::query()
            ->whereIn('status_sdm', self::ADMIN_ASSIGNABLE)
            ->pluck('id')
            ->all();
    }

    /** @return list<string> */
    public static function operationalWorkflowIds(): array
    {
        return self::query()
            ->whereIn('status_sdm', self::OPERATIONAL_WORKFLOW)
            ->pluck('id')
            ->all();
    }

    /** Status aktivitas (baris di statussdms): Ready, Stand By, Not Ready, Absent. */
    public static function activityStatusIds(): array
    {
        return self::query()
            ->whereIn('status_sdm', array_merge(self::OPERATIONAL_WORKFLOW, ['Absent']))
            ->pluck('id')
            ->all();
    }

    /** Untuk reset harian: operasional + Absent di-set ulang ke Not Ready. */
    public static function dailyActivityResetSourceIds(): array
    {
        return array_values(array_unique(array_merge(
            self::operationalWorkflowIds(),
            self::query()->where('status_sdm', 'Absent')->pluck('id')->all()
        )));
    }

    /** Profil: hanya kepegawaian (kolom id_status_sdm). */
    public static function allowedProfileStatusSdmIdsForUser(User $user): array
    {
        return self::employmentTypeIds();
    }

    /** Form admin (legacy): baris statussdms yang boleh di-assign manual termasuk Absent. */
    public static function allowedAdminFormStatusSdmIds(User $user): array
    {
        return self::adminAssignableIds();
    }
}
