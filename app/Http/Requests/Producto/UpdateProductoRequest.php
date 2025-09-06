<?php

namespace App\Http\Requests\Producto;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isPut = $this->isMethod('put');
        $required = $isPut ? 'required' : 'sometimes';
        $productoId = $this->route('id');

        //$productId = $this->route('producto') ?? $this->route('id'); // Ajusta según tu ruta

        Log::info('=== VALIDANDO REQUEST DE ACTUALIZACIÓN DE PRODUCTO ===');
        Log::info('Request data:', $this->all());
        Log::info('Request files:', $this->allFiles());

        return [

            'link' => [$required,'string','unique:productos,link,' . $productoId, 'max:255'],

            'nombre' => [$required, 'string', 'max:255'],
            'titulo' => [$required, 'string', 'max:255'],
            'descripcion' => 'sometimes|nullable|string',
            'seccion' => 'sometimes|nullable|string|max:100',

            // Especificaciones y beneficios como arrays
            'especificaciones' => 'sometimes|nullable|array|max:20',
            'especificaciones.*' => 'sometimes|string|max:500',
            'beneficios' => 'sometimes|nullable|array|max:20',
            'beneficios.*' => 'sometimes|string|max:500',

            'imagen_principal' => 'sometimes|nullable|image|mimes:jpeg,jpg,png,gif,webp|max:10240',

            // Imágenes adicionales
            'imagenes' => 'sometimes|nullable|array|max:10',
            'imagenes.*' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:10240',

            // Array para tipos de imagen
            'imagen_tipos' => 'sometimes|nullable|array',
            'imagen_tipos.*' => 'string|in:imagen_hero,imagen_especificaciones,imagen_beneficios,imagen_popups',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Log::error('=== VALIDACIÓN DE UPDATE FALLÓ ===');
        Log::error('Errores:', $validator->errors()->toArray());
        Log::error('Request data:', $this->all());
        Log::error('Request files:', $this->allFiles());

        parent::failedValidation($validator);
    }
}
