<?php

namespace App\Http\Controllers;

use App\Models\OrderStatus;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    // List all order statuses
    public function index()
    {
        return response()->json(OrderStatus::all(), 200);
    }

    // Create a new order status
    public function store(Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|string|max:255|unique:order_statuses,status',
        ]);

        $orderStatus = OrderStatus::create($validated);

        return response()->json([
            'message' => 'Order status created successfully.',
            'data' => $orderStatus,
        ], 201);
    }

    // Show a specific order status by ID
    public function show(string $id)
    {
        $orderStatus = OrderStatus::findOrFail($id);

        return response()->json($orderStatus, 200);
    }

    // Update a specific order status by ID
    public function update(Request $request, string $id)
    {
        $orderStatus = OrderStatus::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|max:255|unique:order_statuses,status,' . $id,
        ]);

        $orderStatus->update($validated);

        return response()->json([
            'message' => 'Order status updated successfully.',
            'data' => $orderStatus,
        ]);
    }

    // Delete a specific order status by ID
    public function destroy(string $id)
    {
        $orderStatus = OrderStatus::findOrFail($id);

        $orderStatus->delete();

        return response()->json(['message' => 'Order status deleted successfully.']);
    }
}
