<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0",
 *         title="Laravel Swagger API",
 *         description="Laravel Swagger API Documentation",
 *     ),
 *     @OA\Server(
 *         url=L5_SWAGGER_CONST_HOST,
 *         description="API Server - Local"
 *     ),
 *     @OA\Server(
 *         url="https://apiyuntas.yuntaspublicidad.com/",
 *         description="API Server - Production"
 *     )
 * )

 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}