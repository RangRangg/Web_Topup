<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\AuditLog;
use App\Models\JokiProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan oleh administrator.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            $action = $user->isAdmin() ? 'admin_login' : 'user_login';
            AuditLog::create([
                'user_id' => $user->id,
                'action' => $action,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => $user->isAdmin()
                    ? "Administrator {$user->name} berhasil login ke sistem kontrol."
                    : "Pengguna {$user->name} ({$user->role}) berhasil login.",
            ]);

            return $this->redirectBasedOnRole($user)
                ->with('success', "Selamat datang kembali, {$user->name}!");
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $role = $validated['role'] ?? 'customer';

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $role,
        ]);

        // If registered as a joki booster, create their initial JokiProfile
        if ($role === 'joki') {
            JokiProfile::create([
                'user_id' => $user->id,
                'nickname' => $user->name,
                'phone' => $user->phone,
                'bio' => 'Booster baru di platform Aethereal Joki.',
                'specialized_games' => ['Mobile Legends', 'Valorant'],
                'highest_rank' => 'Mythic / Diamond',
                'rating' => 5.00,
                'status' => 'available',
                'is_verified' => false,
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'user_registered',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'details' => "Akun baru {$user->name} berhasil didaftarkan sebagai {$role}.",
        ]);

        return $this->redirectBasedOnRole($user)
            ->with('success', 'Akun berhasil dibuat! Selamat datang di Aethereal Joki.');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'user_logout',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'details' => "Pengguna {$user->name} telah logout.",
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'Anda telah berhasil logout.');
    }

    protected function redirectBasedOnRole(User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isJoki()) {
            return redirect()->route('joki.dashboard');
        }

        return redirect()->route('dashboard');
    }
}
