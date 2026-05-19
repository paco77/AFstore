<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    if (Schema::hasTable('product_inventories')) {
        $columns = Schema::getColumnListing('product_inventories');
        echo "Columns in product_inventories: " . implode(', ', $columns) . "\n";
        
        $sample = DB::table('product_inventories')->first();
        if ($sample) {
            echo "Sample data:\n";
            print_r($sample);
        } else {
            echo "No data in product_inventories.\n";
        }
    } else {
        echo "Table product_inventories does not exist.\n";
        $tables = DB::select('SHOW TABLES');
        echo "Available tables:\n";
        foreach ($tables as $table) {
            echo array_values((array)$table)[0] . "\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
