<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
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
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'exitoDocumento' => fn () => $request->session()->get('exitoDocumento'),
                'sinRegistros' => fn() => $request->session()->get('sinRegistros'),
                'borradoCorrecto' => fn() => $request->session()->get('borradoCorrecto'),
                'actualizacionCorrecta' => fn() => $request->session()->get('actualizacionCorrecta'),
                'creacionCorrecta' => fn() => $request->session()->get('creacionCorrecta'),
            ],
        ];
    }
}
