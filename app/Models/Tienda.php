<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tienda extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'userName',
        'direccion',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
