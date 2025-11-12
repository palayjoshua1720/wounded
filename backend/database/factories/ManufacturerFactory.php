<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Manufacturer>
 */
class ManufacturerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'manufacturer_name' => $this->faker->company(),
            'primary_email'     => $this->faker->unique()->companyEmail(),
            'secondary_email'   => $this->faker->optional()->companyEmail(),
            'website'           => $this->faker->optional()->url(),
            'contact_person'    => $this->faker->name(),
            'contact_number'    => $this->faker->phoneNumber(),
            'address'           => $this->faker->address(),
            'filepath'          => 'uploads/manufacturers/' . $this->faker->uuid() . '.jpg',
            'manufacturer_status' => $this->faker->randomElement([0, 1, 2]),
        ];
    }
}
