<?php

namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/', // Solo letras y espacios
            ],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('clientes')->where(function ($query) {
                    return $query->where('producto_id', $this->producto_id);
                }),
            ],
            'celular' => [
                'required',
                'string',
                'regex:/^[0-9]{9}$/',
                'min:9',
                'max:9',
                Rule::unique('clientes')->where(function ($query) {
                    return $query->where('producto_id', $this->producto_id);
                }),
            ],
            'producto_id' => [
                'nullable',
                'required',
                'integer',
                'exists:productos,id'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede exceder 100 caracteres.',
            'name.regex' => 'El nombre solo puede contener letras y espacios.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El formato del correo es inválido.',
            'email.max' => 'El correo no puede exceder 100 caracteres.',
            'email.unique' => 'El correo ya está registrado.',
            'celular.required' => 'El celular es obligatorio.',
            'celular.regex' => 'El celular solo puede contener números.',
            'celular.min' => 'El celular debe tener exactamente 9 dígitos.',
            'celular.max' => 'El celular debe tener exactamente 9 dígitos.',
            'celular.unique' => 'El número de celular ya está registrado.',
            'email.unique' => 'Este correo ya está registrado para este producto.',
            'celular.unique' => 'Este número de celular ya está registrado para este producto.',
            'producto_id.required' => 'El producto es obligatorio.',
            'producto_id.exists' => 'El producto seleccionado no es válido.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Los datos proporcionados no son válidos.',
            'errors' => $validator->errors()
        ], 422));
    }
}
