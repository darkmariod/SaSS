<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Aplica headers de seguridad OWASP Top 10 a todas las respuestas.
     *
     * - CSP: controla recursos que puede cargar el navegador (mitiga XSS)
     * - HSTS: fuerza HTTPS en producción
     * - X-Frame-Options: previene clickjacking
     * - X-Content-Type-Options: previene MIME sniffing
     * - Referrer-Policy: controla información de referrer
     * - Permissions-Policy: restringe APIs del navegador
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if (app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );

            $response->headers->set(
                'Content-Security-Policy',
                "default-src 'self'; "
                . "script-src 'self'; "
                . "style-src 'self' 'unsafe-inline'; "
                . "img-src 'self' data:; "
                . "font-src 'self'; "
                . "connect-src 'self' " . config('app.url') . ' ' . env('FRONTEND_URL', 'http://localhost:5173') . '; '
                . "base-uri 'self'; "
                . "form-action 'self'"
            );
        }

        return $response;
    }
}
