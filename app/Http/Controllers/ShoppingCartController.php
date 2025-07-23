<?php

namespace App\Http\Controllers;

use App\Models\ShoppingCart;
use Illuminate\Http\Request;

class ShoppingCartController extends Controller
{
    // List all shopping carts (optional: for admin use)
    public function index()
    {
        return response()->json(ShoppingCart::all(), 200);
    }

    // Show a single shopping cart
    public function show($id)
    {
        $cart = ShoppingCart::with('items')->find($id);

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        return response()->json($cart, 200);
    }

    // Create a new cart
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $cart = ShoppingCart::create([
            'user_id' => $request->user_id,
        ]);

        return response()->json($cart, 201);
    }

    // Update a cart (optional, usually not needed)
    public function update(Request $request, $id)
    {
        $cart = ShoppingCart::find($id);

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $cart->update([
            'user_id' => $request->user_id,
        ]);

        return response()->json($cart, 200);
    }

    // Delete a cart
    public function destroy($id)
    {
        $cart = ShoppingCart::find($id);

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $cart->delete();

        return response()->json(['message' => 'Cart deleted'], 200);
    }
}
