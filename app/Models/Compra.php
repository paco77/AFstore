<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tienda;
use App\Models\User;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = [
        'concepto',
        'tipo_gasto',
        'monto',
        'fecha_compra',
        'tienda_id',
        'user_id'
    ];

    public function tienda()
    {
        return $this->belongsTo(Tienda::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
