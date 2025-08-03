<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);
    
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('category_images', 'b2');
            $validated['image'] = $path;
        }
    
        $validated['user_id'] = auth()->id();
        $category = Category::create($validated);
    
        // ✅ This generates the actual HTTPS B2 S3 URL
        $imageUrl = $category->image ? Storage::disk('b2')->url($category->image) : null;
    
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
