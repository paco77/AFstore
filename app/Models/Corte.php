<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Corte extends Model
{
    protected $fillable = [
        'user_id',
        'tienda_id',
        'company_id',
        'total_efectivo',
        'total_tarjeta',
        'total_transferencia',
        'total_global',
        'observaciones',
        'fecha_inicio',
        'fecha_fin'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}
