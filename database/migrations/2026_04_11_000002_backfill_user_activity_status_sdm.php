<?php

use App\Models\Statussdm;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Pindahkan status operasional dari id_status_sdm (legacy) ke id_activity_status_sdm;
     * id_status_sdm hanya menyimpan Tetap/Kontrak/Magang.
     */
    public function up(): void
    {
        $notReady = Statussdm::firstOrCreate(['status_sdm' => 'Not Ready']);
        $activityLabels = ['Ready', 'Stand By', 'Not Ready', 'Absent'];

        User::query()->orderBy('id')->chunk(100, function ($users) use ($notReady, $activityLabels) {
            foreach ($users as $user) {
                if ($user->id_activity_status_sdm !== null) {
                    continue;
                }

                if ($user->id_status_sdm === null) {
                    $user->id_activity_status_sdm = $notReady->id;
                    $user->saveQuietly();

                    continue;
                }

                $sdm = Statussdm::find($user->id_status_sdm);
                if (!$sdm) {
                    $user->id_activity_status_sdm = $notReady->id;
                    $user->saveQuietly();

                    continue;
                }

                if (in_array($sdm->status_sdm, $activityLabels, true)) {
                    $user->id_activity_status_sdm = $sdm->id;
                    $user->id_status_sdm = null;
                } else {
                    $user->id_activity_status_sdm = $notReady->id;
                }

                $user->saveQuietly();
            }
        });
    }
};
