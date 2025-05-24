<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ProductoImagenes;
use App\Models\Dimension;
use App\Http\Controllers\Api\V1\Blog\BlogController;

class Producto extends Model
{
    protected $fillable = [
        'nombre',
        'titulo',
        'subtitulo',
        'stock',
        'precio',
        'seccion',
        'lema',
        'descripcion',
        'especificaciones',
        'imagenes',
        'mensaje_correo'
    ];

    public $timestamps = true;

    public function dimensiones()
    {
        return $this->hasMany(Dimension::class, 'id_producto');
    }

    public function imagenes()
    {
        return $this->hasMany(ProductoImagenes::class, 'producto_id');
    }

    public function productosRelacionados()
    {
        return $this->belongsToMany(Producto::class, 'producto_relacionados', 'id_producto', 'id_relacionado');
    }

    public function interesados(): HasMany
    {
        return $this->hasMany(Interesado::class, 'producto_id', 'id');
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class, 'producto_id', 'id');
    }
}
