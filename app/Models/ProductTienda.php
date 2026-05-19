<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTienda extends Model
{
    use HasFactory;

    protected $table = 'product_inventories';

    protected $fillable = [
        'company_id',
        'product_id',
        'amount',
        'precio',
    ];

    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'company_id');
    }

    public function productAlmacen()
    {
        return $this->belongsTo(ProductAlmacen::class, 'product_id');
    }
}
