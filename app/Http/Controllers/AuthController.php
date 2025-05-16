<?php
namespace App\Http\Controllers;

use App\Models\DarkUser;
use App\Models\DarkUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Models\OtpVerification;
use App\Mail\WelcomeMail;
use Illuminate\Support\Str;
use Google\Client as GoogleClient;


class AuthController extends Controller {
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:dark_users',
            // 'password' => 'required|string|min:8|confirmed',
            'password' => 'required|string|min:8',

            'gender' => 'required|in:male,female',
            'birthdate' => 'required|date',
            'picture' => 'nullable|image|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $age = \Carbon\Carbon::parse($request->birthdate)->age;

        $user = DarkUsers::create([
            'name' => $request->name,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
            'birthdate' => $request->birthdate,
            'age' => $age,
            'picture' => $request->file('picture') ? $request->file('picture')->store('profile_pictures', 'public') : null,
            'request_id' => strtoupper(\Illuminate\Support\Str::random(8)),
        ]);

        Mail::to($user->email)->send(new WelcomeMail($user));


        $token = $user->createToken('dark_vault_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'profile_picture_url' => asset('storage/' . $user->picture) 
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
    
        $user = DarkUsers::where('email', $credentials['email'])->first();
    
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    
        $otpRecord = OtpVerification::where('dark_users_id', $user->id)->first();
    
        $isVerified = $otpRecord ? $otpRecord->is_verified : false;
    
        if ($isVerified) {
            $token = $user->createToken('dark_vault_token')->plainTextToken;
    
            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token,
                'is_verified' => true
            ]);
        }
    
        // Generate OTP if not verified
        $otpCode = rand(100000, 999999);
        OtpVerification::updateOrCreate(
            ['dark_users_id' => $user->id],
            ['otp_code' => $otpCode, 'is_verified' => false, 'verified_at' => null]
        );
    
        // Send OTP via email
        Mail::to($user->email)->send(new OtpMail($otpCode));
    
        $token = $user->createToken('dark_vault_token')->plainTextToken;
    
        return response()->json([
            'message' => 'OTP sent to your email. Please verify to continue.',
            'user' => $user,
            'token' => $token,
            'is_verified' => false
        ]);
    }

    public function googleAuth(Request $request)
    {
        try {
            \Log::info('Received token:', ['token' => $request->token]);
            
            if (empty($request->token)) {
                throw new \Exception('No token provided');
            }

            $client = new GoogleClient(['client_id' => config('services.google.client_id')]);
            $payload = $client->verifyIdToken($request->token);

            if (!$payload) {
                throw new \Exception('Invalid token');
            }

            $user = DarkUsers::where('email', $payload['email'])->first();

            if (!$user) {
                $user = DarkUsers::create([
                    'name' => $payload['given_name'] ?? '',
                    'lastname' => $payload['family_name'] ?? '',
                    'email' => $payload['email'],
                    'password' => Hash::make(Str::random(16)),
                    'gender' => 'male',
                    'birthdate' => now()->subYears(18),
                    'age' => 18,
                    'picture' => $payload['picture'] ?? null,
                    'request_id' => strtoupper(Str::random(8)),
                ]);

                Mail::to($user->email)->send(new WelcomeMail($user));
            }

            $token = $user->createToken('dark_vault_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'is_verified' => true,
                'profile_picture_url' => $user->picture ? asset('storage/' . $user->picture) : null,
            ]);

        } catch (\Exception $e) {
            \Log::error('Google Auth Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Google Auth failed', 
                'details' => $e->getMessage()
            ], 500);
        }
    }
    public function verifyOtp(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:dark_users,id',
        'otp_code' => 'required|digits:6'
    ]);

    $otpRecord = OtpVerification::where('dark_users_id', $request->user_id)
                                ->where('otp_code', $request->otp_code)
                                ->first();

    if (!$otpRecord) {
        return response()->json(['error' => 'Invalid OTP'], 400);
    }

    // Mark OTP as verified
    $otpRecord->update([
        'is_verified' => true,
        'verified_at' => now()
    ]);

    // Generate auth token
    $user = DarkUsers::find($request->user_id);
    $token = $user->createToken('dark_vault_token')->plainTextToken;

    return response()->json([
        'message' => 'OTP verified successfully.',
        'token' => $token,
        'user' => $user
    ]);
}
}
