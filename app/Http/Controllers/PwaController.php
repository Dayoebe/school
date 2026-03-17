<?php

namespace App\Http\Controllers;

use App\Support\SiteSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class PwaController extends Controller
{
    public function manifest(Request $request): JsonResponse
    {
        $school = SiteSettings::resolveSchool($request);
        $settings = SiteSettings::forSchool($school?->id);

        $name = trim((string) data_get($settings, 'school_name', config('app.name', 'School Portal')));
        if ($name === '') {
            $name = config('app.name', 'School Portal');
        }

        $description = trim((string) data_get($settings, 'meta.description', 'School portal and services.'));
        if ($description === '') {
            $description = 'School portal and services.';
        }

        $themeColor = (string) data_get($settings, 'theme.primary_color', '#dc2626');
        if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $themeColor)) {
            $themeColor = '#dc2626';
        }

        $basePath = parse_url(url('/'), PHP_URL_PATH) ?: '/';
        if (!str_ends_with($basePath, '/')) {
            $basePath .= '/';
        }

        $manifest = [
            'name' => $name,
            'short_name' => mb_strlen($name) > 24 ? mb_substr($name, 0, 24) : $name,
            'description' => $description,
            'id' => $basePath,
            'start_url' => $basePath . '?source=pwa',
            'scope' => $basePath,
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => $themeColor,
            'lang' => str_replace('_', '-', app()->getLocale()),
            'icons' => [
                [
                    'src' => asset('icons/pwa-192x192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('icons/pwa-512x512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('icons/pwa-512x512-maskable.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ];

        return response()
            ->json($manifest)
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'no-store, max-age=0');
    }

    public function serviceWorker(): Response
    {
        $path = public_path('service-worker.js');

        abort_unless(is_file($path), 404);

        return response(file_get_contents($path), 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Service-Worker-Allowed' => '/',
        ]);
    }
}
