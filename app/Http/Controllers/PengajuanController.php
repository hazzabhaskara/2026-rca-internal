<?php

namespace App\Http\Controllers;

use App\ApprovalStep;
use App\chartOfAccount;
use App\Employee;
use App\Pengajuan;
use App\PengajuanApproval;
use App\PengajuanDetail;
use App\Rekening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengajuanController extends Controller
{
    //
    public function index()
    {
        $employee = auth()->user()->employee;

        // Ambil hanya pengajuan milik employee ini
        $pengajuan = Pengajuan::with(['rekening'])
            ->where('employee_id', $employee->id)
            ->latest()
            ->get();

        return view('pengajuan.index', compact('pengajuan'));
    }
    public function show($id)
    {
        $pengajuan = Pengajuan::with(['employee', 'rekening', 'details', 'details.account'])->findOrFail($id);
        return view('pengajuan.show', compact('pengajuan'));
    }


    public function create()
    {
        $rekening = Rekening::all();
        $account = chartOfAccount::all();
        return view('pengajuan.create', compact('rekening', 'account'));
    }
    private function generateNoPengajuan($tgl)
    {
        $prefix = 'AJU-' . date('Ymd', strtotime($tgl)) . '-';

        // cari nomor terakhir untuk tanggal ini
        $last = Pengajuan::whereDate('tgl_pengajuan', $tgl)
            ->orderBy('no_pengajuan', 'desc')
            ->first();

        if ($last) {
            // ambil 4 digit terakhir dan tambah 1
            $lastNumber = intval(substr($last->no_pengajuan, -4));
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }
    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {

                dump('=== 📨 Incoming Request Data ===');
                dump($request->all());

                // 🔹 1. Tentukan tanggal pengajuan
                $tgl = $request->tgl_pengajuan ?? now()->toDateString();
                dump(['Tanggal Pengajuan' => $tgl]);

                // 🔹 2. Generate nomor pengajuan (format Aju-YYYYMMDD-0001)
                $noPengajuan = $this->generateNoPengajuan($tgl);
                dump(['Nomor Pengajuan' => $noPengajuan]);

                // 🔹 3. Buat record pengajuan utama
                $pengajuan = Pengajuan::create([
                    'no_pengajuan'   => $noPengajuan,
                    'tgl_pengajuan'  => $tgl,
                    'keterangan'     => $request->keterangan,
                    'no_rek_id'         => $request->no_rek_id,
                    'employee_id'    => auth()->user()->employee->id,
                    'status'         => 'in_progress'
                ]);

                dump(['✅ Pengajuan berhasil dibuat' => $pengajuan->toArray()]);

                // 🔹 4. Proses detail berdasarkan array input dari form
                if (!empty($request->account_id)) {
                    dump('=== 🧾 Proses Detail Pengajuan ===');
                    foreach ($request->account_id as $i => $accountId) {
                        if (empty($accountId)) continue;

                        $detail = [
                            'pengajuan_id' => $pengajuan->id,
                            'account_id'   => $accountId,
                            'uraian'       => $request->uraian[$i] ?? '-',
                            'qty'          => (float) str_replace(',', '', $request->qty[$i] ?? 0),
                            'harga'        => (float) str_replace('.', '', $request->harga[$i] ?? 0),
                            'discount'     => 0,
                        ];

                        dump(["Detail ke-{$i}" => $detail]);
                        PengajuanDetail::create($detail);
                    }
                } else {
                    dump('⚠️ Tidak ada detail pengajuan dikirim dari form.');
                }

                // 🔹 5. Tentukan posisi jabatan pengaju
                $employee = auth()->user()->employee;
                $jabatanPengaju = $employee->jabatan_id;
                dump(['🧩 Jabatan Pengaju' => $jabatanPengaju]);

                // 🔹 6. Ambil semua approval step
                $steps = ApprovalStep::orderBy('step_order')->get();
                dump(['📋 Semua Approval Steps' => $steps->toArray()]);

                // 🔹 7. Tentukan titik mulai approval berdasarkan jabatan pengaju
                $startOrder = optional($steps->firstWhere('jabatan_id', $jabatanPengaju))->step_order ?? 1;
                dump(['Step Order Awal' => $startOrder]);

                // 🔹 8. Generate approval record
                foreach ($steps->where('step_order', '>=', $startOrder) as $step) {
                    $approver = Employee::where('jabatan_id', $step->jabatan_id)->first();
                    $status = $step->step_order == $startOrder ? 'pending' : 'pending';

                    $approval = PengajuanApproval::create([
                        'pengajuan_id'     => $pengajuan->id,
                        'approval_step_id' => $step->id,
                        'approver_id'      => $approver->id ?? null,
                        'step_order'       => $step->step_order,
                        'status'           => $status,
                    ]);

                    dump(['🧱 Approval Step Dibuat' => $approval->toArray()]);
                }

                dump('🎯 Semua data berhasil dibuat.');
            });

            dd('🚀 Proses selesai — cek hasil dump di atas.');
        } catch (\Throwable $e) {
            dd([
                '🔥 ERROR TERDETEKSI' => $e->getMessage(),
                '📁 FILE' => $e->getFile(),
                '🔢 LINE' => $e->getLine(),
                '🧩 TRACE' => collect($e->getTrace())->take(5)->toArray()
            ]);
        }
    }
    public function edit($id)
    {
        $pengajuan = Pengajuan::with(['employee', 'rekening', 'details', 'details.account'])->findOrFail($id);
        $rekening = Rekening::all();
        $account = chartOfAccount::all();
        return view('pengajuan.edit', compact('pengajuan', 'rekening', 'account'));
    }
    public function update(Request $request, $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);

        DB::transaction(function () use ($pengajuan, $request) {
            $pengajuan->update([
                'tgl_pengajuan' => $request->tgl_pengajuan,
                'no_rek_id'     => $request->no_rek_id,
                'keterangan'    => $request->keterangan,
            ]);

            // Hapus detail lama
            $pengajuan->details()->delete();

            // Insert ulang
            $accountIds = $request->account_id ?? [];
            foreach ($accountIds as $i => $accountId) {
                if (!$accountId) continue;

                $pengajuan->details()->create([
                    'account_id' => $accountId,
                    'qty'        => $request->qty[$i],
                    'harga'      => str_replace('.', '', $request->harga[$i]),
                    'uraian'     => $request->uraian[$i] ?? '-',
                ]);
            }
        });

        return redirect()->route('pengajuan.index')->with('success', 'Data berhasil diperbarui.');
    }
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $pengajuan = Pengajuan::with(['details'])->findOrFail($id);



                // ✅ Kalau aman, hapus (details ikut terhapus otomatis via cascade)
                $pengajuan->delete();
            });

            return redirect()->route('pengajuan.index')->with('success', 'Pengajuan berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('pengajuan.index')->with('error', $e->getMessage());
        }
    }
}
