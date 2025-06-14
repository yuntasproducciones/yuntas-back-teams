<?php

namespace App\Http\Controllers\Api\V1\Reclamos;

use App\Http\Contains\HttpStatusCode;
use App\Http\Controllers\Api\V1\BasicController;
use App\Http\Requests\PostReclamo\PostReclamo;
use App\Models\DatosPersonal;
use App\Models\Reclamo;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException; // Importar ModelNotFoundException

/**
 * @OA\Tag(
 *     name="Reclamos",
 *     description="API Endpoints de reclamos"
 * )
 */
class ReclamosController extends BasicController
{
    /**
     * Obtener listado de reclamos
     *
     * @OA\Get(
     *     path="/api/v1/reclamos",
     *     summary="Muestra un listado de todos los reclamos",
     *     description="Retorna un array con todos los reclamos y sus relaciones",
     *     operationId="indexReclamos",
     *     tags={"Reclamos"},
     *     @OA\Response(
     *         response=200,
     *         description="Operación exitosa",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="datos", type="string", example="Información del cliente"),
     *                     @OA\Property(property="tipo_doc", type="string", example="DNI"),
     *                     @OA\Property(property="numero_doc", type="string", example="12345678"),
     *                     @OA\Property(property="correo", type="string", format="email", example="cliente@example.com"),
     *                     @OA\Property(property="telefono", type="string", example="987654321"),
     *                     @OA\Property(property="fecha_compra", type="string", format="date", example="2025-03-21"),
     *                     @OA\Property(property="producto", type="string", example="Producto XYZ"),
     *                     @OA\Property(property="detalle_reclamo", type="string", example="Descripción del reclamo"),
     *                     @OA\Property(property="monto_reclamo", type="number", format="float", example=99.99)
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Reclamos obtenidos exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor"
     *     )
     * )
     */
    public function index()
    {
        try {
            $personal = DatosPersonal::with("reclamos")->get();

            $reclamos = $personal->map(function ($datos) {
                return [
                    'id' => $datos->id,
                    'datos' => $datos->datos,
                    'tipo_doc' => $datos->tipo_doc, 
                    'numero_doc' => $datos->numero_doc,
                    'correo' => $datos->correo,
                    'telefono' => $datos->telefono,
                    'fecha_compra' => $datos->reclamos->pluck('fecha_compra'),
                    'producto' => $datos->reclamos->pluck('producto'),
                    'detalle_reclamo' => $datos->reclamos->pluck('detalle_reclamo'),
                    'monto_reclamo' => $datos->reclamos->pluck('monto_reclamo'),
                ];
            });

            return $this->successResponse($reclamos, 'Reclamos obtenidos exitosamente', HttpStatusCode::OK);

        } catch(\Exception $e) {
            return $this->errorResponse('Error al mostrar los reclamos: ' . $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Crear un nuevo reclamo
     * 
     * @OA\Post(
     *     path="/api/v1/reclamos",
     *     summary="Crea un nuevo reclamo",
     *     description="Almacena un nuevo reclamo y retorna los datos creados",
     *     operationId="storeReclamo",
     *     tags={"Reclamos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="datos", type="string", example="pepito alcachofa"),
     *             required={"tipo_doc", "numero_doc", "correo", "telefono", "reclamos"},
     *             @OA\Property(property="tipo_doc", type="string", example="DNI"),
     *             @OA\Property(property="numero_doc", type="string", example="12345678"),
     *             @OA\Property(property="correo", type="string", format="email", example="usuario@example.com"),
     *             @OA\Property(property="telefono", type="string", example="987654321"),
     *             @OA\Property(property="reclamos", type="array", @OA\Items(
     *                 @OA\Property(property="fecha_compra", type="string", format="date", example="2024-03-21"),
     *                 @OA\Property(property="producto", type="string", example="Producto XYZ"),
     *                 @OA\Property(property="detalle_reclamo", type="string", example="Descripción del reclamo"),
     *                 @OA\Property(property="monto_reclamo", type="number", format="float", example=99.99)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Reclamo creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Reclamo creado exitosamente")
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
    public function store(PostReclamo $request)
    {
        try {
            DB::beginTransaction();
            
            $personal = DatosPersonal::create($request->except('reclamos'));

            if ($request->has('reclamos') && is_array($request->input('reclamos'))) {
                $datos = collect($request->input('reclamos'))->map(function ($item) use ($personal) {
                    return [
                        'fecha_compra' => $item['fecha_compra'],
                        'producto' => $item['producto'],
                        'detalle_reclamo' => $item['detalle_reclamo'],
                        'monto_reclamo' => $item['monto_reclamo'], 
                        'id_data' => $personal->id, 
                    ];
                })->toArray();
            
                Reclamo::insert($datos);
            }

            DB::commit();
            return $this->successResponse($personal, 'Reclamo creado exitosamente', HttpStatusCode::CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al crear el reclamo: ' . $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mostrar un reclamo específico
     * 
     * @OA\Get(
     *     path="/api/v1/reclamos/{id}",
     *     summary="Muestra un reclamo específico",
     *     description="Retorna los datos de un reclamo según su ID",
     *     operationId="showReclamo",
     *     tags={"Reclamos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del reclamo",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reclamo encontrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="datos", type="string"),
     *                 @OA\Property(property="tipo_doc", type="string", example="DNI"),
     *                 @OA\Property(property="numero_doc", type="string", example="12345678"),
     *                 @OA\Property(property="correo", type="string", format="email", example="usuario@example.com"),
     *                 @OA\Property(property="telefono", type="string", example="987654321"),
     *                 @OA\Property(property="fecha_compra", type="string", format="date", example="2024-03-21"),
     *                 @OA\Property(property="detalle_reclamo", type="string", example="Descripción del reclamo"),
     *                 @OA\Property(property="monto_reclamo", type="number", format="float", example=99.99)
     *             ),
     *             @OA\Property(property="message", type="string", example="Reclamo encontrado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Reclamo no encontrado"
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
            $personal = DatosPersonal::with('reclamos')->findOrFail($id);

            $reclamos = [
                'id' => $personal->id,
                'datos' => $personal->datos,
                'tipo_doc' => $personal->tipo_doc, 
                'numero_doc' => $personal->numero_doc,
                'correo' => $personal->correo,
                'telefono' => $personal->telefono,
                'fecha_compra' => $personal->reclamos->pluck('fecha_compra'),
                'detalle_reclamo' => $personal->reclamos->pluck('detalle_reclamo'),
                'monto_reclamo' => $personal->reclamos->pluck('monto_reclamo'),
            ];

            return $this->successResponse($reclamos, 'Reclamo encontrado exitosamente', HttpStatusCode::OK);

        } catch (\Exception $e) {
            return $this->errorResponse('Error al buscar el reclamo: ' . $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Actualizar un reclamo específico
     * 
     * @OA\Put(
     *     path="/api/v1/reclamos/{id}",
     *     summary="Actualiza un reclamo específico",
     *     description="Actualiza los datos de un reclamo existente según su ID",
     *     operationId="updateReclamo",
     *     tags={"Reclamos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del reclamo a actualizar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="datos", type="string"),
     *             @OA\Property(property="tipo_doc", type="string", example="DNI"),
     *             @OA\Property(property="numero_doc", type="string", example="12345678"),
     *             @OA\Property(property="correo", type="string", format="email", example="usuario@example.com"),
     *             @OA\Property(property="telefono", type="string", example="987654321"),
     *             @OA\Property(property="reclamos", type="array", @OA\Items(
     *                 @OA\Property(property="fecha_compra", type="string", format="date", example="2024-03-21"),
     *                 @OA\Property(property="producto", type="string"),
     *                 @OA\Property(property="detalle_reclamo", type="string"),
     *                 @OA\Property(property="monto_reclamo", type="number", format="float")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reclamo actualizado exitosamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Reclamo no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor"
     *     )
     * )
     */
    public function update(PostReclamo $request, $id)
    {
        try {
            DB::beginTransaction(); 

            $personal = DatosPersonal::findOrFail($id);

            $personal->update([
                'datos' => $request->datos,
                'tipo_doc' => $request->tipo_doc,
                'numero_doc' => $request->numero_doc,
                'correo' => $request->correo,
                'telefono' => $request->telefono,
            ]);

            if ($request->has('reclamos') && is_array($request->input('reclamos'))) {
                $personal->reclamos()->delete();
                collect($request->input('reclamos'))->map(function ($item) use ($personal) {
                    $personal->reclamos()->create([
                        'fecha_compra' => $item['fecha_compra'],
                        'producto' => $item['producto'],
                        'detalle_reclamo' => $item['detalle_reclamo'],
                        'monto_reclamo' => $item['monto_reclamo'], 
                    ]);
                });

            }

            DB::commit();
            return $this->successResponse($personal,'Reclamo actualizado exitosamente', HttpStatusCode::OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al actualizar al reclamo: ' . $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Eliminar un reclamo específico
     * 
     * @OA\Delete(
     *     path="/api/v1/reclamos/{id}",
     *     summary="Elimina un reclamo específico",
     *     description="Elimina un reclamo existente según su ID",
     *     operationId="destroyReclamo",
     *     tags={"Reclamos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del reclamo a eliminar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reclamo eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Reclamo eliminado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Reclamo no encontrado"
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

            $reclamo = DatosPersonal::findOrFail($id);
            $reclamo->delete();

            DB::commit();
            return $this->successNoContentResponse('Reclamo eliminado exitosamente');

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorResponse('Recurso no encontrado', HttpStatusCode::NOT_FOUND, $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al eliminar el reclamo: ' . $e->getMessage(), HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}
