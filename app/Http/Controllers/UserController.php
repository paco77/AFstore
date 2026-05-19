<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tienda;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('tienda');
        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%');
        }
        $perPage = $request->input('perPage', 10);
        $users = $query->paginate($perPage)->withQueryString();
        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only('search', 'perPage'),
            'tiendas' => Tienda::all()
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'userName' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'rol' => 'required|in:Admin,Cajero',
            'tienda_id' => 'nullable|exists:tiendas,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'userName' => $request->userName,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'tienda_id' => $request->tienda_id,
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => $user
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'userName' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'rol' => 'required|in:Admin,Cajero',
            'tienda_id' => 'nullable|exists:tiendas,id',
        ]);

        $data = $request->only(['name', 'email', 'userName', 'rol', 'tienda_id']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
