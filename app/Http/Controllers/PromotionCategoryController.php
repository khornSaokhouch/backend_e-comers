<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promotion;
use App\Models\Category;

class PromotionCategoryController extends Controller
{
    // List all categories for a promotion
    public function index($promotionId)
{
    $promotion = Promotion::find($promotionId);

    if (!$promotion) {
        \Log::warning("Promotion ID $promotionId not found.");
        return response()->json([]); // <-- 200 status by default
    }

    $categories = $promotion->categories;

    \Log::info("Categories for promotion $promotionId:", $categories->toArray());

    return response()->json($categories);
}


    // Attach one or more categories to a promotion
    public function store(Request $request, $promotionId)
    {
        // Validate input: category_id is required and can be a single ID or array of IDs
        $request->validate([
            'category_id' => 'required', // Accept either single int or array
            'category_id.*' => 'exists:categories,id', // Validate each if array
        ]);

        $promotion = Promotion::findOrFail($promotionId);

        $categoryIds = $request->input('category_id');

        // Normalize single ID to array
        if (!is_array($categoryIds)) {
            $categoryIds = [$categoryIds];
        }

        // Attach categories without removing existing ones
        $promotion->categories()->syncWithoutDetaching($categoryIds);

        // Return updated categories list
        return response()->json([
            'message' => 'Category(s) attached successfully',
            'categories' => $promotion->categories()->get(),
        ]);
    }

    // Show a specific category in a promotion (optional)
    public function show($promotionId, $categoryId)
    {
        $promotion = Promotion::findOrFail($promotionId);
        $category = $promotion->categories()->findOrFail($categoryId);

        return response()->json($category);
    }

    // Update category relation (not usually needed, placeholder)
    public function update(Request $request, $promotionId, $categoryId)
    {
        return response()->json(['message' => 'Update method not implemented']);
    }

    // Detach a category from a promotion
    public function destroy($promotionId, $categoryId)
    {
        $promotion = Promotion::findOrFail($promotionId);
        $promotion->categories()->detach($categoryId);

        // Return updated categories list
        return response()->json([
            'message' => 'Category detached successfully',
            'categories' => $promotion->categories()->get(),
        ]);
    }
}
