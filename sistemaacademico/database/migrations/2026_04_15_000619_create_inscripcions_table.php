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
        Schema::create('inscripcions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_alumno')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('id_materia')->constrained('materias')->onDelete('cascade');
            $table->enum('ciclo', ['Ciclo I', 'Ciclo II']);
            $table->year('anio');
            $table->decimal('nota_final', 4, 2)->default(0.00);
            $table->enum('estado_materia', ['Cursando', 'Aprobada', 'Reprobada', 'Retirada'])->default('Cursando');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripcions');
    }
};
