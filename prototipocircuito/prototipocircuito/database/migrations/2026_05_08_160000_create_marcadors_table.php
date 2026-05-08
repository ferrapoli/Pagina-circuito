<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('marcadors', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('descripcion')->nullable();
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marcadors');
    }
};
