<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Emisor;
use App\Models\Receptor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $user = User::find(1);
        // if(!$user) return;

        // $rfcEmisores = Emisor::all();
        // $rfcReceptores = Receptor::all();
        // $rfcs = $rfcEmisores->concat($rfcReceptores);

        $files = collect(Storage::allFiles("public/xml"));

        $rfcs = $files->map(function ($file) {
            // Obtén el path del archivo y divídelo en segmentos.
            $segments = explode('/', $file);

            // El RFC debería ser el tercer segmento en tu estructura de archivos.
            $rfc = $segments[2];

            return $rfc;
        })->unique();

        $emisores = Emisor::whereIn('rfc', $rfcs)->get();

        /** @var \App\Models\Client $client **/
        foreach ($emisores as $rfc) {
            Client::factory()->create([
                'rfc' => $rfc->rfc,
                'name' => $rfc->nombre,
                // 'user_id' => $user->id,
            ]);
        }
    }
}
