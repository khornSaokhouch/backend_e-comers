<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    // List all stores
    public function index()
    {
        return Store::all(); // You can paginate if needed
    }

    // Create a new store (user_id is optional)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'nullable|integer',
        ]);

        $store = Store::create($validated);

        return response()->json($store, 201);
    }

    // Show stores by user ID (no auth check)
    public function show(Request $request, $id)
    {
        // Check if the authenticated user is the same as $id
        if (auth()->id() !== (int)$id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        $stores = Store::where('user_id', $id)->get();
    
        if ($stores->isEmpty()) {
            return response()->json(['error' => 'No stores found for this user'], 404);
        }
    
        return response()->json($stores);
    }
    

    public function getStoresByUserId($userId)
    {
        // Optional: check if authenticated user matches $userId
        if (auth()->id() !== (int) $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        $stores = Store::where('user_id', $userId)->get();
    
        if ($stores->isEmpty()) {
            return response()->json(['error' => 'No stores found for this user'], 404);
        }
    
        return response()->json($stores);
    }
    


    // Update a store by store ID (no auth check)
    public function update(Request $request, $id)
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json(['error' => 'Store not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'user_id' => 'sometimes|integer',
        ]);

        $store->update($validated);

        return response()->json($store);
    }

    // Delete a store by store ID (no auth check)
    public function destroy($id)
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json(['error' => 'Store not found'], 404);
        }

        $store->delete();

        return response()->json(['message' => 'Store deleted successfully'], 200);
    }
}
