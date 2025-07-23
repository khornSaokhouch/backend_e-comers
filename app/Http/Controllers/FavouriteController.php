<?php

namespace App\Http\Controllers;

use App\Models\Favourite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavouriteController extends Controller
{
    // List favourites of the authenticated user
    public function index()
    {
        return Favourite::where('user_id', Auth::id())->with('product')->get();
    }

    // Add a new favourite for the authenticated user
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $favourite = Favourite::firstOrCreate([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
        ]);

        return response()->json($favourite, 201);
    }

    // Show all favourites for a specific user by user ID (from route param)
    public function show($userId)
    {
        // Fetch all favourites for the given user with their related products
        $favourites = Favourite::where('user_id', $userId)
            ->with('product')
            ->get();
    
        // Return an empty array if no favourites found
        if ($favourites->isEmpty()) {
            return response()->json([]);
        }
    
        // Return favourites with products
        return response()->json($favourites);
    }
    

    // Delete a favourite by its primary key ID (only if belongs to auth user)
    public function destroy($id)
    {
        $favourite = Favourite::where('user_id', Auth::id())->findOrFail($id);
        $favourite->delete();

        return response()->json(['message' => 'Favourite removed']);
    }
}
