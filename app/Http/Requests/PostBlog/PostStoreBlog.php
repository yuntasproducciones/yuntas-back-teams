<?php

namespace App\Http\Requests\PostBlog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostStoreBlog extends FormRequest
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
    public function rules()
    {
        return [
            'producto_id' => 'required|integer|exists:productos,id',
            'subtitulo' => 'required|string|max:255',

            'imagen_principal' => 'required|file|image|max:2048',
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'required|image|max:2048',

            // 'text_alt' => 'required|array',
            // 'text_alt.*' => 'required|string|max:255',

            'parrafos' => 'required|array',
            'parrafos.*' => 'required|string|max:2047',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
