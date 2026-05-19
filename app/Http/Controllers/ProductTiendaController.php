<?php

namespace App\Http\Controllers;

use App\Models\ProductTienda;
use App\Models\Tienda;
use App\Models\ProductAlmacen;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductTiendaController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductTienda::with(['tienda', 'productAlmacen']);
        
        if ($request->has('search')) {
            $search = $request->search;
            // The original search was on 'tienda' name OR 'productAlmacen' name/clave.
            // The user's snippet implies filtering by a specific 'company_id' which is not available in this general index method.
            // Assuming the intent is to search within productAlmacen for the main inventory list.
            // If a specific company_id filter is needed, it should be passed as a request parameter.
            // For now, I'll adapt the search to filter the main $query based on productAlmacen.
            $query->whereHas('productAlmacen', function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('clave', 'like', "%{$search}%")
                  ->orWhere('tipo', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('perPage', 10);
        $inventory = $query->paginate($perPage)->withQueryString();
        $tiendas = Tienda::all();
        
        // Return JSON if requested implicitly via frontend search inside modal
        if ($request->wantsJson()) {
            if ($request->has('search_products')) {
                $productsQuery = ProductAlmacen::query();
                $sp = $request->search_products;
                if ($sp) {
                    $productsQuery->where('nombre', 'like', "%{$sp}%")
                                  ->orWhere('clave', 'like', "%{$sp}%")
                                  ->orWhere('tipo', 'like', "%{$sp}%");
                }
                return response()->json($productsQuery->take(20)->get());
            }
        }

        return Inertia::render('ProductTienda/Index', [
            'inventory' => $inventory,
            'tiendas' => $tiendas,
            'filters' => $request->only('search', 'perPage')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tienda_id' => 'required|exists:tiendas,id',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:product_almacens,id',
            'products.*.amount' => 'required|integer|min:1',
        ]);

        $tienda_id = $request->tienda_id;

        foreach ($request->products as $item) {
            $productAlmacen = ProductAlmacen::find($item['id']);
            
            $productTienda = ProductTienda::where('company_id', $tienda_id)
                ->where('product_id', $item['id'])
                ->first();

            if ($productTienda) {
                $productTienda->increment('amount', $item['amount']);
            } else {
                ProductTienda::create([
                    'company_id' => $tienda_id,
                    'product_id' => $item['id'],
                    'amount' => $item['amount'],
                    'precio' => $productAlmacen->precio_venta // Default from warehouse
                ]);
            }
        }

        return redirect()->route('product-tienda.index')->with('success', 'Productos asignados a la tienda correctamente.');
    }

    public function update(Request $request, ProductTienda $product_tienda)
    {
        $request->validate([
            'amount' => 'required|integer|min:0',
            'precio' => 'required|numeric|min:0',
        ]);

        $product_tienda->update([
            'amount' => $request->amount,
            'precio' => $request->precio,
        ]);

        return response()->json(['success' => true, 'message' => 'Producto actualizado correctamente.']);
    }

    public function destroy(ProductTienda $product_tienda)
    {
        $product_tienda->delete();
        return redirect()->route('product-tienda.index')->with('success', 'Registro eliminado.');
    }
}
