<?php

namespace App\Http\Controllers;

use App\Models\ShoppingCart;
use App\Models\ShoppingCartItem;
use Illuminate\Http\Request;


class ShoppingCartController extends Controller
{
 // List all shopping carts (optionally filtered by user_id)
 public function index(Request $request)
 {
     $userId = $request->user_id;
 
     // Eager load items, productItem, and product (nested)
     $query = ShoppingCart::with('items.productItem.product');

 
     if ($userId) {
         $query->where('user_id', $userId);
     }
 
     $carts = $query->get();
 
     // Log with proper array structure
     \Log::info('Carts fetched for user ' . $userId, ['carts' => $carts->toArray()]);
 
     return response()->json($carts, 200);
 }
 


    // Create a new cart with optional items
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'sometimes|array',
            'items.*.product_item_id' => 'required_with:items|exists:product_items,id',
            'items.*.qty' => 'required_with:items|integer|min:1',
        ]);
    
        \Log::info('Creating cart for user: ' . $request->user_id);
        \Log::info('Items:', $request->input('items', []));
    
        $cart = ShoppingCart::create([
            'user_id' => $request->user_id,
        ]);
    
        if ($request->has('items')) {
            foreach ($request->items as $itemData) {
                try {
                    ShoppingCartItem::create([
                        'cart_id' => $cart->id,
                        'product_item_id' => $itemData['product_item_id'],
                        'qty' => $itemData['qty'],
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to create cart item: ' . $e->getMessage());
                    return response()->json(['message' => 'Failed to add item'], 500);
                }
            }
        } else {
            \Log::info('No items to add to cart.');
        }
    
        $cart->load('items.productItem.product');
    
        return response()->json($cart, 201);
    }
    

    // Update cart user (usually not often used)
    public function update(Request $request, $id)
    {
        $cart = ShoppingCart::find($id);
    
        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }
    
        // Validate input
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'sometimes|array',
            'items.*.product_item_id' => 'required_with:items|integer',
            'items.*.qty' => 'required_with:items|integer|min:1',
        ]);
    
        // Update the cart user_id
        $cart->update([
            'user_id' => $request->user_id,
        ]);
    
        // Update item quantities if provided
        if ($request->has('items')) {
            foreach ($request->items as $itemData) {
                $item = $cart->items()->where('product_item_id', $itemData['product_item_id'])->first();
                if ($item) {
                    $item->qty = $itemData['qty'];
                    $item->save();
                }
                // Optional: handle adding new items if needed here
            }
        }
    
        // Reload relationships
        $cart->load('items.productItem');
    
        return response()->json($cart, 200);
    }
    
    

    // Delete cart (and optionally cascade delete items)
    public function destroy($id)
    {
        $cart = ShoppingCart::find($id);

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        // Delete all related items first (if not set up cascading deletes in DB)
        $cart->items()->delete();

        $cart->delete();

        return response()->json(['message' => 'Cart deleted'], 200);
    }
}
