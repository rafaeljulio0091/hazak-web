<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureAuthenticationRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->isProduction() && ! $request->isSecure()) {
            $response = $request->isMethodSafe()
                ? redirect()->secure($request->getRequestUri())
                : response('Use HTTPS para acessar a autenticação.', 400);
        } else {
            $response = $next($request);
        }

        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Referrer-Policy', 'same-origin');

        return $response;
    }
}
