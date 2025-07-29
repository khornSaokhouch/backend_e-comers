<?php

namespace App\Http\Controllers;

use App\Models\OrderLine;
use Illuminate\Http\Request;

class OrderLineController extends Controller
{
    public function index()
    {
        return response()->json(OrderLine::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_item_id' => 'required|exists:product_item,id',
            'order_id' => 'required|exists:shop_order,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|integer|min:0',
        ]);

        $orderLine = OrderLine::create($validated);
        return response()->json($orderLine, 201);
    }

    public function show($id)
    {
        $orderLine = OrderLine::findOrFail($id);
        return response()->json($orderLine);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'product_item_id' => 'required|exists:product_item,id',
            'order_id' => 'required|exists:shop_order,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|integer|min:0',
        ]);

        $orderLine = OrderLine::findOrFail($id);
        $orderLine->update($validated);

        return response()->json($orderLine);
    }

    public function destroy($id)
    {
        $orderLine = OrderLine::findOrFail($id);
        $orderLine->delete();

        return response()->json(null, 204);
    }
}
