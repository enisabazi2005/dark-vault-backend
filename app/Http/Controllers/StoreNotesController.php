<?php

namespace App\Http\Controllers;

use App\Models\StoreNotes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreNotesController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'notes' => 'required|string',
        ]);
    
        $userId = Auth::user()->id ;
    
        if (!$userId) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
        $storeNotes = StoreNotes::create([
            'dark_users_id' => $userId, 
            'name' => $request->name,
            'notes' => $request->notes, 
        ]);
    
        return response()->json($storeNotes, 201);
    }
    
    public function index()
    {
        $storeNotes = StoreNotes::where('dark_users_id', Auth::user()->id)
                                       ->get();  
    
        return response()->json($storeNotes);
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'notes' => 'required|string',
        ]);

        $storeNotes = StoreNotes::where('dark_users_id', Auth::user()->id)->findOrFail($id);

        $storeNotes->name = $request->name;  
        $storeNotes->notes = $request->notes;
        $storeNotes->save();

        return response()->json($storeNotes);
    }

    public function destroy($id)
    {
        $storeNotes = StoreNotes::where('dark_users_id', Auth::user()->id)->findOrFail($id);

        $storeNotes->delete();

        return response()->json(['message' => 'Note deleted successfully.']);
    }
}
