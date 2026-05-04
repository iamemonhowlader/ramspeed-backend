<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ids = DB::table('products')->pluck('supplier_id')->unique()->take(5)->toArray();
print_r($ids);

foreach ($ids as $id) {
    echo "ID: $id\n";
    print_r(DB::table('admin_users')->where('id', $id)->first());
    print_r(DB::table('suppliers_info')->where('user_id', $id)->first());
}
