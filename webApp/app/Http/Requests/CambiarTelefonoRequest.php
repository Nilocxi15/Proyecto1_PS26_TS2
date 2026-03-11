<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CambiarTelefonoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'telefono' => 'required|string|max:12|regex:/^[0-9]+$/',
        ];
    }

    public function messages(): array
    {
        return [
            'telefono.required' => 'El número de teléfono es obligatorio.',
            'telefono.max'      => 'El teléfono no puede exceder 12 caracteres.',
            'telefono.regex'    => 'El teléfono solo puede contener números.',
        ];
    }
}
