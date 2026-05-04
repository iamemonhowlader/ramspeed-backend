<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Member;

$user = Member::where('username', 'MYSHOP2003')->first();
if ($user) {
    echo "USER FOUND\n";
    echo "USERNAME: [" . $user->username . "] (Length: " . strlen($user->username) . ")\n";
    echo "PASSWORD: [" . $user->password . "] (Length: " . strlen($user->password) . ")\n";
    
    $input_pass = "MYSHOP200374";
    echo "INPUT PASS: [" . $input_pass . "] (Length: " . strlen($input_pass) . ")\n";
    
    if ($user->password === $input_pass) {
        echo "MATCH: YES\n";
    } else {
        echo "MATCH: NO\n";
    }
} else {
    echo "USER NOT FOUND\n";
}
