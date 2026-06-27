<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Statussdm;
use App\Models\User;
use App\Support\DashboardReferenceData;

$today = now()->toDateString();
$acceptedStatusId = DashboardReferenceData::administrationStatusId('accept');
$statusMap = DashboardReferenceData::sdmActivityStatusIds();
$notReadyStatusId = $statusMap['Not Ready'] ?? null;
$standbyStatusId = $statusMap['Stand By'] ?? null;

echo "Not Ready status ID from cache map: {$notReadyStatusId}\n";
echo "All Not Ready rows in statussdms:\n";
foreach (Statussdm::where('status_sdm', 'Not Ready')->get(['id', 'status_sdm']) as $row) {
    $count = User::where('id_activity_status_sdm', $row->id)->count();
    echo "  {$row->id} => {$count} users\n";
}

$absenceConstraint = function ($q) use ($today, $acceptedStatusId) {
    $q->whereDate('start_date', '<=', $today)
        ->whereDate('end_date', '>=', $today);
    if ($acceptedStatusId) {
        $q->where('id_status', $acceptedStatusId);
    } else {
        $q->whereRaw('1 = 0');
    }
};

$notReadyAll = User::whereHas('role', fn ($q) => $q->whereIn('role', ['staff', 'director']))
    ->where('id_activity_status_sdm', $notReadyStatusId)
    ->count();

$notReadyNoAbsence = User::whereHas('role', fn ($q) => $q->whereIn('role', ['staff', 'director']))
    ->where('id_activity_status_sdm', $notReadyStatusId)
    ->whereDoesntHave('administrations', $absenceConstraint)
    ->count();

echo "Not Ready (map id) staff+director: {$notReadyAll}\n";
echo "Not Ready excluding absence admin: {$notReadyNoAbsence}\n";

$absentActivityId = Statussdm::where('status_sdm', 'Absent')->value('id');
$absentActivityUsers = User::whereHas('role', fn ($q) => $q->whereIn('role', ['staff', 'director']))
    ->where('id_activity_status_sdm', $absentActivityId)
    ->count();
$absentActivityWithAdmin = User::whereHas('role', fn ($q) => $q->whereIn('role', ['staff', 'director']))
    ->where('id_activity_status_sdm', $absentActivityId)
    ->whereHas('administrations', $absenceConstraint)
    ->count();

echo "Users with Absent activity status: {$absentActivityUsers}\n";
echo "  with active accepted leave today: {$absentActivityWithAdmin}\n";

$readyId = Statussdm::where('status_sdm', 'Ready')->value('id');
$readyUsers = User::whereHas('role', fn ($q) => $q->whereIn('role', ['staff', 'director']))
    ->where('id_activity_status_sdm', $readyId)
    ->count();
echo "Users with Ready activity status: {$readyUsers}\n";

$visible = User::whereHas('role', fn ($q) => $q->whereIn('role', ['staff', 'director']))
    ->where(function ($q) use ($notReadyStatusId, $standbyStatusId, $absenceConstraint) {
        $q->where(function ($w) use ($notReadyStatusId, $absenceConstraint) {
            $w->where('id_activity_status_sdm', $notReadyStatusId)
                ->whereDoesntHave('administrations', $absenceConstraint);
        })->orWhere(function ($w) use ($standbyStatusId, $absenceConstraint) {
            $w->where('id_activity_status_sdm', $standbyStatusId)
                ->whereDoesntHave('administrations', $absenceConstraint);
        })->orWhereHas('administrations', $absenceConstraint);
    })
    ->count();

echo "Potentially visible in user tabs (notready+standby+absent logic): {$visible}\n";
echo "Missing staff+director from user tabs: " . (112 - $visible) . "\n";
