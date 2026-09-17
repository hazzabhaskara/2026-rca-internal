# FIX-09: Laporan Cash Flow - Implementasi 3 Mode Tampilan

**Tanggal:** 2025-12-08  
**Commit:** b2695a1  
**File Diubah:** 3 file

---

## Ringkasan
Mengimplementasikan 3 mode tampilan untuk laporan Cash Flow dengan opsi yang dapat dipilih pengguna di halaman filter.

## Perubahan

### 1. Penyempurnaan Halaman Filter
**File:** `resources/views/arus_kas/filter_arus_kas.blade.php`

**Yang Diubah:**
- Menambahkan grup radio button untuk "Tampilan Laporan"
- 3 opsi: Detail per Source, Per Account Kas/Bank, Universal

```blade
{{-- Pilihan Mode Tampilan --}}
<div class="sm:col-span-3">
    <label class="block text-sm font-semibold text-gray-700 mb-2">Tampilan Laporan</label>
    <div class="flex flex-wrap gap-4">
        <label class="inline-flex items-center cursor-pointer">
            <input type="radio" name="display_mode" value="source" checked>
            <span class="ml-2">Detail per Source</span>
        </label>
        <label class="inline-flex items-center cursor-pointer">
            <input type="radio" name="display_mode" value="account">
            <span class="ml-2">Per Account Kas/Bank</span>
        </label>
        <label class="inline-flex items-center cursor-pointer">
            <input type="radio" name="display_mode" value="universal">
            <span class="ml-2">Universal (Tabel Lengkap)</span>
        </label>
    </div>
</div>
```

---

### 2. Update Controller
**File:** `app/Http/Controllers/ReportController.php`

**Yang Diubah:**
- Menambahkan penanganan parameter `display_mode`
- Menambahkan `line_comment` dan `kode_kas` ke data rows
- Mengirim `displayMode` ke view

```php
$displayMode = $request->display_mode ?? 'source';

$rows[] = [
    'tanggal'      => $entry->tanggal,
    'source'       => $entry->source,
    'akun_kas'     => $kas->kode_akun . ' - ' . ($kas->chartOfAccount->nama_akun ?? ''),
    'kode_kas'     => $kas->kode_akun,
    'lawan_akun'   => $lawan->kode_akun . ' - ' . ($lawan->chartOfAccount->nama_akun ?? ''),
    'line_comment' => $lawan->comment ?? $kas->comment ?? '',
    'keterangan'   => $entry->comment ?? '',
    'cash_in'      => $isCashIn ? $nilaiKasProporsional : 0,
    'cash_out'     => !$isCashIn ? $nilaiKasProporsional : 0,
];

return view('arus_kas.report_arus_kas', compact('rows', 'tanggalAwal', 'tanggalAkhir', 'displayMode'));
```

---

### 3. View Laporan - 3 Mode Tampilan
**File:** `resources/views/arus_kas/report_arus_kas.blade.php`

**Yang Diubah:**
- Penulisan ulang total dengan conditional rendering untuk 3 mode
- Ukuran font kompak (`text-[10px]`) dan padding (`px-2 py-1`)

| Mode | Pengelompokan | Kolom |
|------|---------------|-------|
| `source` | Per Source Jurnal | Tanggal, Source, Akun Kas, Lawan Akun, Keterangan |
| `account` | Per Akun Kas/Bank | Tanggal, Source, Lawan Akun, Line Comment + Toggle + Net |
| `universal` | Tanpa pengelompokan | Semua kolom dalam satu tabel |

---

## Pengujian
1. Buka menu Report > Arus Kas
2. Pilih periode dan akun
3. Pilih mode tampilan yang diinginkan
4. Verifikasi format tabel yang tampil sesuai mode
