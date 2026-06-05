<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CancionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Cancion::create([
            'titulo' => 'Te Felicito',
            'artista' => 'Shakira',
            'genero' => 'Pop',
            'url_audio' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
            'caratula' => 'https://i.scdn.co/image/ab67616d0000b273b7a8d5f6d7d7d7d7d7d7d7d7'
        ]);

        \App\Models\Cancion::create([
            'titulo' => 'Provenza',
            'artista' => 'Karol G',
            'genero' => 'Reggaeton',
            'url_audio' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3',
            'caratula' => 'https://i.scdn.co/image/ab67616d0000b273b7a8d5f6d7d7d7d7d7d7d7d7'
        ]);

        \App\Models\Cancion::create([
            'titulo' => 'Ojitos Lindos',
            'artista' => 'Bad Bunny',
            'genero' => 'Urbano',
            'url_audio' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3',
            'caratula' => 'https://i.scdn.co/image/ab67616d0000b273b7a8d5f6d7d7d7d7d7d7d7d7'
        ]);
    }
}
