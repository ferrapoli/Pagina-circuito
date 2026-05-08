<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caminos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->jsonb('geojson');
            $table->string('estado')->default('abierto'); // abierto, obras, staff
            $table->string('color')->default('#E53935');   // hex color
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caminos');
    }
};
