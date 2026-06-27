<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$email = 'paltaobobby30@gmail.com';

$user = User::where('email', $email)->first();

if (!$user) {
    echo "User with email '{$email}' not found.\n";
    exit(1);
}

$user->update(['role' => 'ADMINISTRATOR']);

echo "Successfully made '{$user->name}' ({$email}) an ADMINISTRATOR.\n";