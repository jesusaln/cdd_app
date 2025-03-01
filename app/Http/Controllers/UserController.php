<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    public function profile()
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Retornar la vista de perfil con los datos del usuario
        return Inertia::render('Usuarios/Profile', [
            'usuario' => $user,
        ]);
    }
    public function index()
    {
        $users = User::all();
        return Inertia::render('Usuarios/Index', ['usuarios' => $users]);
    }

    public function create()
    {
        return Inertia::render('Usuarios/Create');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return Inertia::render('Usuarios/Edit', ['usuario' => $user]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function update(Request $request, User $user)
    {
        // Validación
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id, // Permite el email del usuario actual
            'password' => 'nullable|string|min:8|confirmed', // Validación de password solo si se cambia
        ]);

        // Actualiza los campos
        $user->fill($validated);

        // Solo se actualiza la contraseña si el campo no está vacío
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Guarda el usuario
        $user->save();

        // Redirige con mensaje de éxito
        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado exitosamente.');
    }



    public function show($id)
    {
        $user = User::findOrFail($id);
        return Inertia::render('Usuarios/Profile', ['usuario' => $user]);
    }





    public function destroy($id)
    {
        // Buscar el usuario por ID
        $user = User::findOrFail($id);

        // Intentar eliminar el usuario
        try {
            $user->delete(); // Elimina el usuario

            // Redirige a la lista de usuarios con mensaje de éxito
            return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente.');
        } catch (\Exception $e) {
            // Si hay algún error, muestra el mensaje de error
            return redirect()->route('usuarios.index')->with('error', 'No se pudo eliminar el usuario debido a restricciones.');
        }
    }
}
