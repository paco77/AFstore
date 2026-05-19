<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = [
        'user_id',
        'tienda_id',
        'cliente_nombre',
        'total',
        'tipo_venta',
        'metodo_pago'
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
        return $this->hasMany(VentaDetalle::class);
    }
}
