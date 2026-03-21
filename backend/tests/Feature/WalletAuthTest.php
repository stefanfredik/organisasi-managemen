<?php
namespace Tests\Feature;
use App\Models\User;
use App\Models\Wallet;
use Tests\TestCase;
class WalletAuthTest extends TestCase
{
    use \Illuminate\Foundation\Testing\WithoutMiddleware;

    public function test_admin_can_update_wallet()
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
             echo "No admin user found\n";
             return;
        }
        $wallet = Wallet::first();
        if (!$wallet) {
            $wallet = Wallet::create(['name'=>'Test', 'balance'=>0, 'is_active'=>true]);
        }
        echo "Admin ID: {$admin->id}, Wallet ID: {$wallet->id}\n";
        
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        $response = $this->actingAs($admin)->put("/wallets/{$wallet->id}", [
            'name' => 'Updated Wallet',
            'description' => 'Test Desc',
            'is_active' => true,
        ]);
        
        echo "Response Status: " . $response->getStatusCode() . "\n";
        if ($response->getStatusCode() == 403) {
             echo "403 content: " . $response->getContent() . "\n";
        }
    }
}
