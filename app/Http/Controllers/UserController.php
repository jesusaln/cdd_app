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

    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'verified'])->except(['index', 'show']);
    }

    public function profile()
    {
        $user = Auth::user();
        return Inertia::render('Usuarios/Profile', ['usuario' => $user]);
    }

    public function index()
    {
        $this->authorize('viewAny', User::class);
        $users = User::with('roles')->get();
        return Inertia::render('Usuarios/Index', ['usuarios' => $users]);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::all()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'label' => ucfirst(str_replace('_', ' ', $role->name)),
            ];
        });

        return Inertia::render('Usuarios/Create', [
            'roles' => $roles,
        ]);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $roles = Role::all()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'label' => ucfirst(str_replace('_', ' ', $role->name)),
            ];
        });

        // Cargar los roles del usuario autenticado
        $authUser = Auth::user()->load('roles');

        return Inertia::render('Usuarios/Edit', [
            'usuario' => $user->load('roles'), // Cargar los roles del usuario editado
            'roles' => $roles,
            'auth' => ['user' => $authUser], // Pasar el usuario autenticado con roles cargados
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'nullable|string|exists:roles,name',
        ]);

        // Cargar los roles del usuario autenticado
        $authUser = Auth::user()->load('roles');

        // Verificar si el usuario intenta cambiar el rol
        if (isset($validated['role']) && $validated['role'] !== $user->getRoleNames()->first()) {
            if (!$authUser->hasRole('admin')) {
                abort(403, 'No tienes permiso para cambiar el rol de este usuario.');
            }
        }

        // Actualizar los datos del usuario
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
        ]);

        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('view', $user);
        return Inertia::render('Usuarios/Profile', ['usuario' => $user]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);

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
