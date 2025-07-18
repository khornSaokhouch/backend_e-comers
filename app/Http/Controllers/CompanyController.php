<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyInfo;

class CompanyController extends Controller
{
    public function index()
    {
        return CompanyInfo::all();
    }

    public function show($id)
    {
        return CompanyInfo::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name'    => 'required|string|max:255',
            'description'     => 'nullable|string',
            'website_url'     => 'nullable|url',
            'business_hours'  => 'nullable|string',
            'facebook_url'    => 'nullable|url',
            'address'         => 'nullable|string',
            'city'            => 'nullable|string',
            'country'         => 'nullable|string',
            'instagram_url'   => 'nullable|url',
            'twitter_url'     => 'nullable|url',
            'linkedin_url'    => 'nullable|url',
            'company_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        // Handle image upload if present
        if ($request->hasFile('company_image')) {
            $imagePath = $request->file('company_image')->store('company_images', 'public');
            $data['company_image'] = $imagePath;
        }
    
        // Assign the authenticated user's ID
        $data['user_id'] = auth()->id();
    
        // Create the company record
        $company = CompanyInfo::create($data);
    
        return response()->json([
            'message' => 'Company created successfully.',
            'company' => $company,
        ], 201);
    }
    
    

    public function update(Request $request, $id)
    {
        $company = CompanyInfo::findOrFail($id);
    
        $data = $request->validate([
            'company_name'    => 'sometimes|string|max:255',
            'description'     => 'sometimes|string',
            'website_url'     => 'sometimes|url',
            'business_hours'  => 'sometimes|string',
            'facebook_url'    => 'sometimes|url',
            'address'         => 'sometimes|string',
            'city'            => 'sometimes|string',
            'country'         => 'sometimes|string',
            'instagram_url'   => 'sometimes|url',
            'twitter_url'     => 'sometimes|url',
            'linkedin_url'    => 'sometimes|url',
            'company_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        // Handle new image upload
        if ($request->hasFile('company_image')) {
            $imagePath = $request->file('company_image')->store('company_images', 'public');
            $data['company_image'] = $imagePath;
    
            // Optional: Delete old image if exists
            if ($company->company_image) {
                \Storage::disk('public')->delete($company->company_image);
            }
        }
    
        $company->update($data);
    
        return response()->json([
            'message' => 'Company updated successfully.',
            'company' => $company,
        ]);
    }
    

    public function destroy($id)
    {
        $company = CompanyInfo::findOrFail($id);
    
        // Optional: delete company image from storage
        if ($company->company_image) {
            \Storage::disk('public')->delete($company->company_image);
        }
    
        $company->delete();
    
        return response()->json(['message' => 'Company deleted successfully.']);
    }
    
}
