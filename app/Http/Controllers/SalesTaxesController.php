<?php

namespace App\Http\Controllers;

use App\chartOfAccount;
use App\SalesTaxes;
use Illuminate\Http\Request;

class SalesTaxesController extends Controller
{
    //
    public function index()
    {
        $data = SalesTaxes::with(['purchaseAccount', 'salesAccount'])->latest()->paginate(5);
        return view('sales_taxes.index', compact('data'));
    }
    public function create()
    {
        $account = chartOfAccount::all();
        return view('sales_taxes.create', compact('account'));
    }
    public function store(Request $request)
    {
        // === 1) Validasi data ===
        $validated = $request->validate([
            'name'               => 'required|string|max:100',
            'purchase_account_id' => 'nullable|exists:chart_of_accounts,id',
            'sales_account_id'   => 'nullable|exists:chart_of_accounts,id',
            'active'             => 'nullable|boolean',
            'type'                => 'required|in:input_tax,withholding_tax',
            'rate' => 'required|numeric',
            'kategori' => 'required|in:Include,Exclude',
            'tax_code' => 'required|string'
        ]);

        // === 2) Simpan ke database ===
        $salesTax = SalesTaxes::create([
            'name'               => $validated['name'],
            'purchase_account_id' => $validated['purchase_account_id'] ?? null,
            'sales_account_id'   => $validated['sales_account_id'] ?? null,
            'active' => (bool) $validated['active'],
            'rate' => $validated['rate'],
            'type'                => $validated['type'],
            'kategori' => $validated['kategori'],
            'tax_code' => $validated['tax_code']

        ]);

        // === 3) Redirect / response ===
        return redirect()
            ->route('sales_taxes.index')
            ->with('success', 'Sales tax berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $sales_taxes = SalesTaxes::findOrFail($id);
        $account = ChartOfAccount::all(); // untuk dropdown purchase/sales account

        return view('sales_taxes.edit', compact('sales_taxes', 'account'));
    }

    /**
     * Update Sales Tax
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:100',
            'purchase_account_id' => 'nullable|exists:chart_of_accounts,id',
            'sales_account_id'   => 'nullable|exists:chart_of_accounts,id',
            'active'             => 'required',
            'rate' => 'required|numeric',
            'type'                => 'required|in:input_tax,withholding_tax',
            'kategori' => 'required|in:Include,Exclude',
            'tax_code' => 'required|string'

        ]);

        $salesTax = SalesTaxes::findOrFail($id);

        $salesTax->update([
            'name'               => $validated['name'],
            'purchase_account_id' => $validated['purchase_account_id'] ?? null,
            'sales_account_id'   => $validated['sales_account_id'] ?? null,
            'active' => (bool) $validated['active'],
            'rate' => $validated['rate'],
            'type' => $validated['type'],
            'kategori' => $validated['kategori'],
            'tax_code' => $validated['tax_code']

        ]);

        return redirect()
            ->route('sales_taxes.index')
            ->with('success', 'Sales tax berhasil diupdate.');
    }
    public function destroy($id)
    {
        $sales_taxes = SalesTaxes::findOrFail($id);

        $sales_taxes->delete();

        return redirect()->route('sales_taxes.index')->with('success', ' Data berhasil dihapus.');
    }
}
