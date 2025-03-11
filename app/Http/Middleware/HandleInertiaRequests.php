<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * La raíz del template que se carga en la primera carga de página.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determina la versión actual de los activos.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define las propiedades que se comparten por defecto con todas las vistas de Inertia.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'nombre' => $request->user()->name, // Asegúrate de incluir el nombre
                    // Agrega otras propiedades del usuario según sea necesario
                    // Agrega el rol del usuario (ajusta según tu implementación)
                    'rol' => $request->user()->rol ?? $request->user()->roles->pluck('name')->first() ?? 'Usuario',
                ] : null,
            ],
        ]);
    }
}
