<?php

namespace App\Http\Controllers;

use App\Models\BackgroundColor;
use App\Models\CustomBackground;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomBackgroundController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'color_1' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'color_2' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ]);

        $user = Auth::user();
        $userId = $user->id;

        $customBackground = CustomBackground::updateOrCreate([
            'dark_users_id' => $userId,
            'color_1' => $validated['color_1'],
            'color_2' => $validated['color_2'],
        ]);

        if ($customBackground) {
            BackgroundColor::where('dark_users_id', $userId)->delete();
        }

        return response()->json([
            'message' => 'Custom background saved',
            'data' => $customBackground
        ]);
    }

    public function show() 
    {
        $user = Auth::user();

        $customBackground = CustomBackground::where('dark_users_id' , $user->id)->firstOrFail();

        if(!$customBackground) { 
            return response()->json(['message' => 'User not found'] , 404);
        }

        return response()->json(['data' => $customBackground]);
    }
}
