<?php

namespace App\Http\Controllers\Api\V1\Blog;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostBlog\PostStoreBlog;
use App\Http\Requests\PostBlog\UpdateBlog;
use App\Services\ApiResponseService;
use App\Services\ImageService;
use App\Models\Blog;
use App\Http\Contains\HttpStatusCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    protected ApiResponseService $apiResponse;
    protected ImageService $imageService;

    public function __construct(ApiResponseService $apiResponse, ImageService $imageService)
    {
        $this->apiResponse = $apiResponse;
        $this->imageService = $imageService;
    }

    /**
     * @OA\Get(
     *     path="/api/blogs",
     *     tags={"Blogs"},
     *     summary="Obtener todos los blogs",
     *     description="Retorna una lista de todos los blogs con sus imágenes, párrafos y producto asociado",
     *     operationId="getBlogsIndex",
     *     @OA\Response(
     *         response=200,
     *         description="Blogs obtenidos exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Blogs obtenidos exitosamente"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         description="ID único del blog",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="nombre_producto",
     *                         type="string",
     *                         nullable=true,
     *                         description="Nombre del producto asociado al blog",
     *                         example="Smartphone XYZ"
     *                     ),
     *                     @OA\Property(
     *                         property="subtitulo",
     *                         type="string",
     *                         description="Subtítulo del blog",
     *                         example="Descubre las últimas innovaciones tecnológicas"
     *                     ),
     *                     @OA\Property(
     *                         property="imagen_principal",
     *                         type="string",
     *                         description="URL de la imagen principal del blog",
     *                         example="/images/blog/principal_1.jpg"
     *                     ),
     *                     @OA\Property(
     *                         property="imagenes",
     *                         type="array",
     *                         description="Array de imágenes asociadas al blog",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(
     *                                 property="ruta_imagen",
     *                                 type="string",
     *                                 description="Ruta de la imagen",
     *                                 example="/images/blog/imagen_1.jpg"
     *                             ),
     *                             @OA\Property(
     *                                 property="text_alt",
     *                                 type="string",
     *                                 description="Texto alternativo para la imagen",
     *                                 example="Vista frontal del producto"
     *                             )
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="parrafos",
     *                         type="array",
     *                         description="Array de párrafos del contenido del blog",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(
     *                                 property="parrafo",
     *                                 type="string",
     *                                 description="Contenido del párrafo",
     *                                 example="Este es el contenido del párrafo del blog que describe las características principales..."
     *                             )
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="created_at",
     *                         type="string",
     *                         format="date-time",
     *                         description="Fecha y hora de creación del blog",
     *                         example="2024-01-15T10:30:00.000000Z"
     *                     ),
     *                     @OA\Property(
     *                         property="updated_at",
     *                         type="string",
     *                         format="date-time",
     *                         description="Fecha y hora de última actualización del blog",
     *                         example="2024-01-20T14:45:00.000000Z"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Error al obtener los blogs: Database connection failed"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="null",
     *                 example=null
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $blog = Blog::with(['imagenes', 'parrafos', 'producto'])->get();

            $showBlog = $blog->map(function ($blog) {
                return [
                    'id' => $blog->id,
                    'nombre_producto' => $blog->producto ? $blog->producto->nombre : null,
                    'subtitulo' => $blog->subtitulo,
                    'imagen_principal' => $blog->imagen_principal,
                    'imagenes' => $blog->imagenes->map(function ($imagen) {
                        return [
                            'ruta_imagen' => $imagen->ruta_imagen,
                            'text_alt' => $imagen->text_alt,
                        ];
                    }),
                    'parrafos' => $blog->parrafos->map(function ($parrafo) {
                        return [
                            'parrafo' => $parrafo->parrafo,
                        ];
                    }),
                    'created_at' => $blog->created_at,
                    'updated_at' => $blog->updated_at
                ];
            });

            return $this->apiResponse->successResponse(
                $showBlog,
                'Blogs obtenidos exitosamente',
                HttpStatusCode::OK
            );
        } catch (\Exception $e) {
            return $this->apiResponse->errorResponse(
                'Error al obtener los blogs: ' . $e->getMessage(),
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/blogs/{id}",
     *     tags={"Blogs"},
     *     summary="Obtener un blog específico",
     *     description="Retorna los detalles de un blog específico con sus imágenes, párrafos y producto asociado",
     *     operationId="getBlogById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del blog a obtener",
     *         @OA\Schema(
     *             type="integer",
     *             minimum=1,
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog obtenido exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Blog obtenido exitosamente"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     description="ID único del blog",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="nombre_producto",
     *                     type="string",
     *                     nullable=true,
     *                     description="Nombre del producto asociado al blog",
     *                     example="Smartphone XYZ"
     *                 ),
     *                 @OA\Property(
     *                     property="subtitulo",
     *                     type="string",
     *                     description="Subtítulo del blog",
     *                     example="Descubre las últimas innovaciones tecnológicas"
     *                 ),
     *                 @OA\Property(
     *                     property="imagen_principal",
     *                     type="string",
     *                     description="URL de la imagen principal del blog",
     *                     example="/images/blog/principal_1.jpg"
     *                 ),
     *                 @OA\Property(
     *                     property="imagenes",
     *                     type="array",
     *                     description="Array de imágenes asociadas al blog",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(
     *                             property="ruta_imagen",
     *                             type="string",
     *                             description="Ruta de la imagen",
     *                             example="/images/blog/imagen_1.jpg"
     *                         ),
     *                         @OA\Property(
     *                             property="texto_alt",
     *                             type="string",
     *                             description="Texto alternativo para la imagen",
     *                             example="Vista frontal del producto"
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="parrafos",
     *                     type="array",
     *                     description="Array de párrafos del contenido del blog",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(
     *                             property="parrafo",
     *                             type="string",
     *                             description="Contenido del párrafo",
     *                             example="Este es el contenido del párrafo del blog que describe las características principales del producto..."
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="created_at",
     *                     type="string",
     *                     format="date-time",
     *                     description="Fecha y hora de creación del blog",
     *                     example="2024-01-15T10:30:00.000000Z"
     *                 ),
     *                 @OA\Property(
     *                     property="updated_at",
     *                     type="string",
     *                     format="date-time",
     *                     description="Fecha y hora de última actualización del blog",
     *                     example="2024-01-20T14:45:00.000000Z"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog no encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Error al obtener el blog: No query results for model [App\\Models\\Blog] 1"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="null",
     *                 example=null
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Error al obtener el blog: Database connection failed"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="null",
     *                 example=null
     *             )
     *         )
     *     )
     * )
     */
    public function show(int $id)
    {
        try {
            $blog = Blog::with(['imagenes', 'parrafos', 'producto'])
                ->findOrFail($id);

            $showBlog = [
                'id' => $blog->id,
                'nombre_producto' => $blog->producto ? $blog->producto->nombre : null,
                'subtitulo' => $blog->subtitulo,
                'imagen_principal' => $blog->imagen_principal,
                'imagenes' => $blog->imagenes->map(function ($imagen) {
                    return [
                        'ruta_imagen' => $imagen->ruta_imagen,
                        'texto_alt' => $imagen->text_alt,
                    ];
                }),
                'parrafos' => $blog->parrafos->map(function ($parrafo) {
                    return [
                        'parrafo' => $parrafo->parrafo,
                    ];
                }),
                'created_at' => $blog->created_at,
                'updated_at' => $blog->updated_at
            ];

            return $this->apiResponse->successResponse(
                $showBlog,
                'Blog obtenido exitosamente',
                HttpStatusCode::OK
            );
        } catch (\Exception $e) {
            return $this->apiResponse->errorResponse(
                'Error al obtener el blog: ' . $e->getMessage(),
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/blogs",
     *     tags={"Blogs"},
     *     summary="Crear un nuevo blog",
     *     description="Crea un nuevo blog con imagen principal, imágenes adicionales opcionales y párrafos de contenido",
     *     operationId="createBlog",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"producto_id", "subtitulo", "imagen_principal", "parrafos"},
     *                 @OA\Property(
     *                     property="producto_id",
     *                     type="integer",
     *                     description="ID del producto asociado al blog",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="subtitulo",
     *                     type="string",
     *                     description="Subtítulo del blog",
     *                     example="Descubre las últimas innovaciones tecnológicas"
     *                 ),
     *                 @OA\Property(
     *                     property="imagen_principal",
     *                     type="string",
     *                     format="binary",
     *                     description="Archivo de imagen principal del blog (requerido)"
     *                 ),
     *                 @OA\Property(
     *                     property="imagenes[]",
     *                     type="array",
     *                     description="Array de archivos de imágenes adicionales (opcional)",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="parrafos[]",
     *                     type="array",
     *                     description="Array de párrafos de contenido del blog",
     *                     @OA\Items(
     *                         type="string",
     *                         example="Este es un párrafo de contenido del blog que describe las características del producto..."
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Blog creado con éxito",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Blog creado con éxito."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     description="ID único del blog creado",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="producto_id",
     *                     type="integer",
     *                     description="ID del producto asociado",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="subtitulo",
     *                     type="string",
     *                     description="Subtítulo del blog",
     *                     example="Descubre las últimas innovaciones tecnológicas"
     *                 ),
     *                 @OA\Property(
     *                     property="imagen_principal",
     *                     type="string",
     *                     description="URL de la imagen principal guardada",
     *                     example="/images/blog/principal_1.jpg"
     *                 ),
     *                 @OA\Property(
     *                     property="created_at",
     *                     type="string",
     *                     format="date-time",
     *                     description="Fecha y hora de creación",
     *                     example="2024-01-15T10:30:00.000000Z"
     *                 ),
     *                 @OA\Property(
     *                     property="updated_at",
     *                     type="string",
     *                     format="date-time",
     *                     description="Fecha y hora de última actualización",
     *                     example="2024-01-15T10:30:00.000000Z"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The given data was invalid."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="producto_id",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="El campo producto id es obligatorio."
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="subtitulo",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="El campo subtitulo es obligatorio."
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="imagen_principal",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="El campo imagen principal es obligatorio."
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Error al crear el blog: No se recibió imagen_principal como archivo"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="null",
     *                 example=null
     *             )
     *         )
     *     )
     * )
     */

    public function store(PostStoreBlog $request)
    {
        $datosValidados = $request->validated();
        DB::beginTransaction();

        try {
            if (!$request->hasFile('imagen_principal')) {
                throw new \Exception('No se recibió imagen_principal como archivo');
            }

            $imagenPrincipal = $request->file("imagen_principal");
            $rutaImagenPrincipal = $this->imageService->guardarImagen($imagenPrincipal);

            $blog = Blog::create([
                "producto_id" => $datosValidados["producto_id"],
                "subtitulo" => $datosValidados["subtitulo"],
                "imagen_principal" => $rutaImagenPrincipal,
            ]);

            // Guardar imágenes solo si se envían
            if ($request->hasFile('imagenes')) {
                $imagenes = $request->file('imagenes');
                $nombreProducto = $blog->producto ? $blog->producto->nombre : '';
                foreach ($imagenes as $i => $imagen) {
                    $ruta = $this->imageService->guardarImagen($imagen);
                    $blog->imagenes()->create([
                        "ruta_imagen" => $ruta,
                        "text_alt" => 'Imagen del blog ' . $nombreProducto
                    ]);
                }
            }
            foreach ($datosValidados["parrafos"] as $item) {
                $blog->parrafos()->createMany([
                    ["parrafo" => $item]
                ]);
            }
            DB::commit();
            return $this->apiResponse->successResponse($blog->fresh(), 'Blog creado con éxito.', HttpStatusCode::CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->apiResponse->errorResponse(
                'Error al crear el blog: ' . $e->getMessage(),
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/blog/{id}",
     *     tags={"Blogs"},
     *     summary="Actualizar un blog existente",
     *     description="Actualiza un blog existente. Todos los campos son opcionales. Si se envían imágenes o párrafos, reemplazarán completamente los existentes.",
     *     operationId="updateBlog",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del blog a actualizar",
     *         @OA\Schema(
     *             type="integer",
     *             minimum=1,
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="_method",
     *         in="query",
     *         required=false,
     *         description="Método HTTP para override (usar PATCH para form-data)",
     *         @OA\Schema(
     *             type="string",
     *             enum={"PATCH"},
     *             example="PATCH"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="producto_id",
     *                     type="integer",
     *                     description="ID del producto asociado al blog (opcional)",
     *                     example=2
     *                 ),
     *                 @OA\Property(
     *                     property="subtitulo",
     *                     type="string",
     *                     description="Subtítulo del blog (opcional)",
     *                     example="Nueva descripción actualizada del producto"
     *                 ),
     *                 @OA\Property(
     *                     property="imagen_principal",
     *                     type="string",
     *                     format="binary",
     *                     description="Nuevo archivo de imagen principal (opcional). Si se envía, reemplaza la imagen actual"
     *                 ),
     *                 @OA\Property(
     *                     property="imagenes[]",
     *                     type="array",
     *                     description="Array de nuevos archivos de imágenes (opcional). Si se envía, reemplaza todas las imágenes existentes",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="parrafos[]",
     *                     type="array",
     *                     description="Array de párrafos actualizados (opcional). Si se envía, reemplaza todos los párrafos existentes",
     *                     @OA\Items(
     *                         type="string",
     *                         example="Este es un párrafo actualizado del contenido del blog..."
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog actualizado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Blog actualizado exitosamente"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     description="ID único del blog",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="producto_id",
     *                     type="integer",
     *                     description="ID del producto asociado",
     *                     example=2
     *                 ),
     *                 @OA\Property(
     *                     property="subtitulo",
     *                     type="string",
     *                     description="Subtítulo actualizado del blog",
     *                     example="Nueva descripción actualizada del producto"
     *                 ),
     *                 @OA\Property(
     *                     property="imagen_principal",
     *                     type="string",
     *                     description="URL de la imagen principal",
     *                     example="/images/blog/principal_updated_1.jpg"
     *                 ),
     *                 @OA\Property(
     *                     property="created_at",
     *                     type="string",
     *                     format="date-time",
     *                     description="Fecha y hora de creación original",
     *                     example="2024-01-15T10:30:00.000000Z"
     *                 ),
     *                 @OA\Property(
     *                     property="updated_at",
     *                     type="string",
     *                     format="date-time",
     *                     description="Fecha y hora de última actualización",
     *                     example="2024-01-22T15:45:00.000000Z"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog no encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Error al actualizar el blog: No query results for model [App\\Models\\Blog] 1"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="null",
     *                 example=null
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The given data was invalid."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="producto_id",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="El producto seleccionado no es válido."
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="subtitulo",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="El campo subtitulo debe ser una cadena de caracteres."
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Error al actualizar el blog: Database connection failed"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="null",
     *                 example=null
     *             )
     *         )
     *     )
     * )
     */
    public function update(UpdateBlog $request, $id)
    {
        Log::info('PATCH Blog Request received:', ['request_all' => $request->all(), 'id' => $id]);
        $datosValidados = $request->validated();
        Log::info('Validated data:', ['datos_validados' => $datosValidados]);

        DB::beginTransaction();
        $blog = Blog::findOrFail($id);

        try {
            $camposActualizar = [];
            foreach (["producto_id", "subtitulo"] as $campo) {
                if (array_key_exists($campo, $datosValidados)) {
                    $camposActualizar[$campo] = $datosValidados[$campo];
                }
            }

            if ($request->hasFile('imagen_principal')) {
                $nuevaRutaImagenPrincipal = $this->imageService->actualizarImagen(
                    $request->file('imagen_principal'),
                    $blog->imagen_principal
                );
                $camposActualizar['imagen_principal'] = $nuevaRutaImagenPrincipal;
            }

            Log::info('Fields to update:', ['campos_actualizar' => $camposActualizar]);
            $blog->update($camposActualizar);

            if ($request->hasFile('imagenes')) {
                $rutasImagenesAntiguas = $blog->imagenes->pluck('ruta_imagen')->toArray();

                if (!empty($rutasImagenesAntiguas)) {
                    $this->imageService->eliminarImagenes($rutasImagenesAntiguas);
                }
                $blog->imagenes()->delete();
                $imagenes = $request->file('imagenes');
                $nombreProducto = $blog->producto ? $blog->producto->nombre : '';

                foreach ($imagenes as $imagen) {
                    $ruta = $this->imageService->guardarImagen($imagen);
                    $blog->imagenes()->create([
                        "ruta_imagen" => $ruta,
                        "text_alt" => 'Imagen del blog ' . $nombreProducto
                    ]);
                }
            }
            if (isset($datosValidados['parrafos'])) {
                $blog->parrafos()->delete();
                foreach ($datosValidados["parrafos"] as $item) {
                    $blog->parrafos()->create([
                        "parrafo" => $item
                    ]);
                }
            }
            DB::commit();
            return $this->apiResponse->successResponse(
                $blog,
                'Blog actualizado exitosamente',
                HttpStatusCode::OK
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->apiResponse->errorResponse(
                'Error al actualizar el blog: ' . $e->getMessage(),
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Eliminar un blog específico
     * 
     * @OA\Delete(
     *     path="/api/blogs/{id}",
     *     summary="Elimina un blog específico",
     *     description="Elimina un blog existente según su ID",
     *     operationId="destroyBlog",
     *     tags={"Blogs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del blog a eliminar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Blog eliminado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor"
     *     )
     * )
     */

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $blog = Blog::findOrFail($id);
            $rutasImagenes = $blog->imagenes->pluck('ruta_imagen')->toArray();

            if ($blog->imagen_principal) {
                $rutasImagenes[] = $blog->imagen_principal;
            }

            $blog->imagenes()->delete();
            $blog->parrafos()->delete();
            if (!empty($rutasImagenes)) {
                $this->imageService->eliminarImagenes($rutasImagenes);
            }
            $blog->delete();

            DB::commit();

            return $this->apiResponse->successResponse(
                null,
                'Blog eliminado exitosamente',
                HttpStatusCode::OK
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->apiResponse->errorResponse(
                'Error al eliminar el blog: ' . $e->getMessage(),
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }
}
