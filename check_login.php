<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'admin@gmail.com')->first();
if (! $user) {
    echo "User not found\n";
    exit;
}

echo "User found: {$user->email}\n";
$hashCheck = Hash::check('Admin', $user->password);
echo "Password 'Admin' check: ".($hashCheck ? 'MATCH' : 'FAIL')."\n";

$attempt = Auth::attempt(['email' => 'admin@gmail.com', 'password' => 'Admin']);
echo 'Auth::attempt: '.($attempt ? 'SUCCESS' : 'FAIL')."\n";
