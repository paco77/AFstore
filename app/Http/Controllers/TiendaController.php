<?php

namespace App\Http\Controllers;

use App\Models\Tienda;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TiendaController extends Controller
{
    public function index(Request $request)
    {
        $query = Tienda::query();
        if ($request->has('search')) {
            $query->where('nombre', 'like', '%'.$request->search.'%');
        }
        $perPage = $request->input('perPage', 10);
        $tiendas = $query->paginate($perPage)->withQueryString();
        
        return Inertia::render('Tiendas/Index', [
            'tiendas' => $tiendas,
            'filters' => $request->only('search', 'perPage')
        ]);
    }

    public function create()
    {
        return Inertia::render('Tiendas/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'userName' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
        ]);

        Tienda::create($request->all());

        return redirect()->route('tiendas.index')->with('success', 'Tienda creada exitosamente.');
    }

    public function edit(Tienda $tienda)
    {
        return Inertia::render('Tiendas/Edit', [
            'tienda' => $tienda
        ]);
    }

    public function update(Request $request, Tienda $tienda)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'userName' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
        ]);

        $tienda->update($request->all());

        return redirect()->route('tiendas.index')->with('success', 'Tienda actualizada exitosamente.');
    }

    public function destroy(Tienda $tienda)
    {
        $tienda->delete();
        return redirect()->route('tiendas.index')->with('success', 'Tienda eliminada.');
    }
}
