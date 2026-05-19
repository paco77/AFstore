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
        Schema::table('cortes', function (Blueprint $table) {
            if (!Schema::hasColumn('cortes', 'tienda_id')) {
                $table->foreignId('tienda_id')->nullable()->after('user_id')->constrained('tiendas')->onDelete('cascade');
            }
            if (!Schema::hasColumn('cortes', 'total_efectivo')) {
                $table->decimal('total_efectivo', 10, 2)->default(0)->after('tienda_id');
            }
            if (!Schema::hasColumn('cortes', 'total_tarjeta')) {
                $table->decimal('total_tarjeta', 10, 2)->default(0)->after('total_efectivo');
            }
            if (!Schema::hasColumn('cortes', 'total_transferencia')) {
                $table->decimal('total_transferencia', 10, 2)->default(0)->after('total_tarjeta');
            }
            if (!Schema::hasColumn('cortes', 'total_global')) {
                $table->decimal('total_global', 10, 2)->default(0)->after('total_transferencia');
            }
            if (!Schema::hasColumn('cortes', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('total_global');
            }
            if (!Schema::hasColumn('cortes', 'fecha_inicio')) {
                $table->timestamp('fecha_inicio')->nullable()->after('observaciones');
            }
            if (!Schema::hasColumn('cortes', 'fecha_fin')) {
                $table->timestamp('fecha_fin')->nullable()->after('fecha_inicio');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cortes', function (Blueprint $table) {
            //
        });
    }
};
