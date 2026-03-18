<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Clickjacking protection
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Reduce referrer leakage
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Keep capabilities explicit; allow camera for the barcode scanner.
        $response->headers->set(
            'Permissions-Policy',
            'camera=(self), microphone=(), geolocation=(), payment=(), usb=(), autoplay=()'
        );

        // Minimal CSP that avoids breaking Vite/dev assets while still blocking framing and plugins.
        $response->headers->set(
            'Content-Security-Policy',
            "base-uri 'self'; frame-ancestors 'self'; object-src 'none';"
        );

        // HSTS only when actually under HTTPS (avoid breaking local/dev over HTTP).
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}

