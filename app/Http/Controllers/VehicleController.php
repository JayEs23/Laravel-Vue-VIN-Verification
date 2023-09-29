<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use GuzzleHttp\Client;

class VehicleController extends Controller
{
    public function decodeVin(Request $request, $vin)
    {
        $rapidApiKey = env('RAPIDAPI_KEY');
        // Make a request to the VIN Decoder API
        $client = new Client();

        $response = $client->request('GET', 'https://vindecoder.p.rapidapi.com/decode_vin', [
            'headers' => [
                'X-RapidAPI-Host' => 'vindecoder.p.rapidapi.com',
                'X-RapidAPI-Key' => $rapidApiKey,
            ],
            'query' => [
                'vin' => $vin,
            ],
        ]);

        // Check if the request was successful
        if ($response->getStatusCode() == 200) {
            $decodedData = json_decode($response->getBody(), true);

            // Store the decoded data in the vehicles table
            $vehicle = Vehicle::create([
                'vin' => $vin,
                'make' => $decodedData['make'],
                'model' => $decodedData['model'],
                'year' => $decodedData['year'],
                'color' => $decodedData['color'],
                'salvage_data' => $decodedData['salvage_data'],
                'user_id' => auth()->user()->id, // Assuming user is authenticated
            ]);

            return response()->json(['message' => 'VIN decoding successful', 'vehicle' => $vehicle]);
        }

        // Handle API request failure
        return response()->json(['message' => 'Failed to decode VIN'], $response->getStatusCode());
    }
}

