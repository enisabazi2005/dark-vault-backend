<?php

namespace App\Http\Controllers;

use App\Models\ProVersionModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProVersionModelController extends Controller
{
    
    public function purcashe() 
    {
        $user = Auth::user();

        if($user->has_pro) {
            return response()->json(['message' => 'No need again to purcashe'], 400);
        }

        $user->update([
            'has_pro' => true,
            'MAX_STORAGE' => 50,
        
        ]);

        $pro = ProVersionModel::create([
            'dark_users_id' => $user->id,
            'expires_at' => null,
            'activated_at' => Carbon::now(),
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Purcashed Successfully',
            'version' => $pro,
        ], 200);
    }

    public function latest()
    {
        $user = Auth::user();

        if(!$user->proVersion) { 
            return response()->json(['message' => 'No pro version found'], 400);
        }

        return response()->json([
            'message' => 'Latest Pro Version Data',
            'pro_version' => $user->proVersion,
        ]);
    }
}
