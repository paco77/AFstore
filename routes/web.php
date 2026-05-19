<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    if (auth()->user()->email === 'gabo@mail.com') {
        return redirect()->route('product-tienda.index');
    }
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductAlmacenController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\ProductTiendaController;
use App\Http\Controllers\VentaController; // Added this use statement
use App\Http\Controllers\CorteController; // Added this use statement

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // POS routes
    Route::get('product-almacen-export', [ProductAlmacenController::class, 'export'])->name('product-almacen.export');
    Route::resource('users', UserController::class);
    Route::resource('product-almacen', ProductAlmacenController::class);
    Route::resource('tiendas', TiendaController::class);
    Route::resource('product-tienda', ProductTiendaController::class);
    Route::resource('compras', App\Http\Controllers\CompraController::class);
    
    // Sales routes
    Route::get('ventas/history', [VentaController::class, 'history'])->name('ventas.history');
    Route::get('ventas/export', [VentaController::class, 'export'])->name('ventas.export');
    Route::get('ventas/search', [VentaController::class, 'search'])->name('ventas.search');
    Route::get('ventas/{venta}/ticket', [VentaController::class, 'ticket'])->name('ventas.ticket');
    Route::resource('ventas', VentaController::class);

    // Cortes routes
    Route::get('cortes/create', [CorteController::class, 'create'])->name('cortes.create');
    Route::resource('cortes', CorteController::class);
});

require __DIR__.'/auth.php';
