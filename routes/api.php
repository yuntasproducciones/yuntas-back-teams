<?php

use App\Http\Controllers\Api\V1\Reclamos\ReclamosController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\User\UserController;
use App\Http\Controllers\Api\V1\Productos\ProductoController;
use App\Http\Controllers\Api\V1\Cliente\ClienteController;
use App\Http\Controllers\Api\V1\Blog\BlogController;
use App\Http\Controllers\V2ProductoController;
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


// blogs públicos
Route::get('/cards', [CardController::class, "index"]);
Route::get('/blogs/{id}', [BlogController::class, "show"]);
Route::get('/blogs', [BlogController::class, "index"]);
Route::get('/blog_head/{id}', [BlogHeadController::class, "show"]);
Route::get('/blog_footer/{id}', [BlogFooterController::class, "show"]);
Route::get('/blog_body/{id}', [BlogBodyController::class, "show"]);

Route::middleware('auth:sanctum')->group(function () {
    //rutas create blog
    Route::middleware('permission:crear-blogs')->post('/card', [CardController::class, "create"]);
    Route::middleware('permission:crear-blogs')->post('/blog', [BlogController::class, "create"]);
    Route::middleware('permission:crear-blogs')->post('/blog_head', [BlogHeadController::class, "create"]);
    Route::middleware('permission:crear-blogs')->post('/blog_body', [BlogBodyController::class, "create"]);
    Route::middleware('permission:crear-blogs')->post('/blog_footer', [BlogFooterController::class, "create"]);
    Route::middleware('permission:crear-tarjetas')->post('/commend_tarjeta', [CommendTarjetaController::class, "create"]);
    Route::middleware('permission:crear-tarjetas')->post('/tarjeta', [TarjetaController::class, "create"]);
    Route::middleware('permission:crear-tarjetas')->post('/card/blog/image_head/{id}', [CardController::class, "imageHeader"]);
    Route::middleware('permission:crear-tarjetas')->post('/card/blog/images_body/{id}', [CardController::class, "imagesBody"]);
    Route::middleware('permission:crear-tarjetas')->post('/card/blog/images_footer/{id}', [CardController::class, "imagesFooter"]);

    //rutas update blog
    Route::middleware('permission:editar-blogs')->put('/card/{id}', [CardController::class, "update"]);
    Route::middleware('permission:editar-blogs')->put('/blog/{id}', [BlogController::class, "update"]);
    Route::middleware('permission:editar-blogs')->put('/blog_head/{id}', [BlogHeadController::class, "update"]);
    Route::middleware('permission:editar-blogs')->put('/blog_body/{id}', [BlogBodyController::class, "update"]);
    Route::middleware('permission:editar-blogs')->put('/blog_footer/{id}', [BlogFooterController::class, "update"]);
    Route::middleware('permission:editar-blogs')->put('/commend_tarjeta/{id}', [CommendTarjetaController::class, "update"]);
    Route::middleware('permission:editar-blogs')->put('/tarjeta/{id}', [TarjetaController::class, "update"]);

    //rutas delete blog
    Route::middleware('permission:eliminar-blogs')->delete('/cards/{id}', [CardController::class, "destroy"]);
    Route::middleware('permission:eliminar-blogs')->delete('/blogs/{id}', [BlogController::class, "destroy"]);
    Route::middleware('permission:eliminar-blogs')->delete('/blog_head/{id}', [BlogHeadController::class, "destroy"]);
    Route::middleware('permission:eliminar-blogs')->delete('/blog_body/{id}', [BlogBodyController::class, "destroy"]);
    Route::middleware('permission:eliminar-blogs')->delete('/blog_footer/{id}', [BlogFooterController::class, "destroy"]);
    Route::middleware('permission:eliminar-tarjetas')->delete('/commend_tarjeta/{id}', [CommendTarjetaController::class, "destroy"]);
    Route::middleware('permission:eliminar-tarjetas')->delete('/tarjetas_delete/{id}', [TarjetaController::class, "destroyAll"]);

    // Rutas para los permisos
    Route::middleware('permission:gestionar-permisos')->apiResource('permissions', PermissionController::class);

    // Rutas para los roles
    Route::middleware('permission:gestionar-roles')->apiResource('roles', RoleController::class);

    // Rutas para asignar/quitar permisos a los roles
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

    Route::controller(UserController::class)->prefix('users')->group(function(){
        Route::middleware(['auth:sanctum', 'role:ADMIN'])->group(function () {
            Route::post('/', 'store');
            Route::get('/', 'index');
            Route::delete('/{id}', 'destroy');
            Route::put('/{id}', 'update');
        });
    });

    Route::controller(ProductoController::class)->prefix('productos')->group(function(){
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::get('/link/{link}', 'showByLink');

        Route::middleware(['auth:sanctum', 'role:ADMIN|USER', 'permission:ENVIAR'])->group(function () {
            Route::post('/', 'store');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });
    });

    // AUTH (login público)
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::post('/login', 'login');
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

Route::prefix("v2")->group(function(){
    Route::controller(V2ProductoController::class)->prefix("/productos")->group(function(){
        Route::get("/", "index");
        Route::post("/", "store");
        Route::put("/{id}", "update");
        Route::delete("/{id}", "destroy")->whereNumber("id");
    });
});