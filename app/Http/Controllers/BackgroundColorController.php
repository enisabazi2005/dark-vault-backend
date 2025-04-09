<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BackgroundColor;

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
        $request->validate([
            'option' => 'required|in:purple_white,blue_white,green_black,red_black,gray_black',
        ]);

        $user = Auth::user();
        $userId = $user->id;
        $background = BackgroundColor::updateOrCreate(
            ['dark_users_id' => $userId],
            ['option' => $request->option, 'changed_at' => now()]
        );

        return response()->json(['message' => 'Background updated successfully', 'option' => $background->option]);
    }
}
