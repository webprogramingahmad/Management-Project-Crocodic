<?php

/**
 * Preflight Redis untuk Priority 3. Exit 0 = siap, 1 = Redis tidak tersedia.
 * Tidak mengubah database atau file konfigurasi.
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$host = env('REDIS_HOST', '127.0.0.1');
$port = (int) env('REDIS_PORT', 6379);
$password = env('REDIS_PASSWORD');
$client = env('REDIS_CLIENT', 'predis');

echo "Redis preflight (client={$client}, host={$host}, port={$port})\n";

if (! class_exists(\Predis\Client::class) && ! extension_loaded('redis')) {
    echo "FAIL: predis package / phpredis extension tidak tersedia.\n";
    exit(1);
}

$socket = @fsockopen($host, $port, $errno, $errstr, 2.0);
if ($socket === false) {
    echo "FAIL: Redis server tidak merespons di {$host}:{$port} ({$errstr})\n";
    exit(1);
}
fclose($socket);

try {
    if ($client === 'phpredis' && extension_loaded('redis')) {
        $redis = new Redis();
        $redis->connect($host, $port, 2.0);
        if ($password && $password !== 'null') {
            $redis->auth($password);
        }
        $pong = $redis->ping();
        if ($pong !== '+PONG' && $pong !== true && $pong !== 'PONG') {
            throw new RuntimeException('Unexpected ping response');
        }
    } else {
        $params = [
            'scheme' => 'tcp',
            'host' => $host,
            'port' => $port,
            'timeout' => 2.0,
        ];
        if ($password && $password !== 'null') {
            $params['password'] = $password;
        }
        $redis = new Predis\Client($params);
        $redis->ping();
    }
} catch (Throwable $e) {
    echo 'FAIL: Redis ping error - '.$e->getMessage()."\n";
    exit(1);
}

echo "OK: Redis siap digunakan.\n";
exit(0);
