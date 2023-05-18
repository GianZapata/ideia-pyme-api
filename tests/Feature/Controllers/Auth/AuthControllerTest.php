<?php

namespace Tests\Feature\Controllers\Auth;

use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    // use RefreshDatabase;

    /**
     * A basic feature test example.
     */

    public function test_the_application_check_email_route_returns_true_if_email_exists(): void {

        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->postJson('/api/users/check-email', [
            'email' => $user->email
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'exists' => true
        ]);
    }

    public function test_the_application_returns_false_if_email_does_not_exist()
    {
        $response = $this->postJson('/api/users/check-email', [
            'email' => 'nonexistentemail@ideia.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'exists' => false,
        ]);
    }
}
