<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ShopOrder;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    // GET /invoices
    public function index()
    {
        $invoices = Invoice::with('order')->get();
        return response()->json($invoices);
    }

    // GET /invoices/{id}
    public function show($id)
    {
        $invoice = Invoice::with('order')->find($id);

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        return response()->json($invoice);
    }

    // POST /invoices
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:shop_order,id',
            'invoice_number' => 'required|string|unique:invoice,invoice_number',
            'generated_at' => 'nullable|date',
            'total_amount' => 'required|integer',
        ]);

        $invoice = Invoice::create($validated);

        return response()->json([
            'message' => 'Invoice created successfully.',
            'data' => $invoice,
        ], 201);
    }

    // PUT /invoices/{id}
    public function update(Request $request, $id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $validated = $request->validate([
            'order_id' => 'required|exists:shop_order,id',
            'invoice_number' => 'required|string|unique:invoice,invoice_number,' . $id,
            'generated_at' => 'nullable|date',
            'total_amount' => 'required|integer',
        ]);

        $invoice->update($validated);

        return response()->json([
            'message' => 'Invoice updated successfully.',
            'data' => $invoice,
        ]);
    }

    // DELETE /invoices/{id}
    public function destroy($id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted successfully.']);
    }
}
