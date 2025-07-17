<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\NewSellerRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SellerController extends Controller
{
    // List all sellers
    public function index()
    {
        $sellers = Seller::all();
        return response()->json($sellers);
    }

    // Show a single seller by ID
    public function show($id)
    {
        $seller = Seller::findOrFail($id);
        return response()->json($seller);
    }

    // Create a new seller
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'country_region' => 'required|string|max:255',
            'street_address' => 'required|string',
            'phone_number' => 'required|string|max:50',
        ]);
    
        $seller = Seller::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'company_name' => $request->company_name,
            'email' => $request->email,
            'country_region' => $request->country_region,
            'street_address' => $request->street_address,
            'phone_number' => $request->phone_number,
        ]);
    
        // Notify all admins
        $admins = User::where('role', 'admin')->get();
    
        foreach ($admins as $admin) {
            $admin->notify(new NewSellerRequest($seller));
        }
    
        return response()->json($seller, 201);
    }

    // Update an existing seller
    public function update(Request $request, $id)
    {
        $seller = Seller::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'company_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'country_region' => 'sometimes|required|string|max:255',
            'street_address' => 'sometimes|required|string',
            'phone_number' => 'sometimes|required|string|max:20',
        ]);

        $seller->update($validated);

        return response()->json($seller);
    }


    public function approve(Request $request, $id)
    {
        $seller = Seller::findOrFail($id);
        $user = User::findOrFail($seller->user_id);
    
        // Update seller status to approved
        $seller->status = 'approved';
        $seller->save();
    
        // Update user role to company
        $user->role = 'company';
        $user->save();
    
        return response()->json([
            'message' => 'Seller approved and user role updated.',
            'seller' => $seller,
        ]);
    }


    public function reject($id)
{
    $seller = Seller::find($id);

    if (!$seller) {
        return response()->json(['message' => "Seller with id {$id} not found."], 404);
    }

    // Optionally, you could check if already approved or rejected before deleting
    if ($seller->status === 'approved') {
        return response()->json(['message' => 'Approved sellers cannot be rejected and deleted.'], 400);
    }

    $seller->delete();

    return response()->json(['message' => 'Seller rejected and removed from the database.']);
}
    


public function destroy($id)
{
    // Optional: Check if user is admin before deleting
    $user = Auth::user();
    if (!$user || $user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    try {
        $seller = Seller::findOrFail($id);
        $seller->delete();
        return response()->json(null, 204);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'message' => "Seller with id {$id} not found."
        ], 404);
    }
}
}

