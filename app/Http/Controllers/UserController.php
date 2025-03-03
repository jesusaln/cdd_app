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

    // Aquí se deben definir los middleware en el constructor
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'verified'])->except(['index', 'show']);
    }

    public function profile()
    {
        $user = Auth::user();
        return Inertia::render('Usuarios/Profile', [
            'usuario' => $user,
        ]);
    }

    public function index()
    {
        $this->authorize('viewAny', User::class); // Usa la política para verificar autorización
        $users = User::with('roles')->get();
        return Inertia::render('Usuarios/Index', ['usuarios' => $users]);
    }

    public function create()
    {
        $this->authorize('create', User::class); // Usa la política para verificar autorización
        return Inertia::render('Usuarios/Create');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user); // Usa la política para verificar autorización
        return Inertia::render('Usuarios/Edit', ['usuario' => $user]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class); // Usa la política para verificar autorización

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!Role::where('name', $value)->exists()) {
                    $fail("El rol seleccionado no es válido.");
                }
            }],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        $user->assignRole($request->role);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user); // Usa la política para verificar autorización

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!Role::where('name', $value)->exists()) {
                    $fail("El rol seleccionado no es válido.");
                }
            }],
        ]);

        $user->update([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => $validatedData['password'] ? bcrypt($validatedData['password']) : $user->password,
        ]);

        $user->syncRoles([$validatedData['role']]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('view', $user); // Usa la política para verificar autorización
        return Inertia::render('Usuarios/Profile', ['usuario' => $user]);
    }

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
