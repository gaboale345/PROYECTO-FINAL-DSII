<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incidente;
use App\Models\TipoDelito;
use App\Models\ImagenIncidente;
use App\Services\AuditService;
use App\Services\DuplicateDetectionService;
use App\Services\FalseReportService;
use App\Services\UsuarioSyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class IncidenteController extends Controller
{
    private const TIPOS_DELITO_POR_DEFECTO = [
        ['nombre' => 'Robo a vivienda', 'descripcion' => 'Ingreso o intento de ingreso a vivienda', 'nivel_gravedad' => 4],
        ['nombre' => 'Robo de vehiculo', 'descripcion' => 'Sustraccion de auto, moto o bicicleta', 'nivel_gravedad' => 4],
        ['nombre' => 'Asalto en via publica', 'descripcion' => 'Asalto con amenaza o violencia en la calle', 'nivel_gravedad' => 5],
        ['nombre' => 'Hurto', 'descripcion' => 'Sustraccion sin violencia', 'nivel_gravedad' => 3],
        ['nombre' => 'Vandalismo', 'descripcion' => 'Danos a propiedad publica o privada', 'nivel_gravedad' => 3],
        ['nombre' => 'Violencia intrafamiliar', 'descripcion' => 'Agresiones dentro del hogar', 'nivel_gravedad' => 5],
        ['nombre' => 'Venta de drogas', 'descripcion' => 'Microtrafico o venta de sustancias', 'nivel_gravedad' => 5],
        ['nombre' => 'Acoso', 'descripcion' => 'Hostigamiento o persecucion a personas', 'nivel_gravedad' => 4],
        ['nombre' => 'Emergencia medica', 'descripcion' => 'Situacion medica urgente', 'nivel_gravedad' => 5],
        ['nombre' => 'Accidente de transito', 'descripcion' => 'Choque, atropello o incidente vial', 'nivel_gravedad' => 4],
    ];

    public function __construct(
        private AuditService $audit,
        private DuplicateDetectionService $duplicates,
        private FalseReportService $falseReports,
        private UsuarioSyncService $usuarioSync
    ) {}

    public function index()
    {
        $this->ensureDefaultTiposDelito();

        $incidentes = Incidente::with(['reportante', 'tipoDelito', 'validador'])
            ->latest('fecha_hora')
            ->paginate(20);

        $tiposDelito = TipoDelito::all();

        return view('incidentes.index', compact('incidentes', 'tiposDelito'));
    }

    public function create()
    {
        $this->ensureDefaultTiposDelito();

        $tiposDelito = TipoDelito::all();
        $barrios = DB::table('barrios')->where('activo', true)->get();

        return view('incidentes.create', compact('tiposDelito', 'barrios'));
    }

    public function store(Request $request)
    {
        $this->ensureDefaultTiposDelito();

        $idUsuario = $this->usuarioSync->idUsuarioAutenticado();
        if (!$idUsuario || !$this->falseReports->puedeReportar($idUsuario)) {
            return redirect()->route('dashboard')
                ->with('error', $this->falseReports->mensajeBloqueo($idUsuario ?? 0) ?? 'No puedes reportar incidentes.');
        }

        if ($request->input('id_tipo_delito') === 'otro') {
            $request->merge(['id_tipo_delito' => null]);
        }

        $validator = Validator::make($request->all(), [
            'id_tipo_delito' => 'required_without:nuevo_tipo_delito|nullable|exists:tipos_delito,id_tipo_delito',
            'nuevo_tipo_delito' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string|max:1000',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'es_anonimo' => 'boolean',
            'es_por_voz' => 'boolean',
            'es_whatsapp' => 'boolean',
            'imagenes.*' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $idTipoDelito = $request->id_tipo_delito;
        $nuevoTipoDelito = trim((string) $request->input('nuevo_tipo_delito', ''));

        if ($nuevoTipoDelito !== '') {
            $tipoExistente = TipoDelito::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nuevoTipoDelito)])->first();
            if (!$tipoExistente) {
                $tipoExistente = TipoDelito::create([
                    'nombre' => $nuevoTipoDelito,
                    'descripcion' => 'Tipo creado por usuario al reportar incidente',
                    'nivel_gravedad' => 3,
                ]);
            }
            $idTipoDelito = $tipoExistente->id_tipo_delito;
        }

        $incidente = Incidente::create([
            'id_usuario_reportante' => $idUsuario,
            'id_tipo_delito' => $idTipoDelito,
            'descripcion' => $request->descripcion,
            'latitud' => $request->latitud,
            'longitud' => $request->longitud,
            'fecha_hora' => now(),
            'estado' => Incidente::ESTADO_PENDIENTE,
            'es_anonimo' => $request->boolean('es_anonimo'),
            'es_por_voz' => $request->boolean('es_por_voz'),
            'es_whatsapp' => $request->boolean('es_whatsapp'),
            'ip_reportante' => $request->ip(),
        ]);

        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $imagen) {
                $ruta = $imagen->store('incidentes/'.$incidente->id_incidente, 'public');
                ImagenIncidente::create([
                    'id_incidente' => $incidente->id_incidente,
                    'ruta_imagen' => $ruta,
                    'nombre_original' => $imagen->getClientOriginalName(),
                    'tamano_bytes' => $imagen->getSize(),
                    'mime_type' => $imagen->getMimeType(),
                ]);
            }
        }

        $this->audit->registrar(
            'CREAR_INCIDENTE',
            $idUsuario,
            'incidentes',
            $incidente->id_incidente,
            $request,
            ['tipo' => $idTipoDelito, 'anonimo' => $incidente->es_anonimo]
        );

        $fusion = $this->duplicates->procesarIncidenteNuevo($incidente, $idUsuario);

        $mensaje = $fusion['fusionado']
            ? 'Incidente reportado y fusionado con un reporte similar (múltiples testigos).'
            : 'Incidente reportado exitosamente en menos de 30 segundos.';

        $redirectId = $fusion['id_principal'] ?? $incidente->id_incidente;

        return redirect()->route('incidentes.show', $redirectId)->with('success', $mensaje);
    }

    public function show($id)
    {
        $incidente = Incidente::with(['reportante', 'tipoDelito', 'validador'])
            ->findOrFail($id);

        $incidente->load('testigos.usuarioTestigo');
        $fusionados = Incidente::where('id_incidente_principal', $id)->get();

        return view('incidentes.show', compact('incidente', 'fusionados'));
    }

    public function validar(Request $request, $id)
    {
        if (!auth()->user()?->puedeValidar()) {
            abort(403);
        }

        $incidente = Incidente::findOrFail($id);
        $idValidador = $this->usuarioSync->idUsuarioAutenticado();

        $incidente->update([
            'validado' => true,
            'id_usuario_validador' => $idValidador,
            'fecha_validacion' => now(),
            'estado' => Incidente::ESTADO_CONFIRMADO,
        ]);

        Incidente::where('id_incidente_principal', $id)->update(['validado' => true]);

        $this->audit->registrar('VALIDAR_INCIDENTE', $idValidador, 'incidentes', $id, $request);

        return redirect()->back()->with('success', 'Incidente validado correctamente');
    }

    public function marcarFalso(Request $request, $id)
    {
        if (!auth()->user()?->puedeValidar()) {
            abort(403);
        }

        $incidente = Incidente::findOrFail($id);
        $idValidador = $this->usuarioSync->idUsuarioAutenticado();

        $incidente->update([
            'es_falso_reporte' => true,
            'validado' => true,
            'id_usuario_validador' => $idValidador,
            'fecha_validacion' => now(),
            'estado' => Incidente::ESTADO_CERRADO,
        ]);

        $this->falseReports->registrarFalsoReporte($incidente->id_usuario_reportante, $idValidador);
        $this->audit->registrar('MARCAR_FALSO', $idValidador, 'incidentes', $id, $request);

        return redirect()->back()->with('warning', 'Incidente marcado como falso reporte');
    }

    public function fusionarDuplicados()
    {
        if (!auth()->user()?->puedeValidar()) {
            abort(403);
        }

        $total = $this->duplicates->fusionarTodos();

        return response()->json([
            'message' => "Se fusionaron {$total} incidentes duplicados",
            'total' => $total,
        ]);
    }

    private function ensureDefaultTiposDelito(): void
    {
        if (TipoDelito::count() > 0) {
            return;
        }

        foreach (self::TIPOS_DELITO_POR_DEFECTO as $tipo) {
            TipoDelito::create($tipo);
        }
    }
}
