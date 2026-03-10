<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determina permiso del usuario para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validacion del inicio de sesion.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|max:100',
            'password' => 'required|string',
        ];
    }

    /**
     * Mensajes personalizados de validacion.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'El correo electronico es obligatorio',
            'email.email' => 'Debe proporcionar un correo electronico valido',
            'email.max' => 'El correo no puede exceder 100 caracteres',
            'password.required' => 'La contrasena es obligatoria',
        ];
    }
}
