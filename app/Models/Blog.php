<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Blog extends Model
{
    use HasFactory;
    protected $table = 'blogs';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'producto_id',
        'subtitulo',
        'imagen_principal'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'id');
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(BlogImagenes::class, 'blog_id'); 
    }

    public function parrafos(): HasMany
    {
        return $this->hasMany(BlogParrafos::class, 'blog_id'); 
    }
}
