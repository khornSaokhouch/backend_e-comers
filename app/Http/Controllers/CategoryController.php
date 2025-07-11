<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    // List all categories
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    // Store a new category
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048', // Optional image upload
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('category_images', 'public');
            $validated['image'] = $path;
        }

        // Associate category with the authenticated user
        $validated['user_id'] = auth()->id();

        $category = Category::create($validated);

        return response()->json($category, 201);
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
}
