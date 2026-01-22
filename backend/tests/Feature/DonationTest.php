<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\DonationTransaction;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DonationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_member_can_submit_donation_payment()
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'anggota', 'status' => 'active']);
        $member = Member::factory()->create(['user_id' => $user->id]);
        $donation = Donation::factory()->create();

        $response = $this->actingAs($user)->post(route('donations.pay', $donation), [
            'amount' => 50000,
            'donation_date' => now()->toDateString(),
            'receipt' => UploadedFile::fake()->image('receipt.jpg'),
            'is_anonymous' => false,
            'notes' => 'Bismillah',
            'donor_name' => $user->name,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('donation_transactions', [
            'donation_id' => $donation->id,
            'member_id' => $member->id,
            'amount' => 50000,
            'status' => 'pending',
            'notes' => 'Bismillah',
        ]);

        $transaction = DonationTransaction::first();
        $this->assertNotNull($transaction->receipt_path);
        Storage::disk('public')->assertExists($transaction->receipt_path);
    }

    public function test_admin_can_verify_donation_transaction()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $donation = Donation::factory()->create(['collected_amount' => 0]);
        $transaction = DonationTransaction::factory()->create([
            'donation_id' => $donation->id,
            'amount' => 50000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('donations.transactions.verify', $transaction), [
            'action' => 'approve',
            'notes' => 'Verified OK',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $transaction->refresh();
        $this->assertEquals('paid', $transaction->status);
        $this->assertEquals($admin->id, $transaction->verified_by);
        $this->assertNotNull($transaction->verified_at);
        $this->assertStringContainsString('Verified OK', $transaction->notes);

        $donation->refresh();
        $this->assertEquals(50000, $donation->collected_amount);
    }

    public function test_admin_can_reject_donation_transaction()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $donation = Donation::factory()->create(['collected_amount' => 0]);
        $transaction = DonationTransaction::factory()->create([
            'donation_id' => $donation->id,
            'amount' => 50000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('donations.transactions.verify', $transaction), [
            'action' => 'reject',
            'notes' => 'Invalid receipt',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $transaction->refresh();
        $this->assertEquals('rejected', $transaction->status);
        $this->assertEquals($admin->id, $transaction->verified_by);
        $this->assertNotNull($transaction->verified_at);
        $this->assertStringContainsString('Invalid receipt', $transaction->notes);

        $donation->refresh();
        $this->assertEquals(0, $donation->collected_amount);
    }

    public function test_member_cannot_verify_transaction()
    {
        $user = User::factory()->create(['role' => 'anggota']);
        $transaction = DonationTransaction::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($user)->post(route('donations.transactions.verify', $transaction), [
            'action' => 'approve',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_record_manual_donation_linked_to_member()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $member = Member::factory()->create();
        $donation = Donation::factory()->create();

        $response = $this->actingAs($admin)->post(route('donations.transactions.store', $donation), [
            'member_id' => $member->id,
            'amount' => 100000,
            'donation_date' => now()->toDateString(),
            'donor_name' => $member->full_name,
            'notes' => 'Manual entry linked',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('donation_transactions', [
            'donation_id' => $donation->id,
            'member_id' => $member->id,
            'amount' => 100000,
            'status' => 'paid', // Manual entry is auto-paid
            'verified_by' => $admin->id,
        ]);
    }
}
