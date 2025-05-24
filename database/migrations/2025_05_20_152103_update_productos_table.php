<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('productos', 'mensaje_correo')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->dropColumn('mensaje_correo');
            });
        }

        Schema::table('productos', function (Blueprint $table) {
            $table->json('especificaciones')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->text('especificaciones')->change();
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->text('mensaje_correo')->nullable();
        });
    }
};
