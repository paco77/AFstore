<?php

namespace App\Http\Controllers;

use App\Models\ProductAlmacen;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;

class ProductAlmacenController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductAlmacen::query();
        
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%'.$request->search.'%')
                  ->orWhere('clave', 'like', '%'.$request->search.'%')
                  ->orWhere('tipo', 'like', '%'.$request->search.'%');
            });
        }

        $sortField = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');
        
        if (in_array($sortField, ['clave', 'tipo', 'nombre', 'precio_venta', 'precio_mayoreo'])) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $perPage = $request->input('perPage', 10);
        $products = $query->paginate($perPage)->withQueryString();
        
        return Inertia::render('ProductAlmacen/Index', [
            'products' => $products,
            'filters' => $request->only(['search', 'sort_field', 'sort_direction', 'perPage'])
        ]);
    }

    public function create()
    {
        return Inertia::render('ProductAlmacen/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'nullable|string|max:50',
            'clave' => 'required|string|max:255|unique:product_almacens',
            'imagen' => 'nullable|image|max:2048',
            'precio_venta' => 'required|numeric|min:0',
            'precio_mayoreo' => 'required|numeric|min:0',
        ]);

        $data = $request->except('imagen');

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('productos', 'public');
            $data['imagen'] = $path;
        }

        ProductAlmacen::create($data);

        return redirect()->route('product-almacen.index')->with('success', 'Producto creado exitosamente.');
    }

    public function edit(ProductAlmacen $productAlmacen)
    {
        return Inertia::render('ProductAlmacen/Edit', [
            'product' => $productAlmacen
        ]);
    }

    public function update(Request $request, ProductAlmacen $productAlmacen)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'nullable|string|max:50',
            'clave' => 'required|string|max:255|unique:product_almacens,clave,'.$productAlmacen->id,
            'imagen' => 'nullable|image|max:2048',
            'precio_venta' => 'required|numeric|min:0',
            'precio_mayoreo' => 'required|numeric|min:0',
        ]);

        $data = $request->except('imagen');

        if ($request->hasFile('imagen')) {
            if ($productAlmacen->imagen) {
                Storage::disk('public')->delete($productAlmacen->imagen);
            }
            $path = $request->file('imagen')->store('productos', 'public');
            $data['imagen'] = $path;
        }

        $productAlmacen->update($data);

        return redirect()->route('product-almacen.index')->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(ProductAlmacen $productAlmacen)
    {
        $productAlmacen->delete();
        return redirect()->route('product-almacen.index')->with('success', 'Producto eliminado.');
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'productos_almacen.xlsx');
    }
}
