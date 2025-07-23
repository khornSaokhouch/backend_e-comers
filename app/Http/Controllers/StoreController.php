<?php

namespace App\Http\Controllers;


use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index()
    {
        return Store::all(); // Or paginate in production
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $validated['user_id'] = auth()->id();

        $store = Store::create($validated);

        return response()->json($store, 201);
    }

    public function show($user_id)
    {
        // Get stores that belong to this user_id
        $stores = Store::where('user_id', $user_id)->get();
    
        if ($stores->isEmpty()) {
            return response()->json(['error' => 'No stores found for this user'], 404);
        }
    
        // Optional: check if authenticated user matches the user_id or is admin
        if (auth()->id() !== (int)$user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        return response()->json($stores);
    }
    

    public function update(Request $request, $id)
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json(['error' => 'Store not found'], 404);
        }

        if ($store->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
        ]);

        $store->update($validated);

        return response()->json($store);
    }

    public function destroy($id)
    {
        $store = Store::find($id);
    
        if (!$store) {
            return response()->json(['error' => 'Store not found'], 404);
        }
    
        if ($store->user_id !== auth()->id()) {
            \Log::warning('Unauthorized delete attempt on store ID ' . $id . ' by user ID ' . auth()->id());
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        $store->delete();
    
        return response()->json(['message' => 'Store deleted successfully'], 200);
    }
    
}
