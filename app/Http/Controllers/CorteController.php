<?php

namespace App\Http\Controllers;

use App\Models\Corte;
use App\Models\Venta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class CorteController extends Controller
{
    public function index()
    {
        $query = Corte::with(['user', 'tienda'])->orderBy('created_at', 'desc');

        if (auth()->user()->rol === 'Cajero') {
            $query->where('tienda_id', auth()->user()->tienda_id)
                  ->where('user_id', auth()->user()->id);
        }

        $perPage = request('perPage', 10);
        return Inertia::render('Ventas/CortesIndex', [
            'cortes' => $query->paginate($perPage)->withQueryString(),
            'filters' => ['perPage' => $perPage]
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        
        // Ventas del usuario actual que NO tienen corte_id
        $ventasPendientes = Venta::where('user_id', $user->id)
            ->whereNull('corte_id')
            ->get();

        $totales = [
            'efectivo' => $ventasPendientes->where('metodo_pago', 'EFECTIVO')->sum('total'),
            'tarjeta' => $ventasPendientes->where('metodo_pago', 'TARJETA')->sum('total'),
            'transferencia' => $ventasPendientes->where('metodo_pago', 'TRANSFERENCIA')->sum('total'),
        ];
        
        $totalGlobal = array_sum($totales);

        return Inertia::render('Ventas/Corte', [
            'totales' => $totales,
            'total_global' => $totalGlobal,
            'ventas_count' => $ventasPendientes->count(),
            'fecha_inicio' => $ventasPendientes->min('created_at'),
            'fecha_fin' => now()
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $ventasPendientes = Venta::where('user_id', $user->id)
            ->whereNull('corte_id')
            ->get();

        if ($ventasPendientes->isEmpty()) {
            return back()->with('error', 'No hay ventas pendientes para realizar el corte.');
        }

        $totales = [
            'efectivo' => $ventasPendientes->where('metodo_pago', 'EFECTIVO')->sum('total'),
            'tarjeta' => $ventasPendientes->where('metodo_pago', 'TARJETA')->sum('total'),
            'transferencia' => $ventasPendientes->where('metodo_pago', 'TRANSFERENCIA')->sum('total'),
        ];

        DB::transaction(function () use ($user, $totales, $ventasPendientes, $request) {
            $corte = Corte::create([
                'user_id' => $user->id,
                'tienda_id' => $user->tienda_id,
                'total_efectivo' => $totales['efectivo'],
                'total_tarjeta' => $totales['tarjeta'],
                'total_transferencia' => $totales['transferencia'],
                'total_global' => array_sum($totales),
                'observaciones' => $request->observaciones,
                'fecha_inicio' => $ventasPendientes->min('created_at'),
                'fecha_fin' => now(),
            ]);

            Venta::whereIn('id', $ventasPendientes->pluck('id'))->update(['corte_id' => $corte->id]);
        });

        return redirect()->route('cortes.index')->with('success', 'Corte de caja realizado correctamente.');
    }

    public function show(Corte $corte)
    {
        $corte->load(['user', 'tienda', 'ventas.detalles.productAlmacen']);
        return Inertia::render('Ventas/CorteDetail', [
            'corte' => $corte
        ]);
    }
}
