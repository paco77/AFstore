<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVentaFryda extends Model
{
    protected $table = 'detalles_ventas_frydas';

    protected $fillable = [
        'venta_fryda_id',
        'tipo_producto',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'subtotal'
    ];

    public function ventaFryda()
    {
        return $this->belongsTo(VentaFryda::class, 'venta_fryda_id');
    }
}
