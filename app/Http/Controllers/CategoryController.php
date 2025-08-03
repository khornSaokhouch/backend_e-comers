<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // List all categories
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        // Validate request input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);
    
        // Upload image to Backblaze B2 (S3-compatible)
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('category_images', 'b2');
            $validated['image'] = $path;
        }
    
        // Attach the current user to the category
        $validated['user_id'] = auth()->id();
    
        // Create the category
        $category = Category::create($validated);
    
        // Generate a temporary signed image URL if image exists
        $imageUrl = $category->image
            ? Storage::disk('b2')->temporaryUrl($category->image, now()->addMinutes(60))
            : null;
    
        // Return JSON to frontend
        return response()->json([
            'category' => $category,
            'image_url' => $imageUrl,
        ], 201);
    }

    // Show a single category
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    // Update an existing category
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $path = $request->file('image')->store('category_images', 'public');
            $validated['image'] = $path;
        }

        $category->update($validated);

        return response()->json($category);
    }

    

    // Delete a category
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }


    public function products($categoryId)
{
    $products = Product::where('category_id', $categoryId)->get();
    return response()->json($products);
}

}
