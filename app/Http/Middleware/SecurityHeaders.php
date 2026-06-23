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
        $maps = 'https://maps.googleapis.com https://maps.gstatic.com';
        $google = 'https://*.googleapis.com https://*.gstatic.com https://maps.google.com https://*.ggpht.com';

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' {$maps}",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
            "font-src 'self' https://fonts.bunny.net",
            "img-src 'self' data: blob: {$google}",
            "connect-src 'self' {$maps}",
            "worker-src 'self' blob:",
            "frame-src 'self' https://www.google.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]);
    }
}
