<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuario de prueba
        User::create([
            'nombre'           => 'Admin',
            'email'            => 'admin@mangomusic.com',
            'password'         => Hash::make('password123'),
            'rol'              => 'artista',
            'nombre_artistico' => 'Admin Artist',
        ]);

        // Canciones de prueba
        $this->call(CancionSeeder::class);
    }
}
