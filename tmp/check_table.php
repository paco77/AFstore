<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $tables = DB::select('SHOW TABLES');
    echo "Tables:\n";
    foreach ($tables as $table) {
        echo array_values((array)$table)[0] . "\n";
    }

    echo "\nStructure of product_inventories:\n";
    $columns = DB::select('DESCRIBE product_inventories');
    foreach ($columns as $column) {
        echo $column->Field . " (" . $column->Type . ")\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
