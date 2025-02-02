<?php
namespace App\Http\Controllers;

use App\Models\DarkUser;
use App\Models\DarkUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
        ]);

        $token = $user->createToken('dark_vault_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = DarkUsers::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('dark_vault_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }
}
