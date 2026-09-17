# Walkthrough: Cash Flow - 3 Display Modes

## Tanggal Implementasi
Desember 2024

## Tujuan
Menambahkan 3 mode tampilan untuk Laporan Arus Kas:
1. **Detail per Source** - Menampilkan transaksi dikelompokkan berdasarkan source jurnal
2. **Per Account Kas/Bank** - Menampilkan transaksi dikelompokkan per akun kas/bank
3. **Universal** - Menampilkan semua transaksi dalam satu tabel lengkap

---

## File yang Dimodifikasi

### 1. Filter View
**File:** `resources/views/arus_kas/filter_arus_kas.blade.php`

**Perubahan:**
Menambahkan radio button untuk memilih display mode:

```html
{{-- Display Mode Selection --}}
<div class="sm:col-span-3">
    <label class="block text-sm font-semibold text-gray-700 mb-2">Tampilan Laporan</label>
    <div class="flex flex-wrap gap-4">
        <label class="inline-flex items-center cursor-pointer">
            <input type="radio" name="display_mode" value="source" checked
                class="form-radio text-blue-600 focus:ring-blue-500">
            <span class="ml-2 text-sm text-gray-700">Detail per Source</span>
        </label>
        <label class="inline-flex items-center cursor-pointer">
            <input type="radio" name="display_mode" value="account"
                class="form-radio text-blue-600 focus:ring-blue-500">
            <span class="ml-2 text-sm text-gray-700">Per Account Kas/Bank</span>
        </label>
        <label class="inline-flex items-center cursor-pointer">
            <input type="radio" name="display_mode" value="universal"
                class="form-radio text-blue-600 focus:ring-blue-500">
            <span class="ml-2 text-sm text-gray-700">Universal (Tabel Lengkap)</span>
        </label>
    </div>
</div>
```

---

### 2. Controller
**File:** `app/Http/Controllers/ReportController.php`

**Perubahan pada `reportArusKas()`:**

```php
public function reportArusKas(Request $request)
{
    $tanggalAwal = $request->start_date;
    $tanggalAkhir = $request->end_date;
    $displayMode = $request->display_mode ?? 'source'; // Tambah parameter display mode

    // ... existing logic ...

    // Tambah data kode_kas dan line_comment ke setiap row
    $rows[] = [
        'tanggal'      => $entry->tanggal,
        'source'       => $entry->source,
        'akun_kas'     => $kas->kode_akun . ' - ' . ($kas->chartOfAccount->nama_akun ?? ''),
        'kode_kas'     => $kas->kode_akun,  // BARU
        'lawan_akun'   => $lawan->kode_akun . ' - ' . ($lawan->chartOfAccount->nama_akun ?? ''),
        'line_comment' => $lawan->comment ?? $kas->comment ?? '',  // BARU
        'keterangan'   => $entry->comment ?? '',
        'cash_in'      => $isCashIn ? $nilaiKasProporsional : 0,
        'cash_out'     => !$isCashIn ? $nilaiKasProporsional : 0,
    ];

    return view('arus_kas.report_arus_kas', compact('rows', 'tanggalAwal', 'tanggalAkhir', 'displayMode'));
}
```

---

### 3. Report View
**File:** `resources/views/arus_kas/report_arus_kas.blade.php`

**Struktur Conditional Rendering:**

```blade
{{-- MODE 1: DETAIL PER SOURCE (DEFAULT) --}}
@if($displayMode == 'source')
    @php
        $grouped = collect($rows)->groupBy('source');
    @endphp
    {{-- Render table grouped by source --}}
    
{{-- MODE 2: PER ACCOUNT KAS/BANK --}}
@elseif($displayMode == 'account')
    @php
        $groupedByAccount = collect($rows)->groupBy('kode_kas');
    @endphp
    {{-- Render table grouped by kode_kas --}}
    
{{-- MODE 3: UNIVERSAL (TABEL LENGKAP) --}}
@else
    {{-- Render flat table with all rows --}}
@endif
```

---

## Hasil

### Mode 1: Detail per Source
![Source Mode](screenshots/mode-source.png)
- Transaksi dikelompokkan berdasarkan nomor source jurnal
- Subtotal per source
- Grand total di akhir

### Mode 2: Per Account Kas/Bank
![Account Mode](screenshots/mode-account.png)
- Transaksi dikelompokkan berdasarkan akun kas/bank
- Subtotal per akun
- Net Cash Flow per akun

### Mode 3: Universal
![Universal Mode](screenshots/mode-universal.png)
- Semua transaksi dalam satu tabel
- Kolom lengkap: Tanggal, Source, Akun Kas/Bank, Lawan Akun, Line Comment, Keterangan, Cash In, Cash Out

---

## Testing Checklist
- [x] Radio button berfungsi dengan benar
- [x] Mode Source menampilkan grouping per source
- [x] Mode Account menampilkan grouping per akun kas/bank
- [x] Mode Universal menampilkan tabel lengkap
- [x] Subtotal dan grand total dihitung dengan benar
- [x] Line comment ditampilkan
