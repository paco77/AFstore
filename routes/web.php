<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/link-storage', function () {
    Artisan::call('storage:link');
    return 'Storage link created successfully!';
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
    Route::post('/upload-logo', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240', // 10MB limit
        ]);
        
        $file = $request->file('logo');
        $path = public_path('logo.png');
        
        // Try to optimize with GD
        $imageString = file_get_contents($file->getRealPath());
        $image = @imagecreatefromstring($imageString);

        if ($image !== false) {
            $width = imagesx($image);
            $height = imagesy($image);
            
            // Max dimensions for logo
            $maxSize = 800;
            
            if ($width > $maxSize || $height > $maxSize) {
                $ratio = min($maxSize / $width, $maxSize / $height);
                $newWidth = (int) ($width * $ratio);
                $newHeight = (int) ($height * $ratio);
                
                $newImage = imagecreatetruecolor($newWidth, $newHeight);
                
                // Preserve transparency
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
                
                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                
                // Save optimized PNG
                imagepng($newImage, $path, 6);
                imagedestroy($newImage);
            } else {
                // Image is small enough, just save as PNG preserving alpha
                imagealphablending($image, false);
                imagesavealpha($image, true);
                imagepng($image, $path, 6);
            }
            imagedestroy($image);
        } else {
            // Fallback if GD fails
            $file->move(public_path(), 'logo.png');
        }

        return back();
    })->name('upload.logo');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Sales routes
    Route::get('ventas/history', [VentaController::class, 'history'])->name('ventas.history');
    Route::get('ventas/export', [VentaController::class, 'export'])->name('ventas.export');
    Route::get('ventas/search', [VentaController::class, 'search'])->name('ventas.search');
    Route::get('ventas/{venta}/ticket', [VentaController::class, 'ticket'])->name('ventas.ticket');
    Route::resource('ventas', VentaController::class);

    // Inventario Tienda (Only index accessible to Cajero)
    Route::resource('product-tienda', ProductTiendaController::class)->only(['index']);

    // Admin only routes
    Route::middleware([\App\Http\Middleware\CheckAdmin::class])->group(function () {
        Route::get('product-almacen-export', [ProductAlmacenController::class, 'export'])->name('product-almacen.export');
        Route::resource('users', UserController::class);
        Route::resource('product-almacen', ProductAlmacenController::class);
        Route::resource('tiendas', TiendaController::class);
        Route::resource('product-tienda', ProductTiendaController::class)->except(['index']);
        Route::resource('compras', App\Http\Controllers\CompraController::class);
        
        // Cortes routes
        Route::get('cortes/create', [CorteController::class, 'create'])->name('cortes.create');
        Route::resource('cortes', CorteController::class);
    });
});

require __DIR__.'/auth.php';
