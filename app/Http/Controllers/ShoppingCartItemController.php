<?php

namespace App\Http\Controllers;

use App\Models\ShoppingCartItem;
use App\Models\ProductItem;
use Illuminate\Http\Request;

class ShoppingCartItemController extends Controller
{
    // List all cart items (typically filtered by cart_id)
    public function index(Request $request)
    {
        if ($request->has('cart_id')) {
            $items = ShoppingCartItem::where('cart_id', $request->cart_id)->with('productItem')->get();
        } else {
            $items = ShoppingCartItem::with(['productItem', 'cart'])->get();
        }

        return response()->json($items, 200);
    }

    // Show a single cart item
    public function show($id)
    {
        $item = ShoppingCartItem::with(['productItem', 'cart'])->find($id);

        if (!$item) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        return response()->json($item, 200);
    }

    // Add a product item to cart
    public function store(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|exists:shopping_carts,id',
            'product_item_id' => 'required|exists:product_items,id',
            'qty' => 'required|integer|min:1',
        ]);
    
        $productItem = ProductItem::find($request->product_item_id);
    
        // Check if enough stock is available
        if ($productItem->quantity_in_stock < $request->qty) {
            return response()->json([
                'message' => 'Not enough stock available.'
            ], 400);
        }
    
        // Subtract the quantity from stock
        $productItem->quantity_in_stock -= $request->qty;
        $productItem->save();
    
        // Create the shopping cart item
        $item = ShoppingCartItem::create($request->only(['cart_id', 'product_item_id', 'qty']));
    
        return response()->json($item, 201);
    }

    // Update quantity or product
    public function update(Request $request, $id)
    {
        $item = ShoppingCartItem::find($id);

        if (!$item) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $request->validate([
            'qty' => 'sometimes|integer|min:1',
            'product_item_id' => 'sometimes|exists:product_items,id',
        ]);

        $item->update($request->only(['qty', 'product_item_id']));

        return response()->json($item, 200);
    }

    // Remove a cart item
    public function destroy($id)
    {
        $item = ShoppingCartItem::find($id);

        if (!$item) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Item removed from cart'], 200);
    }
}

