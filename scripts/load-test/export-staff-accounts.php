<?php

/**
 * Export akun staff dari database untuk load test k6 (multi-VU).
 * Password default factory UserSeeder: "password"
 * Akun tetap crocodic3@gmail.com: crocodic123
 *
 * Usage:
 *   php scripts/load-test/export-staff-accounts.php
 *   php scripts/load-test/export-staff-accounts.php --limit=50
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$limit = 50;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--limit=')) {
        $limit = max(1, (int) substr($arg, 8));
    }
}

$defaultPassword = getenv('LOAD_TEST_STAFF_PASSWORD') ?: 'password';
$passwordOverrides = [
    'crocodic3@gmail.com' => 'crocodic123',
    'sanantaui@gmail.com' => getenv('LOAD_TEST_SANANTA_PASSWORD') ?: 'sananta123',
];

$staff = User::query()
    ->select('users.id', 'users.email', 'users.name')
    ->whereHas('role', fn ($q) => $q->where('role', 'staff'))
    ->orderBy('email')
    ->limit($limit)
    ->get();

if ($staff->isEmpty()) {
    fwrite(STDERR, "ERROR: Tidak ada user staff di database.\n");
    exit(1);
}

$accounts = $staff->map(function (User $user) use ($defaultPassword, $passwordOverrides) {
    $email = strtolower((string) $user->email);

    return [
        'email' => $email,
        'password' => $passwordOverrides[$email] ?? $defaultPassword,
        'name' => $user->name,
    ];
})->values()->all();

$outDir = __DIR__ . '/k6/data';
if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$outFile = $outDir . '/staff-accounts.json';
$payload = [
    'generated_at' => now()->toIso8601String(),
    'count' => count($accounts),
    'default_password' => $defaultPassword,
    'accounts' => $accounts,
];

file_put_contents(
    $outFile,
    json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
);

echo "Exported {$payload['count']} staff account(s) to:\n  {$outFile}\n";
echo "Default password: {$defaultPassword}\n";
echo "First: {$accounts[0]['email']}\n";
if (count($accounts) > 1) {
    echo "Last:  {$accounts[count($accounts) - 1]['email']}\n";
}
