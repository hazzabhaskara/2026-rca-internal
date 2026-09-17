<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\Customers;
use App\Receipts;
use App\ReceiptsDetail;
use App\SalesInovice;
use App\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceiptsController extends Controller
{
    //
    public function index()
    {
        $data = Receipts::with(['customer', 'depositTo'])->paginate(5);
        return view('receipts.index', compact('data'));
    }
    public function create()
    {
        $customers = Customers::all();
        $accounts = chartOfAccount::all();
        $invoices = SalesInvoice::all();

        return view('receipts.create', compact('customers', 'accounts', 'invoices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receipt_number' => 'required|unique:receipts,receipt_number',
            'customer_id' => 'required|exists:customers,id',
            'deposit_to_id' => 'required|exists:chart_of_accounts,id',
            'date' => 'required|date',
            'comment' => 'nullable|string',
            'details' => 'required|array',
            'details.*.sales_invoice_id' => 'required|exists:sales_invoices,id',
            'details.*.invoice_date' => 'required|date',
            'details.*.original_amount' => 'required|numeric',
            'details.*.amount_owing' => 'required|numeric',
            'details.*.discount_available' => 'nullable|numeric',
            'details.*.discount_taken' => 'nullable|numeric',
            'details.*.amount_received' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            $receipt = Receipts::create([
                'receipt_number' => $request->receipt_number,
                'customer_id' => $request->customer_id,
                'deposit_to_id' => $request->deposit_to_id,
                'date' => $request->date,
                'comment' => $request->comment,
            ]);

            foreach ($request->details as $detail) {
                ReceiptsDetail::create([
                    'receipt_id' => $receipt->id,
                    'sales_invoice_id' => $detail['sales_invoice_id'],
                    'invoice_date' => $detail['invoice_date'],
                    'original_amount' => $detail['original_amount'],
                    'amount_owing' => $detail['amount_owing'],
                    'discount_available' => $detail['discount_available'] ?? 0,
                    'discount_taken' => $detail['discount_taken'] ?? 0,
                    'amount_received' => $detail['amount_received'],
                ]);
            }

            DB::commit();
            return redirect()->route('receipts.index')->with('success', 'Receipt berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan receipt: ' . $e->getMessage())->withInput();
        }
    }
    public function getInvoices($customerId)
    {
        $invoices = \App\SalesInvoice::with('details')
            ->where('customers_id', $customerId)
            ->where('status', 1)
            ->get();

        return view('receipts.partials.invoice_table', compact('invoices'));
    }
}
