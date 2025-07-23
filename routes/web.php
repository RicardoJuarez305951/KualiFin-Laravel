<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentoClienteController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home redirige a login
Route::get('/', fn() => redirect('/login'));

// Rutas protegidas (autenticado y verificado)
Route::middleware(['auth','verified'])->group(function () {
    // Dashboard (usa Blade, no Inertia)
    Route::get('/dashboard', fn() => view('dashboard'))
         ->name('dashboard');

    // Perfil de usuario
    Route::get('/profile',   [ProfileController::class, 'edit'])
         ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
         ->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])
         ->name('profile.destroy');

    // Clientes
    Route::get('/nuevoCliente',           [ClienteController::class, 'create'])
         ->name('nuevoCliente');
    Route::post('/nuevoCliente/store',    [ClienteController::class, 'store'])
         ->name('client.store');

    // Documentos de cliente (RESTful, ajusta si necesitas solo web)
    Route::resource('documentos', DocumentoClienteController::class);

    // Páginas Blade (AJUSTA los nombres de las vistas Blade)
    Route::get('/nuevoCredito',         fn() => view('solicitud'))
         ->name('solicitud');
    Route::get('/recreditoCliente',     fn() => view('recreditoClientes'))
         ->name('recreditoClientes');
    Route::get('/reportes',             fn() => view('reportes'))
         ->name('reportes');
    Route::get('/panelRevision',        fn() => view('PanelRevision'))
         ->name('panelRevision');
    Route::get('/panelAdministrativo',  fn() => view('AdminDashboard'))
         ->name('AdminDashboard');

    // Registro de empleados
    Route::get('/registrarEmpleado',    fn() => view('Users.RegisterUserForm'))
         ->name('register.form');
    Route::post('/registrarEmpleado',   [UserController::class, 'store'])
         ->name('register.user');
});

// Rutas de autenticación (login, register, etc.)
// require __DIR__.'/auth.php';
