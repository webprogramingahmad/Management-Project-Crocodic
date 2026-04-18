<?php

namespace App\Console\Commands;

use App\Support\StatusSdmManager;
use Illuminate\Console\Command;

class ResetNotReadyStatus extends Command
{
    protected $signature = 'sdm:reset-not-ready';

    protected $description = 'Reset status operasional SDM (Ready/Stand By/Not Ready) ke Not Ready untuk staff & director (harian)';

    public function handle(): int
    {
        $count = StatusSdmManager::dailyOperationalReset();
        $this->info("Status operasional di-reset ke Not Ready untuk {$count} SDM.");

        return self::SUCCESS;
    }
}
