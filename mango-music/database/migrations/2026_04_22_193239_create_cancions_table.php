<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cancions', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->unsignedBigInteger('artista_id');
            $table->string('duracion');
            $table->string('archivo_audio')->nullable(); // Para guardar el audio
            $table->timestamps();

            $table->foreign('artista_id')->references('id')->on('artistas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancions');
    }
};
