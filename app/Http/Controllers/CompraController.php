<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use Illuminate\Http\Request;

use App\Models\Tienda;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        $query = Compra::with(['tienda', 'user']);

        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('concepto', 'like', '%' . $request->search . '%')
                  ->orWhere('tipo_gasto', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('fecha_inicio') && $request->fecha_inicio) {
            $query->whereDate('fecha_compra', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin') && $request->fecha_fin) {
            $query->whereDate('fecha_compra', '<=', $request->fecha_fin);
        }

        // Clonar la query para sacar la sumatoria total del filtro actual
        $totalGastado = (clone $query)->sum('monto');

        $compras = $query->orderBy('fecha_compra', 'desc')->paginate(10)->withQueryString();
        $tiendas = Tienda::all();

        return Inertia::render('Compras/Index', [
            'compras' => $compras,
            'tiendas' => $tiendas,
            'totalGastado' => $totalGastado,
            'filters' => $request->only(['search', 'fecha_inicio', 'fecha_fin']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'concepto' => 'required|string|max:255',
            'tipo_gasto' => 'required|string|max:100',
            'monto' => 'required|numeric|min:0.01',
            'fecha_compra' => 'required|date',
            'tienda_id' => 'nullable|exists:tiendas,id',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();

        Compra::create($data);

        return redirect()->route('compras.index')->with('success', 'Gasto registrado correctamente.');
    }

    public function update(Request $request, Compra $compra)
    {
        $request->validate([
            'concepto' => 'required|string|max:255',
            'tipo_gasto' => 'required|string|max:100',
            'monto' => 'required|numeric|min:0.01',
            'fecha_compra' => 'required|date',
            'tienda_id' => 'nullable|exists:tiendas,id',
        ]);

        $compra->update($request->all());

        return redirect()->route('compras.index')->with('success', 'Gasto actualizado correctamente.');
    }

    public function destroy(Compra $compra)
    {
        $compra->delete();
        return redirect()->route('compras.index')->with('success', 'Gasto eliminado.');
    }
}
