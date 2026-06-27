<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Support\DashboardLeftTabsQuery;

$today = now()->toDateString();
$tabs = DashboardLeftTabsQuery::build($today);

$totalUsers = User::count();
$staffDirector = User::whereHas('role', fn ($q) => $q->whereIn('role', ['staff', 'director']))->count();
$executive = User::whereHas('role', fn ($q) => $q->where('role', 'executive'))->count();
$inTabs = $tabs['notready']->count() + $tabs['standby']->count() + $tabs['absent']->count();
$readyUsers = $tabs['ready']->pluck('id_user')->unique()->count();

echo "Total users: {$totalUsers}\n";
echo "Staff+Director: {$staffDirector}\n";
echo "Executive: {$executive}\n";
echo "Not Ready tab: {$tabs['notready']->count()}\n";
echo "Stand By tab: {$tabs['standby']->count()}\n";
echo "Absent tab: {$tabs['absent']->count()}\n";
echo "Ready tab tasks: {$tabs['ready']->count()} (unique users: {$readyUsers})\n";
echo "Complete tab tasks: {$tabs['complete']->count()}\n";
echo "Sum user tabs (notready+standby+absent): {$inTabs}\n";

$nullActivity = User::whereHas('role', fn ($q) => $q->whereIn('role', ['staff', 'director']))
    ->whereNull('id_activity_status_sdm')
    ->count();
echo "Staff/Director null activity: {$nullActivity}\n";

$statusCounts = User::query()
    ->join('roles', 'users.id_role', '=', 'roles.id')
    ->whereIn('roles.role', ['staff', 'director'])
    ->leftJoin('statussdms as act', 'users.id_activity_status_sdm', '=', 'act.id')
    ->selectRaw('COALESCE(act.status_sdm, "(null)") as status_sdm, count(*) as c')
    ->groupBy('act.status_sdm')
    ->pluck('c', 'status_sdm');

echo "Activity status breakdown (staff+director):\n";
foreach ($statusCounts as $status => $count) {
    echo "  {$status}: {$count}\n";
}

$readyStatusId = \App\Models\Statussdm::where('status_sdm', 'Ready')->value('id');
$readyNoTask = User::whereHas('role', fn ($q) => $q->whereIn('role', ['staff', 'director']))
    ->where('id_activity_status_sdm', $readyStatusId)
    ->whereDoesntHave('tasks', function ($q) {
        $q->whereHas('status', fn ($s) => $s->whereIn('class', ['todo', 'progress', 'review', 'revision']));
    })
    ->count();
echo "Staff/Director with Ready status but no active task on dashboard: {$readyNoTask}\n";
