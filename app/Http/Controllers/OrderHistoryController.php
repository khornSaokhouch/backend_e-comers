<?php

namespace App\Http\Controllers;

use App\Models\OrderHistory;
use App\Models\User;
use App\Models\ShopOrder;
use Illuminate\Http\Request;

class OrderHistoryController extends Controller
{
    public function index()
    {
        $histories = OrderHistory::with(['user', 'order'])->paginate(10);
        return view('order_histories.index', compact('histories'));
    }

    public function create()
    {
        $users = User::all();
        $orders = ShopOrder::all();
        return view('order_histories.create', compact('users', 'orders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'order_id' => 'required|exists:shop_order,id',
        ]);

        OrderHistory::create($validated);

        return redirect()->route('order_histories.index')->with('success', 'Order history recorded.');
    }

    public function show(OrderHistory $orderHistory)
    {
        $orderHistory->load(['user', 'order']);
        return view('order_histories.show', compact('orderHistory'));
    }

    public function edit(OrderHistory $orderHistory)
    {
        $users = User::all();
        $orders = ShopOrder::all();
        return view('order_histories.edit', compact('orderHistory', 'users', 'orders'));
    }

    public function update(Request $request, OrderHistory $orderHistory)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'order_id' => 'required|exists:shop_order,id',
        ]);

        $orderHistory->update($validated);

        return redirect()->route('order_histories.index')->with('success', 'Order history updated.');
    }

    public function destroy(OrderHistory $orderHistory)
    {
        $orderHistory->delete();

        return redirect()->route('order_histories.index')->with('success', 'Order history deleted.');
    }
}
