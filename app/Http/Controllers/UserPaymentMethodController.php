<?php

namespace App\Http\Controllers;

use App\Models\UserPaymentMethod;
use App\Models\User;
use App\Models\PaymentType;
use Illuminate\Http\Request;

class UserPaymentMethodController extends Controller
{
    // GET /user-payment-methods?user_id=1
    public function index(Request $request)
    {
        $userId = $request->query('user_id');

        $query = UserPaymentMethod::with(['user', 'paymentType']);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return response()->json($query->get());
    }

    // GET /user-payment-methods/{id}
    public function show($id)
    {
        $paymentMethod = UserPaymentMethod::with(['user', 'paymentType'])->findOrFail($id);

        return response()->json($paymentMethod);
    }

    // POST /user-payment-methods
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'payment_type_id' => 'required|exists:payment_type,id',
            'provider' => 'required|string|max:255',
            'card_number' => 'required|string|max:20',
            'expiry_date' => 'required|date',
        ]);

        $method = UserPaymentMethod::create($validated);

        return response()->json($method, 201);
    }

    // PUT /user-payment-methods/{id}
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'payment_type_id' => 'required|exists:payment_type,id',
            'provider' => 'required|string|max:255',
            'card_number' => 'required|string|max:20',
            'expiry_date' => 'required|date',
        ]);

        $method = UserPaymentMethod::findOrFail($id);
        $method->update($validated);

        return response()->json($method);
    }

    // DELETE /user-payment-methods/{id}
    public function destroy($id)
    {
        $method = UserPaymentMethod::findOrFail($id);
        $method->delete();

        return response()->json(['message' => 'Payment method deleted.']);
    }
}
