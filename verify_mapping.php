<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $product_inventory = DB::table('product_inventories')->first();
    echo "Product Inventory Sample:\n";
    print_r($product_inventory);

    if ($product_inventory) {
        $tienda = DB::table('tiendas')->where('id', $product_inventory->company_id)->first();
        echo "\nMatching Tienda (company_id: {$product_inventory->company_id}):\n";
        print_r($tienda);

        $product = DB::table('product_almacens')->where('id', $product_inventory->product_id)->first();
        echo "\nMatching Product (product_id: {$product_inventory->product_id}):\n";
        print_r($product);
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
