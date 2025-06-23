<?php

use App\Http\Controllers\Api\V1\Reclamos\ReclamosController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\User\UserController;
use App\Http\Controllers\Api\V1\Cliente\ClienteController;
use App\Http\Controllers\Api\V1\Blog\BlogController;
use App\Http\Controllers\Api\V1\Productos\ProductoController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\EmailController;
use App\Models\Reclamo;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\BlogHeadController;
use App\Http\Controllers\Api\BlogFooterController;
use App\Http\Controllers\Api\BlogBodyController;
use App\Http\Controllers\Api\CommendTarjetaController;
use App\Http\Controllers\Api\TarjetaController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

// blogs públicos

Route::get('/blogs', [BlogController::class, "index"]);
Route::get('/blogs/{id}', [BlogController::class, "show"]);
Route::get('/blogs/link/{link}', [BlogController::class, "getByLink"]);


Route::middleware('auth:sanctum')->group(function () {
  
    Route::middleware('permission:crear-blogs')->post('/blogs', [BlogController::class, "store"]);
    Route::middleware('permission:editar-blogs')->put('/blog/{id}', [BlogController::class, "update"]);
    Route::middleware('permission:eliminar-blogs')->delete('/blogs/{id}', [BlogController::class, "destroy"]);


    Route::controller(PermissionController::class)->prefix("permissions")->middleware('permission:gestionar-permisos')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });

    Route::middleware('permission:gestionar-roles')->apiResource('roles', RoleController::class);

    Route::prefix('roles/{roleId}/permissions')->middleware('permission:asignar-permisos-roles')->group(function () {
        Route::get('/', [RolePermissionController::class, 'index']);
        Route::post('/', [RolePermissionController::class, 'store']);
        Route::delete('/{permissionId}', [RolePermissionController::class, 'destroy']);
    });
});

Route::prefix('v1')->group(function () {

    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::post('/login', 'login');
        Route::post('/logout', 'logout')->middleware(['auth:sanctum', 'role:ADMIN|USER']);
    });

    Route::controller(UserController::class)->prefix('users')->group(function () {
        Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::post('/', 'store');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });
    });

    Route::controller(ProductoController::class)->prefix('productos')->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::get('/link/{link}', 'showByLink');

        Route::middleware(['auth:sanctum', 'role:ADMIN|USER', 'permission:ENVIAR'])->group(function () {
            Route::post('/', 'store');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });
    });

    Route::controller(BlogController::class)->prefix('blogs')->group(function () {

        Route::post('/', 'store');

        Route::middleware(['auth:sanctum', 'role:ADMIN|USER', 'permission:ENVIAR'])->group(function () {
           
        });
    });

    // AUTH (login público)
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::post('/login', 'login');
    });

    Route::controller(EmailController::class)->prefix('email')->group(function () {
        Route::post('/', 'sendEmail');
    });

    Route::middleware('auth:sanctum')->group(function () {
        // AUTH (logout autenticado)
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // USERS
        Route::prefix('users')->controller(UserController::class)->group(function () {
            Route::get('/', 'index')->middleware('permission:ver-usuarios');
            Route::post('/', 'store')->middleware('permission:crear-usuarios');
            Route::put('/{id}', 'update')->middleware('permission:editar-usuarios');
            Route::delete('/{id}', 'destroy')->middleware('permission:eliminar-usuarios');
            Route::post('/{id}/role', 'assignRoleToUser')->middleware('permission:asignar-roles-usuarios');
        });

        // CLIENTES
        Route::prefix('clientes')->controller(ClienteController::class)->group(function () {
            Route::get('/', 'index')->middleware('permission:ver-clientes');
            Route::get('/{id}', 'show')->middleware('permission:ver-clientes');
            Route::post('/', 'store')->middleware('permission:crear-clientes');
            Route::put('/{id}', 'update')->middleware('permission:editar-clientes');
            Route::delete('/{id}', 'destroy')->middleware('permission:eliminar-clientes');
        });

        // RECLAMOS
        Route::prefix('reclamos')->controller(ReclamosController::class)->group(function () {
            Route::get('/', 'index')->middleware('permission:ver-reclamos');
            Route::get('/{id}', 'show')->middleware('permission:ver-reclamos');
            Route::post('/', 'store')->middleware('permission:crear-reclamos');
            Route::put('/{id}', 'update')->middleware('permission:editar-reclamos');
            Route::delete('/{id}', 'destroy')->middleware('permission:eliminar-reclamos');
        });
        // BLOQUES
        /*
        Route::prefix('bloques')->controller(BloqueContenidoController::class)->group(function () {
            Route::get('/', 'index')->middleware('permission:ver-bloques');
            Route::get('/{bloque}', 'show')->middleware('permission:ver-bloques');
            Route::post('/', 'store')->middleware('permission:crear-bloques');
            Route::put('/{bloque}', 'update')->middleware('permission:editar-bloques');
            Route::delete('/{bloque}', 'destroy')->middleware('permission:eliminar-bloques');
        });
        */
    });
});

Route::get('/exportProducto', [ExportController::class, 'exportProducto']);
Route::get('/exportBlog', [ExportController::class, 'exportBlog']);
Route::get('/exportCliente', [ExportController::class, 'exportCliente']);
Route::get('/exportReclamo', [ExportController::class, 'exportReclamo']);