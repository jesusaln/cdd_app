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
        $almacenes = \App\Models\Almacen::select('id', 'nombre')->where('estado', 'activo')->orderBy('nombre')->get();
        return Inertia::render('Profile/Show', [
            'user' => $user,
            'almacenes' => $almacenes,
            'sessions' => collect(),
            'confirmsTwoFactorAuthentication' => false
        ]);
    }

    public function index(Request $request)
    {
        try {
            $this->authorize('viewAny', User::class);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->route('panel')->with('error', 'No tienes permisos para ver la lista de usuarios.');
        } catch (\Exception $e) {
            Log::error('Error en UserController@index: ' . $e->getMessage());
            return redirect()->route('panel')->with('error', 'Error al cargar la lista de usuarios.');
        }

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
        try {
            $this->authorize('create', User::class);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->route('usuarios.index')->with('error', 'No tienes permisos para crear usuarios.');
        } catch (\Exception $e) {
            Log::error('Error en UserController@create: ' . $e->getMessage());
            return redirect()->route('usuarios.index')->with('error', 'Error al procesar la solicitud de crear usuario.');
        }

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
        try {
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
            $authUser = User::with('roles')->find(Auth::id());

            return Inertia::render('Usuarios/Edit', [
                'usuario' => $user->load('roles'), // Cargar los roles del usuario editado
                'roles' => $roles,
                'auth' => ['user' => $authUser], // Pasar el usuario autenticado con roles cargados
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->route('usuarios.index')->with('error', 'No tienes permisos para editar este usuario.');
        } catch (\Exception $e) {
            Log::error('Error en UserController@edit: ' . $e->getMessage());
            return redirect()->route('usuarios.index')->with('error', 'Error al cargar el usuario para editar.');
        }
    }

    public function store(Request $request)
    {
        try {
            $this->authorize('create', User::class);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->route('usuarios.index')->with('error', 'No tienes permisos para crear usuarios.');
        } catch (\Exception $e) {
            Log::error('Error en UserController@store: ' . $e->getMessage());
            return redirect()->route('usuarios.index')->with('error', 'Error al procesar la solicitud de crear usuario.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'telefono' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'curp' => 'nullable|string|size:18|unique:users,curp',
            'rfc' => 'nullable|string|size:13|unique:users,rfc',
            'direccion' => 'nullable|string|max:500',
            'nss' => 'nullable|string|size:11|unique:users,nss',
            'puesto' => 'nullable|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'fecha_contratacion' => 'nullable|date',
            'salario' => 'nullable|numeric|min:0',
            'tipo_contrato' => 'nullable|in:tiempo_completo,medio_tiempo,temporal,por_obra',
            'numero_empleado' => 'nullable|string|unique:users,numero_empleado',
            'contacto_emergencia_nombre' => 'nullable|string|max:255',
            'contacto_emergencia_telefono' => 'nullable|string|max:20',
            'contacto_emergencia_parentesco' => 'nullable|string|max:100',
            'banco' => 'nullable|string|max:255',
            'numero_cuenta' => 'nullable|string|max:20',
            'clabe_interbancaria' => 'nullable|string|size:18|unique:users,clabe_interbancaria',
            'observaciones' => 'nullable|string|max:1000',
            'es_empleado' => 'boolean',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
            'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
            'curp' => $validated['curp'] ?? null,
            'rfc' => $validated['rfc'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
            'nss' => $validated['nss'] ?? null,
            'puesto' => $validated['puesto'] ?? null,
            'departamento' => $validated['departamento'] ?? null,
            'fecha_contratacion' => $validated['fecha_contratacion'] ?? null,
            'salario' => $validated['salario'] ?? null,
            'tipo_contrato' => $validated['tipo_contrato'] ?? null,
            'numero_empleado' => $validated['numero_empleado'] ?? null,
            'contacto_emergencia_nombre' => $validated['contacto_emergencia_nombre'] ?? null,
            'contacto_emergencia_telefono' => $validated['contacto_emergencia_telefono'] ?? null,
            'contacto_emergencia_parentesco' => $validated['contacto_emergencia_parentesco'] ?? null,
            'banco' => $validated['banco'] ?? null,
            'numero_cuenta' => $validated['numero_cuenta'] ?? null,
            'clabe_interbancaria' => $validated['clabe_interbancaria'] ?? null,
            'observaciones' => $validated['observaciones'] ?? null,
            'es_empleado' => $validated['es_empleado'] ?? false,
            'password' => Hash::make($validated['password']),
        ];

        $user = User::create($userData);
        $user->assignRole($validated['role']);

        $tipo = $user->es_empleado ? 'empleado' : 'usuario';
        return redirect()->route('usuarios.index')->with('success', ucfirst($tipo) . ' creado exitosamente.');
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $this->authorize('update', $user);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->route('usuarios.index')->with('error', 'No tienes permisos para editar este usuario.');
        } catch (\Exception $e) {
            Log::error('Error en UserController@update: ' . $e->getMessage());
            return redirect()->route('usuarios.index')->with('error', 'Error al cargar el usuario para editar.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'telefono' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'curp' => 'nullable|string|size:18|unique:users,curp,' . $user->id,
            'rfc' => 'nullable|string|size:13|unique:users,rfc,' . $user->id,
            'direccion' => 'nullable|string|max:500',
            'nss' => 'nullable|string|size:11|unique:users,nss,' . $user->id,
            'puesto' => 'nullable|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'fecha_contratacion' => 'nullable|date',
            'salario' => 'nullable|numeric|min:0',
            'tipo_contrato' => 'nullable|in:tiempo_completo,medio_tiempo,temporal,por_obra',
            'numero_empleado' => 'nullable|string|unique:users,numero_empleado,' . $user->id,
            'contacto_emergencia_nombre' => 'nullable|string|max:255',
            'contacto_emergencia_telefono' => 'nullable|string|max:20',
            'contacto_emergencia_parentesco' => 'nullable|string|max:100',
            'banco' => 'nullable|string|max:255',
            'numero_cuenta' => 'nullable|string|max:20',
            'clabe_interbancaria' => 'nullable|string|size:18|unique:users,clabe_interbancaria,' . $user->id,
            'observaciones' => 'nullable|string|max:1000',
            'es_empleado' => 'boolean',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'nullable|string|exists:roles,name',
        ]);

        // Cargar los roles del usuario autenticado
        $authUser = User::with('roles')->find(Auth::id());

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
            'telefono' => $validated['telefono'] ?? null,
            'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
            'curp' => $validated['curp'] ?? null,
            'rfc' => $validated['rfc'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
            'nss' => $validated['nss'] ?? null,
            'puesto' => $validated['puesto'] ?? null,
            'departamento' => $validated['departamento'] ?? null,
            'fecha_contratacion' => $validated['fecha_contratacion'] ?? null,
            'salario' => $validated['salario'] ?? null,
            'tipo_contrato' => $validated['tipo_contrato'] ?? null,
            'numero_empleado' => $validated['numero_empleado'] ?? null,
            'contacto_emergencia_nombre' => $validated['contacto_emergencia_nombre'] ?? null,
            'contacto_emergencia_telefono' => $validated['contacto_emergencia_telefono'] ?? null,
            'contacto_emergencia_parentesco' => $validated['contacto_emergencia_parentesco'] ?? null,
            'banco' => $validated['banco'] ?? null,
            'numero_cuenta' => $validated['numero_cuenta'] ?? null,
            'clabe_interbancaria' => $validated['clabe_interbancaria'] ?? null,
            'observaciones' => $validated['observaciones'] ?? null,
            'es_empleado' => $validated['es_empleado'] ?? false,
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
        ]);

        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        $tipo = $user->es_empleado ? 'empleado' : 'usuario';
        return redirect()->route('usuarios.index')->with('success', ucfirst($tipo) . ' actualizado exitosamente.');
    }

    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            $this->authorize('view', $user);
            return Inertia::render('Usuarios/Profile', ['usuario' => $user]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->route('usuarios.index')->with('error', 'No tienes permisos para ver este usuario.');
        } catch (\Exception $e) {
            Log::error('Error en UserController@show: ' . $e->getMessage());
            return redirect()->route('usuarios.index')->with('error', 'Error al cargar el usuario.');
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $this->authorize('delete', $user);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->route('usuarios.index')->with('error', 'No tienes permisos para eliminar este usuario.');
        } catch (\Exception $e) {
            Log::error('Error en UserController@destroy: ' . $e->getMessage());
            return redirect()->route('usuarios.index')->with('error', 'Error al cargar el usuario para eliminar.');
        }

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
        try {
            $this->authorize('update', $user);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->back()->with('error', 'No tienes permisos para cambiar el estado de este usuario.');
        } catch (\Exception $e) {
            Log::error('Error en UserController@toggle: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cambiar el estado del usuario.');
        }

        try {
            $user->update(['activo' => !$user->activo]);
            return redirect()->back()->with('success', $user->activo ? 'Usuario activado correctamente' : 'Usuario desactivado correctamente');
        } catch (Exception $e) {
            Log::error('Error cambiando estado del usuario: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Hubo un problema al cambiar el estado del usuario.');
        }
    }

    public function updateAlmacenVenta(Request $request)
    {
        $request->validate([
            'almacen_venta_id' => 'nullable|exists:almacenes,id',
        ]);

        $user = Auth::user();
        $user->update([
            'almacen_venta_id' => $request->almacen_venta_id,
        ]);

        $almacenVenta = null;
        if ($request->almacen_venta_id) {
            $almacenVenta = \App\Models\Almacen::find($request->almacen_venta_id);
        }

        return response()->json([
            'success' => true,
            'almacen_venta' => $almacenVenta,
            'message' => 'Almacén de venta actualizado correctamente.'
        ]);
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
