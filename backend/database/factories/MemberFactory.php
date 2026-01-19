<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['active', 'inactive']);

        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('08##########'),
            'address' => fake()->address(),
            'date_of_birth' => fake()->date('Y-m-d', '-20 years'),
            'join_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'status' => $status,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the member is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the member is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
