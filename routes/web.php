<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidenteController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\WebhookController;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('throttle:global')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::get('/verify-email', [AuthController::class, 'showVerifyForm'])->name('verification.notice');
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/verification/resend', [AuthController::class, 'resendVerification'])->name('verification.resend');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Webhooks WhatsApp (sin auth, verificación por token)
Route::get('/webhook/whatsapp', [WebhookController::class, 'whatsappVerify']);
Route::post('/webhook/whatsapp', [WebhookController::class, 'whatsappMessage']);

Route::middleware(['auth', 'throttle:global'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/api/mapa-predictivo', [DashboardController::class, 'mapaPredictivo'])->name('api.mapa.predictivo');
    Route::get('/api/datos-barrio', [DashboardController::class, 'getDatosBarrio'])->name('api.datos.barrio');

    // Incidentes
    Route::get('/incidentes', [IncidenteController::class, 'index'])->name('incidentes.index');
    Route::get('/incidentes/create', [IncidenteController::class, 'create'])->middleware('can.report')->name('incidentes.create');
    Route::post('/incidentes', [IncidenteController::class, 'store'])->middleware('can.report')->name('incidentes.store');
    Route::get('/incidentes/{incidente}', [IncidenteController::class, 'show'])->name('incidentes.show');
    Route::post('/incidentes/{id}/validar', [IncidenteController::class, 'validar'])->middleware('role:2,3,4')->name('incidentes.validar');
    Route::post('/incidentes/{id}/marcar-falso', [IncidenteController::class, 'marcarFalso'])->middleware('role:2,3,4')->name('incidentes.marcar-falso');
    Route::post('/incidentes/fusionar-duplicados', [IncidenteController::class, 'fusionarDuplicados'])->middleware('role:2,3,4')->name('incidentes.fusionar');

    // Alertas predictivas
    Route::get('/alertas', [AlertaController::class, 'index'])->name('alertas.index');

    // Reportes PDF / estadísticos
    Route::get('/reportes/mensual', [ReporteController::class, 'mensual'])->middleware('role:2,3,4')->name('reportes.mensual');
    Route::get('/reportes/semanal', [ReporteController::class, 'semanal'])->middleware('role:2,3,4')->name('reportes.semanal');
});
