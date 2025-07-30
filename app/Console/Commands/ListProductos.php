<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Producto;

class ListProductos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'productos:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista todos los productos con ID, titulo y link';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productos = Producto::select('id', 'titulo', 'link')->get();
        
        $this->info('Productos en la base de datos:');
        $this->line('');
        
        foreach ($productos as $producto) {
            $this->line("ID: {$producto->id} - Título: {$producto->titulo} - Link: {$producto->link}");
        }
        
        $this->line('');
        $this->info("Total de productos: " . $productos->count());
    }
}
