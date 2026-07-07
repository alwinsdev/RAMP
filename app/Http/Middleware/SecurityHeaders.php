<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies baseline HTTP security headers (OWASP Secure Headers Project):
 * clickjacking protection, MIME-sniffing protection, referrer hygiene, a tight
 * permissions policy, and a Content-Security-Policy that restricts which origins
 * may load scripts/styles/images/frames.
 *
 * The CSP allows 'unsafe-inline'/'unsafe-eval' for scripts because Livewire 3 and
 * Alpine.js evaluate inline expressions — that is a framework requirement, not a
 * choice. XSS is still defended at the source by Blade's automatic output escaping;
 * the CSP's value here is locking down resource origins (no third-party script,
 * no cross-origin framing, object-src none, base-uri self).
 *
 * Set RAMP_SECURITY_HEADERS=false to disable (e.g. while debugging the map/CSP).
 */
final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! config('ramp.security_headers', true)) {
            return $response;
        }

        $headers = [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'X-XSS-Protection' => '0', // modern guidance: disable the legacy, buggy auditor
            'Permissions-Policy' => 'geolocation=(), camera=(), microphone=(), payment=(), usb=()',
            'Content-Security-Policy' => $this->contentSecurityPolicy(),
        ];

        // Enforce HTTPS for a year once served over TLS in production (Rule 17).
        // Only emitted on secure production responses so local HTTP dev is unaffected.
        if (app()->environment('production') && $request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $name => $value) {
            // Don't clobber a header a downstream layer may have intentionally set.
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        // Maps use Leaflet (bundled, served from 'self') with OpenStreetMap raster
        // tiles loaded as images from the *.tile.openstreetmap.org hosts. No API key,
        // no third-party script, no cross-origin fetch.
        $osmTiles = 'https://*.tile.openstreetmap.org https://tile.openstreetmap.org';

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
            "font-src 'self' data: https://fonts.bunny.net",
            "img-src 'self' data: blob: {$osmTiles}",
            "connect-src 'self'",
            "worker-src 'self' blob:",
            "frame-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]);
    }
}
