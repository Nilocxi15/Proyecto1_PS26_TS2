<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistroCiudadanoRequest extends FormRequest
{
    /**
     * Determina permiso del usuario para realizar esta solicitud
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación a aplicar en el request
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100|min:3',
            'email' => 'required|email|max:100|unique:usuarios,email',
            'telefono' => 'required|string|max:15|regex:/^[0-9]+$/',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ];
    }

    /**
     * Mensajes de error
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'Debe proporcionar un correo electrónico válido',
            'email.unique' => 'Este correo electrónico ya está registrado',
            'email.max' => 'El correo no puede exceder 100 caracteres',
            
            'telefono.required' => 'El teléfono es obligatorio',
            'telefono.max' => 'El teléfono no puede exceder 15 caracteres',
            'telefono.regex' => 'El teléfono solo puede contener números',
            
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            
            'password_confirmation.required' => 'Debe confirmar su contraseña',
            'password_confirmation.min' => 'La confirmación debe tener al menos 8 caracteres',
        ];
    }
}
