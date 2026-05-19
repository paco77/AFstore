<?php

namespace App\Exports;

use App\Models\ProductAlmacen;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return ProductAlmacen::orderBy('id', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Clave',
            'Nombre',
            'Tipo',
            'Precio Venta',
        ];
    }

    public function map($product): array
    {
        $precioFinal = $product->precio_venta * 1.05;

        return [
            $product->clave,
            $product->nombre,
            $product->tipo ? $product->tipo : 'N/A',
            number_format($precioFinal, 2, '.', ''),
        ];
    }
}
