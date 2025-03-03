<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use Inertia\Inertia;
use Illuminate\Database\QueryException;
use Spatie\Permission\Models\Role;

class UserController extends BaseController
{
    use AuthorizesRequests;

    // Middleware para proteger las rutas
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'verified'])->except(['index', 'show']);
    }

    /**
     * Muestra el perfil del usuario autenticado.
     */
    public function profile()
    {
        $user = Auth::user();
        return Inertia::render('Usuarios/Profile', ['usuario' => $user]);
    }

    /**
     * Muestra la lista de usuarios.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class); // Usa la política para verificar autorización
        $users = User::with('roles')->get();
        return Inertia::render('Usuarios/Index', ['usuarios' => $users]);
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     */
    public function create()
    {
        $this->authorize('create', User::class); // Usa la política para verificar autorización
        return Inertia::render('Usuarios/Create');
    }

    /**
     * Muestra el formulario para editar un usuario existente.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user); // Usa la política para verificar autorización
        return Inertia::render('Usuarios/Edit', ['usuario' => $user]);
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class); // Usa la política para verificar autorización

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name', // El rol debe existir en la tabla roles
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']); // Asigna el rol al usuario

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Actualiza un usuario existente en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user); // Usa la política para verificar autorización

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'nullable|string|exists:roles,name', // El rol debe existir en la tabla roles
        ]);

        // Verificar si el usuario intenta cambiar el rol
        if (isset($validated['role']) && $validated['role'] !== $user->getRoleNames()->first()) {
            // Solo los administradores pueden cambiar roles
            if (!Auth::user()->hasRole('admin')) {
                abort(403, 'No tienes permiso para cambiar el rol de este usuario.');
            }
        }

        // Actualizar los datos del usuario
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
        ]);

        // Sincronizar el rol si está presente en la solicitud y el usuario tiene permiso
        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Muestra el perfil de un usuario específico.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('view', $user); // Usa la política para verificar autorización
        return Inertia::render('Usuarios/Profile', ['usuario' => $user]);
    }

    /**
     * Elimina un usuario de la base de datos.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user); // Usa la política para verificar autorización

        try {
            $user->delete();
            return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente.');
        } catch (QueryException $e) {
            return redirect()->route('usuarios.index')->with('error', 'No se pudo eliminar el usuario debido a restricciones de la base de datos.');
        } catch (\Exception $e) {
            return redirect()->route('usuarios.index')->with('error', 'Ocurrió un error inesperado.');
        }
    }
}
