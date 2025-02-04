<?php

namespace App\Http\Controllers;

use App\Models\StoreEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreEmailController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string',
        ]);
    
        $userId = Auth::user()->id ;
    
        if (!$userId) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
        $storeEmail = StoreEmail::create([
            'dark_users_id' => $userId, 
            'email' => $request->email, 
            'name' => $request->name,
        ]);
    
        return response()->json($storeEmail, 201);
    }
    
    public function index()
    {
        $userEmail = StoreEmail::where('dark_users_id', Auth::user()->id)
                                       ->get();  
    
        return response()->json($userEmail);
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string',
        ]);

        $storeEmail = StoreEmail::where('dark_users_id', Auth::user()->id)->findOrFail($id);

        $storeEmail->email = $request->email;
        $storeEmail->name = $request->name;  
        $storeEmail->save();

        return response()->json($storeEmail);
    }

    public function destroy($id)
    {
        $storeEmail = StoreEmail::where('dark_users_id', Auth::user()->id)->findOrFail($id);

        $storeEmail->delete();

        return response()->json(['message' => 'Email deleted successfully.']);
    }
}
