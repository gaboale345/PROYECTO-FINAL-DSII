<?php

namespace App\Http\Middleware;

use App\Services\FalseReportService;
use App\Services\UsuarioSyncService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCanReport
{
    public function __construct(
        private FalseReportService $falseReports,
        private UsuarioSyncService $usuarioSync
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $idUsuario = $this->usuarioSync->idUsuarioAutenticado();

        if (!$idUsuario || !$this->falseReports->puedeReportar($idUsuario)) {
            $mensaje = $idUsuario
                ? ($this->falseReports->mensajeBloqueo($idUsuario) ?? 'No puedes reportar incidentes en este momento.')
                : 'Debes iniciar sesión para reportar.';

            return redirect()->route('dashboard')->with('error', $mensaje);
        }

        return $next($request);
    }
}
