<?php

namespace App\Http\Controllers;

use App\Models\ShopOrder;
use App\Models\User;
use App\Models\UserPaymentMethod;
use App\Models\ShippingMethod;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\ProductItem;
use App\Models\OrderLine;
use Telegram\Bot\Laravel\Facades\Telegram;


class ShopOrderController extends Controller
{
    // List all orders (pagination, eager load relations)
    public function index()
    {
        $orders = ShopOrder::with(['user', 'paymentMethod', 'shippingMethod', 'orderStatus'])
            ->paginate(10);

        if (request()->wantsJson()) {
            return response()->json($orders);
        }

        return view('shop_orders.index', compact('orders'));
    }

    // Show form to create order (web only)
    public function create()
    {
        $users = User::all();

        // If logged in user is admin, maybe allow selecting any user's payment methods,
        // otherwise, filter by current user:
        $currentUserId = auth()->id();
        $paymentMethods = UserPaymentMethod::where('user_id', $currentUserId)->get();

        $shippingMethods = ShippingMethod::all();
        $orderStatuses = OrderStatus::all();

        return view('shop_orders.create', compact('users', 'paymentMethods', 'shippingMethods', 'orderStatuses'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'order_date' => 'required|date',
                'payment_method_id' => 'required|exists:user_payment_method,id',
                'shipping_address' => 'required|string',
                'shipping_method_id' => 'required|exists:shipping_method,id',
                'order_total' => 'required|numeric',
                'order_status_id' => 'required|exists:order_statuses,id',
                'order_lines' => 'required|array|min:1',
                'order_lines.*.product_item_id' => 'required|exists:product_items,id',
                'order_lines.*.quantity' => 'required|integer|min:1',
                'order_lines.*.price' => 'required|numeric|min:0',
            ]);
    
            DB::beginTransaction();
    
            $order = ShopOrder::create([
                'user_id' => $validated['user_id'],
                'order_date' => $validated['order_date'],
                'payment_method_id' => $validated['payment_method_id'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_method_id' => $validated['shipping_method_id'],
                'order_total' => $validated['order_total'],
                'order_status_id' => $validated['order_status_id'],
            ]);
    
            foreach ($validated['order_lines'] as $line) {
                OrderLine::create([
                    'order_id' => $order->id,
                    'product_item_id' => $line['product_item_id'],
                    'quantity' => $line['quantity'],
                    'price' => $line['price'],
                ]);
            }
    
            DB::commit();
    
            // Load user and product items with company relation
            $user = User::find($validated['user_id']);
            $productItemIds = collect($validated['order_lines'])->pluck('product_item_id');
            $productItems = ProductItem::with('company')->whereIn('id', $productItemIds)->get()->keyBy('id');
    
            $companyItems = [];
    
            foreach ($validated['order_lines'] as $line) {
                $item = $productItems[$line['product_item_id']];
                $companyId = $item->company_id;
    
                if (!isset($companyItems[$companyId])) {
                    $companyItems[$companyId] = [
                        'company' => $item->company,
                        'items' => [],
                    ];
                }
    
                $companyItems[$companyId]['items'][] = [
                    'name' => htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'),
                    'quantity' => $line['quantity'],
                    'price' => $line['price'],
                ];
            }
    
            foreach ($companyItems as $companyData) {
                $company = $companyData['company'];
    
                if (!$company || !$company->telegram_chat_id) continue;
    
                $message = "🛒 <b>New Order Received</b>\n";
                $message .= "👤 <b>Customer</b>: " . htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') . "\n";
                $message .= "📦 <b>Order ID</b>: {$order->id}\n";
                $message .= "📅 <b>Date</b>: {$order->order_date}\n\n";
                $message .= "🛍 <b>Items from your store:</b>\n";
    
                foreach ($companyData['items'] as $item) {
                    $message .= "- {$item['name']} × {$item['quantity']} at {$item['price']}\n";
                }
    
                $message .= "\n📍 <b>Shipping Address</b>:\n" . htmlspecialchars($order->shipping_address, ENT_QUOTES, 'UTF-8');
    
                Telegram::sendMessage([
                    'chat_id' => $company->telegram_chat_id,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]);
            }
    
            return response()->json([
                'message' => 'Order placed and companies notified.',
                'order' => $order->load('orderLines'),
            ], 201);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    // Show order details
    public function show($id)
    {
        $order = ShopOrder::with([
            'user',
            'paymentMethod',
            'shippingMethod',
            'orderStatus',
            'orderLines.productItem.product' // deep eager loading
        ])->findOrFail($id);
    
        return response()->json($order);
    }


    public function showOrdersByUser($userId)
{
    $orders = ShopOrder::with(['user', 'paymentMethod', 'shippingMethod', 'orderStatus'])
                ->where('user_id', $userId)
                ->get();

    if ($orders->isEmpty()) {
        return response()->json(['message' => 'No orders found for this user'], 404);
    }

    return response()->json($orders);
}

    

    

    // Update order
    public function update(Request $request, ShopOrder $shopOrder)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'order_date' => 'required|date',
            'payment_method_id' => 'required|exists:user_payment_method,id',
            'shipping_address' => 'required|string|max:255',
            'shipping_method_id' => 'required|exists:shipping_method,id',
            'order_total' => 'required|numeric|min:0',
            'order_status_id' => 'required|exists:order_statuses,id',
            'order_lines' => 'sometimes|array|min:1', // optionally update order lines
            'order_lines.*.id' => 'sometimes|exists:order_lines,id', // existing line id if editing
            'order_lines.*.product_item_id' => 'required_with:order_lines|exists:product_items,id',
            'order_lines.*.quantity' => 'required_with:order_lines|integer|min:1',
            'order_lines.*.price' => 'required_with:order_lines|numeric|min:0',
        ]);
    
        DB::beginTransaction();
    
        try {
            // Update the main order
            $shopOrder->update([
                'user_id' => $validated['user_id'],
                'order_date' => $validated['order_date'],
                'payment_method_id' => $validated['payment_method_id'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_method_id' => $validated['shipping_method_id'],
                'order_total' => $validated['order_total'],
                'order_status_id' => $validated['order_status_id'],
            ]);
    
            // If order lines are provided, update them
            if (isset($validated['order_lines'])) {
                // Delete existing order lines that are not present in the update
                $existingLineIds = $shopOrder->orderLines()->pluck('id')->toArray();
                $updatedLineIds = collect($validated['order_lines'])->pluck('id')->filter()->toArray();
                $linesToDelete = array_diff($existingLineIds, $updatedLineIds);
                OrderLine::destroy($linesToDelete);
    
                foreach ($validated['order_lines'] as $line) {
                    if (isset($line['id'])) {
                        // Update existing order line
                        $orderLine = OrderLine::find($line['id']);
                        $orderLine->update([
                            'product_item_id' => $line['product_item_id'],
                            'quantity' => $line['quantity'],
                            'price' => $line['price'],
                        ]);
                    } else {
                        // Create new order line
                        OrderLine::create([
                            'order_id' => $shopOrder->id,
                            'product_item_id' => $line['product_item_id'],
                            'quantity' => $line['quantity'],
                            'price' => $line['price'],
                        ]);
                    }
                }
            }
    
            DB::commit();
    
            if ($request->wantsJson()) {
                return response()->json($shopOrder->load('orderLines'));
            }
    
            return redirect()->route('shop_orders.index')
                ->with('success', 'Order updated successfully.');
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to update order: ' . $e->getMessage()], 500);
            }
    
            return back()->withErrors('Failed to update order')->withInput();
        }
    }


    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'order_status_id' => 'required|exists:order_statuses,id',
        ]);
    
        $shopOrder = ShopOrder::findOrFail($id);
    
        try {
            $shopOrder->update([
                'order_status_id' => $validated['order_status_id'],
            ]);
    
            return response()->json([
                'message' => 'Order status updated successfully.',
                'order' => $shopOrder->load('orderStatus'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }
    

    public function destroy($id)
    {
        DB::beginTransaction();
    
        try {
            $shopOrder = ShopOrder::findOrFail($id);
    
            // Optional: delete related order lines
            $shopOrder->orderLines()->delete();
    
            $shopOrder->delete();
    
            DB::commit();
    
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Order deleted successfully']);
            }
    
            return redirect()->route('shop_orders.index')
                ->with('success', 'Order deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
    
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to delete order: ' . $e->getMessage()], 500);
            }
    
            return back()->withErrors('Failed to delete order')->withInput();
        }
    }
    
}
