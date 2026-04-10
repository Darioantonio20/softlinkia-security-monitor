<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Protección contra Clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        
        // Protección contra XSS en navegadores antiguos
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // Prevenir sniffing de MIME types
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Política de referer
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        
        // HSTS (Solo si es HTTPS)
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
