<?php

namespace App\Http\Controllers\Api\V1\Productos;

use App\Models\Producto;
use App\Http\Requests\Producto\StoreProductoRequest;
use App\Http\Requests\Producto\UpdateProductoRequest;
use Illuminate\Support\Facades\DB;
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
     *                     @OA\Property(property="title", type="string", example="Producto Premium"),
     *                     @OA\Property(property="subtitle", type="string", example="La mejor calidad"),
     *                     @OA\Property(property="tagline", type="string", example="Innovación y calidad"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="specs", type="object"),
     *                     @OA\Property(property="dimensions", type="object"),
     *                     @OA\Property(property="relatedProducts", type="array", @OA\Items(type="integer")),
     *                     @OA\Property(property="images", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="image", type="string"),
     *                     @OA\Property(property="nombreProducto", type="string"),
     *                     @OA\Property(property="stockProducto", type="integer"),
     *                     @OA\Property(property="precioProducto", type="number", format="float"),
     *                     @OA\Property(property="seccion", type="string")
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
        $productos = Producto::with(['dimensiones', 'imagenes', 'productosRelacionados'])->get();

        $formattedProductos = $productos->map(function ($producto) {
            return [
                'id' => $producto->id,
                'title' => $producto->titulo,
                'subtitle' => $producto->subtitulo,
                'tagline' => $producto->lema,
                'description' => $producto->descripcion,
                // Aquí obtenemos especificaciones como JSON decodificado (array asociativo)
                'specs' => json_decode($producto->especificaciones, true) ?? [],
                'dimensions' => $producto->dimensiones->pluck('valor', 'tipo'),
                'relatedProducts' => $producto->productosRelacionados->pluck('id'),
                'images' => $producto->imagenes->pluck('url_imagen'),
                'image' => $producto->imagen_principal,
                'nombreProducto' => $producto->nombre,
                'stockProducto' => $producto->stock,
                'precioProducto' => $producto->precio,
                'seccion' => $producto->seccion,
            ];
        });

        return $this->successResponse($formattedProductos, 'Productos obtenidos exitosamente');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
     *         @OA\JsonContent(
     *             required={"nombre", "titulo", "descripcion", "imagen_principal", "stock", "precio", "seccion"},
     *             @OA\Property(property="nombre", type="string", example="Producto XYZ"),
     *             @OA\Property(property="titulo", type="string", example="Producto Premium XYZ"),
     *             @OA\Property(property="subtitulo", type="string", example="La mejor calidad"),
     *             @OA\Property(property="lema", type="string", example="Innovación y calidad"),
     *             @OA\Property(property="descripcion", type="string", example="Descripción detallada del producto"),
     *             @OA\Property(property="imagen_principal", type="string", example="https://placehold.co/100x150/blue/white?text=XYZ"),
     *             @OA\Property(property="stock", type="integer", example=100),
     *             @OA\Property(property="precio", type="number", format="float", example=199.99),
     *             @OA\Property(property="seccion", type="string", example="electrónica"),
     *             @OA\Property(property="especificaciones", type="object",
     *                 example={"color": "rojo", "material": "aluminio"}
     *             ),
     *             @OA\Property(property="dimensiones", type="object",
     *                 example={"alto": "10cm", "ancho": "20cm", "largo": "30cm"}
     *             ),
     *             @OA\Property(property="imagenes", type="array", @OA\Items(type="string"), example={"https://placehold.co/100x150/blue/white?text=Product_X", "https://placehold.co/100x150/blue/white?text=Product_Y"}),
     *             @OA\Property(property="relacionados", type="array", @OA\Items(type="integer"), example={1,2,3})
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
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor"
     *     )
     * )
     */
    public function store(StoreProductoRequest $request)
    {
        DB::beginTransaction();
        try {
            // Guardamos especificaciones como JSON en el campo 'especificaciones'
            $data = $request->except(['especificaciones', 'dimensiones', 'imagenes', 'relacionados']);
            if ($request->has('especificaciones')) {
                // Convertimos el array asociativo de especificaciones a JSON
                $data['especificaciones'] = json_encode($request->especificaciones);
            }

            $producto = Producto::create($data);

            if ($request->has('dimensiones')) {
                foreach ($request->dimensiones as $tipo => $valor) {
                    $producto->dimensiones()->create([
                        'tipo' => $tipo,
                        'valor' => $valor
                    ]);
                }
            }

            if ($request->has('imagenes')) {
                foreach ($request->imagenes as $url) {
                    $producto->imagenes()->create([
                        'url_imagen' => $url
                    ]);
                }
            }

            if ($request->has('relacionados')) {
                foreach ($request->relacionados as $idRelacionado) {
                    $producto->productosRelacionados()->attach($idRelacionado);
                }
            }

            DB::commit();
            return $this->successResponse($producto, 'Producto creado exitosamente', HttpStatusCode::CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al crear el producto: ' . $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
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
     *                 @OA\Property(property="dimensions", type="object"),
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
            $producto = Producto::with(['especificaciones', 'dimensiones', 'imagenes', 'productosRelacionados'])->findOrFail($id);

            $formattedProducto = [
                'id' => $producto->id,
                'title' => $producto->titulo,
                'subtitle' => $producto->subtitulo,
                'tagline' => $producto->lema,
                'description' => $producto->descripcion,
                'specs' => $producto->especificaciones->pluck('valor', 'clave'),
                'dimensions' => $producto->dimensiones->pluck('valor', 'tipo'),
                'relatedProducts' => $producto->productosRelacionados->pluck('id'),
                'images' => $producto->imagenes->pluck('url_imagen'),
                'image' => $producto->imagen_principal,
                'nombreProducto' => $producto->nombre,
                'stockProducto' => $producto->stock,
                'precioProducto' => $producto->precio,
                'seccion' => $producto->seccion,
            ];

            return $this->successResponse($formattedProducto, 'Producto encontrado exitosamente');
        } catch (\Exception $e) {
            return $this->notFoundResponse('Producto no encontrado');
        }
    }

    public function showByLink($link)
    {
        try {
            $producto = Producto::with(['dimensiones', 'imagenes', 'productosRelacionados'])->where('link', $link)->firstOrFail();

            $formattedProducto = [
                'id' => $producto->id,
                'title' => $producto->titulo,
                'subtitle' => $producto->subtitulo,
                'tagline' => $producto->lema,
                'description' => $producto->descripcion,
                'specs' => $producto->especificaciones,
                'dimensions' => $producto->dimensiones->pluck('valor', 'tipo'),
                'relatedProducts' => $producto->productosRelacionados->pluck('id'),
                'images' => $producto->imagenes->pluck('url_imagen'),
                'image' => $producto->imagen_principal,
                'nombreProducto' => $producto->nombre,
                'stockProducto' => $producto->stock,
                'precioProducto' => $producto->precio,
                'seccion' => $producto->seccion
            ];

            return $this->successResponse($formattedProducto, 'Producto encontrado exitosamente');
        } catch (\Exception $e) {
            return $this->notFoundResponse('Producto no encontrado');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        //
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
     *             @OA\Property(property="titulo", type="string", example="Producto Premium XYZ V2"),
     *             @OA\Property(property="subtitulo", type="string", example="La mejor calidad actualizada"),
     *             @OA\Property(property="lema", type="string", example="Innovación y calidad mejorada"),
     *             @OA\Property(property="descripcion", type="string", example="Descripción actualizada del producto"),
     *             @OA\Property(property="imagen_principal", type="string", example="https://ejemplo.com/imagen_nueva.jpg"),
     *             @OA\Property(property="stock", type="integer", example=150),
     *             @OA\Property(property="precio", type="number", format="float", example=249.99),
     *             @OA\Property(property="seccion", type="string", example="electrónica premium"),
     *             @OA\Property(property="especificaciones", type="object",
     *                 example={"color": "negro", "material": "titanio"}
     *             ),
     *             @OA\Property(property="dimensiones", type="object",
     *                 example={"alto": "12cm", "ancho": "22cm", "largo": "32cm"}
     *             ),
     *             @OA\Property(property="imagenes", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="relacionados", type="array", @OA\Items(type="integer"))
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
            $producto->update([
                'nombre' => $request->nombre,
                'link' => $request->link,
                'titulo' => $request->titulo,
                'subtitulo' => $request->subtitulo,
                'lema' => $request->lema,
                'descripcion' => $request->descripcion,
                'imagen_principal' => $request->imagen_principal,
                'stock' => $request->stock,
                'precio' => $request->precio,
                'seccion' => $request->seccion
            ]);

            if ($request->has('especificaciones')) {
                $producto->especificaciones()->delete();
                foreach ($request->especificaciones as $clave => $valor) {
                    $producto->especificaciones()->create([
                        'clave' => $clave,
                        'valor' => $valor
                    ]);
                }
            }

            if ($request->has('dimensiones')) {
                $producto->dimensiones()->delete();
                foreach ($request->dimensiones as $tipo => $valor) {
                    $producto->dimensiones()->create([
                        'tipo' => $tipo,
                        'valor' => $valor
                    ]);
                }
            }

            // Actualizamos imágenes
            if ($request->has('imagenes')) {
                // Eliminamos imágenes anteriores
                $producto->imagenes()->delete();
                foreach ($request->imagenes as $url) {
                    $producto->imagenes()->create([
                        'url_imagen' => $url
                    ]);
                }
            }

            // Actualizamos productos relacionados
            if ($request->has('relacionados')) {
                // Sincronizamos relaciones (quita las que no están y agrega las nuevas)
                $producto->productosRelacionados()->sync($request->relacionados);
            }

            DB::commit();
            return $this->successResponse($producto, 'Producto actualizado exitosamente', HttpStatusCode::OK);
        } catch (\Exception $e) {
            DB::rollBack();
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
            $producto = Producto::findOrFail($id);
            $producto->delete();
            return $this->successResponse(null, 'Producto eliminado exitosamente');
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->notFoundResponse('Producto no encontrado');
            }
            return $this->errorResponse('Error al eliminar el producto: ' . $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}
