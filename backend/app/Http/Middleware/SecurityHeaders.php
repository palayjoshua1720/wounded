<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Fixes: CSP header not set
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self'; " .
            "style-src 'self' 'unsafe-inline'; " .  // Vue/Vite injects inline styles
            "img-src 'self' data: blob:; " .
            "font-src 'self'; " .
            "connect-src 'self'; " .
            "form-action 'self'; " .           // fixes: no fallback alert
            "frame-ancestors 'none';"           // fixes: no fallback alert
        );

        // Fixes: X-Content-Type-Options missing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        $response->headers->set(
            'Referrer-Policy',
            'strict-origin-when-cross-origin'
        );

        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=()'
        );

        $response->headers->set(
            'X-Frame-Options',
            'DENY'
        );

        // Fixes: X-Powered-By leaking
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}
