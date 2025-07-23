<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\CompanyInfo;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = CompanyInfo::all();

        return response()->json([
            'message' => 'Companies retrieved successfully.',
            'companies' => $companies,
        ]);
    }

    public function show($id)
    {
    
        $company = CompanyInfo::where('user_id', $id)
                    ->where('user_id', auth()->id())
                    ->first();
        if (!$company) {
            return response()->json([
                'message' => "Company with ID $id not found."
            ], 404);
        }
    
        return response()->json($company);
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

        // Handle image upload
        if ($request->hasFile('company_image')) {
            $data['company_image'] = $request->file('company_image')->store('company_images', 'public');
        }

        // Assign user_id only if authenticated
        $data['user_id'] = auth()->check() ? auth()->id() : null;

        $company = CompanyInfo::create($data);

        return response()->json([
            'message' => 'Company created successfully.',
            'company' => $company,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $company = CompanyInfo::find($id);

        if (!$company) {
            return response()->json([
                'message' => "Company with ID $id not found."
            ], 404);
        }

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

            // Delete old image if exists
            if ($company->company_image) {
                Storage::disk('public')->delete($company->company_image);
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
        $company = CompanyInfo::find($id);

        if (!$company) {
            return response()->json([
                'message' => "Company with ID $id not found."
            ], 404);
        }

        if ($company->company_image) {
            Storage::disk('public')->delete($company->company_image);
        }

        $company->delete();

        return response()->json([
            'message' => 'Company deleted successfully.'
        ]);
    }
}
