<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade los campos 'tipo' y 'activo' a la tabla de marcadores.
     * - tipo: categoría del marcador (1=Entradas, 2=Tiendas, etc.)
     * - activo: permite desactivar marcadores sin eliminarlos
     */
    public function up(): void
    {
        Schema::table('marcadors', function (Blueprint $table) {
            $table->unsignedTinyInteger('tipo')->default(1)->after('longitud');
            $table->boolean('activo')->default(true)->after('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marcadors', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'activo']);
        });
    }
};
