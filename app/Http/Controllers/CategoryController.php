<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;

class CategoryController extends Controller
{
    // List all categories with temporary signed URLs for images
    public function index()
    {
        $categories = Category::all()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'image_url' => $category->image
                    ? Storage::disk('b2')->temporaryUrl(
                        $category->image,
                        now()->addMinutes(60) // link valid for 60 mins
                    )
                    : null,
                'user_id' => $category->user_id,
            ];
        });
    
        return response()->json($categories);
    }
    

    // Store a new category with image upload to Backblaze B2 and return temporary URL
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

        $imageUrl = $category->image
            ? Storage::disk('b2')->temporaryUrl($category->image, now()->addMinutes(60))
            : null;

        return response()->json([
            'category' => $category,
            'image_url' => $imageUrl,
        ], 201);
    }

    // Show a single category with signed image URL
    public function show($id)
    {
        $category = Category::findOrFail($id);

        $imageUrl = $category->image
            ? Storage::disk('b2')->temporaryUrl($category->image, now()->addMinutes(60))
            : null;

        return response()->json([
            'category' => $category,
            'image_url' => $imageUrl,
        ]);
    }

    // Update a category and handle image upload/delete on Backblaze B2
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image from B2 if exists
            if ($category->image) {
                Storage::disk('b2')->delete($category->image);
            }

            $path = $request->file('image')->store('category_images', 'b2');
            $validated['image'] = $path;
        }

        $category->update($validated);

        $imageUrl = $category->image
            ? Storage::disk('b2')->temporaryUrl($category->image, now()->addMinutes(60))
            : null;

        return response()->json([
            'category' => $category,
            'image_url' => $imageUrl,
        ]);
    }

    // Delete a category and its image from Backblaze B2
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category->image) {
            Storage::disk('b2')->delete($category->image);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }

    // List products of a category
    public function products($categoryId)
    {
        $products = Product::where('category_id', $categoryId)->get();
        return response()->json($products);
    }
}
