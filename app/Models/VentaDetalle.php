<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    protected $fillable = [
        'venta_id',
        'product_almacen_id',
        'cantidad',
        'precio_unitario',
        'descuento_porcentaje',
        'subtotal'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function productAlmacen()
    {
        return $this->belongsTo(ProductAlmacen::class);
    }
}
