<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_decode_vin()
    {
        // Create a user
        $user = User::factory()->create();

        // Simulate authentication
        $this->actingAs($user);

        $vin = '4F2YU09161KM33122';

        $response = $this->json('GET', "/api/vehicles/{$vin}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'vehicle' => [
                    'id',
                    'vin',
                    'make',
                    'model',
                    'year',
                    'color',
                    'salvage_data',
                    'user_id',
                    'created_at',
                    'updated_at',
                ],
            ]);

            $vehicle = Vehicle::where('vin', $vin)->first();

            $this->assertNotNull($vehicle); // Check if the vehicle is stored in the database
        
            $response = $this->json('GET', '/api/search-history');
        
            $response->assertStatus(200)
                ->assertJsonStructure(['search_history' => [['id', 'user_id', 'vin', 'searched_at']]]);
        
            // Check if the latest search history entry corresponds to the decoded VIN
            $latestSearch = $response->json('search_history')[0];
            $this->assertEquals($latestSearch['vin'], $vin);
            $this->assertEquals($latestSearch['user_id'], auth()->user()->id);
        
    }

    // Add more test methods for other vehicle-related functionalities.
}
