<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ProductoImagenes;
use App\Http\Controllers\Api\V1\Blog\BlogController;

class Producto extends Model
{
    protected $fillable = [
        'nombre',
        'link',
        'titulo',
        'subtitulo',
        'stock',
        'precio',
        'seccion',
        'lema',
        'descripcion',
        'imagen_principal',
        'imagenes'
    ];

    public $timestamps = true;

    public function imagenes()
    {
        return $this->hasMany(ProductoImagenes::class, 'producto_id');
    }

    public function productos_relacionados()
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
    public function especificaciones(): HasMany
    {
        return $this->hasMany(Especificacion::class, 'producto_id');
    }
}
