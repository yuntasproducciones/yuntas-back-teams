<?php

namespace App\Http\Controllers\Api\V1\Blog;

use App\Http\Controllers\Api\V1\BasicController;
use App\Models\Blog;
use App\Models\BloqueContenido;
use App\Http\Requests\Blog\StoreBlogRequest;
use App\Http\Requests\Blog\UpdateBlogRequest;
use App\Http\Contains\HttpStatusCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\BlogHeadController;
use App\Http\Controllers\Api\BlogFooterController;
use App\Http\Controllers\Api\TarjetaController;
use App\Http\Controllers\Api\CommendTarjetaController;
use App\Models\BlogBody;

/**
 * @OA\Tag(
 *     name="Blogs",
 *     description="API para la gestión de blogs"
 * )
 */
class BlogController extends BasicController
{
    /**
     * @OA\Get(
     *     path="/api/blogs",
     *     summary="Obtener todos los blogs",
     *     description="Obtiene todos los blogs disponibles con su card asociada",
     *     operationId="getBlogs",
     *     tags={"Blogs"},
     *     @OA\Response(
     *         response=200,
     *         description="Operación exitosa",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Operación exitosa"),
     *             @OA\Property(property="data", type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id_blog", type="integer", example=1),
     *                     @OA\Property(property="link", type="string", example="producto"),
     *                     @OA\Property(property="producto_id", type="integer", example=1),
     *                     @OA\Property(property="id_blog_head", type="integer", example=1),
     *                     @OA\Property(property="id_blog_body", type="integer", example=1),
     *                     @OA\Property(property="id_blog_footer", type="integer", example=1),
     *                     @OA\Property(property="fecha", type="string", example="2025-05-09")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error en el servidor")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $blogs = Blog::with('card', 'producto')->get();
        return response()->json([
            'status' => 200,
            'message' => 'Operación exitosa',
            'data' => $blogs
        ], 200);
    }
    /**
     * @OA\Post(
     *     path="/api/blogs",
     *     summary="Crear un nuevo blog",
     *     description="Crea un nuevo blog con sus atributos",
     *     operationId="createBlog",
     *     tags={"Blogs"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"producto_id","link","id_blog_head", "id_blog_body", "id_blog_footer", "fecha"},
     *             @OA\Property(property="producto_id", type="integer", example=1),
     *             @OA\Property(property="link", type="string", example="producto"),
     *             @OA\Property(property="id_blog_head", type="integer", example=1),
     *             @OA\Property(property="id_blog_body", type="integer", example=1),
     *             @OA\Property(property="id_blog_footer", type="integer", example=1),
     *             @OA\Property(property="fecha", type="string", format="date", example="2025-05-09")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Blog creada correctamente"),
     *             @OA\Property(property="id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de entrada inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error en el servidor")
     *         )
     *     )
     * )
     */
    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'producto_id' => 'required|integer|exists:productos,id',
                'link' => 'required|string|max:255',
                'id_blog_head' => 'required|integer|exists:blog_heads,id_blog_head',
                'id_blog_body' => 'required|integer|exists:blog_bodies,id_blog_body',
                'id_blog_footer' => 'required|integer|exists:blog_footers,id_blog_footer',
                'fecha' => 'required|date'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            DB::beginTransaction();

            $blog = Blog::create($request->all());

            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => "Blog creada correctamente",
                "id" => $blog->id_blog
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/blogs/{id}",
     *     summary="Obtener un blog específico",
     *     description="Obtiene los detalles de un blog específico usando su ID",
     *     operationId="showBlog",
     *     tags={"Blogs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del blog",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Operación exitosa",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Blog encontrado"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id_blog", type="integer", example=1),
     *                 @OA\Property(property="link", type="string", example="producto"),
     *                 @OA\Property(property="producto_id", type="integer", example=1),
     *                 @OA\Property(property="id_blog_head", type="integer", example=1),
     *                 @OA\Property(property="id_blog_body", type="integer", example=1),
     *                 @OA\Property(property="id_blog_footer", type="integer", example=1),
     *                 @OA\Property(property="fecha", type="string", example="2025-05-09")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Blog no encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error en el servidor")
     *         )
     *     )
     * )
     */
    public function show(int $id)
    {
        try {
            $blog = Blog::with('card', 'producto')->find($id);

            if (!$blog) {
                return response()->json([
                    "status" => 404,
                    "message" => "Blog no encontrada"
                ], 404);
            }

            return response()->json([
                "status" => 200,
                'data' => $blog
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/blogs/{id}",
     *     summary="Actualizar un blog existente",
     *     description="Actualiza los detalles de un blog específico",
     *     operationId="updateBlog",
     *     tags={"Blogs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del blog a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"producto_id", "link","id_blog_head", "id_blog_body", "id_blog_footer", "fecha"},
     *             @OA\Property(property="producto_id", type="integer", example=1),
     *             @OA\Property(property="link", type="string", example="producto"),
     *             @OA\Property(property="id_blog_head", type="integer", example=1),
     *             @OA\Property(property="id_blog_body", type="integer", example=1),
     *             @OA\Property(property="id_blog_footer", type="integer", example=1),
     *             @OA\Property(property="fecha", type="string", format="date", example="2025-05-09")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Blog actualizado"),
     *             @OA\Property(property="id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Blog no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de entrada inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error en el servidor")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'producto_id' => 'required|integer|exists:productos,id',
                'link' => 'required|string|max:255',
                'id_blog_head' => 'required|integer|exists:blog_heads,id_blog_head',
                'id_blog_body' => 'required|integer|exists:blog_bodies,id_blog_body',
                'id_blog_footer' => 'required|integer|exists:blog_footers,id_blog_footer',
                'fecha' => 'required|date'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            $blog = Blog::find($id);

            if (!$blog) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Blog no encontrado'
                ], 404);
            }

            DB::beginTransaction();

            $blog->update($request->all());

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Blog actualizado',
                'id' => $blog->id_blog,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/blogs/{id}",
     *     summary="Eliminar un blog",
     *     description="Elimina un blog específico usando su ID",
     *     operationId="destroyBlog",
     *     tags={"Blogs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del blog a eliminar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog eliminado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Blog eliminado correctamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Blog no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error en el servidor")
     *         )
     *     )
     * )
     */
    public function destroy(int $id)
    {
        try {
            $blog = Blog::with(['card', 'head'])->find($id);

            if (!$blog) {
                return response()->json([
                    "status" => 404,
                    "message" => "Blog no encontrado"
                ], 404);
            }

            $id_header_blog = $blog->id_blog_head;
            $id_body_blog = $blog->id_blog_body;
            $id_footer_blog = $blog->id_blog_footer;

            $relativePath = "images/templates/plantilla{$blog->card->id_plantilla}/" . Str::slug($blog->head->titulo) . $blog->id_blog;

            // Eliminar directorio de imágenes si existe
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->deleteDirectory($relativePath);
            }

            // Eliminar card
            $card_object = new CardController();
            $card_object->destroy($blog->card->id_card);

            // Eliminar blog
            $blog->delete();

            // Eliminar blog_head
            $blog_head = new BlogHeadController();
            $blog_head->destroy($id_header_blog);

            // Eliminar blog_footer
            $blog_footer = new BlogFooterController();
            $blog_footer->destroy($id_footer_blog);

            // Eliminar tarjeta
            $tarjeta = new TarjetaController();
            $tarjeta->destroyAll($id_body_blog);

            // Eliminar commend_tarjeta
            $blog_body_model = BlogBody::find($id_body_blog);
            $commend_tarjeta = new CommendTarjetaController();
            $commend_tarjeta->destroy($blog_body_model->id_commend_tarjeta);

            // Eliminar blog_body
            $blog_body_model->delete();

            return response()->json([
                "status" => 200,
                "message" => "Blog eliminado correctamente"
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/blogs/link/{link}",
     *     summary="Obtener un blog por su campo link",
     *     description="Retorna un blog específico buscando por el valor único del campo 'link'.",
     *     operationId="getBlogByLink",
     *     tags={"Blogs"},
     *     @OA\Parameter(
     *         name="link",
     *         in="path",
     *         required=true,
     *         description="Valor del campo 'link' para buscar el blog",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog encontrado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id_blog", type="integer", example=123),
     *             @OA\Property(property="link", type="string", example="mi-link-unico"),
     *             @OA\Property(property="producto_id", type="integer", example=10),
     *             @OA\Property(property="id_blog_head", type="integer", example=1),
     *             @OA\Property(property="id_blog_body", type="integer", example=1),
     *             @OA\Property(property="id_blog_footer", type="integer", example=1),
     *             @OA\Property(property="fecha", type="string", example="2025-05-09")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog no encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Blog no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Error en el servidor")
     *         )
     *     )
     * )
     */

    public function getByLink($link)
    {
        $blog = Blog::where('link', $link)->first();
        if (!$blog) {
            return response()->json(['message' => 'Blog no encontrado'], 404);
        }
        return response()->json($blog);
    }
}
