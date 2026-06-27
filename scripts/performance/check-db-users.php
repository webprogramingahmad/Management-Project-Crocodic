<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $count = App\Models\User::count();
    echo "users_count={$count}\n";

    $emails = App\Models\User::query()->orderBy('email')->pluck('email');
    foreach ($emails as $email) {
        echo "user: {$email}\n";
    }
} catch (Throwable $e) {
    echo 'error: ' . $e->getMessage() . "\n";
    exit(1);
}
