<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Donation>
 */
class DonationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $programName = $this->faker->sentence(3);
        return [
            'program_name' => $programName,
            'slug' => Str::slug($programName) . '-' . Str::random(5),
            'description' => $this->faker->paragraph(),
            'target_amount' => $this->faker->numberBetween(1000000, 100000000),
            'collected_amount' => 0,
            'start_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'status' => 'active',
            'is_public' => true,
            'created_by' => User::factory(),
        ];
    }
}
