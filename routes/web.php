<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentoClienteController;
use App\Http\Controllers\SolicitudCreditoController;
use App\Http\Controllers\VistaMobileController;  // <-- Solo este controlador
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    // if (Auth::check() && Auth::user()->rol === 'promotor') {
    if (Auth::check() && in_array(Auth::user()->rol, ['promotor', 'supervisor', 'ejecutivo'])) {
        return redirect()->route('mobile.index');
    }
    return redirect()->route('login');
});

Route::middleware(['auth','verified'])->group(function () {

    // Rutas de la vista móvil (reemplaza a “promotor”)
    Route::prefix('mobile')
         ->name('mobile.')
         ->controller(VistaMobileController::class)
         ->group(function () {
             Route::get('/',                 'index')             ->name('index');
             Route::get('venta',             'venta')             ->name('venta');
             Route::get('cartera',           'cartera')           ->name('cartera');
             Route::get('objetivo',          'objetivo')          ->name('objetivo');
             Route::get('solicitar-venta',   'solicitar_venta')   ->name('solicitar_venta');
             Route::get('ingresar-cliente',  'ingresar_cliente')  ->name('ingresar_cliente');
             Route::get('cliente-historial', 'cliente_historial') ->name('cliente_historial');
             //SUPERVISOR
             Route::get('vigente',  'cartera_vigente') ->name('vigente');
             Route::get('vencida',  'cartera_vencida') ->name('vencida');
             Route::get('inactiva',  'cartera_inactiva') ->name('inactiva');
             Route::get('historial_promotora',  'cartera_historial_promotora') ->name('historial_promotora');
         });

    // Dashboard
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    // Perfil de usuario
    Route::get('/profile',   [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');

    // Clientes
    Route::get('/nuevoCliente',        [ClienteController::class, 'create'])->name('nuevoCliente');
    Route::post('/nuevoCliente/store', [ClienteController::class, 'store'])->name('client.store');

    // Documentos de cliente
    Route::resource('documentos', DocumentoClienteController::class);

    // Wizard de Solicitud de Crédito
    Route::prefix('solicitud-credito')
         ->name('credito.')
         ->controller(SolicitudCreditoController::class)
         ->group(function () {
             Route::get('/',     'create')->name('create');
             Route::post('/',    'store') ->name('store');
             Route::get('/back', 'back')  ->name('back');
         });

    // Otras páginas Blade
    Route::get('/recreditoCliente', fn() => view('recreditoClientes'))->name('recreditoClientes');
    Route::get('/reportes',         fn() => view('reportes'))         ->name('reportes');
    Route::get('/panelRevision',    fn() => view('PanelRevision'))     ->name('panelRevision');
    Route::get('/preAprobacion',    fn() => view('preaprobacion.index'))->name('preAprobacion');

    // Panel Administrativo
    Route::prefix('admin')
         ->name('admin.')
         ->group(function () {
             Route::get('/',                  fn() => view('admin.index'))->name('index');
             Route::get('/empleados/create',  fn() => view('admin.create-user'))->name('empleados.create');
             Route::post('/empleados/create', [UserController::class, 'store'])->name('empleados.store');
         });
});

require __DIR__.'/auth.php';
