<?php

namespace App\Http\Controllers;

use App\Models\CustomerList;
use Illuminate\Http\Request;

class CustomerListController extends Controller
{
    public function index()
    {
        return CustomerList::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'user_id' => 'required|exists:users,id',
        ]);

        return CustomerList::create($validated);
    }

    public function show(CustomerList $customerList)
    {
        return $customerList;
    }

    public function update(Request $request, CustomerList $customerList)
    {
        $validated = $request->validate([
            'store_id' => 'sometimes|exists:stores,id',
            'user_id' => 'sometimes|exists:users,id',
        ]);

        $customerList->update($validated);
        return $customerList;
    }

    public function destroy(CustomerList $customerList)
    {
        $customerList->delete();
        return response()->noContent();
    }
}
