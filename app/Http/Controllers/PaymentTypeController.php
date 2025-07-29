<?php

namespace App\Http\Controllers;

use App\Models\PaymentType;
use Illuminate\Http\Request;

class PaymentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(PaymentType::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255|unique:payment_type,type',
        ]);
    
        $paymentType = PaymentType::create($validated);
    
        return response()->json([
            'message' => 'Payment type created successfully.',
            'data' => $paymentType,
        ], 201);
    }
    
    
    

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $paymentType = PaymentType::find($id);

        if (!$paymentType) {
            return response()->json(['message' => 'Payment type not found.'], 404);
        }

        return response()->json($paymentType, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $paymentType = PaymentType::find($id);

        if (!$paymentType) {
            return response()->json(['message' => 'Payment type not found.'], 404);
        }

        $validated = $request->validate([
            'type' => 'required|string|max:255|unique:payment_type,type,' . $id,
        ]);

        $paymentType->update($validated);

        return response()->json([
            'message' => 'Payment type updated successfully.',
            'data' => $paymentType,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $paymentType = PaymentType::find($id);

        if (!$paymentType) {
            return response()->json(['message' => 'Payment type not found.'], 404);
        }

        $paymentType->delete();

        return response()->json(['message' => 'Payment type deleted successfully.']);
    }
}
