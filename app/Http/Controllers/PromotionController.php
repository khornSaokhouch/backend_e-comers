<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promotion;
use App\Models\Category;

class PromotionController extends Controller
{
    // List all promotions
    public function index()
    {
        $promotions = Promotion::all();
        return response()->json($promotions);
    }

    // Show a single promotion with categories
    public function show($id)
    {
        $promotion = Promotion::with('categories')->find($id);

        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }

        return response()->json($promotion);
    }

    // Create a new promotion
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $promotion = Promotion::create($request->all());

        return response()->json($promotion, 201);
    }

    // Update an existing promotion
    public function update(Request $request, $id)
    {
        $promotion = Promotion::find($id);

        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }

        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        

        $promotion->update($request->all());

        return response()->json($promotion);
    }

    // Delete a promotion
    public function destroy($id)
    {
        $promotion = Promotion::find($id);

        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }

        $promotion->delete();

        return response()->json(['message' => 'Promotion deleted successfully']);
    }

    // List categories for a promotion
    public function categories($promotionId)
    {
        $promotion = Promotion::with('categories')->find($promotionId);

        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }

        return response()->json($promotion->categories);
    }

    // Attach category to a promotion
    public function attachCategory(Request $request, $promotionId)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
        ]);

        $promotion = Promotion::find($promotionId);

        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }

        $promotion->categories()->syncWithoutDetaching([$request->category_id]);

        return response()->json(['message' => 'Category attached successfully']);
    }

    // Detach category from a promotion
    public function detachCategory($promotionId, $categoryId)
    {
        $promotion = Promotion::find($promotionId);

        if (!$promotion) {
            return response()->json(['message' => 'Promotion not found'], 404);
        }

        $promotion->categories()->detach($categoryId);

        return response()->json(['message' => 'Category detached successfully']);
    }
}
