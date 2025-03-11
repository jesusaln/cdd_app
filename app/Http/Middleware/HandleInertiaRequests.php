<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->getRoleNames()->first(),
                    'profile_photo_url' => $request->user()->profile_photo_url, // Asegúrate de que este campo exista en tu modelo de usuario
                ] : null,

            ],
            'jetstream' => [
                'canUpdateProfileInformation' => true,
                'canUpdatePassword' => true,
                'canManageTwoFactorAuthentication' => true,
                'hasAccountDeletionFeatures' => true,
                'managesProfilePhotos' => true,
                'hasEmailVerification' => true,
            ],
        ]);
    }
}
