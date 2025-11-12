<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Role
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user();
        if (!$user || $user->type !== $role) {
            throw new HttpException(403, 'Accès refusé. Rôle requis : ' . $role);
        }
        return $next($request);
    }
}