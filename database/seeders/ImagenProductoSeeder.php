<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImagenProductoSeeder extends Seeder
{
    public function run(): void
    {
        $imagenes = [
        [
            'producto_id' => 1,
            'url_imagen' => 'https://placehold.co/100x150/orange/white?text=letrero-neon-1',
            'texto_alt_SEO' => 'Descripción alternativa para SEO 1',
        ],
        ];

        DB::table('producto_imagenes')->insert($imagenes);
    }
}
