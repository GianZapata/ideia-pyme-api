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
        return [
            'name'                   => $this->faker->company(),
            'score'                  => $this->faker->randomFloat(2, 300, 850),
            'rfc'                    => $this->faker->regexify('[A-Z]{4}[0-9]{6}[A-Z0-9]{3}'),
            'anioConstitucion'       => $this->faker->year,
            'sector_actividad'       => $this->faker->randomElement(self::$sectorActividad),
            'ventas'                 => $this->faker->numberBetween(10000, 500000),
            'ventasAnterior'         => $this->faker->numberBetween(10000, 500000),
            'trabActivo'             => $this->faker->numberBetween(0, 1000),
            'otrosIng'               => $this->faker->numberBetween(0, 500000),
            'resExplotacion'         => $this->faker->numberBetween(0, 500000),
            'resFinanciero'          => $this->faker->numberBetween(0, 500000),
            'resAntesImp'            => $this->faker->numberBetween(0, 500000),
            'deudoresComerciales'    => $this->faker->numberBetween(0, 1000000),
            'inversionesFin'         => $this->faker->numberBetween(0, 1000000),
            'efectivoLiquidez'       => $this->faker->numberBetween(0, 1000000),
            'activoTotal'            => $this->faker->numberBetween(1000000, 500000),
            'pasivoNoCirculante'     => $this->faker->numberBetween(0, 1000000),
            'provisionesLargoPlazo'  => $this->faker->numberBetween(0, 1000000),
            'pasivoCirculante'       => $this->faker->numberBetween(0, 1000000),
            'capitalContable'        => $this->faker->numberBetween(0, 1000000),
            'prestamosActuales'      => $this->faker->numberBetween(0, 1000000),
            'antiguedadEmpresa'      => $this->faker->numberBetween(0, 100),
            'reconocimientoMercado'  => $this->faker->numberBetween(0, 5),
            'informeComercial'       => $this->faker->numberBetween(0, 5),
            'infraestructura'        => $this->faker->numberBetween(0, 5),
            'problemasLegales'       => $this->faker->numberBetween(0, 5),
            'calidadCartera'         => $this->faker->numberBetween(0, 5),
            'referenciasBancarias'   => $this->faker->numberBetween(0, 5),
            'referenciasComerciales' => $this->faker->numberBetween(0, 5),
            'importanciaMop'         => $this->faker->numberBetween(0, 5),
            'perteneceHolding'       => $this->faker->numberBetween(0, 5),
            'idAnalisis'             => $this->faker->numberBetween(1, 100),
        ];
    }
}
