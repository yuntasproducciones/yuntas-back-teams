<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * @OA\Tag(
 *     name="RolePermissions",
 *     description="Gestión de permisos asignados a roles"
 * )
 */
class RolePermissionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/roles/{roleId}/permissions",
     *     tags={"RolePermissions"},
     *     summary="Listar permisos de un rol",
     *     description="Obtiene todos los permisos asignados a un rol específico",
     *     @OA\Parameter(
     *         name="roleId",
     *         in="path",
     *         description="ID del rol",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de permisos del rol",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Permission")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rol no encontrado"
     *     )
     * )
     */
    public function index($roleId)
    {
        $role = Role::with('permissions')->findOrFail($roleId);
        return response()->json($role->permissions);
    }

    /**
     * @OA\Post(
     *     path="/api/roles/{roleId}/permissions",
     *     tags={"RolePermissions"},
     *     summary="Asignar o sincronizar permisos a un rol",
     *     description="Asigna o actualiza la lista de permisos asociados a un rol",
     *     @OA\Parameter(
     *         name="roleId",
     *         in="path",
     *         description="ID del rol",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Lista de permisos para asignar",
     *         @OA\JsonContent(
     *             required={"permissions"},
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="string", example="edit articles")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permisos asignados correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Permisos asignados correctamente"),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Permission")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rol no encontrado"
     *     )
     * )
     */
    public function store(Request $request, $roleId)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::findOrFail($roleId);
        $role->syncPermissions($request->permissions);

        return response()->json(['message' => 'Permisos asignados correctamente', 'permissions' => $role->permissions]);
    }

    /**
     * @OA\Delete(
     *     path="/api/roles/{roleId}/permissions/{permissionId}",
     *     tags={"RolePermissions"},
     *     summary="Eliminar un permiso de un rol",
     *     description="Revoca un permiso específico asignado a un rol",
     *     @OA\Parameter(
     *         name="roleId",
     *         in="path",
     *         description="ID del rol",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="permissionId",
     *         in="path",
     *         description="ID del permiso a eliminar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permiso eliminado del rol correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Permiso eliminado del rol correctamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Rol o permiso no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Role or Permission not found")
     *         )
     *     )
     * )
     */
    public function destroy($roleId, $permissionId)
    {
        $role = Role::findOrFail($roleId);
        $permission = Permission::findOrFail($permissionId);

        $role->revokePermissionTo($permission);

        return response()->json(['message' => 'Permiso eliminado del rol correctamente']);
    }
}
