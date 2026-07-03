<?php

use Illuminate\Support\Facades\Route;

// La raíz manda directo a la app: dashboard si estás logueado, login si no.
Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    // Placeholders navegables hasta que cada módulo tenga su pantalla real (Fases 1–6).
    Route::inertia('vender', 'placeholder', ['title' => 'Vender', 'phase' => 3])->name('vender');
    Route::inertia('productos', 'placeholder', ['title' => 'Productos', 'phase' => 1])->name('productos');
    Route::inertia('stock', 'placeholder', ['title' => 'Stock', 'phase' => 2])->name('stock');
    Route::inertia('clientes', 'placeholder', ['title' => 'Clientes', 'phase' => 4])->name('clientes');
    Route::inertia('reportes', 'placeholder', ['title' => 'Reportes', 'phase' => 5])->name('reportes');
    Route::inertia('configuracion', 'placeholder', ['title' => 'Configuración', 'phase' => 6])->name('configuracion');
});

require __DIR__.'/settings.php';
