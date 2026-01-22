<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DonationTransaction>
 */
class DonationTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'donation_id' => Donation::factory(),
            'member_id' => Member::factory(),
            'donor_name' => $this->faker->name(),
            'donor_email' => $this->faker->email(),
            'donor_phone' => $this->faker->phoneNumber(),
            'amount' => $this->faker->numberBetween(10000, 1000000),
            'donation_date' => $this->faker->date(),
            'is_anonymous' => $this->faker->boolean(),
            'notes' => $this->faker->sentence(),
            'receipt_path' => null,
            'status' => 'paid',
            'verified_by' => null,
            'verified_at' => null,
        ];
    }
}
