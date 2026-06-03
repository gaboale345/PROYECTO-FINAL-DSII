<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barrio;
use App\Models\Incidente;
use App\Models\TipoDelito;
use App\Models\Usuario;
use App\Services\AuditService;
use App\Services\UsuarioSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IncidenteAdminController extends Controller
{
    public function __construct(
        private AuditService $audit,
        private UsuarioSyncService $usuarioSync
    ) {}

    public function index(Request $request)
    {
        $query = Incidente::with(['reportante.barrio', 'tipoDelito', 'validador'])
            ->withCount('incidentesFusionados');

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                if (is_numeric($buscar)) {
                    $q->where('id_incidente', (int) $buscar);
                }
                $q->orWhere('descripcion', 'like', '%'.$buscar.'%')
                    ->orWhereHas('reportante', fn ($r) => $r->where('nombre_completo', 'like', '%'.$buscar.'%'));
            });
        }

        if ($request->filled('id_barrio')) {
            $query->porBarrio((int) $request->id_barrio);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->get('validado') === '1') {
            $query->where('validado', true);
        } elseif ($request->get('validado') === '0') {
            $query->where('validado', false);
        }

        if ($request->get('falso') === '1') {
            $query->where('es_falso_reporte', true);
        } elseif ($request->get('falso') === '0') {
            $query->where('es_falso_reporte', false);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_hora', '>=', $request->date('fecha_desde')->startOfDay());
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_hora', '<=', $request->date('fecha_hasta')->endOfDay());
        }

        $stats = [
            'total' => (clone $query)->count(),
            'validados' => (clone $query)->where('validado', true)->where('es_falso_reporte', false)->count(),
            'pendientes' => (clone $query)->where('validado', false)->where('es_falso_reporte', false)->count(),
            'falsos' => (clone $query)->where('es_falso_reporte', true)->count(),
        ];

        $incidentes = $query->latest('fecha_hora')->paginate(25)->withQueryString();

        $barrios = Barrio::where('activo', true)->orderBy('nombre')->get();
        $estados = Incidente::getEstados();

        return view('admin.incidentes.index', compact('incidentes', 'barrios', 'estados', 'stats'));
    }

    public function create()
    {
        return view('admin.incidentes.create', $this->datosFormulario());
    }

    public function store(Request $request)
    {
        $datos = $this->validarIncidente($request);
        $incidente = Incidente::create($datos);

        $this->aplicarValidacionManual($incidente, $request);
        $this->audit->registrar('ADMIN_CREAR_INCIDENTE', $this->idAdmin(), 'incidentes', $incidente->id_incidente, $request);

        return redirect()
            ->route('admin.incidentes.index')
            ->with('success', 'Incidente #'.$incidente->id_incidente.' creado correctamente.');
    }

    public function edit(int $incidente)
    {
        $incidente = Incidente::with(['reportante', 'tipoDelito', 'imagenes'])->findOrFail($incidente);

        return view('admin.incidentes.edit', array_merge($this->datosFormulario(), compact('incidente')));
    }

    public function update(Request $request, int $incidente)
    {
        $incidente = Incidente::findOrFail($incidente);
        $antes = $incidente->only(['estado', 'validado', 'es_falso_reporte', 'id_tipo_delito']);

        $datos = $this->validarIncidente($request);
        $incidente->update($datos);
        $this->aplicarValidacionManual($incidente, $request);

        $this->audit->registrar(
            'ADMIN_ACTUALIZAR_INCIDENTE',
            $this->idAdmin(),
            'incidentes',
            $incidente->id_incidente,
            $request,
            ['antes' => $antes, 'despues' => $incidente->only(['estado', 'validado', 'es_falso_reporte'])]
        );

        return redirect()
            ->route('admin.incidentes.index')
            ->with('success', 'Incidente #'.$incidente->id_incidente.' actualizado.');
    }

    public function destroy(Request $request, int $incidente)
    {
        $incidente = Incidente::with('imagenes')->findOrFail($incidente);
        $id = $incidente->id_incidente;

        DB::transaction(function () use ($incidente) {
            Incidente::where('id_incidente_principal', $incidente->id_incidente)
                ->update(['id_incidente_principal' => null]);

            foreach ($incidente->imagenes as $imagen) {
                Storage::disk('public')->delete($imagen->ruta_imagen);
                $imagen->delete();
            }

            $incidente->testigos()->delete();
            $incidente->delete();
        });

        $this->audit->registrar('ADMIN_ELIMINAR_INCIDENTE', $this->idAdmin(), 'incidentes', $id, $request);

        return redirect()
            ->route('admin.incidentes.index')
            ->with('success', 'Incidente #'.$id.' eliminado.');
    }

    private function datosFormulario(): array
    {
        return [
            'tiposDelito' => TipoDelito::orderBy('nombre')->get(),
            'usuarios' => Usuario::with('barrio')->where('activo', true)->orderBy('nombre_completo')->get(),
            'estados' => Incidente::getEstados(),
        ];
    }

    private function validarIncidente(Request $request): array
    {
        $request->validate([
            'id_usuario_reportante' => 'required|exists:usuarios,id_usuario',
            'id_tipo_delito' => 'required|exists:tipos_delito,id_tipo_delito',
            'descripcion' => 'nullable|string|max:2000',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'fecha_hora' => 'required|date',
            'estado' => 'required|in:'.implode(',', array_keys(Incidente::getEstados())),
            'validado' => 'nullable|boolean',
            'es_falso_reporte' => 'nullable|boolean',
            'es_anonimo' => 'nullable|boolean',
            'es_por_voz' => 'nullable|boolean',
            'es_whatsapp' => 'nullable|boolean',
            'id_incidente_principal' => 'nullable|integer|exists:incidentes,id_incidente',
        ]);

        return [
            'id_usuario_reportante' => $request->integer('id_usuario_reportante'),
            'id_tipo_delito' => $request->integer('id_tipo_delito'),
            'descripcion' => $request->descripcion,
            'latitud' => $request->latitud,
            'longitud' => $request->longitud,
            'fecha_hora' => $request->fecha_hora,
            'estado' => $request->estado,
            'validado' => $request->boolean('validado'),
            'es_falso_reporte' => $request->boolean('es_falso_reporte'),
            'es_anonimo' => $request->boolean('es_anonimo'),
            'es_por_voz' => $request->boolean('es_por_voz'),
            'es_whatsapp' => $request->boolean('es_whatsapp'),
            'id_incidente_principal' => $request->filled('id_incidente_principal')
                ? $request->integer('id_incidente_principal')
                : null,
        ];
    }

    private function aplicarValidacionManual(Incidente $incidente, Request $request): void
    {
        $incidente->refresh();

        if ($request->boolean('validado') || $request->boolean('es_falso_reporte')) {
            $incidente->update([
                'id_usuario_validador' => $this->idAdmin(),
                'fecha_validacion' => $incidente->fecha_validacion ?? now(),
            ]);
        } elseif (!$request->boolean('validado') && !$request->boolean('es_falso_reporte')) {
            $incidente->update([
                'id_usuario_validador' => null,
                'fecha_validacion' => null,
            ]);
        }

        if ($request->boolean('es_falso_reporte')) {
            $incidente->update(['estado' => Incidente::ESTADO_CERRADO, 'validado' => true]);
        } elseif ($request->boolean('validado')) {
            $incidente->update(['estado' => Incidente::ESTADO_CONFIRMADO]);
        }
    }

    private function idAdmin(): ?int
    {
        return $this->usuarioSync->idUsuarioAutenticado();
    }
}
