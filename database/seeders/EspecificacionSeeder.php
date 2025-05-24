<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EspecificacionSeeder extends Seeder
{
    public function run(): void
    {
        $especificaciones = [
            // Producto 1: Letreros Neón LED - Especificaciones
            ['id_producto' => 1, 'clave' => 'especificacion', 'valor' => 'Materiales duraderos', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id_producto' => 1, 'clave' => 'especificacion', 'valor' => '100% personalizables', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id_producto' => 1, 'clave' => 'especificacion', 'valor' => 'Eficiencia energética', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id_producto' => 1, 'clave' => 'especificacion', 'valor' => 'Adaptable a espacios', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            
            // Producto 1: Letreros Neón LED - Beneficios
            ['id_producto' => 1, 'clave' => 'beneficio', 'valor' => 'Iluminación con colores vibrantes', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id_producto' => 1, 'clave' => 'beneficio', 'valor' => 'Facil instalación', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id_producto' => 1, 'clave' => 'beneficio', 'valor' => 'Brinda personalidad', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id_producto' => 1, 'clave' => 'beneficio', 'valor' => 'Atractivo visual', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            
        //     // Producto 1: Letreros Neón LED - Imágenes extras
        //     ['id_producto' => 1, 'clave' => 'imagen_beneficios', 'valor' => '/Productos/letrero-neon-beneficios.webp', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 1, 'clave' => 'imagen_banner', 'valor' => '/Productos/letrero-neon-banner.webp', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],

        //     // Producto 2: Sillas y Mesas LED - Especificaciones
        //     ['id_producto' => 2, 'clave' => 'especificacion', 'valor' => 'Materiales de alta calidad', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 2, 'clave' => 'especificacion', 'valor' => '100% personalizables', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 2, 'clave' => 'especificacion', 'valor' => 'Bajo consumo de energía', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 2, 'clave' => 'especificacion', 'valor' => 'Adaptable a interiores y exteriores', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            
        //     // Producto 2: Sillas y Mesas LED - Beneficios
        //     ['id_producto' => 2, 'clave' => 'beneficio', 'valor' => 'Crea un ambiente único', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 2, 'clave' => 'beneficio', 'valor' => 'Fácil instalación', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 2, 'clave' => 'beneficio', 'valor' => 'Toque exclusivo a tu evento', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 2, 'clave' => 'beneficio', 'valor' => 'Adaptable a tu estilo', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            
        //     // Producto 2: Sillas y Mesas LED - Imágenes extras
        //     ['id_producto' => 2, 'clave' => 'imagen_beneficios', 'valor' => '/Productos/sillas-mesas-beneficios.webp', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 2, 'clave' => 'imagen_banner', 'valor' => '/Productos/sillas-mesas-banner.webp', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            
        //     // Producto 3: Pisos LED - Especificaciones
        //     ['id_producto' => 3, 'clave' => 'especificacion', 'valor' => 'Alta resolución y brillo', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 3, 'clave' => 'especificacion', 'valor' => 'Resistencia al peso', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 3, 'clave' => 'especificacion', 'valor' => 'Superficie antideslizante', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 3, 'clave' => 'especificacion', 'valor' => 'Soporta proyecciones HD', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            
        //     // Producto 3: Pisos LED - Beneficios
        //     ['id_producto' => 3, 'clave' => 'beneficio', 'valor' => 'Ambientes inmersivos', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 3, 'clave' => 'beneficio', 'valor' => 'Mayor interacción', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 3, 'clave' => 'beneficio', 'valor' => 'Flexibilidad de diseños', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 3, 'clave' => 'beneficio', 'valor' => 'Aumento de visibilidad', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            
        //     // Producto 3: Pisos LED - Imágenes extras
        //     ['id_producto' => 3, 'clave' => 'imagen_beneficios', 'valor' => '/Productos/pisos-led-beneficios.webp', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 3, 'clave' => 'imagen_banner', 'valor' => '/Productos/pisos-led-banner.webp', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            
        //     // Producto 4: Barras Pixel LED - Especificaciones
        //     ['id_producto' => 4, 'clave' => 'especificacion', 'valor' => 'Ideales para exposiciones', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 4, 'clave' => 'especificacion', 'valor' => 'Imágenes de alto relieve', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 4, 'clave' => 'especificacion', 'valor' => 'Tecnología eficiente y moderna', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 4, 'clave' => 'especificacion', 'valor' => 'Colores vibrantes', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            
        //     // Producto 4: Barras Pixel LED - Beneficios
        //     ['id_producto' => 4, 'clave' => 'beneficio', 'valor' => 'Imágenes de alto impacto', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 4, 'clave' => 'beneficio', 'valor' => 'Fácil instalación y mantenimiento', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 4, 'clave' => 'beneficio', 'valor' => 'Personalización rápida', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 4, 'clave' => 'beneficio', 'valor' => 'Alto atractivo visual', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            
        //     // Producto 4: Barras Pixel LED - Imágenes extras
        //     ['id_producto' => 4, 'clave' => 'imagen_beneficios', 'valor' => '/Productos/barras-pixel-beneficios.webp', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        //     ['id_producto' => 4, 'clave' => 'imagen_banner', 'valor' => '/Productos/barras-pixel-banner.webp', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
         ];

        DB::table('especificaciones')->insert($especificaciones);
    }
}
