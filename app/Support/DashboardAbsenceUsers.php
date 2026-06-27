<?php

namespace App\Support;

use App\Models\Administration;
use Illuminate\Support\Facades\Cache;

class DashboardAbsenceUsers
{
    /**
     * User IDs with accepted leave/administration covering the given date.
     *
     * @return list<string>
     */
    public static function idsFor(string $today): array
    {
        $acceptedStatusId = DashboardReferenceData::administrationStatusId('accept');
        $cacheKey = 'dashboard:absent_user_ids:'.$today.':'.($acceptedStatusId ?? 'none');

        return Cache::remember($cacheKey, 60, function () use ($today, $acceptedStatusId) {
            if (! $acceptedStatusId) {
                return [];
            }

            return Administration::query()
                ->where('id_status', $acceptedStatusId)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->distinct()
                ->pluck('id_user')
                ->all();
        });
    }
}
