<?php

namespace App\Http\Controllers;

use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class ShippingMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shippingMethods = ShippingMethod::all();
        return response()->json($shippingMethods, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:shipping_method,name',
            'price' => 'required|numeric|min:0',
        ]);

        $shippingMethod = ShippingMethod::create($validated);

        return response()->json([
            'message' => 'Shipping method created successfully.',
            'data' => $shippingMethod,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $shippingMethod = ShippingMethod::find($id);

        if (!$shippingMethod) {
            return response()->json(['message' => 'Shipping method not found.'], 404);
        }

        return response()->json($shippingMethod, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $shippingMethod = ShippingMethod::find($id);

        if (!$shippingMethod) {
            return response()->json(['message' => 'Shipping method not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:shipping_method,name,' . $id,
            'price' => 'required|numeric|min:0',
        ]);

        $shippingMethod->update($validated);

        return response()->json([
            'message' => 'Shipping method updated successfully.',
            'data' => $shippingMethod,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $shippingMethod = ShippingMethod::find($id);

        if (!$shippingMethod) {
            return response()->json(['message' => 'Shipping method not found.'], 404);
        }

        $shippingMethod->delete();

        return response()->json(['message' => 'Shipping method deleted successfully.']);
    }
}
