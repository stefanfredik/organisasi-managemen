<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\DonationTransaction;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DonationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles if necessary or create them
        if (!Role::where('name', 'anggota')->exists()) {
            Role::create(['name' => 'anggota']);
        }
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin']);
        }
    }

    public function test_member_can_submit_donation_payment()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $user->assignRole('anggota');
        $member = Member::factory()->create(['user_id' => $user->id]);

        $donation = Donation::factory()->create([
            'target_amount' => 1000000,
            'collected_amount' => 0,
        ]);

        $file = UploadedFile::fake()->image('receipt.jpg');

        $response = $this->actingAs($user)->post(route('donations.pay', $donation->id), [
            'amount' => 50000,
            'donation_date' => now()->toDateString(),
            'receipt' => $file,
            'is_anonymous' => false,
            'notes' => 'Bismillah',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('donation_transactions', [
            'donation_id' => $donation->id,
            'member_id' => $member->id,
            'amount' => 50000,
            'status' => 'pending',
            'notes' => 'Bismillah',
        ]);

        // Assert file stored
        $transaction = DonationTransaction::where('donation_id', $donation->id)->first();
        Storage::disk('public')->assertExists($transaction->receipt_path);
    }

    public function test_admin_can_verify_donation()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $donation = Donation::factory()->create([
            'target_amount' => 1000000,
            'collected_amount' => 0,
        ]);

        $transaction = DonationTransaction::factory()->create([
            'donation_id' => $donation->id,
            'amount' => 50000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('donations.transactions.verify', $transaction->id), [
            'action' => 'approve',
            'notes' => 'Verified',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('donation_transactions', [
            'id' => $transaction->id,
            'status' => 'paid',
            'verified_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'collected_amount' => 50000,
        ]);
    }
}
