<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use Inertia\Inertia;
use Illuminate\Database\QueryException;
use Spatie\Permission\Models\Role;
use Exception;

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

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        try {
            $query = User::with('roles');

            // Filtros de búsqueda
            if ($s = trim((string) $request->input('search', ''))) {
                $query->where(function ($w) use ($s) {
                    $w->where('name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%");
                });
            }

            // Filtrar por estado activo/inactivo
            if ($request->query->has('activo')) {
                $val = (string) $request->query('activo');
                if ($val === '1') {
                    $query->where(function ($query) {
                        $query->where('activo', true)->orWhereNull('activo');
                    });
                } elseif ($val === '0') {
                    $query->where('activo', false);
                }
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $validSort = ['name', 'email', 'created_at', 'activo'];

            if (!in_array($sortBy, $validSort)) $sortBy = 'created_at';
            if (!in_array($sortDirection, ['asc', 'desc'])) $sortDirection = 'desc';

            $query->orderBy($sortBy, $sortDirection);

            // Paginación
            $usuarios = $query->paginate(10)->appends($request->query());

            // Estadísticas
            $usuariosCount = User::count();
            $usuariosActivos = User::where(function ($q) {
                $q->where('activo', true)->orWhereNull('activo');
            })->count();

            return Inertia::render('Usuarios/Index', [
                'usuarios' => $usuarios,
                'stats' => [
                    'total' => $usuariosCount,
                    'activos' => $usuariosActivos,
                    'inactivos' => $usuariosCount - $usuariosActivos,
                ],
                'filters' => $request->only(['search', 'activo']),
                'sorting' => ['sort_by' => $sortBy, 'sort_direction' => $sortDirection],
            ]);
        } catch (Exception $e) {
            Log::error('Error en UserController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar la lista de usuarios.');
        }
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

    public function toggle(User $user)
    {
        $this->authorize('update', $user);

        try {
            $user->update(['activo' => !$user->activo]);
            return redirect()->back()->with('success', $user->activo ? 'Usuario activado correctamente' : 'Usuario desactivado correctamente');
        } catch (Exception $e) {
            Log::error('Error cambiando estado del usuario: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Hubo un problema al cambiar el estado del usuario.');
        }
    }

    public function export(Request $request)
    {
        $this->authorize('viewAny', User::class);

        try {
            $query = User::with('roles');

            // Aplicar los mismos filtros que en index
            if ($s = trim((string) $request->input('search', ''))) {
                $query->where(function ($w) use ($s) {
                    $w->where('name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%");
                });
            }

            if ($request->query->has('activo')) {
                $val = (string) $request->query('activo');
                if ($val === '1') {
                    $query->where(function ($query) {
                        $query->where('activo', true)->orWhereNull('activo');
                    });
                } elseif ($val === '0') {
                    $query->where('activo', false);
                }
            }

            $usuarios = $query->get();

            $filename = 'usuarios_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($usuarios) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                fputcsv($file, [
                    'ID',
                    'Nombre',
                    'Email',
                    'Rol',
                    'Activo',
                    'Fecha Creación'
                ]);

                foreach ($usuarios as $usuario) {
                    fputcsv($file, [
                        $usuario->id,
                        $usuario->name,
                        $usuario->email,
                        $usuario->getRoleNames()->first() ?? 'Sin rol',
                        $usuario->activo ? 'Sí' : 'No',
                        $usuario->created_at?->format('d/m/Y H:i:s')
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            Log::error('Error en exportación de usuarios: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar los usuarios.');
        }
    }
}
