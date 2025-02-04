<?php

namespace App\Http\Controllers;

use App\Models\StorePrivateInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StorePrivateInfoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'info_1' => 'required|string',
            'info_2' => 'required|string',
            'info_3' => 'required|string',
        ]);
    
        $userId = Auth::user()->id ;
    
        if (!$userId) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
        $storePrivateInfo = StorePrivateInfo::create([
            'dark_users_id' => $userId, 
            'name' => $request->name,
            'info_1' => $request->info_1,
            'info_2' => $request->info_2, 
            'info_3' => $request->info_3,
        ]);
    
        return response()->json($storePrivateInfo, 201);
    }
    
    public function index()
    {
        $privateInfo = StorePrivateInfo::where('dark_users_id', Auth::user()->id)
                                       ->get();  
    
        return response()->json($privateInfo);
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'info_1' => 'required|string',
            'info_2' => 'required|string',
            'info_3' => 'required|string',
        ]);

        $storePrivateInfo = StorePrivateInfo::where('dark_users_id', Auth::user()->id)->findOrFail($id);

        $storePrivateInfo->name = $request->name;
        $storePrivateInfo->info_1 = $request->info_1;    
        $storePrivateInfo->info_2 = $request->info_2;  
        $storePrivateInfo->info_3 = $request->info_3;  
        $storePrivateInfo->save();

        return response()->json($storePrivateInfo);
    }

    public function destroy($id)
    {
        $storePrivateInfo = StorePrivateInfo::where('dark_users_id', Auth::user()->id)->findOrFail($id);

        $storePrivateInfo->delete();

        return response()->json(['message' => 'Private Info deleted successfully.']);
    }
}
