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
    private static $sectorActividad = [
        "AGRICULTURA_Y_GANADERIA",
        "INDUSTRIA_EXTRACTIVA",
        "INDUSTRIA_MANUFACTURERA",
        "ENERGIA",
        "AGUAS_Y_SANEAMIENTO",
        "CONSTRUCCION",
        "COMERCIAL",
        "TRANSPORTE",
        "HOTELERIA",
        "COMUNICACIÓN",
        "FINANZAS_Y_SEGUROS",
        "ACTIVIDADES_INMOBILIARIAS",
        "ACTIVIDADES_PROFESIONALES",
        "OTROS_SERVICIOS",
        "OTRAS_ACTIVIDADES"
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $files = collect(Storage::allFiles("public/xml"));

        $rfcs = $files->map(function ($file) {
            $segments = explode('/', $file);   // Obtén el path del archivo y divídelo en segmentos.
            $rfc = $segments[2]; // El RFC debería ser el tercer segmento en tu estructura de archivos.
            return $rfc;
        })->unique();

        $emisores = Emisor::whereIn('rfc', $rfcs)->get();

        /** @var \App\Models\Client $client **/
        foreach ($emisores as $rfc) {

            $countries = collect(config('countries'));
            $country = $countries->keys()->random();

            $federalEntities = collect(config('federal_entities'));
            $randomEntity = $federalEntities->first(); // Cambiado a first() en lugar de random()
            $stateSlug = $randomEntity['slug'];

            $randomMunicipality = collect($randomEntity['municipalities'])->first(); // Cambiado a first() en lugar de random()
            $municipalityCode = $randomMunicipality['code'];

            Client::create([
                'rfc' => $rfc->rfc,
                'name' => $rfc->nombre,
                'score'             => 500,
                'anioConstitucion'  => 2000, // Año de constitución
                'sector_actividad'  => self::$sectorActividad[array_rand(self::$sectorActividad)],
                'street'            => 'Calle 1',
                'house_number'      => '10',
                'neighborhood'      => 'Colonia 1',
                'postal_code'       => '00000',
                'city'              => 'Ciudad 1',
                'country'           => $country,
                'state'             => $stateSlug,
                'municipality'      => $municipalityCode
            ]);
        }
    }
}
