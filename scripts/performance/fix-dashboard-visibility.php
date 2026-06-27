<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\StatusSdmManager;
use Illuminate\Support\Facades\Cache;

Cache::forget('dashboard:sdm_activity_status_id_groups');
Cache::forget('dashboard:sdm_activity_status_ids');
Cache::forget('dashboard:left_tabs:' . now()->toDateString());

$resetCount = StatusSdmManager::dailyOperationalReset();
echo "SDM reset/sync affected rows: {$resetCount}\n\n";

require __DIR__ . '/analyze-dashboard-users.php';
