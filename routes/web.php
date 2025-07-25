<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentoClienteController;
use App\Http\Controllers\PromotoraController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home redirige a login
Route::get('/', fn() => redirect('/login'));

// Rutas protegidas (autenticado y verificado)
Route::middleware(['auth','verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Panel y vistas de Promotora
    |--------------------------------------------------------------------------
    | URL: /promotora, /promotora/venta, /promotora/cartera, /promotora/objetivo
    | Nombres: promotora.index, promotora.venta, promotora.cartera, promotora.objetivo
    */
    Route::prefix('promotora')
         ->name('promotora.')
         ->controller(PromotoraController::class)
         ->group(function () {
             Route::get('/',         'index')   ->name('index');
             Route::get('venta',     'venta')   ->name('venta');
             Route::get('cartera',   'cartera') ->name('cartera');
             Route::get('objetivo',  'objetivo')->name('objetivo');
             Route::get('solicitar_venta', 'solicitar_venta')->name('solicitar_venta');
             Route::get('ingresar_cliente', 'ingresar_cliente')->name('ingresar_cliente');
             Route::get('cliente_historial', 'cliente_historial')->name('cliente_historial');
         });

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
    Route::get('/nuevoCliente',        [ClienteController::class, 'create'])
         ->name('nuevoCliente');
    Route::post('/nuevoCliente/store', [ClienteController::class, 'store'])
         ->name('client.store');

    // Documentos de cliente (RESTful)
    Route::resource('documentos', DocumentoClienteController::class);

    // Páginas Blade adicionales
    Route::get('/nuevoCredito',        fn() => view('solicitud'))
         ->name('solicitud');
    Route::get('/recreditoCliente',    fn() => view('recreditoClientes'))
         ->name('recreditoClientes');
    Route::get('/reportes',            fn() => view('reportes'))
         ->name('reportes');
    Route::get('/panelRevision',       fn() => view('PanelRevision'))
         ->name('panelRevision');
    Route::get('/panelAdministrativo', fn() => view('AdminDashboard'))
         ->name('AdminDashboard');

    // Registro de empleados
    Route::get('/registrarEmpleado',  fn() => view('Users.RegisterUserForm'))
         ->name('register.form');
    Route::post('/registrarEmpleado', [UserController::class, 'store'])
         ->name('register.user');
});

// Rutas de autenticación (login, register, etc.)
require __DIR__.'/auth.php';
