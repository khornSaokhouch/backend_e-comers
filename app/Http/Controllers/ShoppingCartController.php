<?php

namespace App\Http\Controllers;

use App\Models\ShoppingCart;
use App\Models\ShoppingCartItem;
use App\Models\ProductItem;
use Illuminate\Http\Request;

class ShoppingCartController extends Controller
{
    // List all shopping carts (optionally filtered by user_id)
    public function index(Request $request)
    {
        $userId = $request->user_id;

        $query = ShoppingCart::with('items.productItem.product');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $carts = $query->get();

        \Log::info('Carts fetched for user ' . $userId, ['carts' => $carts->toArray()]);

        return response()->json($carts, 200);
    }

    // Create a new cart with optional items
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'sometimes|array|min:1',
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
                $productItem = ProductItem::findOrFail($itemData['product_item_id']);

                if ($productItem->quantity_in_stock < $itemData['qty']) {
                    return response()->json([
                        'message' => 'Insufficient stock for product item ID: ' . $productItem->id
                    ], 400);
                }

                // Check if item already exists in cart
                $existingItem = ShoppingCartItem::where('cart_id', $cart->id)
                    ->where('product_item_id', $itemData['product_item_id'])
                    ->first();

                if ($existingItem) {
                    // Increment quantity
                    $existingItem->qty += $itemData['qty'];
                    $existingItem->save();
                } else {
                    // Create new item
                    ShoppingCartItem::create([
                        'cart_id' => $cart->id,
                        'product_item_id' => $itemData['product_item_id'],
                        'qty' => $itemData['qty'],
                    ]);
                }

                // Deduct stock
                $productItem->decrement('quantity_in_stock', $itemData['qty']);
            }
        } else {
            \Log::info('No items to add to cart.');
        }

        $cart->load('items.productItem.product');

        return response()->json($cart, 201);
    }

    // Update cart user and optionally its items
    public function update(Request $request, $id)
    {
        $cart = ShoppingCart::find($id);

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'sometimes|array|min:1',
            'items.*.product_item_id' => 'required_with:items|integer|exists:product_items,id',
            'items.*.qty' => 'required_with:items|integer|min:1',
        ]);

        $cart->update([
            'user_id' => $request->user_id,
        ]);

        if ($request->has('items')) {
            foreach ($request->items as $itemData) {
                $productItem = ProductItem::findOrFail($itemData['product_item_id']);

                $existingItem = $cart->items()->where('product_item_id', $itemData['product_item_id'])->first();

                $qtyChange = 0;
                if ($existingItem) {
                    // Calculate qty difference to update stock accordingly
                    $qtyChange = $itemData['qty'] - $existingItem->qty;

                    if ($qtyChange > 0 && $productItem->quantity_in_stock < $qtyChange) {
                        return response()->json([
                            'message' => 'Insufficient stock for product item ID: ' . $productItem->id
                        ], 400);
                    }

                    // Update item quantity
                    $existingItem->qty = $itemData['qty'];
                    $existingItem->save();
                } else {
                    $qtyChange = $itemData['qty'];

                    if ($productItem->quantity_in_stock < $qtyChange) {
                        return response()->json([
                            'message' => 'Insufficient stock for product item ID: ' . $productItem->id
                        ], 400);
                    }

                    ShoppingCartItem::create([
                        'cart_id' => $cart->id,
                        'product_item_id' => $itemData['product_item_id'],
                        'qty' => $itemData['qty'],
                    ]);
                }

                // Adjust stock accordingly
                if ($qtyChange !== 0) {
                    if ($qtyChange > 0) {
                        $productItem->decrement('quantity_in_stock', $qtyChange);
                    } else {
                        $productItem->increment('quantity_in_stock', abs($qtyChange));
                    }
                }
            }
        }

        $cart->load('items.productItem.product');

        return response()->json($cart, 200);
    }

    // Delete cart and restore stock
    public function destroy($id)
    {
        $cart = ShoppingCart::with('items.productItem')->find($id);

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        // Restore stock for each item
        foreach ($cart->items as $item) {
            if ($item->productItem) {
                $item->productItem->increment('quantity_in_stock', $item->qty);
            }
        }

        // Delete cart items
        $cart->items()->delete();

        // Delete cart
        $cart->delete();

        return response()->json(['message' => 'Cart deleted and stock restored'], 200);
    }
}
