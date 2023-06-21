<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaludFinanciera>
 */
class SaludFinancieraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ventas'                 => $this->faker->numberBetween(100000000, 150000000),
            'ventasAnterior'         => $this->faker->numberBetween(100000000, 150000000),
            'trabActivo'             => $this->faker->numberBetween(0, 100000),
            'otrosIng'               => $this->faker->numberBetween(0, 5000),
            'resExplotacion'         => $this->faker->numberBetween(0, 50000000),
            'resFinanciero'          => $this->faker->numberBetween(0, 50000000),
            'resAntesImp'            => $this->faker->numberBetween(0, 50000000),
            'deudoresComerciales'    => $this->faker->numberBetween(0, 50000000),
            'inversionesFin'         => $this->faker->numberBetween(0, 50000000),
            'efectivoLiquidez'       => $this->faker->numberBetween(0, 50000000),
            'activoTotal'            => $this->faker->numberBetween(100000000, 150000000),
            'pasivoNoCirculante'     => $this->faker->numberBetween(0, 50000000),
            'provisionesLargoPlazo'  => $this->faker->numberBetween(0, 50000000),
            'pasivoCirculante'       => $this->faker->numberBetween(0, 50000000),
            'capitalContable'        => $this->faker->numberBetween(0, 50000000),
            'prestamosActuales'      => $this->faker->numberBetween(0, 50000000),

            'antiguedadEmpresa'      => $this->faker->numberBetween(1, 5), // 5: 3 a 6 meses, 4: 7 a 12 meses, 3: 1 a 2 años, 2: 3 a 5 años, 1: más de 5 años
            'reconocimientoMercado'  => $this->faker->numberBetween(1, 5), // 5: 3 a 6 meses, 4: 7 a 12 meses, 3: 1 a 2 años, 2: 3 a 5 años, 1: más de 5 años
            'informeComercial'       => $this->faker->numberBetween(1, 2),
            'infraestructura'        => $this->faker->randomElement([1, 5]),
            'problemasLegales'       => $this->faker->randomElement([1, 5]),
            'calidadCartera'         => $this->faker->numberBetween(1, 5),
            'referenciasBancarias'   => $this->faker->randomElement([1, 5]),
            'referenciasComerciales' => $this->faker->randomElement([1, 5]),
            'importanciaMop'         => $this->faker->randomElement([1, 5]),
            'perteneceHolding'       => $this->faker->randomElement([1, 5]),
        ];
    }
}
