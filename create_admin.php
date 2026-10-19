<?php

use App\Models\User;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

User::ensureDefaultAdminAccounts();

echo 'Admin accounts seeded. System administrator: '.config('admin.system.email').PHP_EOL;