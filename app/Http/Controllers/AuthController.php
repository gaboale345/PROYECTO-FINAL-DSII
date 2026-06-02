<?php

namespace App\Http\Controllers;

use App\Mail\VerificationCodeMail;
use App\Models\EmailVerificationToken;
use App\Models\User;
use App\Services\AuditService;
use App\Services\LoginSecurityService;
use App\Services\UsuarioSyncService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(
        private LoginSecurityService $loginSecurity,
        private AuditService $audit,
        private UsuarioSyncService $usuarioSync
    ) {}

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
        ]);

        if ($this->loginSecurity->estaBloqueado($data['email'])) {
            return back()->withErrors([
                'email' => 'Demasiados intentos fallidos. Espera 15 minutos e intenta de nuevo.',
            ])->withInput();
        }

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            $this->loginSecurity->registrarIntento($data['email'], $request, false);

            if ($user && $this->loginSecurity->estaBloqueado($data['email'])) {
                $this->loginSecurity->bloquearCuenta($user);
            }

            return back()->withErrors(['email' => 'Correo o contraseña incorrectos'])->withInput();
        }

        if ($this->loginSecurity->cuentaBloqueada($user)) {
            return back()->withErrors([
                'email' => 'Cuenta bloqueada temporalmente. Intenta en '
                    .$this->loginSecurity->minutosRestantesBloqueo($user).' minutos.',
            ])->withInput();
        }

        if (!$user->email_verified_at) {
            return back()->withErrors(['email' => 'Tu correo no está verificado. Revisa el código enviado.'])->withInput();
        }

        $this->loginSecurity->registrarIntento($data['email'], $request, true);
        $this->usuarioSync->sincronizarDesdeUser($user);

        Auth::login($user, $request->boolean('remember'));

        return redirect()->intended(route('dashboard'));
    }

    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $barrios = DB::table('barrios')->where('activo', true)->orderBy('nombre')->get();

        return view('auth.register', compact('barrios'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'id_barrio' => 'nullable|exists:barrios,id_barrio',
            'telefono' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'id_rol' => 1,
            'id_barrio' => $data['id_barrio'] ?? null,
            'telefono' => $data['telefono'] ?? null,
        ]);

        $this->usuarioSync->sincronizarDesdeUser($user, [
            'id_barrio' => $data['id_barrio'] ?? null,
            'telefono' => $data['telefono'] ?? null,
        ]);

        // Suscripción automática al barrio del vecino
        if (!empty($data['id_barrio'])) {
            $idUsuario = $user->fresh()->id_usuario;
            if ($idUsuario) {
                DB::table('suscripciones_barrio')->insertOrIgnore([
                    'id_usuario' => $idUsuario,
                    'id_barrio' => $data['id_barrio'],
                    'activo' => true,
                    'fecha_suscripcion' => now(),
                ]);
            }
        }

        $this->audit->registrar('REGISTRO_USUARIO', $user->id_usuario, 'users', $user->id, $request);

        $token = $this->createVerificationToken($user);

        if (!$this->sendVerificationCode($user, $token->token)) {
            return redirect()
                ->route('verification.notice')
                ->with('error', 'Tu cuenta fue creada, pero no se pudo enviar el correo de verificacion.');
        }

        return redirect()->route('verification.notice')->with('success', 'Se envió un código de verificación a tu correo.');
    }

    public function showVerifyForm(Request $request)
    {
        return view('auth.verify');
    }

    public function verifyEmail(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string|max:10',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No existe una cuenta con ese correo.'])->withInput();
        }

        $verification = EmailVerificationToken::where('user_id', $user->id)
            ->where('token', $data['token'])
            ->first();

        if (!$verification) {
            return back()->withErrors(['token' => 'Código de verificación inválido o expirado.'])->withInput();
        }

        $user->email_verified_at = Carbon::now();
        $user->save();

        $verification->delete();
        $this->usuarioSync->sincronizarDesdeUser($user);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Tu cuenta ha sido verificada correctamente.');
    }

    public function resendVerification(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No existe una cuenta con ese correo.'])->withInput();
        }

        if ($user->email_verified_at) {
            return redirect()->route('login')->with('success', 'El correo ya está verificado. Por favor inicia sesión.');
        }

        $token = $this->createVerificationToken($user);

        if (!$this->sendVerificationCode($user, $token->token)) {
            return back()->withErrors([
                'email' => 'No se pudo enviar el correo de verificacion.',
            ])->withInput();
        }

        return back()->with('success', 'Se envió un nuevo código de verificación a tu correo.');
    }

    public function logout(Request $request)
    {
        $this->audit->registrar('LOGOUT', auth()->user()?->id_usuario, 'users', auth()->id(), $request);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function createVerificationToken(User $user)
    {
        EmailVerificationToken::where('user_id', $user->id)->delete();

        return EmailVerificationToken::create([
            'user_id' => $user->id,
            'token' => strtoupper(Str::random(6)),
            'created_at' => Carbon::now(),
        ]);
    }

    protected function sendVerificationCode(User $user, string $token): bool
    {
        try {
            Mail::to($user->email)->send(new VerificationCodeMail($user, $token));

            return true;
        } catch (Exception $exception) {
            report($exception);

            return false;
        }
    }
}
