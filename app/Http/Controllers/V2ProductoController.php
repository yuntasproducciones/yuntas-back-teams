<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Http\Requests\V2StoreProductoRequest;
use App\Http\Requests\V2UpdateProductoRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class V2ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Obtener listado de productos
     * 
     * @OA\Get(
     *     path="/api/v2/productos",
     *     summary="Muestra un listado de todos los productos",
     *     description="Retorna un array con todos los productos y sus relaciones",
     *     operationId="indexProductos2",
     *     tags={"Productos"},
     *     @OA\Response(
     *         response=200,
     *         description="Operación exitosa",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nombre", type="string", example="Laptop"),
     *                     @OA\Property(property="titulo", type="string", example="Producto premium"),
     *                     @OA\Property(property="subtitulo", type="string", example="Innovación y calidad"),
     *                     @OA\Property(property="stock", type="integer"),
     *                     @OA\Property(property="precio", type="number"),
     *                     @OA\Property(property="seccion", type="string"),
     *                     @OA\Property(property="lema", type="string"),
     *                     @OA\Property(property="descripcion", type="string"),
     *                     @OA\Property(property="especificaciones", type="string"),
     *                     @OA\Property(property="mensaje_correo", type="string"),
     *                     @OA\Property(property="created_at", type="string"),
     *                     @OA\Property(property="updated_at", type="string"),
     *                     @OA\Property(property="imagenes", type="object")
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
        //
        $productos = Producto::with('dimensiones', 'imagenes')->get();

        // Para decodificar especificaciones
        $productos->transform(function ($producto) {
            $producto->especificaciones = json_decode($producto->especificaciones, true) ?? [];
            return $producto;
        });

        return response()->json($productos);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
 * Crear un nuevo producto
 * 
 * @OA\Post(
 *     path="/api/v2/productos",
 *     summary="Crear un nuevo producto (no funciona en Swagger)",
 *     description="Almacena un nuevo producto, guarda la imagen en el servidor. Si lo intentas usar en Swagger no funcionará, pero si lo pruebas desde Postman si funciona. Los campos a enviar ya sea o desde Postman o desde un frontend son los mismos listados a continuación.",
 *     operationId="storeProducto2",
 *     tags={"Productos"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={
 *                     "nombre", "titulo", "subtitulo", "stock", "precio", 
 *                     "seccion", "lema", "descripcion", "especificaciones",
 *                     "imagenes", "textos_alt", "mensaje_correo"
 *                 },
 *                 @OA\Property(property="nombre", type="string", example="Selladora"),
 *                 @OA\Property(property="titulo", type="string", example="Titulo increíble"),
 *                 @OA\Property(property="subtitulo", type="string", example="Subtitulo increíble"),
 *                 @OA\Property(property="stock", type="integer", example=20),
 *                 @OA\Property(property="precio", type="number", format="float", example=100.50),
 *                 @OA\Property(property="seccion", type="string", example="Decoración"),
 *                 @OA\Property(property="lema", type="string", example="Lema increíble"),
 *                 @OA\Property(property="descripcion", type="string", example="Descripción increíble"),
 *                 @OA\Property(property="especificaciones", type="string", example="Especificaciones increíbles"),
 *                 
 *                 @OA\Property(
 *                     property="imagenes",
 *                     type="array",
 *                     @OA\Items(type="string", format="binary"),
 *                     description="Array de imágenes a subir"
 *                 ),
 *                 
 *                 @OA\Property(
 *                     property="textos_alt",
 *                     type="array",
 *                     @OA\Items(type="string", example="Texto ALT para la imagen"),
 *                     description="Array de textos alternativos para las imágenes"
 *                 ),
 *                 
 *                 @OA\Property(property="mensaje_correo", type="string", example="Mensaje increíble")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Producto creado exitosamente",
 *         @OA\JsonContent(
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
    private function guardarImagen($x){
        Storage::putFileAs("imagenes", $x, $x->hashName());
        return "/storage/imagenes/" . $x->hashName();
    }

    public function store(V2StoreProductoRequest $request)
    {
        //
        $datosValidados = $request->validated();

        $imagenes = $datosValidados["imagenes"];
        $textos = $datosValidados["textos_alt"];

        $imagenesProcesadas = [];
        foreach ($imagenes as $i => $img) {
            $url = $this->guardarImagen($img);
            $imagenesProcesadas[] = [
                "url_imagen" => $url,
                "texto_alt_SEO" => $textos[$i]
            ];
        }
        
        $producto = Producto::create([
            "nombre" => $datosValidados["nombre"],
            "link" => $datosValidados["link"],
            "titulo" => $datosValidados["titulo"],
            "subtitulo" => $datosValidados["subtitulo"],
            "stock" => $datosValidados["stock"],
            "precio" => $datosValidados["precio"],
            "seccion" => $datosValidados["seccion"],
            "lema" => $datosValidados["lema"],
            "descripcion" => $datosValidados["descripcion"],
            "especificaciones" => $datosValidados["especificaciones"],
        ]);

        $producto->productosRelacionados()->sync($datosValidados['relacionados']);
        $producto->imagenes()->createMany($imagenesProcesadas);

        return response()->json(["message"=>"Producto insertado exitosamente"], 201);
    }

    /**
     * Obtener un producto por su ID
     * 
     * @OA\Get(
     *     path="/api/v2/productos/{id}",
     *     summary="Muestra un producto por su ID",
     *     description="Retorna los datos completos de un producto según su ID",
     *     operationId="getProductoByIdV2",
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
     *         description="Producto obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=11),
     *             @OA\Property(property="nombre", type="string", example="Producto Ejemplo 11"),
     *             @OA\Property(property="link", type="string", example="p11"),
     *             @OA\Property(property="titulo", type="string", example="Producto Premium"),
     *             @OA\Property(property="subtitulo", type="string", example="La mejor calidad"),
     *             @OA\Property(property="stock", type="integer", example=100),
     *             @OA\Property(property="precio", type="number", format="float", example=199.99),
     *             @OA\Property(property="seccion", type="string", example="electrónica"),
     *             @OA\Property(property="lema", type="string", example="Innovación y calidad"),
     *             @OA\Property(property="descripcion", type="string", example="Descripción detallada del producto ejemplo"),
     *             @OA\Property(
     *                 property="especificaciones",
     *                 type="object",
     *                 @OA\Property(property="color", type="string", example="rojo"),
     *                 @OA\Property(property="material", type="string", example="aluminio")
     *             ),
     *             @OA\Property(
     *                 property="dimensiones",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="tipo", type="string", example="alto"),
     *                     @OA\Property(property="valor", type="string", example="30cm")
     *                 )
     *             ),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-28T21:52:09.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-28T21:52:09.000000Z"),
     *             @OA\Property(
     *                 property="imagenes",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=23),
     *                     @OA\Property(property="producto_id", type="integer", example=11),
     *                     @OA\Property(property="url_imagen", type="string", example="/storage/imagenes/imagen.jpg"),
     *                     @OA\Property(property="texto_alt_SEO", type="string", example="Texto SEO para imagen"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-28T21:52:09.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-28T21:52:09.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Hubo un error en el servidor")
     *         )
     *     ),
     *     security={}
     * )
     */

    public function show(string $id)
    {
        try {
            $producto = Producto::with(['dimensiones', 'imagenes'])->find($id);

            if ($producto === null) {
                return response()->json([
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            $formattedProducto = [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'link' => $producto->link,
                'titulo' => $producto->titulo,
                'subtitulo' => $producto->subtitulo,
                'stock' => $producto->stock,
                'precio' => $producto->precio,
                'seccion' => $producto->seccion,
                'lema' => $producto->lema,
                'descripcion' => $producto->descripcion,
                'especificaciones' => json_decode($producto->especificaciones, true) ?? [],
                'dimensiones' => $producto->dimensiones,
                'created_at' => $producto->created_at,
                'updated_at' => $producto->updated_at,
                'imagenes' => $producto->imagenes,
            ];

            return response()->json([
                'message' => 'Producto encontrado exitosamente',
                'data' => $formattedProducto
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Hubo un error en el servidor'
            ], 500);
        }
    }

    /**
     * Obtener un producto por su enlace único
     * 
     * @OA\Get(
     *     path="/api/v2/productos/link/{link}",
     *     summary="Muestra un producto usando su enlace único",
     *     description="Retorna los datos completos de un producto identificado por su campo 'link'",
     *     operationId="getProductoByLink",
     *     tags={"Productos"},
     *     @OA\Parameter(
     *         name="link",
     *         in="path",
     *         description="Enlace único del producto",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=11),
     *             @OA\Property(property="nombre", type="string", example="Producto Ejemplo 11"),
     *             @OA\Property(property="link", type="string", example="p11"),
     *             @OA\Property(property="titulo", type="string", example="Producto Premium"),
     *             @OA\Property(property="subtitulo", type="string", example="La mejor calidad"),
     *             @OA\Property(property="stock", type="integer", example=100),
     *             @OA\Property(property="precio", type="number", format="float", example=199.99),
     *             @OA\Property(property="seccion", type="string", example="electrónica"),
     *             @OA\Property(property="lema", type="string", example="Innovación y calidad"),
     *             @OA\Property(property="descripcion", type="string", example="Descripción detallada del producto ejemplo"),
     *             @OA\Property(
     *                 property="especificaciones",
     *                 type="object",
     *                 @OA\Property(property="color", type="string", example="rojo"),
     *                 @OA\Property(property="material", type="string", example="aluminio")
     *             ),
     *             @OA\Property(
     *                 property="dimensiones",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="tipo", type="string", example="alto"),
     *                     @OA\Property(property="valor", type="string", example="30cm")
     *                 )
     *             ),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-28T21:52:09.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-28T21:52:09.000000Z"),
     *             @OA\Property(
     *                 property="imagenes",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=23),
     *                     @OA\Property(property="producto_id", type="integer", example=11),
     *                     @OA\Property(property="url_imagen", type="string", example="/storage/imagenes/imagen.jpg"),
     *                     @OA\Property(property="texto_alt_SEO", type="string", example="Texto SEO para imagen"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-28T21:52:09.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-28T21:52:09.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Hubo un error en el servidor")
     *         )
     *     ),
     *     security={}
     * )
     */

    public function showByLink($link)
    {
        try {
            $producto = Producto::with(['dimensiones', 'imagenes'])
                ->where('link', $link)
                ->first();

            if ($producto === null) {
                return response()->json(["message" => "Producto no encontrado"], 404);
            }

            $formattedProducto = [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'link' => $producto->link,
                'titulo' => $producto->titulo,
                'subtitulo' => $producto->subtitulo,
                'stock' => $producto->stock,
                'precio' => $producto->precio,
                'seccion' => $producto->seccion,
                'lema' => $producto->lema,
                'descripcion' => $producto->descripcion,
                'especificaciones' => json_decode($producto->especificaciones, true) ?? [],
                'dimensiones' => $producto->dimensiones,
                'created_at' => $producto->created_at,
                'updated_at' => $producto->updated_at,
                'imagenes' => $producto->imagenes,
            ];

            return response()->json([
                'message' => 'Producto encontrado exitosamente',
                'data' => $formattedProducto
            ], 200);

        } catch (\Exception $e) {
            return response()->json(["message" => "Hubo un error en el servidor"], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Actualizar un producto específico
     * 
     * @OA\Post(
     *     path="/api/v2/productos/{id}",
     *     summary="Actualiza un producto específico (no funciona en Swagger)",
     *     description="Actualiza producto, elimina todas las antiguas imagenes y guarda las nuevas imagen en el servidor. Si lo intentas usar en Swagger no funcionará, pero si lo pruebas desde Postman si funciona. Los campos a enviar ya sea o desde Postman o desde un frontend son los mismos listados a continuación.",
     *     operationId="updateProducto2",
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
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={
     *                     "nombre", "titulo", "subtitulo", "stock", "precio", 
     *                     "seccion", "lema", "descripcion", "especificaciones",
     *                      "imagenes", "textos_alt", "mensaje_correo", "_method"
     *                 },
     *                 @OA\Property(property="nombre", type="string", example="Selladora"),
     *                 @OA\Property(property="titulo", type="string", example="Titulo increíble"),
     *                 @OA\Property(property="subtitulo", type="string", example="Subtitulo increíble"),
     *                 @OA\Property(property="stock", type="integer", example=20),
     *                 @OA\Property(property="precio", type="number", format="float", example=100.50),
     *                 @OA\Property(property="seccion", type="string", example="Decoración"),
     *                 @OA\Property(property="lema", type="string", example="Lema increíble"),
     *                 @OA\Property(property="descripcion", type="string", example="Descripción increíble"),
     *                 @OA\Property(property="especificaciones", type="string", example="Especificaciones increíbles"),
     *                 
     *                 @OA\Property(
     *                     property="imagenes",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Array de imágenes a subir"
     *                 ),
     *                 
     *                 @OA\Property(
     *                     property="textos_alt",
     *                     type="array",
     *                     @OA\Items(type="string", example="Texto ALT para la imagen"),
     *                     description="Array de textos alternativos para las imágenes"
     *                 ),
     *                 
     *                 @OA\Property(property="mensaje_correo", type="string", example="Mensaje increíble"),
     *                 @OA\Property(property="_method", type="string", example="PUT")
     *             )
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
    public function update(V2UpdateProductoRequest $request, string $id)
    {
        //
        $producto = Producto::with("imagenes")->find($id);
        if ($producto == null) {
            return response()->json(["message"=>"Producto no encontrado"], status: 404);
        }
        $datosValidados = $request->validated();
        $imagenes = $datosValidados["imagenes"];
        $textos = $datosValidados["textos_alt"];
        $imagenesArray = $producto->imagenes->toArray();
        $productoImagenes = array_map(function ($x) {
            $archivo = str_ireplace("/storage/imagenes/", "", $x["url_imagen"]);
            return $archivo;
        }, $imagenesArray);
        foreach ($productoImagenes as $imagen) {
            Storage::delete("imagenes/" . $imagen);
        }
        $imagenesProcesadas = [];
        foreach ($imagenes as $i => $img) {
            $url = $this->guardarImagen($img);
            $imagenesProcesadas[] = [
                "url_imagen" => $url,
                "texto_alt_SEO" => $textos[$i]
            ];
        }

        $producto->update([
            "nombre" => $datosValidados["nombre"],
            "link" => $datosValidados["link"],
            "titulo" => $datosValidados["titulo"],
            "subtitulo" => $datosValidados["subtitulo"],
            "stock" => $datosValidados["stock"],
            "precio" => $datosValidados["precio"],
            "seccion" => $datosValidados["seccion"],
            "lema" => $datosValidados["lema"],
            "descripcion" => $datosValidados["descripcion"],
            "especificaciones" => json_encode($datosValidados["especificaciones"]),
        ]);

        $producto->productosRelacionados()->sync($datosValidados['relacionados']);
        $producto->imagenes()->delete();
        $producto->imagenes()->createMany($imagenesProcesadas);
        
        return response()->json(["message"=>"Producto actualizado exitosamente"], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Eliminar un producto específico
     * 
     * @OA\Delete(
     *     path="/api/v2/productos/{id}",
     *     summary="Elimina un producto específico",
     *     description="Elimina un producto existente según su ID",
     *     operationId="destroyProducto2",
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
    public function destroy(string $id)
    {
        //
        DB::beginTransaction();
        try {
            $producto = Producto::with("imagenes")->find($id);
            if ($producto == null) {
                return response()->json(["message" => "Producto no encontrado"], status: 404);
            }
            $imagenesArray = $producto->imagenes->toArray();
            $productoImagenes = array_map(function ($x) {
                $archivo = str_ireplace("/storage/imagenes/", "", $x["url_imagen"]);
                return $archivo;
            }, $imagenesArray);
            foreach ($productoImagenes as $imagen) {
                Storage::delete("imagenes/" . $imagen);
            }

            $producto->delete();
            DB::commit();
            return response()->json(["message" => "Producto eliminado exitosamente"], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["message"=>"Hubo un error en el servidor"], 500);
        }
    }
}