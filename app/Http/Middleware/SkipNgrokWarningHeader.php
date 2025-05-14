<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SkipNgrokWarningHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add header for storage/image routes
        if (
            $request->is('storage/*') ||
            str_starts_with($response->headers->get('Content-Type'), 'image/')
        ) {
            $response->headers->set('ngrok-skip-browser-warning', 'true');
        }

        return $response;
    }
}