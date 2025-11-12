<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Clinic>
 */
class ClinicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // System-generated unique clinic code (e.g., CLN-20251016-ABC123)
            'clinic_code' => 'CLN-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),

            // Random public UUID
            'clinic_public_id' => (string) Str::uuid(),

            // Random list of clinician IDs (stored as JSON)
            'assigned_clinician_ids' => json_encode($this->faker->randomElements(range(1, 20), rand(1, 4))),

            // Clinic info
            'clinic_name'   => $this->faker->company() . ' Clinic',
            'email'         => $this->faker->unique()->companyEmail(),
            'contact_person'=> $this->faker->name(),
            'phone'         => $this->faker->phoneNumber(),
            'address'       => $this->faker->address(),

            // Random status
            'clinic_status' => $this->faker->randomElement([0, 1, 2]),
        ];
    }
}
