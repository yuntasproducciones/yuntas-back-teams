<?php

namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:clientes,email,' . $this->route('id') . '|max:100',
            'celular' => 'sometimes|string|min:9|max:9|unique:clientes,celular,' . $this->route('id'),
            'producto_id' => 'sometimes|nullable|exists:productos,id',
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.exists' => 'El producto seleccionado no existe.',
            'producto_id' => 'sometimes|exists:productos,id',

        ];
    }
}
