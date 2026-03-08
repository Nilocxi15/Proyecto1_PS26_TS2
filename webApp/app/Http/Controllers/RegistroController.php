<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroCiudadanoRequest;
use App\Services\UsuarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class RegistroController extends Controller
{
    protected $usuarioService;

    /**
     * Constructor
     */
    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    /**
     * Mostrar el formulario de registro
     *
     * @return View
     */
    public function mostrarFormulario(): View
    {
        return view('register');
    }

    /**
     * Procesar el registro de un nuevo ciudadano
     *
     * @param RegistroCiudadanoRequest $request
     * @return RedirectResponse
     */
    public function registrar(RegistroCiudadanoRequest $request): RedirectResponse
    {
        try {
            // Los datos ya vienen validados por el FormRequest
            $datos = $request->validated();

            // Registrar el ciudadano
            $usuario = $this->usuarioService->registrarCiudadano($datos);

            // Redirigir con mensaje de éxito
            return redirect()
                ->route('login')
                ->with('success', '¡Registro exitoso! Ya puedes iniciar sesión.');

        } catch (Exception $e) {
            // Redirigir con mensaje de error
            return redirect()
                ->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Error al registrar el usuario: ' . $e->getMessage());
        }
    }
}
