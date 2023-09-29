<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $userData = [
            'full_name' => 'John Doe',
            'username' => 'johndoe',
            'IdNo' => '123456789',
            'email' => 'john@testmail.com',
            'phone_number' => '1234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->json('POST', '/api/register', $userData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'full_name',
                    'username',
                    'IdNo',
                    'email',
                    'phone_number',
                    'verification_status',
                ],
                'token',
            ]);
        
            $user = User::where('phone_number', $userData['phone_number'])->first();

        $this->assertFalse($user->verification_status); // Check initial verification status

        $response = $this->json('POST', '/api/verify', [
            'phone_number' => $userData['phone_number'],
            'verification_code' => $user->verification_code,
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Verification successful']);

        $user->refresh();
        $this->assertTrue($user->verification_status); // Check updated verification status

    }

    public function test_user_can_login()
    {
        // Register a user
        $userData = [
            'full_name' => 'John Doe',
            'username' => 'johndoe',
            'IdNo' => '123456789',
            'email' => 'john@testmail.com',
            'phone_number' => '1234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        $this->json('POST', '/api/register', $userData);

        // Attempt to login with correct credentials
        $loginCredentials = [
            'phone_number' => $userData['phone_number'],
            'password' => $userData['password'],
        ];
        $response = $this->json('POST', '/api/login', $loginCredentials);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);

        // Attempt to login with incorrect credentials
        $wrongCredentials = [
            'phone_number' => $userData['phone_number'],
            'password' => 'wrong_password',
        ];
        $response = $this->json('POST', '/api/login', $wrongCredentials);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_user_verification_fails_with_invalid_code()
    {
        // Register a user
        $userData = [
            'full_name' => 'John Doe',
            'username' => 'johndoe',
            'IdNo' => '123456789',
            'email' => 'john@testmail.com',
            'phone_number' => '1234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        $this->json('POST', '/api/register', $userData);

        // Attempt verification with an incorrect code
        $verificationData = [
            'phone_number' => $userData['phone_number'],
            'verification_code' => 'wrong_verification_code',
        ];
        $response = $this->json('POST', '/api/verify', $verificationData);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Invalid verification code']);
    }

}
