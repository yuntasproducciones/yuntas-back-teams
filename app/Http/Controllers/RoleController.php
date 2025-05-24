<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

/**
 * @OA\Tag(
 *     name="Roles",
 *     description="Operaciones relacionadas con roles"
 * )
 */
class RoleController extends Controller
{
/**
 * @OA\Get(
 *     path="/api/roles",
 *     summary="Listar todos los roles",
 *     description="Obtiene una lista con todos los roles disponibles",
 *     operationId="indexRoles",
 *     tags={"Roles"},
 *     @OA\Response(
 *         response=200,
 *         description="Lista de roles obtenida correctamente",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="ADMIN"),
 *                 @OA\Property(property="guard_name", type="string", example="web"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00Z")
 *             )
 *         )
 *     )
 * )
 */
    public function index()
    {
        return response()->json(Role::all());
    }
/**
 * @OA\Post(
 *     path="/api/roles",
 *     summary="Crear un nuevo rol",
 *     description="Crea un nuevo rol con un nombre único",
 *     operationId="storeRole",
 *     tags={"Roles"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="ADMIN", description="Nombre único del rol")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Rol creado exitosamente",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="ADMIN"),
 *             @OA\Property(property="guard_name", type="string", example="web"),
 *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00Z")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
        ]);

        $role = Role::create(['name' => $request->name]);

        return response()->json($role, 201);
    }

/**
 * @OA\Get(
 *     path="/api/roles/{id}",
 *     summary="Mostrar un rol específico",
 *     description="Obtiene un rol por su ID",
 *     operationId="showRole",
 *     tags={"Roles"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID del rol a obtener",
 *         required=true,
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Rol encontrado",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="ADMIN"),
 *             @OA\Property(property="guard_name", type="string", example="web"),
 *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00Z")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Rol no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Role not found")
 *         )
 *     )
 * )
 */
    public function show($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        return response()->json($role);
    }

/**
 * @OA\Put(
 *     path="/api/roles/{id}",
 *     summary="Actualizar un rol",
 *     description="Actualiza el nombre de un rol existente",
 *     operationId="updateRole",
 *     tags={"Roles"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID del rol a actualizar",
 *         required=true,
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="ADMIN_UPDATED", description="Nuevo nombre del rol")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Rol actualizado exitosamente",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="ADMIN_UPDATED"),
 *             @OA\Property(property="guard_name", type="string", example="web"),
 *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-05-01T12:00:00Z")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Rol no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Role not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Error de validación",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
        ]);

        $role->update(['name' => $request->name]);

        return response()->json($role);
    }

/**
 * @OA\Delete(
 *     path="/api/roles/{id}",
 *     summary="Eliminar un rol",
 *     description="Elimina un rol por su ID",
 *     operationId="destroyRole",
 *     tags={"Roles"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID del rol a eliminar",
 *         required=true,
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Rol eliminado exitosamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Role deleted successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Rol no encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Role not found")
 *         )
 *     )
 * )
 */
    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted successfully']);
    }
}
