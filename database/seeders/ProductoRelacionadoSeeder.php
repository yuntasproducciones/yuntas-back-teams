<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductoRelacionadoSeeder extends Seeder
{
    public function run(): void
    {
        $relaciones = [
            // Producto 1: Letreros Neón LED
            ['id_producto' => 1, 'id_relacionado' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
        ];

        DB::table('producto_relacionados')->insert($relaciones);
    }
}
