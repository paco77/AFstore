<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\ProductTienda;
use App\Models\ProductAlmacen;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VentaController extends Controller
{
    public function index()
    {
        return Inertia::render('Ventas/Index');
    }

    public function search(Request $request)
    {
        $user = Auth::user();
        $tienda_id = $user->tienda_id;

        if (!$tienda_id) {
            return response()->json(['error' => 'Usuario no tiene tienda asignada.'], 403);
        }

        $query = ProductTienda::with('productAlmacen')
            ->where('company_id', $tienda_id)
            ->whereHas('productAlmacen'); // Ensures we only get ones with a warehouse product

        if (!empty($request->search)) {
            $search = $request->search;
            $query->whereHas('productAlmacen', function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('clave', 'like', "%{$search}%")
                  ->orWhere('tipo', 'like', "%{$search}%");
            });
        }
        
        $products = $query->take(100)->get()
            ->map(function($pt) {
                return [
                    'id' => $pt->productAlmacen->id,
                    'nombre' => $pt->productAlmacen->nombre,
                    'clave' => $pt->productAlmacen->clave,
                    'tipo' => $pt->productAlmacen->tipo,
                    'precio_venta' => $pt->precio, // Use store-specific price
                    'stock' => $pt->amount, // Use new 'amount' column
                    'imagen' => $pt->productAlmacen->imagen,
                ];
            });

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:product_almacens,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'required|numeric|min:0',
            'productos.*.descuento_porcentaje' => 'required|numeric|min:0|max:100',
            'tipo_venta' => 'required|string',
            'metodo_pago' => 'required|string',
            'cliente_nombre' => 'nullable|string',
        ]);

        $user = Auth::user();
        $tienda_id = $user->tienda_id;

        if (!$tienda_id) {
            return back()->withErrors(['error' => 'Usuario no tiene tienda asignada.']);
        }

        return DB::transaction(function () use ($request, $user, $tienda_id) {
            $total = collect($request->productos)->sum(function($p) {
                $subtotal = $p['cantidad'] * $p['precio_unitario'];
                $descuento = $subtotal * ($p['descuento_porcentaje'] / 100);
                return $subtotal - $descuento;
            });

            $venta = Venta::create([
                'user_id' => $user->id,
                'tienda_id' => $tienda_id,
                'cliente_nombre' => $request->cliente_nombre,
                'total' => $total,
                'tipo_venta' => $request->tipo_venta,
                'metodo_pago' => $request->metodo_pago,
            ]);

            foreach ($request->productos as $p) {
                $subtotal = $p['cantidad'] * $p['precio_unitario'];
                $descuento = $subtotal * ($p['descuento_porcentaje'] / 100);
                $finalSubtotal = $subtotal - $descuento;

                VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'product_almacen_id' => $p['id'],
                    'cantidad' => $p['cantidad'],
                    'precio_unitario' => $p['precio_unitario'],
                    'descuento_porcentaje' => $p['descuento_porcentaje'],
                    'subtotal' => $finalSubtotal,
                ]);

                // Update stock
                $productTienda = ProductTienda::where('company_id', $tienda_id)
                    ->where('product_id', $p['id'])
                    ->first();
                
                if ($productTienda) {
                    $productTienda->decrement('amount', $p['cantidad']);
                }
            }

            return redirect()->route('ventas.index')->with([
                'success' => 'Venta realizada con éxito.',
                'ticket' => [
                    'id' => $venta->id,
                    'pago' => $request->input('pago_recibido', 0),
                    'cambio' => $request->input('cambio_entregado', 0)
                ]
            ]);
        });
    }

    public function history(Request $request)
    {
        $query = Venta::with(['user', 'tienda'])->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('cliente_nombre', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Si es cajero, solo ve sus ventas de su tienda
        if (auth()->user()->rol === 'Cajero') {
            $query->where('tienda_id', auth()->user()->tienda_id)
                  ->where('user_id', auth()->user()->id);
        }

        $perPage = $request->input('perPage', 20);
        return Inertia::render('Ventas/History', [
            'ventas' => $query->paginate($perPage)->withQueryString(),
            'filters' => $request->only(['search', 'date_from', 'date_to', 'perPage'])
        ]);
    }

    public function ticket(Request $request, Venta $venta)
    {
        $venta->load(['user', 'tienda', 'detalles.productAlmacen']);
        $pago_recibido = $request->query('pago', 0);
        $cambio_entregado = $request->query('cambio', 0);

        return view('ventas.ticket', compact('venta', 'pago_recibido', 'cambio_entregado'));
    }

    public function export(Request $request)
    {
        $query = Venta::with(['user', 'tienda', 'detalles.productAlmacen']);

        // Aplicar mismos filtros que en history
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if (auth()->user()->rol === 'Cajero') {
            $query->where('tienda_id', auth()->user()->tienda_id)
                  ->where('user_id', auth()->user()->id);
        }

        $ventas = $query->get();

        $callback = function() use ($ventas) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Fecha', 'Cliente', 'Usuario', 'Tienda', 'Total', 'Metodo Pago', 'Tipo']);

            foreach ($ventas as $venta) {
                fputcsv($file, [
                    $venta->id,
                    $venta->created_at->format('Y-m-d H:i'),
                    $venta->cliente_nombre,
                    $venta->user->name ?? 'N/A',
                    $venta->tienda->nombre ?? 'N/A',
                    $venta->total,
                    $venta->metodo_pago,
                    $venta->tipo_venta
                ]);
            }
            fclose($file);
        };

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=ventas_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return response()->stream($callback, 200, $headers);
    }
    public function show(Venta $venta)
    {
        $venta->load(['user', 'tienda', 'detalles.productAlmacen']);
        return response()->json($venta);
    }

    public function destroy(Venta $venta)
    {
        if (auth()->user()->rol !== 'Admin') {
            return back()->with('error', 'No tienes permiso para eliminar ventas.');
        }

        DB::transaction(function () use ($venta) {
            // Restaurar stock
            foreach ($venta->detalles as $detalle) {
                $productTienda = ProductTienda::where('company_id', $venta->tienda_id)
                    ->where('product_id', $detalle->product_almacen_id)
                    ->first();
                
                if ($productTienda) {
                    $productTienda->increment('amount', $detalle->cantidad);
                }
            }

            // Eliminar detalles y venta
            $venta->detalles()->delete();
            $venta->delete();
        });

        return back()->with('success', 'Venta eliminada y stock restaurado.');
    }
}
