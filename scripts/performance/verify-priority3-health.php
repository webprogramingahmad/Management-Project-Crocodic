<?php

/**
 * Verifikasi kesehatan app setelah Priority 3 — tidak mengubah data.
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Cache;

$errors = [];

$userCount = User::count();
echo "Users in database: {$userCount}\n";

$cacheStore = config('cache.default');
$sessionDriver = config('session.driver');
echo "CACHE_STORE effective: {$cacheStore}\n";
echo "SESSION_DRIVER effective: {$sessionDriver}\n";

try {
    Cache::put('priority3:health', 'ok', 60);
    if (Cache::get('priority3:health') !== 'ok') {
        $errors[] = 'Cache read/write test failed';
    } else {
        echo "Cache test: OK\n";
    }
} catch (Throwable $e) {
    $errors[] = 'Cache error: '.$e->getMessage();
}

if ($cacheStore === 'redis' || $sessionDriver === 'redis') {
    $host = config('database.redis.default.host');
    $port = config('database.redis.default.port');
    $socket = @fsockopen($host, $port, $errno, $errstr, 2.0);
    if ($socket === false) {
        $errors[] = "Redis not reachable at {$host}:{$port}";
    } else {
        fclose($socket);
        echo "Redis port: OK\n";
    }
}

if ($errors !== []) {
    echo "\nFAILED:\n";
    foreach ($errors as $error) {
        echo " - {$error}\n";
    }
    exit(1);
}

echo "\nHealth check passed.\n";
exit(0);
