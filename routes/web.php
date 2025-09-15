<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentoClienteController;
use App\Http\Controllers\SolicitudCreditoController;
use App\Http\Controllers\PromotorController;
use App\Http\Controllers\EjecutivoController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\PagoRealController;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ShareRole;
use App\Http\Controllers\ExcelController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    // if (Auth::check() && Auth::user()->hasRole('promotor')) {
    if (Auth::check() && Auth::user()->hasAnyRole(['promotor', 'supervisor', 'ejecutivo'])) {
        return redirect()->route('mobile.index');
    }
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/consulta-base-datos-historica', [ExcelController::class, 'index'])
        ->name('consulta.historica');
    Route::get('/consulta-base-datos-historica/deudores', [ExcelController::class, 'deudores'])
        ->name('consulta.deudores');
    Route::get('/consulta-historial', [ExcelController::class, 'historial'])
        ->name('consulta.historial');
});

Route::middleware(['auth','verified'])->group(function () {

    Route::prefix('mobile')
         ->name('mobile.')
         ->middleware(ShareRole::class)
         ->group(function () {
             Route::get('/', function () {
                 $role = Auth::user()->getRoleNames()->first();
                 return redirect()->route("mobile.{$role}.index");
             })->name('index');

             Route::prefix('promotor')
                  ->name('promotor.')
                  ->controller(PromotorController::class)
                  ->group(function () {
                      Route::get('/',                 'index')             ->name('index');
                      Route::get('venta',             'venta')             ->name('venta');
                        Route::get('cartera',           'cartera')           ->name('cartera');
                        Route::get('objetivo',          'objetivo')          ->name('objetivo');
                      Route::get('solicitar-venta',   'solicitar_venta')   ->name('solicitar_venta');
                      Route::get('ingresar-cliente',  'ingresar_cliente')  ->name('ingresar_cliente');
                      Route::post('ingresar-cliente', 'storeCliente')      ->name('store_cliente');
                      Route::post('recredito',        'storeRecredito')    ->name('store_recredito');
                      Route::get('cliente-historial/{cliente}', 'cliente_historial') ->name('cliente_historial');
                      Route::post('enviar-ventas',    'enviarVentas')      ->name('enviar_ventas');
                  });

             Route::prefix('ejecutivo')
                  ->name('ejecutivo.')
                  ->controller(EjecutivoController::class)
                  ->group(function () {
                      Route::get('/',                 'index')             ->name('index');
                      Route::get('venta',             'venta')             ->name('venta');
                        Route::get('cartera',           'cartera')           ->name('cartera');
                        Route::get('objetivo',          'objetivo')          ->name('objetivo');
                      Route::get('solicitar-venta',   'solicitar_venta')   ->name('solicitar_venta');
                      Route::get('ingresar-cliente',  'ingresar_cliente')  ->name('ingresar_cliente');
                      Route::get('cliente-historial/{cliente}', 'cliente_historial') ->name('cliente_historial');
                  });

               Route::prefix('supervisor')
                    ->name('supervisor.')
                    ->controller(SupervisorController::class)
                    ->group(function () {
                        Route::get('/',                 'index')             ->name('index');
                        Route::get('venta',             'venta')             ->name('venta');
                        Route::get('cartera',           'cartera')           ->name('cartera');
                        Route::get('cartera/promotor/{promotor}', 'carteraPromotor')->name('cartera_promotor');
                        Route::get('objetivo',          'objetivo')          ->name('objetivo');
                      Route::get('reporte',           'reporte')           ->name('reporte');
                      Route::get('solicitar-venta',   'solicitar_venta')   ->name('solicitar_venta');
                      Route::get('ingresar-cliente',  'ingresar_cliente')  ->name('ingresar_cliente');
                      Route::get('cliente-historial/{cliente}', 'cliente_historial') ->name('cliente_historial');
                      Route::get('cartera-activa',    'cartera_activa')    ->name('cartera_activa');
                      Route::get('cartera-vencida',   'cartera_vencida')   ->name('cartera_vencida');
                      Route::get('cartera-inactiva',  'cartera_inactiva')  ->name('cartera_inactiva');
                      Route::get('cartera-falla',     'cartera_falla')     ->name('cartera_falla');
                      Route::get('historial-promotor','historial_promotor')->name('historial_promotor');
                      Route::get('reacreditacion',    'reacreditacion')    ->name('reacreditacion');
                      Route::get('clientes-prospectados',    'clientes_prospectados')    ->name('clientes_prospectados');
                      Route::get('clientes-supervisados',    'clientes_supervisados')    ->name('clientes_supervisados');
                      Route::get('busqueda',          'busqueda')          ->name('busqueda');
                      Route::get('apertura',          'apertura')          ->name('apertura');  
                  });
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

// Ruta para registrar múltiples pagos
Route::post('/mobile/promotor/pagos-multiples', [PagoRealController::class, 'storeMultiple']);
