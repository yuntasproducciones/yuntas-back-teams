<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DimensionSeeder extends Seeder
{
    public function run(): void
    {
        $dimensiones = [
            // Producto 1: Letreros Neón LED
            ['id_producto' => 1, 'tipo' => 'alto', 'valor' => '30-150 cm', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id_producto' => 1, 'tipo' => 'largo', 'valor' => '40-200 cm', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id_producto' => 1, 'tipo' => 'ancho', 'valor' => '5 cm', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ];

        DB::table('dimensions')->insert($dimensiones);
    }
}
