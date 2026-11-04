<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $role = $request->user()?->getAttribute('role');
        $role = $role instanceof UserRole ? $role->value : $role;

        abort_unless(in_array($role, $roles, true), 403, 'Você não tem acesso a esta área.');

        return $next($request);
    }
}
