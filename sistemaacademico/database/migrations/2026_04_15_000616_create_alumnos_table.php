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
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('email', 100)->unique()->nullable();
            $table->string('telefono', 9)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->enum('departamento', [
                'Ahuachapán', 'Santa Ana', 'Sonsonate', 'Chalatenango', 'La Libertad', 
                'San Salvador', 'Cuscatlán', 'La Paz', 'Cabañas', 'San Vicente', 
                'Usulután', 'San Miguel', 'Morazán', 'La Unión'
            ])->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
