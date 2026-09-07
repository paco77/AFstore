<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaFryda extends Model
{
    protected $table = 'ventas_frydas';

    protected $fillable = [
        'user_id',
        'tienda_id',
        'cliente_nombre',
        'total',
        'metodo_pago',
        'notas'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVentaFryda::class, 'venta_fryda_id');
    }
}
