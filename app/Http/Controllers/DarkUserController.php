<?php

namespace App\Http\Controllers;

use App\Http\Resources\DarkUserResource;
use App\Models\DarkUsers;
use Illuminate\Http\Request;

class DarkUserController extends Controller
{
    public function index() {
        $darkUsers = DarkUsers::all();

        return DarkUserResource::collection($darkUsers);
    }

    public function show($id)
    {
        $user = DarkUsers::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user);
    }
    
}
