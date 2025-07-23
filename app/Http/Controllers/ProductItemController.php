<?php

namespace App\Http\Controllers;

use App\Models\ProductItem;
use Illuminate\Http\Request;

class ProductItemController extends Controller
{
    // List all product items
    public function index()
    {
        return response()->json(ProductItem::all(), 200);
    }

    // Show a single product item
    public function show($id)
    {
        $item = ProductItem::find($id);

        if (!$item) {
            return response()->json(['message' => 'Product item not found'], 404);
        }

        return response()->json($item, 200);
    }

    // Create a new product item
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity_in_stock' => 'required|integer|min:0',
        ]);

        $item = ProductItem::create($request->only(['product_id', 'quantity_in_stock']));

        return response()->json($item, 201);
    }

    // Update an existing product item
    public function update(Request $request, $id)
    {
        $item = ProductItem::find($id);

        if (!$item) {
            return response()->json(['message' => 'Product item not found'], 404);
        }

        $request->validate([
            'quantity_in_stock' => 'sometimes|integer|min:0',
            'product_id' => 'sometimes|exists:products,id',
        ]);

        $item->update($request->only(['product_id', 'quantity_in_stock']));

        return response()->json($item, 200);
    }

    // Delete a product item
    public function destroy($id)
    {
        $item = ProductItem::find($id);

        if (!$item) {
            return response()->json(['message' => 'Product item not found'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Product item deleted'], 200);
    }
}
