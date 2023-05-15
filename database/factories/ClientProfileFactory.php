<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientProfile>
 */
class ClientProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'last_name'         => $this->faker->firstName(),
            'middle_name'       => $this->faker->lastName(),
            'phone_number'      => $this->faker->e164PhoneNumber(),
            'street'            => $this->faker->streetName,
            'house_number'      => $this->faker->buildingNumber,
            'neighborhood'      => $this->faker->secondaryAddress(),
            'municipality'      => $this->faker->city,
            'state'             => $this->faker->state(),
            'postal_code'       => $this->faker->postcode,
            'country'           => $this->faker->country(),
            'birth_date'        => $this->faker->dateTimeBetween('-50 years', '-25 years')->format('Y-m-d'),
            'score'             => $this->faker->randomFloat(2, 300, 850),
            'rfc'               => $this->faker->regexify('[A-Z]{4}[0-9]{6}[A-Z0-9]{3}'),
        ];
    }
}
