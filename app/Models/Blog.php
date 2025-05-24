<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Blog extends Model
{
    use HasFactory;
    protected $table = 'blogs';
    protected $primaryKey = 'id_blog';
    public $timestamps = false;

    protected $fillable = [
        'producto_id',
        'link',
        'id_blog_head',
        'id_blog_body',
        'id_blog_footer',
        'fecha'
    ];

    public function head(){
        return $this->hasOne(BlogHead::class, 'id_blog_head', 'id_blog_head');
    }

    public function body(){
        return $this->hasOne(BlogBody::class, 'id_blog_body', 'id_blog_body');
    }

    public function footer(){
        return $this->hasOne(BlogFooter::class, 'id_blog_footer', 'id_blog_footer');
    }

    public function card(){
        return $this->belongsTo(Card::class, 'id_blog', 'id_blog');
    }

    public function producto(){
        return $this->belongsTo(Producto::class, 'producto_id', 'id');
    }

}
