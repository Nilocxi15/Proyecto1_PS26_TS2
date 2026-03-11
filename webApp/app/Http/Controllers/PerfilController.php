<?php

namespace App\Http\Controllers;

use App\Http\Requests\CambiarTelefonoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PerfilController extends Controller
{
    // Actualizar el número de teléfono del usuario
    public function cambiarTelefono(CambiarTelefonoRequest $request): JsonResponse
    {
        Auth::user()->update(['telefono' => $request->telefono]);

        return response()->json([
            'success'  => true,
            'message'  => '¡Número de teléfono actualizado exitosamente!',
            'telefono' => $request->telefono,
        ]);
    }
}
