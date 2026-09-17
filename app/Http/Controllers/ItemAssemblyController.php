<?php

namespace App\Http\Controllers;

use App\Item;
use App\ItemAccount;
use App\ItemAssemblie;
use App\ItemAssemblieDetail;
use App\ItemQuantities;
use App\JournalEntry;
use App\JournalEntryDetail;
use App\LocationInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemAssemblyController extends Controller
{
    //
    public function index()
    {
        $data = ItemAssemblie::with(['parentItem', 'details'])->paginate(10);
        return view('item_assembly.index', compact('data'));
    }
    public function create()
    {
        $item = Item::all();
        $fromLocation = LocationInventory::all();
        return view('item_assembly.create', compact('item', 'fromLocation'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'             => 'required|date',
            'parent_item_id'   => 'required|exists:items,id',
            'location_id' => 'required|exists:location_inventories,id',
            'qty_built'        => 'required|numeric|min:1',
            'total_cost'       => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            /**
             * 1. Simpan Header
             */
            $assembly = ItemAssemblie::create([
                'date'             => $request->date,
                'parent_item_id'   => $request->parent_item_id,
                'qty_built'        => $request->qty_built,
                'total_cost'       => $request->total_cost,
                'notes'            => $request->notes,
                'from_location_id' => $request->location_id
            ]);

            /**
             * 2. Simpan Detail
             */
            $details = [];
            if ($request->has('component_id')) {
                foreach ($request->component_id as $i => $compId) {
                    $details[] = ItemAssemblieDetail::create([
                        'item_assembly_id'  => $assembly->id,
                        'component_item_id' => $compId,
                        'unit'              => $request->unit[$i] ?? '-',
                        'qty_used'          => $request->qty_used[$i] ?? 0,
                        'unit_cost'         => $request->unit_cost[$i] ?? 0,
                        'total_cost'        => $request->component_total_cost[$i] ?? 0,
                    ]);
                }
            }

            /**
             * 3. Update Inventory
             */
            // Parent item → tambah
            $this->adjustItemQuantity(
                $request->parent_item_id,
                $request->location_id,
                $request->qty_built,
                $request->total_cost
            );

            // Komponen → kurangi
            foreach ($details as $d) {
                $this->adjustItemQuantity(
                    $d->component_item_id,
                    $request->location_id,
                    -$d->qty_used,
                    -$d->total_cost
                );
            }

            /**
             * 4. Simpan Jurnal
             */
            $journal = JournalEntry::create([
                'source'  => 'ItemAssembly',
                'tanggal' => $request->date,
                'comment' => 'Assembly ID ' . $assembly->id,
            ]);

            $totalDebit  = 0;
            $totalCredit = 0;

            // Debit parent item
            $parentAccount = optional(
                ItemAccount::where('item_id', $request->parent_item_id)->first()
            )->assetAccount;

            if ($parentAccount) {
                JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'kode_akun'        => $parentAccount->kode_akun,
                    'debits'           => $request->total_cost,
                    'credits'          => 0,
                    'comment'          => 'Persediaan Barang Jadi (Assembly)',
                ]);
                $totalDebit += $request->total_cost;
            }

            // Credit komponen
            foreach ($details as $d) {
                $compAccount = optional(
                    ItemAccount::where('item_id', $d->component_item_id)->first()
                )->assetAccount;

                if ($compAccount) {
                    JournalEntryDetail::create([
                        'journal_entry_id' => $journal->id,
                        'kode_akun'        => $compAccount->kode_akun,
                        'debits'           => 0,
                        'credits'          => $d->total_cost,
                        'comment'          => 'Pemakaian Komponen ' . $d->component->item_description,
                    ]);
                    $totalCredit += $d->total_cost;
                }
            }

            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new \Exception("Jurnal tidak balance! Debit: $totalDebit, Credit: $totalCredit");
            }

            DB::commit();

            return redirect()->route('item_assembly.index')
                ->with('success', 'Assembly berhasil disimpan');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Helper update item_quantities per lokasi
     */
    protected function adjustItemQuantity($itemId, $locationId, $qtyChange, $valueChange)
    {
        $itemQty = ItemQuantities::firstOrCreate(
            ['item_id' => $itemId, 'location_id' => $locationId],
            ['on_hand_qty' => 0, 'on_hand_value' => 0]
        );

        $itemQty->on_hand_qty   += $qtyChange;
        $itemQty->on_hand_value += $valueChange;
        $itemQty->save();
    }

    public function show($id)
    {
        $assembly = \App\ItemAssemblie::with([
            'parentItem',                 // produk utama
            'details.component'       // detail + komponen
        ])->findOrFail($id);

        return view('item_assembly.show', compact('assembly'));
    }
    public function edit($id)
    {
        $assembly = ItemAssemblie::with(['parentItem', 'details.component'])->findOrFail($id);
        $item = item::all();
        $fromLocation = LocationInventory::all();
        $itemAccounts = \App\ItemAccount::with('assetAccount')->get()->mapWithKeys(function ($acc) {
            return [$acc->item_id => [
                'kode_akun' => $acc->assetAccount->kode_akun ?? '-',
                'nama_akun' => $acc->assetAccount->nama_akun ?? '-',
            ]];
        });
        return view('item_assembly.edit', compact('assembly', 'item', 'fromLocation', 'itemAccounts'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'date'             => 'required|date',
            'parent_item_id'   => 'required|exists:items,id',
            'from_location_id' => 'required|exists:location_inventories,id',
            'qty_to_build'     => 'required|numeric|min:1',
            'total_cost'       => 'required', // kita normalisasi sendiri
        ]);

        DB::beginTransaction();

        try {
            $assembly = ItemAssemblie::with(['details'])->findOrFail($id);

            // Helper untuk normalisasi angka (hapus koma, spasi, dsb.)
            $toNumber = function ($value) {
                if (is_array($value)) $value = reset($value);
                $value = str_replace([',', ' '], '', (string) $value);
                return (float) preg_replace('/[^0-9.\-]/', '', $value);
            };

            // ----------------------------
            // 1. Rollback stok lama
            // ----------------------------
            $this->adjustItemQuantity(
                $assembly->parent_item_id,
                $assembly->from_location_id,
                -$assembly->qty_built,
                -$assembly->total_cost
            );

            foreach ($assembly->details as $old) {
                $this->adjustItemQuantity(
                    $old->component_item_id,
                    $assembly->from_location_id,
                    $old->qty_used,
                    $old->total_cost
                );
            }

            // ----------------------------
            // 2. Update header
            // ----------------------------
            $headerTotalCost = $toNumber($request->input('total_cost'));

            $assembly->update([
                'date'             => $request->date,
                'parent_item_id'   => $request->parent_item_id,
                'qty_built'        => $request->qty_to_build,
                'total_cost'       => $headerTotalCost,
                'notes'            => $request->notes,
                'from_location_id' => $request->from_location_id,
            ]);

            // ----------------------------
            // 3. Update detail
            // ----------------------------
            $assembly->details()->delete();
            $details = [];

            if ($request->has('component_id')) {
                foreach ($request->component_id as $i => $compId) {
                    $qtyUsed   = $toNumber($request->qty_used[$i] ?? 0);
                    $unitCost  = $toNumber($request->unit_cost[$i] ?? 0);
                    $rowTotal  = $toNumber($request->component_total_cost[$i] ?? 0);

                    $details[] = ItemAssemblieDetail::create([
                        'item_assembly_id'  => $assembly->id,
                        'component_item_id' => $compId,
                        'unit'              => $request->unit[$i] ?? '-',
                        'qty_used'          => $qtyUsed,
                        'unit_cost'         => $unitCost,
                        'total_cost'        => $rowTotal,
                    ]);
                }
            }

            // ----------------------------
            // 4. Update stok baru
            // ----------------------------
            $this->adjustItemQuantity(
                $request->parent_item_id,
                $request->from_location_id,
                $request->qty_to_build,
                $headerTotalCost
            );

            foreach ($details as $d) {
                $this->adjustItemQuantity(
                    $d->component_item_id,
                    $request->from_location_id,
                    -$d->qty_used,
                    -$d->total_cost
                );
            }

            // ----------------------------
            // 5. Update jurnal
            // ----------------------------
            // Hapus jurnal lama
            $oldJournal = JournalEntry::where('source', 'ItemAssembly')
                ->where('comment', 'Assembly ID ' . $assembly->id)
                ->first();
            if ($oldJournal) {
                $oldJournal->details()->delete();
                $oldJournal->delete();
            }

            // Buat jurnal baru
            $journal = JournalEntry::create([
                'source'  => 'ItemAssembly',
                'tanggal' => $request->date,
                'comment' => 'Assembly ID ' . $assembly->id,
            ]);

            $totalDebit  = 0;
            $totalCredit = 0;

            // Debit produk jadi
            $parentAccount = optional(
                ItemAccount::where('item_id', $request->parent_item_id)->first()
            )->assetAccount;

            if ($parentAccount) {
                JournalEntryDetail::create([
                    'journal_entry_id' => $journal->id,
                    'kode_akun'        => $parentAccount->kode_akun,
                    'debits'           => $headerTotalCost,
                    'credits'          => 0,
                    'comment'          => 'Persediaan Barang Jadi (Assembly)',
                ]);
                $totalDebit += $headerTotalCost;
            }

            // Credit komponen
            foreach ($details as $d) {
                $compAccount = optional(
                    ItemAccount::where('item_id', $d->component_item_id)->first()
                )->assetAccount;

                if ($compAccount) {
                    JournalEntryDetail::create([
                        'journal_entry_id' => $journal->id,
                        'kode_akun'        => $compAccount->kode_akun,
                        'debits'           => 0,
                        'credits'          => $d->total_cost,
                        'comment'          => 'Pemakaian Komponen ' . optional($d->component)->item_description,
                    ]);
                    $totalCredit += $d->total_cost;
                }
            }

            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new \Exception("Jurnal tidak balance! Debit: $totalDebit, Credit: $totalCredit");
            }

            DB::commit();

            return redirect()->route('item_assembly.index')
                ->with('success', 'Assembly berhasil diupdate');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // 1. Ambil assembly + detail
            $assembly = ItemAssemblie::with('details.component')->findOrFail($id);
            $locationId = $assembly->from_location_id;

            // 2. Rollback stok parent item (kurangi barang jadi)
            $this->adjustItemQuantity(
                $assembly->parent_item_id,
                $locationId,
                -$assembly->qty_built,
                -$assembly->total_cost
            );

            // 3. Rollback stok komponen (tambah kembali sesuai pemakaian)
            foreach ($assembly->details as $d) {
                $this->adjustItemQuantity(
                    $d->component_item_id,
                    $locationId,
                    $d->qty_used,
                    $d->total_cost
                );
            }

            // 4. Hapus detail
            $assembly->details()->delete();

            // 5. Hapus jurnal terkait assembly ini
            $journal = JournalEntry::where('source', 'ItemAssembly')
                ->where('comment', 'Assembly ID ' . $assembly->id)
                ->first();

            if ($journal) {
                $journal->details()->delete(); // hapus detail jurnal dulu
                $journal->delete();
            }

            // 6. Hapus header
            $assembly->delete();

            DB::commit();

            return redirect()->route('item_assembly.index')
                ->with('success', 'Assembly berhasil dihapus dan stok dikembalikan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e); // supaya error tercatat di log
            return back()->withErrors('Gagal menghapus assembly: ' . $e->getMessage());
        }
    }
}
