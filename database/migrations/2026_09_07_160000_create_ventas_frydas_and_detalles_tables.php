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
        Schema::create('ventas_frydas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('tienda_id')->nullable()->constrained('tiendas')->onDelete('set null');
            $table->string('cliente_nombre')->nullable();
            $table->decimal('total', 10, 2);
            $table->string('metodo_pago')->default('EFECTIVO');
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        Schema::create('detalles_ventas_frydas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_fryda_id')->constrained('ventas_frydas')->onDelete('cascade');
            $table->string('tipo_producto'); // Ropa, Accesorios, Ropa interior, Sticker
            $table->string('descripcion')->nullable();
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_ventas_frydas');
        Schema::dropIfExists('ventas_frydas');
    }
};
