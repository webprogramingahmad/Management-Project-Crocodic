<?php

use App\Models\Statussdm;
use App\Models\User;
use App\Support\StatusSdmManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['Tetap', 'Kontrak', 'Magang', 'Ready', 'Stand By', 'Not Ready', 'Absent'] as $statusName) {
            $rows = Statussdm::query()
                ->where('status_sdm', $statusName)
                ->orderBy('created_at')
                ->get(['id']);

            if ($rows->count() <= 1) {
                continue;
            }

            $canonicalId = $rows->first()->id;
            $duplicateIds = $rows->skip(1)->pluck('id')->all();

            User::query()
                ->whereIn('id_activity_status_sdm', $duplicateIds)
                ->update(['id_activity_status_sdm' => $canonicalId]);

            User::query()
                ->whereIn('id_status_sdm', $duplicateIds)
                ->update(['id_status_sdm' => $canonicalId]);

            Statussdm::query()->whereIn('id', $duplicateIds)->delete();
        }

        Cache::forget('dashboard:sdm_activity_status_id_groups');
        Cache::forget('dashboard:sdm_activity_status_ids');

        StatusSdmManager::dailyOperationalReset();
    }

    public function down(): void
    {
        // Duplicate rows are not recreated.
    }
};
