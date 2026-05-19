<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $data = DB::table('product_inventories')->get();
    echo "Found " . $data->count() . " records in product_inventories.\n";
    
    $copied = 0;
    foreach ($data as $row) {
        $existsTienda = DB::table('tiendas')->where('id', $row->company_id)->exists();
        $existsProduct = DB::table('product_almacens')->where('id', $row->product_id)->exists();
        
        if ($existsTienda && $existsProduct) {
            DB::table('product_tiendas')->updateOrInsert(
                [
                    'tienda_id' => $row->company_id,
                    'product_almacen_id' => $row->product_id,
                ],
                [
                    'amount' => $row->amount,
                    'precio' => $row->precio,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]
            );
            $copied++;
        }
    }
    echo "Copied $copied records to product_tiendas.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
