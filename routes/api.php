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

// blogs públicos para ver los clientes
Route::get('/cards', [CardController::class, "index"]);
Route::get('/blogs/{id}', [BlogController::class, "show"]);
Route::get('/blogs', [BlogController::class, "index"]);
Route::get('/blog_head/{id}', [BlogHeadController::class, "show"]);
Route::get('/blog_footer/{id}', [BlogFooterController::class, "show"]);
Route::get('/blog_body/{id}', [BlogBodyController::class, "show"]);

// rutas create blog
Route::post('/card', [CardController::class, "create"]);
Route::post('/blogs', [BlogController::class, "create"]);
Route::post('/blog_head', [BlogHeadController::class, "create"]);
Route::post('/blog_body', [BlogBodyController::class, "create"]);
Route::post('/blog_footer', [BlogFooterController::class, "create"]);
Route::post('/commend_tarjeta', [CommendTarjetaController::class, "create"]);
Route::post('/tarjeta', [TarjetaController::class, "create"]);
Route::post('/card/blog/image_head/{id}', [CardController::class, "imageHeader"]);
Route::post('/card/blog/images_body/{id}', [CardController::class, "imagesBody"]);
Route::post('/card/blog/images_footer/{id}', [CardController::class, "imagesFooter"]);

// rutas update blog
Route::put('/card/{id}', [CardController::class, "update"]);
Route::put('/blogs/{id}', [BlogController::class, "update"]);
Route::put('/blog_head/{id}', [BlogHeadController::class, "update"]);
Route::put('/blog_body/{id}', [BlogBodyController::class, "update"]);
Route::put('/blog_footer/{id}', [BlogFooterController::class, "update"]);
Route::put('/commend_tarjeta/{id}', [CommendTarjetaController::class, "update"]);
Route::put('/tarjeta/{id}', [TarjetaController::class, "update"]);

// rutas delete blog
Route::delete('/cards/{id}', [CardController::class, "destroy"]);
Route::delete('/blogs/{id}', [BlogController::class, "destroy"]);
Route::delete('/blog_head/{id}', [BlogHeadController::class, "destroy"]);
Route::delete('/blog_body/{id}', [BlogBodyController::class, "destroy"]);
Route::delete('/blog_footer/{id}', [BlogFooterController::class, "destroy"]);
Route::delete('/commend_tarjeta/{id}', [CommendTarjetaController::class, "destroy"]);
Route::delete('/tarjetas_delete/{id}', [TarjetaController::class, "destroyAll"]);

// Rutas para los permisos
Route::get('/permissions', [PermissionController::class, 'index']);
Route::get('/permissions/{id}', [PermissionController::class, 'show']);
Route::post('/permissions', [PermissionController::class, 'store']);
Route::put('/permissions/{id}', [PermissionController::class, 'update']);
Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);

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

        Route::middleware(['auth:sanctum', 'role:ADMIN|USER', 'permission:ENVIAR'])->group(function () {
            Route::post('/', 'store');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });
    });

    Route::controller(BlogController::class)->prefix('blogs')->group(function(){
        Route::get('/', 'index');
        Route::get('/{blog}', 'show');
        Route::get('/link/{link}', 'getByLink');

        Route::middleware(['auth:sanctum', 'role:ADMIN|USER'])->group(function () {
            Route::post('/', 'store');
            Route::put('/{blog}', 'update');
            Route::delete('/{blog}', 'destroy');
        });
    });

    Route::controller(ClienteController::class)->prefix('clientes')->group(function(){
        Route::get('/', 'index');
        Route::get('/{id}', 'show');

        Route::middleware(['auth:sanctum', 'role:ADMIN|USER'])->group(function () {
            Route::post('/', 'store');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });
    });

    // Route::controller(BloqueContenidoController::class)->prefix('bloques')->group(function(){
    //     Route::get('/', 'index');
    //     Route::get('/{bloque}', 'show');

    //     Route::middleware(['auth:sanctum', 'role:ADMIN|USER'])->group(function () {
    //         Route::post('/', 'store');
    //         Route::put('/{bloque}', 'update');
    //         Route::delete('/{bloque}', 'destroy');
    //     });
    // });

    Route::controller(ReclamosController::class)->prefix('reclamos')->group(function(){
        Route::post('/', 'store');

        Route::middleware(['auth:sanctum', 'role:ADMIN|USER'])->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });
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