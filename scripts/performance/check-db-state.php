<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$db = config('database.connections.mysql.database');
echo "database={$db}\n";

$tables = Illuminate\Support\Facades\DB::select('SHOW TABLES');
$key = 'Tables_in_' . $db;
echo 'tables=' . count($tables) . "\n";

foreach (['users', 'tasks', 'projects', 'sessions', 'roles'] as $table) {
    try {
        $count = Illuminate\Support\Facades\DB::table($table)->count();
        echo "{$table}={$count}\n";
    } catch (Throwable $e) {
        echo "{$table}=missing\n";
    }
}
