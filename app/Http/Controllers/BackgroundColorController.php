<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BackgroundColor;
use App\Models\CustomBackground;
use Illuminate\Validation\Rule;

class BackgroundColorController extends Controller
{
    public function getBackground()
    {
        $user = Auth::user();
        $userId = $user->id;
        $background = BackgroundColor::where('dark_users_id', $userId)->first();

        if (!$background) {
            return response()->json(['message' => 'No background found'], 404);
        }

        return response()->json(['option' => $background->option]);
    }

    public function updateBackground(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
    
        $standardOptions = ['purple_white', 'blue_white', 'green_black', 'red_black', 'gray_black'];
        $premiumOptions = ['galaxy', 'supernova', 'heart_nebula', 'sunset_vibe', 'northern_lights', 'cosmic_fusion'];
        
        $allowedOptions = $user->has_pro ? array_merge($standardOptions, $premiumOptions) : $standardOptions;
        
        $request->validate([
            'option' => ['required', Rule::in($allowedOptions)],
        ]);
        
        $background = BackgroundColor::updateOrCreate(
            ['dark_users_id' => $userId],
            ['option' => $request->option, 'changed_at' => now()]
        );

        if($background) { 
            CustomBackground::where('dark_users_id', $userId)->delete();
        }
    
        return response()->json([
            'message' => 'Background updated successfully',
            'option' => $background->option
        ]);
    }
}
