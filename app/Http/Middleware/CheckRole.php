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

        $idRol = (int) ($user->id_rol ?? 1);
        $rolesPermitidos = array_map('intval', $roles);

        if (!in_array($idRol, $rolesPermitidos, true) && !in_array('*', $roles, true)) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
