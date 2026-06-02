<?php

namespace App\Http\Controllers;

use App\Models\AlertaPredictiva;
use Illuminate\Http\Request;

class AlertaController extends Controller
{
    public function index(Request $request)
    {
        $idBarrio = auth()->user()?->id_barrio;

        $alertas = AlertaPredictiva::with('barrio')
            ->where('activa', true)
            ->when($idBarrio && !auth()->user()?->esPolicia() && !auth()->user()?->esSuperAdmin(), function ($q) use ($idBarrio) {
                $q->where('id_barrio', $idBarrio);
            })
            ->orderByDesc('probabilidad')
            ->paginate(20);

        return view('alertas.index', compact('alertas'));
    }
}
