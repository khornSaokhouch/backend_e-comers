<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Store;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        return Stock::paginate(15);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'category_id' => 'nullable|integer',
        ]);

        $store = Store::find($validated['store_id']);

        if ($store->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stock = Stock::create($validated);

        return response()->json($stock, 201);
    }

    public function show($id)
    {
        $stock = Stock::find($id);

        if (!$stock) {
            return response()->json(['error' => 'Stock not found'], 404);
        }

        if ($stock->store->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($stock);
    }

    public function update(Request $request, $id)
    {
        $stock = Stock::find($id);

        if (!$stock) {
            return response()->json(['error' => 'Stock not found'], 404);
        }

        if ($stock->store->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'category_id' => 'nullable|integer',
        ]);

        $stock->update($validated);

        return response()->json($stock);
    }

    public function destroy($id)
    {
        $stock = Stock::find($id);

        if (!$stock) {
            return response()->json(['error' => 'Stock not found'], 404);
        }

        if ($stock->store->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stock->delete();

        return response()->json(['message' => 'Stocks deleted successfully'], 200);
    }
}
