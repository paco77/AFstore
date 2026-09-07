<?php

namespace App\Http\Controllers;

use App\Models\VentaFryda;
use App\Models\DetalleVentaFryda;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FridaVentaController extends Controller
{
    /**
     * Display the POS register page for Fridas.
     */
    public function index()
    {
        return Inertia::render('Fridas/Index', [
            'categorias' => [
                'Ropa',
                'Accesorios',
                'Ropa interior',
                'Sticker',
            ]
        ]);
    }

    /**
     * Store a newly created Fridas sale in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.tipo_producto' => 'required|string',
            'items.*.descripcion' => 'nullable|string',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'cliente_nombre' => 'nullable|string|max:255',
            'metodo_pago' => 'required|string',
            'notas' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $tienda_id = $user->tienda_id;

        return DB::transaction(function () use ($request, $user, $tienda_id) {
            $total = collect($request->items)->sum(function ($item) {
                return $item['cantidad'] * $item['precio_unitario'];
            });

            $venta = VentaFryda::create([
                'user_id' => $user->id,
                'tienda_id' => $tienda_id,
                'cliente_nombre' => $request->cliente_nombre,
                'total' => $total,
                'metodo_pago' => $request->metodo_pago,
                'notas' => $request->notas,
            ]);

            foreach ($request->items as $item) {
                $subtotal = $item['cantidad'] * $item['precio_unitario'];

                DetalleVentaFryda::create([
                    'venta_fryda_id' => $venta->id,
                    'tipo_producto' => $item['tipo_producto'],
                    'descripcion' => $item['descripcion'] ?? null,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $subtotal,
                ]);
            }

            return redirect()->route('fridas.index')->with([
                'success' => 'Venta de Fridas registrada con éxito.',
                'ticket' => [
                    'id' => $venta->id,
                    'pago' => $request->input('pago_recibido', 0),
                    'cambio' => $request->input('cambio_entregado', 0)
                ]
            ]);
        });
    }

    /**
     * Display history of Fridas sales with filtering and metrics.
     */
    public function history(Request $request)
    {
        $query = VentaFryda::with(['user', 'tienda', 'detalles'])->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('cliente_nombre', 'like', "%{$search}%")
                  ->orWhere('metodo_pago', 'like', "%{$search}%")
                  ->orWhereHas('detalles', function ($dq) use ($search) {
                      $dq->where('tipo_producto', 'like', "%{$search}%")
                         ->orWhere('descripcion', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('tipo_producto')) {
            $tipo = $request->tipo_producto;
            $query->whereHas('detalles', function ($dq) use ($tipo) {
                $dq->where('tipo_producto', $tipo);
            });
        }

        if ($request->filled('metodo_pago')) {
            $query->where('metodo_pago', $request->metodo_pago);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Metrics calculation based on filtered query or overall
        $totalSalesCount = (clone $query)->count();
        $totalRevenue = (clone $query)->sum('total');

        // Sales breakdown by category
        $categoryBreakdown = DetalleVentaFryda::whereIn('venta_fryda_id', (clone $query)->pluck('id'))
            ->select('tipo_producto', DB::raw('SUM(subtotal) as total_monto'), DB::raw('SUM(cantidad) as total_items'))
            ->groupBy('tipo_producto')
            ->get();

        $perPage = $request->input('perPage', 15);
        $ventas = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Fridas/History', [
            'ventas' => $ventas,
            'metrics' => [
                'total_count' => $totalSalesCount,
                'total_revenue' => $totalRevenue,
                'breakdown' => $categoryBreakdown,
            ],
            'filters' => $request->only(['search', 'tipo_producto', 'metodo_pago', 'date_from', 'date_to', 'perPage']),
            'categorias' => ['Ropa', 'Accesorios', 'Ropa interior', 'Sticker'],
        ]);
    }

    /**
     * Show single sales record details (JSON).
     */
    public function show(VentaFryda $frida)
    {
        $frida->load(['user', 'tienda', 'detalles']);
        return response()->json($frida);
    }

    /**
     * Ticket response for printing receipt.
     */
    public function ticket(Request $request, VentaFryda $frida)
    {
        $frida->load(['user', 'tienda', 'detalles']);
        $pago_recibido = $request->query('pago', 0);
        $cambio_entregado = $request->query('cambio', 0);

        return response()->json([
            'venta' => $frida,
            'pago_recibido' => $pago_recibido,
            'cambio_entregado' => $cambio_entregado,
        ]);
    }

    /**
     * Remove the specified Fridas sale from storage.
     */
    public function destroy(VentaFryda $frida)
    {
        if (auth()->user()->rol !== 'Admin') {
            return back()->with('error', 'No tienes permiso para eliminar registros de Fridas.');
        }

        $frida->detalles()->delete();
        $frida->delete();

        return back()->with('success', 'Registro de venta Fridas eliminado correctamente.');
    }
}
