<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;
use App\Models\Product;

class CategoryController extends Controller
{
    public function index()
    {
        $disk = App::environment('local') ? 'public' : 'b2';

        $categories = Category::all()->map(function ($category) use ($disk) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'image_url' => $category->image
                    ? $this->generateImageUrl($category->image, $disk)
                    : null,
                'user_id' => $category->user_id,
            ];
        });

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $disk = App::environment('local') ? 'public' : 'b2';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('category_images', $disk);
            $validated['image'] = $path;
        }

        $validated['user_id'] = auth()->id();
        $category = Category::create($validated);

        $imageUrl = $category->image
            ? $this->generateImageUrl($category->image, $disk)
            : null;

        return response()->json([
            'category' => $category,
            'image_url' => $imageUrl,
        ], 201);
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);
        $disk = App::environment('local') ? 'public' : 'b2';

        $imageUrl = $category->image
            ? $this->generateImageUrl($category->image, $disk)
            : null;

        return response()->json([
            'category' => $category,
            'image_url' => $imageUrl,
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $disk = App::environment('local') ? 'public' : 'b2';

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk($disk)->delete($category->image);
            }

            $path = $request->file('image')->store('category_images', $disk);
            $validated['image'] = $path;
        }

        $category->update($validated);

        $imageUrl = $category->image
            ? $this->generateImageUrl($category->image, $disk)
            : null;

        return response()->json([
            'category' => $category,
            'image_url' => $imageUrl,
        ]);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $disk = App::environment('local') ? 'public' : 'b2';

        if ($category->image) {
            Storage::disk($disk)->delete($category->image);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }

    public function products($categoryId)
    {
        $products = Product::where('category_id', $categoryId)->get();
        return response()->json($products);
    }

    private function generateImageUrl($path, $disk)
    {
        if ($disk === 'public') {
            return asset('storage/' . $path);
        }

        return Storage::disk('b2')->temporaryUrl($path, now()->addMinutes(60));
    }
}
