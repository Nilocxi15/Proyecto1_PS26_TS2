<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{    
    // Muestra la vista de login.     
    public function mostrarFormulario(): View
    {
        return view('welcome');
    }

    // Procesa inicio de sesión y redirige según rol.
    public function iniciarSesion(LoginRequest $request): RedirectResponse
    {
        $credenciales = $request->validated();

        if (!Auth::attempt([
            'email' => $credenciales['email'],
            'password' => $credenciales['password'],
            'estado' => 'activo',
        ])) {
            return redirect()
                ->back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Credenciales invalidas o usuario inactivo.',
                ]);
        }

        $request->session()->regenerate();

        $usuario = Auth::user();

        return redirect()->route($this->obtenerRutaPorRol((int) $usuario->id_role));
    }

    // Cierre de sesión del usuario autenticado y redirección al login.
    public function cerrarSesion(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }

    // Mapeo id de rol a ruta de inicio correspondiente.
    private function obtenerRutaPorRol(int $idRol): string
    {
        return match ($idRol) {
            1 => 'home-admin',
            2 => 'home-coordinator',
            3 => 'home-public',
            4 => 'home-citizen',
            5 => 'home-auditor',
            default => 'login',
        };
    }
}
