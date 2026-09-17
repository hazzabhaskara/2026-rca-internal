<?php

namespace App\Http\Controllers;

use App\Item;
use App\ItemQuantities;
use App\LocationInventory;
use App\TransferInventory;
use App\TransferInventoryDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferInventoryController extends Controller
{
    //
    public function index()
    {
        $data = TransferInventory::with(['fromInventory', 'toInventory', 'details'])->paginate(10);
        return view('transfer_inventory.index', compact('data'));
    }
    public function create()
    {
        $toLocation = LocationInventory::all();
        $fromLocation = LocationInventory::all();
        $item = Item::all();
        return view('transfer_inventory.create', compact('toLocation', 'fromLocation', 'item'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'date'             => 'required|date',
            'from_location_id' => 'required|exists:location_inventories,id',
            'to_location_id'   => 'required|exists:location_inventories,id|different:from_location_id',
            'source'           => 'required|string',
            'notes'            => 'nullable|string',
            'component_id'     => 'required|array',
            'qty'              => 'required|array',
        ]);

        $debug = [];

        DB::transaction(function () use ($request, &$debug) {
            try {
                // 🔹 Simpan header transfer
                $transfer = TransferInventory::create([
                    'date'             => $request->date,
                    'from_location_id' => $request->from_location_id,
                    'to_location_id'   => $request->to_location_id,
                    'source'           => $request->source,
                    'notes'            => $request->notes,
                    'unit_cost' => $request->unit_cost
                ]);
                $debug['transfer_header'] = $transfer->toArray();

                // 🔹 Loop detail transfer
                foreach ($request->component_id as $i => $compId) {
                    $qtyTransfer = (int) $request->qty[$i];
                    $unit        = $request->unit[$i] ?? '-';
                    $unitCost = $request->unit_cost[$i] ?? '0';

                    // Cek stok gudang asal
                    $from = ItemQuantities::where('item_id', $compId)
                        ->where('location_id', $request->from_location_id)
                        ->first();

                    if (!$from || $from->on_hand_qty < $qtyTransfer) {
                        throw new \Exception("Stok tidak mencukupi untuk item ID {$compId} di gudang asal.");
                    }

                    // Hitung unit cost dari gudang asal
                    $fromUnitCost = $from->on_hand_qty > 0
                        ? $from->on_hand_value / $from->on_hand_qty
                        : 0;

                    $amount = $qtyTransfer * $fromUnitCost;

                    // 🔹 Simpan detail transfer
                    $detail = TransferInventoryDetail::create([
                        'transfer_inventory_id' => $transfer->id,
                        'component_item_id'     => $compId,
                        'unit'                  => $unit,
                        'qty'                   => $qtyTransfer,
                        'amount'                => $amount,
                        'unit_cost' => $unitCost,
                    ]);
                    $debug['detail'][] = $detail->toArray();

                    // 🔹 Update gudang asal
                    $beforeFrom = $from->toArray();
                    $from->on_hand_qty   -= $qtyTransfer;
                    $from->on_hand_value -= $amount;
                    $from->save();
                    $debug['from_update'][] = [
                        'before' => $beforeFrom,
                        'after'  => $from->toArray(),
                    ];

                    // 🔹 Update / insert gudang tujuan
                    $to = ItemQuantities::firstOrNew([
                        'item_id'     => $compId,
                        'location_id' => $request->to_location_id,
                    ]);
                    $beforeTo = $to->exists ? $to->toArray() : [];

                    if (!$to->exists) {
                        $to->on_hand_qty   = 0;
                        $to->on_hand_value = 0;
                    }

                    $to->on_hand_qty   += $qtyTransfer;
                    $to->on_hand_value += $amount;
                    $to->save();

                    // $debug['to_update'][] = [
                    //     'before' => $beforeTo,
                    //     'after'  => $to->toArray(),
                    // ];
                }
            } catch (\Throwable $e) {
                Log::error('Error TransferInventory Store: ' . $e->getMessage(), [
                    'trace'   => $e->getTraceAsString(),
                    'request' => $request->all(),
                ]);
                throw $e; // rollback
            }
        });

        // 🔹 Debug tampilkan semua hasil
        // dd($debug);

        // Kalau sudah stabil → komentar dd($debug) dan pakai redirect
        return redirect()->route('transfer_inventory.index')
            ->with('success', 'Transfer Inventory berhasil disimpan');
    }
    public function show($id)
    {
        $data = TransferInventory::with(['fromInventory', 'toInventory', 'details'])->findOrFail($id);
        return view('transfer_inventory.show', compact('data'));
    }
    public function edit($id)
    {
        $transfer = TransferInventory::with(['fromInventory', 'toInventory', 'details'])->findOrFail($id);
        $fromLocation = LocationInventory::all();
        $toLocation = LocationInventory::all();
        $items = Item::all();
        return view('transfer_inventory.edit', compact('transfer', 'fromLocation', 'toLocation', 'items'));
    }
    public function update(Request $request, TransferInventory $transfer_inventory)
    {
        $request->validate([
            'date'             => 'required|date',
            'from_location_id' => 'required|exists:location_inventories,id',
            'to_location_id'   => 'required|exists:location_inventories,id|different:from_location_id',
            'source'           => 'required|string',
            'notes'            => 'nullable|string',
            'component_id'     => 'required|array',
            'qty'              => 'required|array',
        ]);

        DB::transaction(function () use ($request, $transfer_inventory) {
            try {
                // 🔹 Rollback stok lama dulu
                foreach ($transfer_inventory->details as $detail) {
                    $qtyRollback    = $detail->qty;
                    $amountRollback = $detail->amount;

                    // kembalikan stok ke gudang asal
                    $from = ItemQuantities::where('item_id', $detail->component_item_id)
                        ->where('location_id', $transfer_inventory->from_location_id)
                        ->first();
                    if ($from) {
                        $from->on_hand_qty   += $qtyRollback;
                        $from->on_hand_value += $amountRollback;
                        $from->save();
                    }

                    // kurangi stok dari gudang tujuan
                    $to = ItemQuantities::where('item_id', $detail->component_item_id)
                        ->where('location_id', $transfer_inventory->to_location_id)
                        ->first();
                    if ($to) {
                        $to->on_hand_qty   -= $qtyRollback;
                        $to->on_hand_value -= $amountRollback;
                        $to->save();
                    }
                }

                // 🔹 Update header transfer
                $transfer_inventory->update([
                    'date'             => $request->date,
                    'from_location_id' => $request->from_location_id,
                    'to_location_id'   => $request->to_location_id,
                    'source'           => $request->source,
                    'notes'            => $request->notes,
                ]);

                // 🔹 Hapus detail lama
                $transfer_inventory->details()->delete();

                // 🔹 Insert detail baru + update stok
                foreach ($request->component_id as $i => $compId) {
                    $qtyTransfer = (int) $request->qty[$i];
                    $unit        = $request->unit[$i] ?? '-';

                    // normalisasi unit cost
                    $rawUnitCost = $request->unit_cost[$i] ?? 0;
                    $unitCost    = (float) str_replace([',', ' '], '', $rawUnitCost);

                    // normalisasi amount
                    $rawAmount = $request->amount[$i] ?? 0;
                    $amount    = (float) str_replace([',', ' '], '', $rawAmount);

                    if ($amount <= 0) {
                        $amount = $qtyTransfer * $unitCost;
                    }

                    // ambil stok asal → pakai cost aktual
                    $from = ItemQuantities::where('item_id', $compId)
                        ->where('location_id', $request->from_location_id)
                        ->first();

                    if ($from && $from->on_hand_qty > 0) {
                        $fromUnitCost = $from->on_hand_value / $from->on_hand_qty;
                        $amount       = $qtyTransfer * $fromUnitCost;
                    }

                    // simpan detail baru
                    $transfer_inventory->details()->create([
                        'transfer_inventory_id' => $transfer_inventory->id,
                        'component_item_id'     => $compId,
                        'unit'                  => $unit,
                        'qty'                   => $qtyTransfer,
                        'unit_cost'             => $unitCost,
                        'amount'                => $amount,
                    ]);

                    // update stok asal
                    if ($from) {
                        $from->on_hand_qty   -= $qtyTransfer;
                        $from->on_hand_value -= $amount;
                        $from->save();
                    }

                    // update stok tujuan
                    $to = ItemQuantities::firstOrNew([
                        'item_id'     => $compId,
                        'location_id' => $request->to_location_id,
                    ]);

                    if (!$to->exists) {
                        $to->on_hand_qty   = 0;
                        $to->on_hand_value = 0;
                    }

                    $to->on_hand_qty   += $qtyTransfer;
                    $to->on_hand_value += $amount;
                    $to->save();
                }
            } catch (\Throwable $e) {
                Log::error('Error TransferInventory Update: ' . $e->getMessage(), [
                    'trace'   => $e->getTraceAsString(),
                    'request' => $request->all(),
                ]);
                throw $e; // rollback
            }
        });

        return redirect()->route('transfer_inventory.index')
            ->with('success', 'Transfer Inventory berhasil diperbarui');
    }
    public function destroy(TransferInventory $transfer_inventory)
    {
        DB::transaction(function () use ($transfer_inventory) {
            try {
                // 🔹 Rollback stok sesuai detail
                foreach ($transfer_inventory->details as $detail) {
                    $qty    = $detail->qty;
                    $amount = $detail->amount;

                    // kembalikan ke gudang asal
                    $from = ItemQuantities::where('item_id', $detail->component_item_id)
                        ->where('location_id', $transfer_inventory->from_location_id)
                        ->first();
                    if ($from) {
                        $from->on_hand_qty   += $qty;
                        $from->on_hand_value += $amount;
                        $from->save();
                    }

                    // kurangi dari gudang tujuan
                    $to = ItemQuantities::where('item_id', $detail->component_item_id)
                        ->where('location_id', $transfer_inventory->to_location_id)
                        ->first();
                    if ($to) {
                        $to->on_hand_qty   -= $qty;
                        $to->on_hand_value -= $amount;
                        $to->save();
                    }
                }

                // 🔹 Hapus detail & header transfer
                $transfer_inventory->details()->delete();
                $transfer_inventory->delete();
            } catch (\Throwable $e) {
                Log::error('Error TransferInventory Destroy: ' . $e->getMessage(), [
                    'trace'      => $e->getTraceAsString(),
                    'transferId' => $transfer_inventory->id ?? null,
                ]);
                throw $e; // rollback
            }
        });

        return redirect()->route('transfer_inventory.index')
            ->with('success', 'Transfer Inventory berhasil dihapus dan stok sudah di-rollback.');
    }
}
