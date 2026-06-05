<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mongodb';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ── Colección: usuarios ──────────────────────────────────────────
        if (!Schema::hasTable('usuarios')) {
            Schema::connection('mongodb')->create('usuarios', function (Blueprint $collection) {
                $collection->unique('email');
                $collection->index('rol');
            });
        }

        // ── Colección: canciones ─────────────────────────────────────────
        if (!Schema::hasTable('canciones')) {
            Schema::connection('mongodb')->create('canciones', function (Blueprint $collection) {
                $collection->index('user_id');
                $collection->index('genero');
            });
        }

        // ── Colección: artistas ──────────────────────────────────────────
        if (!Schema::hasTable('artistas')) {
            Schema::connection('mongodb')->create('artistas', function (Blueprint $collection) {
                $collection->index('genero_musical');
            });
        }

        // ── Colección: eventos ───────────────────────────────────────────
        if (!Schema::hasTable('eventos')) {
            Schema::connection('mongodb')->create('eventos', function (Blueprint $collection) {
                $collection->index('user_id');
                $collection->index('fecha');
            });
        }

        // ── Colección: seguidores ────────────────────────────────────────
        if (!Schema::hasTable('seguidores')) {
            Schema::connection('mongodb')->create('seguidores', function (Blueprint $collection) {
                $collection->index('seguidor_id');
                $collection->index('artista_id');
                $collection->unique(['seguidor_id', 'artista_id']);
            });
        }

        // ── Colección: likes ─────────────────────────────────────────────
        if (!Schema::hasTable('likes')) {
            Schema::connection('mongodb')->create('likes', function (Blueprint $collection) {
                $collection->index('usuario_id');
                $collection->index('cancion_id');
                $collection->unique(['usuario_id', 'cancion_id']);
            });
        }

        // ── Colección: personal_access_tokens (Sanctum) ──────────────────
        if (!Schema::hasTable('personal_access_tokens')) {
            Schema::connection('mongodb')->create('personal_access_tokens', function (Blueprint $collection) {
                $collection->index('tokenable_id');
                $collection->unique('token');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('usuarios');
        Schema::connection('mongodb')->dropIfExists('canciones');
        Schema::connection('mongodb')->dropIfExists('artistas');
        Schema::connection('mongodb')->dropIfExists('eventos');
        Schema::connection('mongodb')->dropIfExists('seguidores');
        Schema::connection('mongodb')->dropIfExists('likes');
        Schema::connection('mongodb')->dropIfExists('personal_access_tokens');
    }
};
