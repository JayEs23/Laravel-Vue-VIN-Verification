<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'full_name' => 'string|max:255',
            'username' => 'required|string|unique:users|max:255',
            'IdNo' => 'string|unique:users|max:255',
            'email' => 'string|email|unique:users|max:255',
            'phone_number' => 'required|string|unique:users|max:15',
            'password' => 'required|string|min:6|confirmed',
        ]);
        
        // Generate a random verification code
        $verificationCode = Str::random(6);

        // Create user object and save it in the database
        $user = User::create([
            'full_name' => $validatedData['full_name'],
            'username' => $validatedData['username'],
            'IdNo' => $validatedData['IdNo'],
            'email' => $validatedData['email'],
            'phone_number' => $validatedData['phone_number'],
            'verification_status' => false,
            'password' => Hash::make($validatedData['password']),
            'verification_code' => $verificationCode,
        ]);

        // Send the verification code to the user's phone number.
        $this->sendVerificationCode($user->phone_number, $verificationCode);

        $token = JWTAuth::fromUser($user);

        return response()->json(['message' => 'User registration successful', 'user' => $user, 'token' => $token]);
    }

    // Send verification code method
    private function sendVerificationCode($phoneNumber, $verificationCode)
    {
        $rapidApiKey = env('RAPIDAPI_KEY');
        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', 'https://telesign-telesign-send-sms-verification-code-v1.p.rapidapi.com/sms-verification-code', [
            'headers' => [
                'X-RapidAPI-Host' => 'telesign-telesign-send-sms-verification-code-v1.p.rapidapi.com',
                'X-RapidAPI-Key' => $rapidApiKey,
            ],
            'query' => [
                'phoneNumber' => $phoneNumber,
                'verifyCode' => $verificationCode,
                'appName' => "Gems Task",
            ],
        ]);

        return $response->getBody();
    }

    public function confirmVerificationCode(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'verification_code' => 'required|string',
        ]);

        $user = User::where('phone_number', $request->input('phone_number'))
            ->where('verification_code', $request->input('verification_code'))
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid verification code'], 400);
        }

        // Update the verification status
        $user->verification_status = true;
        $user->save();

        return response()->json(['message' => 'Verification successful']);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone_number' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json(['message' => 'Logged in Successfully','token' => $token],200);
    }
}
