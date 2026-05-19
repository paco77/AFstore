<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_tiendas', function (Blueprint $table) {
            $table->renameColumn('cantidad', 'amount');
            $table->renameColumn('precio_venta', 'precio');
        });
    }

    public function down(): void
    {
        Schema::table('product_tiendas', function (Blueprint $table) {
            $table->renameColumn('amount', 'cantidad');
            $table->renameColumn('precio', 'precio_venta');
        });
    }
};
