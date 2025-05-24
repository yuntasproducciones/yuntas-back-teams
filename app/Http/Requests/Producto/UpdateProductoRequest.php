<?php

namespace App\Http\Requests\Producto;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'nullable|string|max:255',
            'link' => 'required|string|unique|max:255',
            'titulo' => 'nullable|string|max:255',
            'subtitulo' => 'nullable|string|max:255',
            'lema' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen_principal' => 'nullable|image',
            'stock' => 'nullable|integer|min:0',
            'precio' => 'nullable|numeric|min:0|max:99999999.99',
            'seccion' => 'nullable|string|max:100'
        ];
    }
}
