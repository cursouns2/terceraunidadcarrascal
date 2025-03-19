<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (!str_ends_with($value, '@gmail.com')) {
                        $fail('El correo debe ser una dirección de Gmail.');
                    }
                },
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-zA-Z])(?=.*[0-9]).+$/', // Alfanumérico
                'confirmed', // Requiere el campo `password_confirmation`
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'La contraseña debe tener al menos una letra y un número.',
        ];
    }
}
