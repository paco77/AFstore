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
        Schema::dropIfExists('product_tiendas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('product_tiendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tienda_id');
            $table->foreignId('product_almacen_id');
            $table->integer('amount');
            $table->decimal('precio', 10, 2);
            $table->timestamps();
        });
    }
};
