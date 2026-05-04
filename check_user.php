<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Member;

$user = Member::where('username', 'MYSHOP2003')->first();
if ($user) {
    echo "USER FOUND\n";
    echo "USERNAME: " . $user->username . "\n";
    echo "PASSWORD IN DB: " . $user->password . "\n";
    echo "ACTIVE: " . $user->active . "\n";
} else {
    echo "USER NOT FOUND\n";
}
