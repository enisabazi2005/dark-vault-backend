<?php

namespace App\Http\Controllers;

use App\Http\Resources\StorePasswordResource;
use App\Models\DarkUsers;
use App\Models\StorePassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StorePasswordController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6',
        ]);
    
        $userId = Auth::user()->id ;
    
        if (!$userId) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
        $storePassword = StorePassword::create([
            'dark_users_id' => $userId, 
            'password' => bcrypt($request->password), 
        ]);
    
        return response()->json($storePassword, 201);
    }
    
    public function index()
    {
        $userPasswords = StorePassword::where('dark_users_id', Auth::user()->id)
                                       ->get();  
    
        return response()->json($userPasswords);
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:6', 
        ]);

        $storePassword = StorePassword::where('dark_users_id', Auth::user()->id)->findOrFail($id);

        $storePassword->password = bcrypt($request->password); 
        $storePassword->save();

        return response()->json($storePassword);
    }

    public function destroy($id)
    {
        $storePassword = StorePassword::where('dark_users_id', Auth::user()->id)->findOrFail($id);

        $storePassword->delete();

        return response()->json(['message' => 'Password deleted successfully.']);
    }
}
