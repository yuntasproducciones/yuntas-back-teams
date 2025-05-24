<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    public function run()
    {
        // Crear un producto
        $producto = Producto::create([
            'nombre' => 'Producto Ejemplo',
            'titulo' => 'Producto Premium',
            'subtitulo' => 'La mejor calidad',
            'lema' => 'Innovación y calidad',
            'descripcion' => 'Descripción detallada del producto ejemplo.',
            'stock' => 100,
            'precio' => 199.99,
            'seccion' => 'electrónica',
            'especificaciones' => json_encode([
                'color' => 'rojo',
                'material' => 'aluminio',
            ]),
        ]);

        // Crear dimensiones (si tienes relación y modelo)
        if (method_exists($producto, 'dimensiones')) {
            $producto->dimensiones()->createMany([
                ['tipo' => 'alto', 'valor' => '10cm'],
                ['tipo' => 'ancho', 'valor' => '20cm'],
                ['tipo' => 'largo', 'valor' => '30cm'],
            ]);
        }
    }
}
