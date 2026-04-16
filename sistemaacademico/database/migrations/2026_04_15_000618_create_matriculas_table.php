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
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_alumno')->constrained('alumnos')->onDelete('cascade');
            $table->year('anio_academico');
            $table->enum('ciclo', ['Ciclo I', 'Ciclo II', 'Ciclo Extraordinario']);
            $table->date('fecha_pago')->nullable();
            $table->enum('estado', ['Pendiente', 'Pagado', 'Retirado'])->default('Pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};
