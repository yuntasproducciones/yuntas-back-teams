<?php

namespace App\Http\Controllers\Api\V1\Productos;

use App\Models\Producto;
use App\Http\Requests\Producto\StoreProductoRequest;
use App\Services\ApiResponseService;
use App\Http\Requests\Producto\UpdateProductoRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\V1\BasicController;
use App\Http\Contains\HttpStatusCode;

/**
 * @OA\Tag(
 *     name="Productos",
 *     description="API Endpoints de productos"
 * )
 */
class ProductoController extends BasicController
{
    protected ApiResponseService $apiResponse;

    public function __construct(ApiResponseService $apiResponse)
    {
        $this->apiResponse = $apiResponse;
    }
    /**
     * Obtener listado de productos
     * 
     * @OA\Get(
     *     path="/api/v1/productos",
     *     summary="Muestra un listado de todos los productos",
     *     description="Retorna un array con todos los productos y sus relaciones",
     *     operationId="indexProductos",
     *     tags={"Productos"},
     *     @OA\Response(
     *         response=200,
     *         description="Operación exitosa",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nombre", type="string", example="Producto Premium"),
     *                     @OA\Property(property="link", type="string", example="producto-premium"),
     *                     @OA\Property(property="titulo", type="string", example="La mejor calidad"),
     *                     @OA\Property(property="descripcion", type="string"),
     *                     @OA\Property(property="seccion", type="string"),
     *                     @OA\Property(property="imagen_principal", type="string", example="https://placehold.co/100x150/blue/white?text=Producto"),
     *                     @OA\Property(property="especificaciones", type="object", example={"peso": "1kg", "dimensiones": "10x10x10cm"}),
     *                     @OA\Property(property="beneficios", type="array", @OA\Items(type="string"), example={"Beneficio 1", "Beneficio 2"}),
     *                     @OA\Property(property="imagenes", type="array", @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="url_imagen", type="string", example="https://placehold.co/100x150/blue/white?text=Imagen1"),
     *                         @OA\Property(property="texto_alt_SEO", type="string", example="Texto alternativo SEO")
     *                     )),
     *                     @OA\Property(property="etiquetas", type="array", @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="meta_titulo", type="string", example="Meta Título"),
     *                         @OA\Property(property="meta_descripcion", type="string", example="Meta Descripción")
     *                     )),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Productos obtenidos exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor"
     *     )
     * ),
     * security={}
     */
    public function index()
    {
        try {
            $productos = Producto::with(['imagenes', 'etiqueta'])
                ->orderBy('created_at', 'desc')
                ->get();

            $showProductos = $productos->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'link' => $producto->link,
                    'nombre' => $producto->nombre,
                    'titulo' => $producto->titulo,
                    'descripcion' => $producto->descripcion,
                    'seccion' => $producto->seccion,
                    'imagen_principal' => asset($producto->imagen_principal),
                    'especificaciones' => $producto->especificaciones ?? [],
                    'beneficios' => $producto->beneficios ?? [],
                    'imagenes' => $producto->imagenes->map(function ($imagen) {
                        return [
                            'id' => $imagen->id,
                            'url_imagen' => asset($imagen->url_imagen),
                            'texto_alt_SEO' => $imagen->texto_alt_SEO,
                        ];
                    }),
                    'etiqueta' => $producto->etiqueta ? [
                        'meta_titulo' => $producto->etiqueta->meta_titulo,
                        'meta_descripcion' => $producto->etiqueta->meta_descripcion,
                    ] : null,

                    'created_at' => $producto->created_at,
                    'updated_at' => $producto->updated_at,
                ];
            });

            return $this->apiResponse->successResponse(
                $showProductos,
                'Productos obtenidos exitosamente',
                HttpStatusCode::OK
            );
        } catch (\Exception $e) {
            return $this->apiResponse->errorResponse(
                'Error al obtener los productos: ' . $e->getMessage(),
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }


    /**
     * Crear un nuevo producto
     * 
     * @OA\Post(
     *     path="/api/v1/productos",
     *     summary="Crea un nuevo producto",
     *     description="Almacena un nuevo producto y retorna los datos creados",
     *     operationId="storeProducto",
     *     tags={"Productos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"nombre", "titulo", "descripcion", "imagen_principal", "seccion"},
     * 
     *                 @OA\Property(property="nombre", type="string", example="Producto XYZ"),
     *                 @OA\Property(property="link", type="string", example="producto-xyz"),
     *                 @OA\Property(property="titulo", type="string", example="Producto Premium XYZ"),
     *                 @OA\Property(property="descripcion", type="string", example="Descripción detallada del producto"),
     *                 @OA\Property(property="seccion", type="string", example="electrónica"),
     *
     *                 @OA\Property(
     *                     property="imagen_principal",
     *                     type="string",
     *                     format="binary",
     *                     description="Imagen principal del producto"
     *                 ),
     *
     *                 @OA\Property(
     *                     property="imagenes[0]",
     *                     type="string",
     *                     format="binary",
     *                     description="Imagen adicional 1"
     *                 ),
     *                 @OA\Property(
     *                     property="imagenes[1]",
     *                     type="string",
     *                     format="binary",
     *                     description="Imagen adicional 2"
     *                 ),
     *
     *                 @OA\Property(
     *                     property="especificaciones[0]",
     *                     type="string",
     *                     example="color:rojo",
     *                     description="Primera especificación"
     *                 ),
     *                 @OA\Property(
     *                     property="especificaciones[1]",
     *                     type="string",
     *                     example="material:aluminio",
     *                     description="Segunda especificación"
     *                 ),
     *
     *                 @OA\Property(
     *                     property="beneficios[0]",
     *                     type="string",
     *                     example="Beneficio 1",
     *                     description="Primer beneficio"
     *                 ),
     *                 @OA\Property(
     *                     property="beneficios[1]",
     *                     type="string",
     *                     example="Beneficio 2",
     *                     description="Segundo beneficio"
     *                 ),
     *
     *                 
     *                  @OA\Property(
     *                     property="etiquetas",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="meta_titulo", type="string", example="Meta Título"),
     *                         @OA\Property(property="meta_descripcion", type="string", example="Meta Descripción")
     *                     ),
     *                     description="Array de etiquetas SEO"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Producto creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Producto creado exitosamente")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Error de validación"),
     *     @OA\Response(response=500, description="Error del servidor")
     * )
     */


public function store(StoreProductoRequest $request)
{
    try {
        // Normalizar arrays (si vienen como string se convierten a array)
        $especificaciones = $request->input('especificaciones');
        if (!is_array($especificaciones)) {
            $especificaciones = $especificaciones ? [$especificaciones] : [];
        }

        $beneficios = $request->input('beneficios');
        if (!is_array($beneficios)) {
            $beneficios = $beneficios ? [$beneficios] : [];
        }

        $imagenes = $request->file('imagenes');
        if (!is_array($imagenes)) {
            $imagenes = $imagenes ? [$imagenes] : [];
        }

        // Crear producto
        $producto = Producto::create([
            'link'              => $request->link,
            'nombre'            => $request->nombre,
            'titulo'            => $request->titulo,
            'descripcion'       => $request->descripcion,
            'seccion'           => $request->seccion,
            'imagen_principal'  => $request->file('imagen_principal')
                                    ? $request->file('imagen_principal')->store('productos', 'public')
                                    : null,
            'especificaciones'  => $especificaciones,
            'beneficios'        => $beneficios,
        ]);

        // Guardar imágenes adicionales
        if (!empty($imagenes)) {
            foreach ($imagenes as $imagen) {
                if ($imagen) {
                    $path = $imagen->store('productos', 'public');
                    $producto->imagenes()->create([
                        'url_imagen' => $path,
                        'texto_alt_SEO' => 'Imagen del producto'
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Producto creado correctamente',
            'data' => $producto
        ], 201);

    } catch (\Exception $e) {
        \Log::error("Error al crear el producto: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => "Error al crear el producto: " . $e->getMessage(),
            'errors' => null
        ], 500);
    }
}

    /**
     * Mostrar un producto específico
     * 
     * @OA\Get(
     *     path="/api/v1/productos/{id}",
     *     summary="Muestra un producto específico",
     *     description="Retorna los datos de un producto según su ID",
     *     operationId="showProducto",
     *     tags={"Productos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del producto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto encontrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Producto Premium"),
     *                 @OA\Property(property="subtitle", type="string", example="La mejor calidad"),
     *                 @OA\Property(property="tagline", type="string", example="Innovación y calidad"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="specs", type="object"),
     *                 @OA\Property(property="relatedProducts", type="array", @OA\Items(type="integer")),
     *                 @OA\Property(property="images", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="image", type="string"),
     *                 @OA\Property(property="nombreProducto", type="string"),
     *                 @OA\Property(property="stockProducto", type="integer"),
     *                 @OA\Property(property="precioProducto", type="number", format="float"),
     *                 @OA\Property(property="seccion", type="string"),
     *                 @OA\Property(property="mensaje_correo", type="string")
     *             ),
     *             @OA\Property(property="message", type="string", example="Producto encontrado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $producto = Producto::with(['imagenes', 'etiqueta'])->findOrFail($id);

            $showProducto = [
                'id' => $producto->id,
                'link' => $producto->link,
                'nombre' => $producto->nombre,
                'titulo' => $producto->titulo,
                'descripcion' => $producto->descripcion,
                'seccion' => $producto->seccion,
                'imagen_principal' => asset($producto->imagen_principal),
                'especificaciones' => $producto->especificaciones ?? [],
                'beneficios' => $producto->beneficios ?? [],
                'imagenes' => $producto->imagenes->map(function ($imagen) {
                    return [
                        'id' => $imagen->id,
                        'url_imagen' => asset($imagen->url_imagen),
                        'texto_alt_SEO' => $imagen->texto_alt_SEO,
                    ];
                }),
                'etiqueta' => $producto->etiqueta ? [
                    'meta_titulo' => $producto->etiqueta->meta_titulo,
                    'meta_descripcion' => $producto->etiqueta->meta_descripcion,
                ] : null,
                'created_at' => $producto->created_at,
                'updated_at' => $producto->updated_at,
            ];

            return $this->apiResponse->successResponse(
                $showProducto,
                'Producto obtenido exitosamente',
                HttpStatusCode::OK
            );
        } catch (\Exception $e) {
            return $this->apiResponse->errorResponse(
                'Error al obtener el producto: ' . $e->getMessage(),
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }

    public function showByLink($link)
    {
        try {
            $producto = Producto::with(['imagenes', 'etiqueta'])->where('link', $link)->firstOrFail();

            $showProducto = [
                'id' => $producto->id,
                'link' => $producto->link,
                'nombre' => $producto->nombre,
                'titulo' => $producto->titulo,
                'descripcion' => $producto->descripcion,
                'seccion' => $producto->seccion,
                'imagen_principal' => asset($producto->imagen_principal),
                'especificaciones' => $producto->especificaciones ?? [],
                'beneficios' => $producto->beneficios ?? [],
                'imagenes' => $producto->imagenes->map(function ($imagen) {
                    return [
                        'id' => $imagen->id,
                        'url_imagen' => asset($imagen->url_imagen),
                        'texto_alt_SEO' => $imagen->texto_alt_SEO,
                    ];
                }),
                'etiqueta' => $producto->etiqueta ? [
                    'meta_titulo' => $producto->etiqueta->meta_titulo,
                    'meta_descripcion' => $producto->etiqueta->meta_descripcion,
                ] : null,
                'created_at' => $producto->created_at,
                'updated_at' => $producto->updated_at,
            ];

            return $this->apiResponse->successResponse(
                $showProducto,
                'Producto obtenido exitosamente',
                HttpStatusCode::OK
            );
        } catch (\Exception $e) {
            return $this->apiResponse->errorResponse(
                'Error al obtener el producto: ' . $e->getMessage(),
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }


    /**
     * Actualizar un producto específico
     * 
     * @OA\Put(
     *     path="/api/v1/productos/{id}",
     *     summary="Actualiza un producto específico",
     *     description="Actualiza los datos de un producto existente según su ID",
     *     operationId="updateProducto",
     *     tags={"Productos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del producto a actualizar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nombre", type="string", example="Producto XYZ Actualizado"),
     *             @OA\Property(property="link", type="string", example="producto-xyz-actualizado"),
     *             @OA\Property(property="titulo", type="string", example="Producto Premium XYZ V2"),
     *             @OA\Property(property="descripcion", type="string", example="Descripción actualizada del producto"),
     *             @OA\Property(property="imagen_principal", type="string", example="https://ejemplo.com/imagen_nueva.jpg"),
     *             @OA\Property(property="seccion", type="string", example="electrónica premium"),
     *             @OA\Property(property="especificaciones", type="object",
     *                 example={"color": "negro", "material": "titanio"}
     *             ),
     *             @OA\Property(property="beneficios", type="array", @OA\Items(type="string"), example={"Beneficio 1", "Beneficio 2"}),
     *             @OA\Property(property="imagenes", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="etiquetas", type="array", @OA\Items(type="object",
     *                 @OA\Property(property="meta_titulo", type="string", example="Meta Título Actualizado"),
     *                 @OA\Property(property="meta_descripcion", type="string", example="Meta Descripción Actualizada")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Producto actualizado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor"
     *     )
     * )
     */
    public function update(UpdateProductoRequest $request, $id)
    {
        try {
            $producto = Producto::findOrFail($id);
        } catch (\Exception $e) {
            return $this->notFoundResponse('Producto no encontrado');
        }

        DB::beginTransaction();
        try {
            Log::info('=== INICIANDO ACTUALIZACIÓN DE PRODUCTO ===');
            Log::info('Producto ID: ' . $id);
            Log::info('Request all data:', $request->all());
            Log::info('Request files:', $request->allFiles());

            // Preparar datos del producto (excluyendo especificaciones, beneficios, imagenes y etiquetas)
            $data = $request->except(['especificaciones', 'beneficios', 'imagenes', 'imagen_principal', 'etiquetas']);

            // Manejar imagen_principal
            if ($request->hasFile('imagen_principal')) {
                $imagenPrincipal = $request->file('imagen_principal');
                $nombreArchivo = time() . '_principal_' . $imagenPrincipal->getClientOriginalName();
                $rutaImagen = $imagenPrincipal->storeAs('productos', $nombreArchivo, 'public');
                $data['imagen_principal'] = '/storage/' . $rutaImagen;
                Log::info('Nueva imagen principal guardada: ' . $data['imagen_principal']);
            }

            // Actualizar especificaciones y beneficios como JSON
            if ($request->has('especificaciones')) {
                $data['especificaciones'] = $request->especificaciones;
            }
            if ($request->has('beneficios')) {
                $data['beneficios'] = $request->beneficios;
            }

            // Actualizar el producto
            $producto->update($data);

            // Procesar imágenes adicionales
            if ($request->has('imagenes')) {
                // Eliminar imágenes anteriores
                $producto->imagenes()->delete();

                foreach ($request->imagenes as $index => $imagen) {
                    try {
                        if ($request->hasFile("imagenes.$index")) {
                            $archivo = $request->file("imagenes.$index");

                            // Validar archivo
                            if (!$archivo->isValid() || $archivo->getSize() == 0) {
                                Log::info("Saltando archivo inválido en índice {$index}");
                                continue;
                            }

                            $nombreArchivo = time() . '_' . $index . '_' . $archivo->getClientOriginalName();
                            $rutaImagen = $archivo->storeAs('productos/adicionales', $nombreArchivo, 'public');

                            if ($rutaImagen) {
                                $producto->imagenes()->create([
                                    'url_imagen' => '/storage/' . $rutaImagen,
                                    'texto_alt_SEO' => 'Imagen del producto ' . $producto->nombre
                                ]);
                            }
                        }
                    } catch (\Exception $imageException) {
                        Log::error("Error procesando imagen índice {$index}: " . $imageException->getMessage());
                    }
                }
            }

            // Procesar etiquetas
            if ($request->has('etiquetas')) {
                $producto->etiquetas()->delete(); // Eliminar etiquetas anteriores
                foreach ($request->etiquetas as $etiquetaData) {
                    $producto->etiquetas()->create([
                        'meta_titulo' => $etiquetaData['meta_titulo'] ?? null,
                        'meta_descripcion' => $etiquetaData['meta_descripcion'] ?? null,
                    ]);
                }
            }

            DB::commit();
            Log::info('=== PRODUCTO ACTUALIZADO EXITOSAMENTE ===');
            return $this->successResponse($producto, 'Producto actualizado exitosamente', HttpStatusCode::OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar producto: ' . $e->getMessage());
            return $this->errorResponse('Error al actualizar el producto: ' . $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Eliminar un producto específico
     * 
     * @OA\Delete(
     *     path="/api/v1/productos/{id}",
     *     summary="Elimina un producto específico",
     *     description="Elimina un producto existente según su ID",
     *     operationId="destroyProducto",
     *     tags={"Productos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del producto a eliminar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Producto eliminado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Buscar el producto
            $producto = Producto::findOrFail($id);

            // Eliminar las imágenes asociadas
            $producto->imagenes()->delete();

            // Manejar los blogs asociados sin eliminarlos
            // Solo desvincular la relación estableciendo producto_id a null
            $blogs = $producto->blogs;
            foreach ($blogs as $blog) {
                $blog->producto_id = null;
                $blog->save();
            }

            // Eliminar las etiquetas asociadas
            $producto->etiquetas()->delete();

            // Finalmente eliminar el producto
            $producto->delete();

            DB::commit();
            return $this->successResponse(null, 'Producto eliminado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->notFoundResponse('Producto no encontrado');
            }
            return $this->errorResponse('Error al eliminar el producto: ' . $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}
