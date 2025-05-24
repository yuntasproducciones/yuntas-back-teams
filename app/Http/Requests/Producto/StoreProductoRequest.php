<?php

namespace App\Http\Requests\Producto;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
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
            'nombre' => 'required|string|max:255',
            'link' => 'required|string|unique:productos,link|max:255',
            'titulo' => 'required|string|max:255',
            'subtitulo' => 'nullable|string|max:255',
            'lema' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'imagen_principal' => 'required|image',
            'stock' => 'required|integer|min:0',
            'precio' => 'required|numeric|min:0|max:99999999.99',
            'seccion' => 'nullable|string|max:100',
            'especificaciones' => 'nullable|array'
        ];
    }
}
