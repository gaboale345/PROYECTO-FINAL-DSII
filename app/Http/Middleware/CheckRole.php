<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * @param  string  ...$roles  IDs de rol permitidos (1=vecino, 2=admin, 3=policía, 4=superadmin)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $idRol = (string) ($user->id_rol ?? 1);

        if (!in_array($idRol, $roles, true) && !in_array('*', $roles, true)) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
