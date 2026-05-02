<?php

namespace App\Console\Commands;

use App\Models\Statussdm;
use App\Models\User;
use Illuminate\Console\Command;

class CheckSdmStatusHealth extends Command
{
    protected $signature = 'sdm:health-check {--warn-threshold=90 : Warn when one status exceeds this percentage}';

    protected $description = 'Ringkasan status operasional SDM harian (Ready, Stand By, Not Ready, Absent) + warning anomali.';

    public function handle(): int
    {
        $targetStatuses = ['Ready', 'Stand By', 'Not Ready', 'Absent'];
        $statusRows = Statussdm::query()
            ->whereIn('status_sdm', $targetStatuses)
            ->get(['id', 'status_sdm']);

        $countsById = User::query()
            ->whereIn('id_activity_status_sdm', $statusRows->pluck('id'))
            ->selectRaw('id_activity_status_sdm, COUNT(*) as total')
            ->groupBy('id_activity_status_sdm')
            ->pluck('total', 'id_activity_status_sdm');

        $summary = [];
        $totalOperational = 0;
        foreach ($targetStatuses as $label) {
            $row = $statusRows->firstWhere('status_sdm', $label);
            $count = $row ? (int) ($countsById[$row->id] ?? 0) : 0;
            $summary[] = ['status' => $label, 'total' => $count];
            $totalOperational += $count;
        }

        $this->info('SDM activity status health check');
        $this->line('Date: '.now()->toDateTimeString());
        $this->table(['Status', 'Total'], $summary);
        $this->line("Total operational users with activity status: {$totalOperational}");

        if ($totalOperational === 0) {
            $this->warn('WARNING: No operational users currently mapped to activity statuses.');

            return self::SUCCESS;
        }

        $warnThreshold = max(1, min(100, (int) $this->option('warn-threshold')));
        foreach ($summary as $row) {
            $percentage = ($row['total'] / $totalOperational) * 100;
            if ($percentage >= $warnThreshold) {
                $this->warn(sprintf(
                    'WARNING: %s dominates %.2f%% (%d/%d). Please verify daily reset and task distribution.',
                    $row['status'],
                    $percentage,
                    $row['total'],
                    $totalOperational
                ));
            }
        }

        return self::SUCCESS;
    }
}
