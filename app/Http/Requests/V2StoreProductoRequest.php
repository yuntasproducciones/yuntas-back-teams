<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class V2StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => "required|unique:productos,nombre," . $this->getProductId() . "|string|max:255",
            'link' => 'required|string|unique:productos,link,' . $this->getProductId() . '|max:255',
            'titulo' => "required|string|max:255",
            'subtitulo' => "required|string|max:255",
            'stock' => "required|integer|max:1000|min:0",
            'precio' => "required|string|max:100000|min:0",
            'seccion' => "required|string|max:255",
            'lema' => "required|string|max:255",
            'descripcion' => "required|string|max:65535",
            'especificaciones' => "required|string|max:65535",
            'imagenes' => "required|array|min:1|max:10",
            'imagenes.*' => "file|image|max:2048",
            'textos_alt' => "required|array|min:1|max:10",
            'textos_alt.*' => "string|max:255",
            'relacionados' => "required|array|min:1",
            'relacionados.*' => [
                'integer',
                'exists:productos,id',
                function ($attribute, $value, $fail) {
                    if ($value == $this->getProductId()) {
                        $fail('Un producto no puede estar relacionado consigo mismo.');
                    }
                },
            ],
        ];
    }

    private function getProductId()
    {
        // Para edición
        if ($this->route('producto')) {
            return $this->route('producto')->id;
        }

        // Para creación, retorna null
        return null;
    }
}
