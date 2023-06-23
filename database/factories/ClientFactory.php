<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\client>
 */
class ClientFactory extends Factory
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
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {


        $countries = config('countries');

        $country = $this->faker->randomElement(array_keys($countries));

        $federalEntities = collect(config('federal_entities'));
        $randomEntity = $federalEntities->random();
        $stateSlug = $randomEntity['slug'];

        $randomMunicipality = collect($randomEntity['municipalities'])->random();
        $municipalityCode = $randomMunicipality['code'];

        return [
            'name'              => $this->faker->company(),
            'score'             => $this->faker->randomFloat(2, 300, 850),
            'rfc'               => $this->faker->mexicanRfcFisica(),
            'anioConstitucion'  => $this->faker->year,
            'sector_actividad'  => $this->faker->randomElement(self::$sectorActividad),
            'street'            => $this->faker->streetName,
            'house_number'      => $this->faker->buildingNumber,
            'neighborhood'      => $this->faker->secondaryAddress(),
            'postal_code'       => $this->faker->postcode,
            'city'              => $this->faker->city,
            'country'           => $country,
            'state'             => $stateSlug,
            'municipality'      => $municipalityCode
        ];
    }
}
