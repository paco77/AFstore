<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAlmacen extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'clave',
        'imagen',
        'precio_venta',
        'precio_mayoreo',
        'tipo',
    ];
}
