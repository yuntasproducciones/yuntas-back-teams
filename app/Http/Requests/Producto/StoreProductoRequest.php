<?php

namespace App\Http\Requests\Producto;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepara los datos antes de validarlos.
     * Aquí normalizamos Swagger/Postman para que siempre sean arrays.
     */
    protected function prepareForValidation()
    {
        // Normalizar especificaciones
        if ($this->has('especificaciones') && is_string($this->especificaciones)) {
            $this->merge([
                'especificaciones' => json_decode($this->especificaciones, true) ?? [$this->especificaciones]
            ]);
        }

        // Normalizar beneficios
        if ($this->has('beneficios') && is_string($this->beneficios)) {
            $this->merge([
                'beneficios' => json_decode($this->beneficios, true) ?? [$this->beneficios]
            ]);
        }

        // Normalizar imagenes (si vienen como string desde Swagger)
        if ($this->has('imagenes') && is_string($this->imagenes)) {
            $this->merge([
                'imagenes' => json_decode($this->imagenes, true) ?? [$this->imagenes]
            ]);
        }

        // Normalizar tipos de imagen
        if ($this->has('imagen_tipos') && is_string($this->imagen_tipos)) {
            $this->merge([
                'imagen_tipos' => json_decode($this->imagen_tipos, true) ?? [$this->imagen_tipos]
            ]);
        }
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        Log::info('=== VALIDANDO REQUEST DE PRODUCTO ===');
        Log::info('Request data:', $this->all());
        Log::info('Request files:', $this->allFiles());

        return [
            'link' => 'required|string|unique:productos,link|max:255',
            'nombre' => 'required|string|max:255',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'seccion' => 'nullable|string|max:100',

            // Especificaciones y beneficios como arrays
            'especificaciones' => 'nullable|array|max:20',
            'especificaciones.*' => 'sometimes|string|max:500',

            'beneficios' => 'nullable|array|max:20',
            'beneficios.*' => 'sometimes|string|max:500',

            //Imagen Principal
            'imagen_principal' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:10240',

            // Imágenes adicionales
            'imagenes' => 'sometimes|array|max:10',
            'imagenes.*' => 'sometimes|nullable|image|mimes:jpeg,jpg,png,gif,webp|max:10240',

            // Tipos de imagen
            'imagen_tipos' => 'nullable|array',
            'imagen_tipos.*' => 'sometimes|string|in:imagen_hero,imagen_especificaciones,imagen_beneficios',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        Log::error('=== VALIDACIÓN FALLÓ ===');
        Log::error('Errores de validación:', $validator->errors()->toArray());
        Log::error('Request data durante fallo:', $this->all());
        Log::error('Request files durante fallo:', $this->allFiles());

        parent::failedValidation($validator);
    }
}
