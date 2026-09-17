<?php

namespace App\Http\Controllers;

use App\BuildOfBom;
use App\BuildOfBomDetail;
use App\Item;
use App\ItemBuild;
use App\LocationInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function GuzzleHttp\Promise\all;

class BuildOfBomController extends Controller
{
    //
    public function index()
    {
        $data = BuildOfBom::with(['item'])->paginate(10);
        return view('build_of_bom.index', compact('data'));
    }
    public function create()
    {
        $item = Item::all();
        $fromLocation = LocationInventory::all();
        return view('build_of_bom.create', compact('item', 'fromLocation'));
    }
    public function store(Request $request)
    {
        // dump('BuildOfBOM Store - Incoming Request', $request->all());

        $request->validate([
            'date'             => 'required|date',
            'item_id'          => 'required|exists:items,id',
            'from_location_id' => 'required|exists:location_inventories,id',
            'qty_to_build'     => 'required|numeric|min:1',
            'total_cost'       => 'required|numeric|min:0',
            'notes'            => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            try {
                // 🔹 Simpan header Build of BOM
                $build = BuildOfBom::create([
                    'date'             => $request->date,
                    'item_id'          => $request->item_id,
                    'qty_to_build'     => $request->qty_to_build,
                    'total_cost'       => $request->total_cost,
                    'status'           => 'draft',
                    'notes'            => $request->notes,
                    'from_location_id' => $request->from_location_id
                ]);

                // dump('BuildOfBOM Created', ['build_id' => $build->id]);

                // 🔹 Tambah stok produk jadi
                $this->adjustItemQuantity(
                    $request->item_id,
                    $request->from_location_id,
                    $request->qty_to_build,
                    $request->total_cost
                );
                // dump('Produk Jadi ditambah', [
                //     'item_id' => $request->item_id,
                //     'qty'     => $request->qty_to_build,
                //     'value'   => $request->total_cost,
                // ]);

                // 🔹 Detail komponen
                $details = [];
                if ($request->has('component_id')) {
                    foreach ($request->component_id as $i => $compId) {
                        $detail = BuildOfBomDetail::create([
                            'build_of_bom_id'   => $build->id,
                            'component_item_id' => $compId,
                            'unit'              => $request->unit[$i] ?? '-',
                            'qty_per_unit'      => $request->base_qty_per[$i] ?? 0,
                            'qty_total'         => $request->qty_per[$i] ?? 0,
                            'cost_component'    => $request->amount[$i] ?? 0,
                        ]);

                        $details[] = $detail;

                        // dump('Komponen diproses', [
                        //     'component_id' => $compId,
                        //     'qty_used'     => $request->qty_per[$i] ?? 0,
                        //     'value_used'   => $request->amount[$i] ?? 0,
                        // ]);

                        $this->adjustItemQuantity(
                            $compId,
                            $request->from_location_id,
                            - ($request->qty_per[$i] ?? 0),
                            - ($request->amount[$i] ?? 0)
                        );
                    }
                }

                // 🔹 Simpan jurnal
                $journal = \App\JournalEntry::create([
                    'source'   => 'BuildOfBOM',
                    'tanggal'  => $request->date,
                    'comment'  => 'Build of BOM ID ' . $build->id,
                ]);

                // dump('Journal Created', ['journal_id' => $journal->id]);

                $totalDebit = 0;
                $totalCredit = 0;

                // Debit Produk Jadi
                $productAccount = optional(\App\ItemAccount::where('item_id', $request->item_id)->first())->assetAccount;
                if ($productAccount) {
                    \App\JournalEntryDetail::create([
                        'journal_entry_id' => $journal->id,
                        'kode_akun'        => $productAccount->kode_akun,
                        'debits'           => $request->total_cost,
                        'credits'          => 0,
                        'comment'          => 'Persediaan Produk Jadi',
                    ]);
                    $totalDebit += $request->total_cost;
                }

                // Credit Komponen
                foreach ($details as $d) {
                    $compAccount = optional(\App\ItemAccount::where('item_id', $d->component_item_id)->first())->assetAccount;

                    if ($compAccount) {
                        \App\JournalEntryDetail::create([
                            'journal_entry_id' => $journal->id,
                            'kode_akun'        => $compAccount->kode_akun,
                            'debits'           => 0,
                            'credits'          => $d->cost_component,
                            'comment'          => 'Pemakaian Komponen ' . ($d->component->item_description ?? ''),
                        ]);
                        $totalCredit += $d->cost_component;
                    }
                }

                // dump('Journal Balance Check', [
                //     'total_debit'  => $totalDebit,
                //     'total_credit' => $totalCredit
                // ]);

                if (abs($totalDebit - $totalCredit) > 0.01) {
                    throw new \Exception("Jurnal tidak balance! Debit: $totalDebit, Credit: $totalCredit");
                }
            } catch (\Throwable $e) {
                // dump('Error BuildOfBOM Store', [
                //     'message' => $e->getMessage(),
                //     'request' => $request->all(),
                // ]);
                throw $e; // rollback
            }
        });

        return redirect()->route('build_of_bom.index')->with('success', 'Build of BOM berhasil disimpan');
    }

    public function show($id)
    {
        $data = BuildOfBom::with(['item', 'details.component'])->findOrFail($id);
        return view('build_of_bom.show', compact('data'));
    }
    public function edit($id)
    {
        $build = BuildOfBom::with(['item', 'details.component'])->findOrFail($id);
        $fromLocation = LocationInventory::all();
        $item  = \App\Item::all();

        $itemQty = \App\ItemQuantities::where('item_id', $build->item_id)
            ->where('location_id', $build->from_location_id)
            ->first();

        $unitCost = $itemQty && $itemQty->on_hand_qty > 0
            ? $itemQty->on_hand_value / $itemQty->on_hand_qty
            : 0;

        $journal = \App\JournalEntry::with(['details.chartOfAccount'])
            ->where('comment', 'Build of BOM ID ' . $build->id)
            ->first();

        // mapping akun
        $itemAccounts = \App\ItemAccount::with('assetAccount')->get()->mapWithKeys(function ($acc) {
            return [$acc->item_id => [
                'kode_akun' => $acc->assetAccount->kode_akun ?? '-',
                'nama_akun' => $acc->assetAccount->nama_akun ?? '-',
            ]];
        });

        return view('build_of_bom.edit', compact('build', 'item', 'fromLocation', 'unitCost', 'journal', 'itemAccounts'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date'             => 'required|date',
            'item_id'          => 'required|exists:items,id',
            'from_location_id' => 'required|exists:location_inventories,id',
            'qty_to_build'     => 'required|numeric|min:1',
            'total_cost'       => 'required|numeric|min:0',
            'notes'            => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $id) {
            $build = BuildOfBom::with('details')->findOrFail($id);

            // dump('===== START UPDATE BOM =====');
            // dump('Data Lama (sebelum rollback)', [
            //     'build_id'       => $build->id,
            //     'item_id'        => $build->item_id,
            //     'from_location'  => $build->from_location_id,
            //     'qty_to_build'   => $build->qty_to_build,
            //     'total_cost'     => $build->total_cost,
            //     'details_count'  => $build->details->count(),
            // ]);

            // ========== 1. Rollback stok lama ==========
            // dump('Rollback Produk Jadi', [
            //     'item_id'   => $build->item_id,
            //     'qty_minus' => $build->qty_to_build,
            //     'value_minus' => $build->total_cost,
            // ]);
            $this->adjustItemQuantity(
                $build->item_id,
                $build->from_location_id,
                -$build->qty_to_build,
                -$build->total_cost
            );

            foreach ($build->details as $oldDetail) {
                // dump('Rollback Komponen', [
                //     'component_id' => $oldDetail->component_item_id,
                //     'qty_plus'     => $oldDetail->qty_total,
                //     'value_plus'   => $oldDetail->cost_component,
                // ]);
                $this->adjustItemQuantity(
                    $oldDetail->component_item_id,
                    $build->from_location_id,
                    $oldDetail->qty_total,
                    $oldDetail->cost_component
                );
            }

            // Hapus jurnal lama
            \App\JournalEntry::where('comment', 'Build of BOM ID ' . $build->id)->delete();

            // ========== 2. Update header ==========
            $build->update([
                'date'             => $request->date,
                'item_id'          => $request->item_id,
                'qty_to_build'     => $request->qty_to_build,
                'total_cost'       => $request->total_cost,
                'status'           => $request->status ?? 'draft',
                'notes'            => $request->notes,
                'from_location_id' => $request->from_location_id,
            ]);

            // Hapus detail lama
            $build->details()->delete();

            // dump('Header Build BOM diupdate', $build->toArray());

            // ========== 3. Tambah stok baru ==========
            // dump('Tambah Produk Jadi', [
            //     'item_id'   => $request->item_id,
            //     'qty_plus'  => $request->qty_to_build,
            //     'value_plus' => $request->total_cost,
            // ]);
            $this->adjustItemQuantity(
                $request->item_id,
                $request->from_location_id,
                $request->qty_to_build,
                $request->total_cost
            );

            $details = [];
            if ($request->has('component_id')) {
                foreach ($request->component_id as $i => $compId) {
                    $detail = BuildOfBomDetail::create([
                        'build_of_bom_id'   => $build->id,
                        'component_item_id' => $compId,
                        'unit'              => $request->unit[$i] ?? '-',
                        'qty_per_unit'      => $request->base_qty_per[$i] ?? 0,
                        'qty_total'         => $request->qty_per[$i] ?? 0,
                        'cost_component'    => $request->amount[$i] ?? 0,
                    ]);
                    $details[] = $detail;

                    // dump('Kurangi Komponen Baru', [
                    //     'component_id' => $compId,
                    //     'qty_minus'    => $request->qty_per[$i] ?? 0,
                    //     'value_minus'  => $request->amount[$i] ?? 0,
                    // ]);

                    $this->adjustItemQuantity(
                        $compId,
                        $request->from_location_id,
                        - ($request->qty_per[$i] ?? 0),
                        - ($request->amount[$i] ?? 0)
                    );
                }
            }

            // ========== 4. Buat jurnal baru ==========
            $journal = \App\JournalEntry::create([
                'source'   => 'BuildOfBOM',
                'tanggal'  => $request->date,
                'comment'  => 'Build of BOM ID ' . $build->id,
            ]);

            $totalDebit = 0;
            $totalCredit = 0;

            $productAccount = optional(\App\ItemAccount::where('item_id', $request->item_id)->first())->assetAccount;
            if ($productAccount) {
                \App\JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'kode_akun'        => $productAccount->kode_akun,
                    'debits'           => $request->total_cost,
                    'credits'          => 0,
                    'comment'          => 'Persediaan Produk Jadi',
                ]);
                $totalDebit += $request->total_cost;
            }

            foreach ($details as $d) {
                $compAccount = optional(\App\ItemAccount::where('item_id', $d->component_item_id)->first())->assetAccount;

                if ($compAccount) {
                    \App\JournalEntryDetail::create([
                        'journal_entry_id' => $journal->id,
                        'kode_akun'        => $compAccount->kode_akun,
                        'debits'           => 0,
                        'credits'          => $d->cost_component,
                        'comment'          => 'Pemakaian Komponen ' . ($d->component->item_description ?? ''),
                    ]);
                    $totalCredit += $d->cost_component;
                }
            }

            // dump('Journal Balance Check', [
            //     'debit'  => $totalDebit,
            //     'credit' => $totalCredit,
            // ]);

            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new \Exception("Jurnal tidak balance! Debit: $totalDebit, Credit: $totalCredit");
            }

            // dump('===== END UPDATE BOM =====');
        });

        return redirect()->route('build_of_bom.index')->with('success', 'Build of BOM berhasil diperbarui');
    }


    protected function adjustItemQuantity($itemId, $locationId, $qtyChange, $valueChange)
    {
        $itemQty = \App\ItemQuantities::firstOrCreate(
            ['item_id' => $itemId, 'location_id' => $locationId],
            ['on_hand_qty' => 0, 'on_hand_value' => 0]
        );

        Log::debug('Adjusting Stock', [
            'item_id'     => $itemId,
            'location_id' => $locationId,
            'qty_before'  => $itemQty->on_hand_qty,
            'value_before' => $itemQty->on_hand_value,
            'qty_change'  => $qtyChange,
            'value_change' => $valueChange,
        ]);

        $itemQty->on_hand_qty   += $qtyChange;
        $itemQty->on_hand_value += $valueChange;
        $itemQty->save();

        Log::debug('Stock Updated', [
            'item_id'     => $itemId,
            'location_id' => $locationId,
            'qty_after'   => $itemQty->on_hand_qty,
            'value_after' => $itemQty->on_hand_value,
        ]);
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $build = BuildOfBom::with('details')->findOrFail($id);

            // Rollback stok produk jadi (dikurangi kembali)
            $this->adjustItemQuantity(
                $build->item_id,
                $build->from_location_id,
                -$build->qty_to_build,
                -$build->total_cost
            );

            // Rollback stok komponen (ditambah kembali)
            foreach ($build->details as $d) {
                $this->adjustItemQuantity(
                    $d->component_item_id,
                    $build->from_location_id,
                    $d->qty_total,
                    $d->cost_component
                );
            }

            // Hapus jurnal (header + details)
            $journal = \App\JournalEntry::where('comment', 'Build of BOM ID ' . $build->id)->first();
            if ($journal) {
                $journal->details()->delete();
                $journal->delete();
            }

            // Hapus build (otomatis hapus details kalau pakai FK cascade)
            $build->delete();

            Log::info("BuildOfBOM #{$id} berhasil dihapus dan stok di-rollback");
        });

        return redirect()->route('build_of_bom.index')->with('success', 'Build of BOM berhasil dihapus');
    }
}
