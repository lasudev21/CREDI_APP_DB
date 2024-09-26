<?php

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\ParametrosController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\CreditosController;
use App\Http\Controllers\FlujoCajaController;
use App\Http\Controllers\FlujoUtilidadesController;
use App\Http\Controllers\ReportesController;

// header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Authorization, Content-Type');


Route::group(['middleware' => ['api']], function () {

    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', [UserController::class, 'postSignIn']);
    });

    Route::group(['middleware' => 'jwt.auth'], function () {

        Route::group(['prefix' => 'dashboard'], function () {
            Route::get('/newclientes', [DashboardController::class, 'getNewClientes']);
        });

        Route::group(['prefix' => 'roles'], function () {
            Route::get('/{id}', [RolesController::class, 'getPermisoByRol']);
            Route::put('/', [RolesController::class, 'putPermisos']);
            Route::post('/views', [RolesController::class, 'getAllViewsRole']);
            Route::post('/savePermission', [RolesController::class, 'postRolesView']);
        });

        Route::group(['prefix' => 'usuarios'], function () {
            Route::get('/', [UserController::class, 'getUsers']);
            Route::post('/', [UserController::class, 'saveUser']);
            Route::post('/changePassword', [UserController::class, 'changePassword']);
            Route::put('/', [UserController::class, 'updateUser']);
        });

        Route::group(['prefix' => 'clientes'], function () {
            Route::get('/', [ClientesController::class, 'getClientes']);
            Route::post('/', [ClientesController::class, 'saveCliente']);
            Route::post('/{id}', [ClientesController::class, 'changeState']);
            Route::get('/{id}', [ClientesController::class, 'getDetallesCredito']);
            Route::put('/', [ClientesController::class, 'updateCliente']);
        });

        Route::group(['prefix' => 'parametros'], function () {
            Route::get('/', [ParametrosController::class, 'getParametros']);
            Route::get('/datosRutas', [ParametrosController::class, 'getDatosRutas']);
            Route::get('/{nombre?}', [ParametrosController::class, 'getListaParametros']);
            Route::post('/', [ParametrosController::class, 'postParametros']);
            Route::put('/', [ParametrosController::class, 'putParametros']);
        });

        Route::group(['prefix' => 'creditos'], function () {
            Route::get('/clientes1', [CreditosController::class, 'getClientes']);
            Route::get('/{id}', [CreditosController::class, 'getCredito']);
            Route::post('/', [CreditosController::class, 'postCredito']);
            Route::post('/abonos', [CreditosController::class, 'postAbonos']);
            //         Route::post('/reorder', 'CreditosController@postSetEstadosCreditos');
            Route::post('/renovaciones/{id}', [CreditosController::class, 'postRenovaciones']);
        });

        Route::group(['prefix' => 'flujoCaja'], function () {
            Route::get('/', [FlujoCajaController::class, 'getFlujoCaja']);
            Route::post('/', [FlujoCajaController::class, 'postSaveFlujo']);
        });

        Route::group(['prefix' => 'flujoUtilidades'], function () {
            Route::get('/', [FlujoUtilidadesController::class, 'getFlujoUtilidades']);
            Route::post('/', [FlujoUtilidadesController::class, 'postSaveFlujoUtilidades']);
        });

        Route::group(['prefix' => 'reportes'], function () {
            Route::get('/coteos', [ReportesController::class, 'getCoteos']);
        });
    });
});
