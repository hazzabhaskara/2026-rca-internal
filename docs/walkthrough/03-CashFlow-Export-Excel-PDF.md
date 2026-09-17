# Walkthrough: Cash Flow - Export Excel & PDF

## Tanggal Implementasi
Desember 2024

## Tujuan
Menambahkan fitur export Laporan Arus Kas ke:
1. **Excel** - Format .xlsx menggunakan Maatwebsite Excel
2. **PDF** - Format .pdf menggunakan DomPDF

Kedua format mendukung semua 3 display mode (Source, Account, Universal).

---

## File yang Dibuat

### 1. Export Class
**File:** `app/Exports/ArusKasExport.php`

```php
<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ArusKasExport implements FromView
{
    protected $rows;
    protected $tanggalAwal;
    protected $tanggalAkhir;
    protected $displayMode;

    public function __construct($rows, $tanggalAwal, $tanggalAkhir, $displayMode)
    {
        $this->rows = $rows;
        $this->tanggalAwal = $tanggalAwal;
        $this->tanggalAkhir = $tanggalAkhir;
        $this->displayMode = $displayMode;
    }

    public function view(): View
    {
        return view('arus_kas.export_excel', [
            'rows' => $this->rows,
            'tanggalAwal' => $this->tanggalAwal,
            'tanggalAkhir' => $this->tanggalAkhir,
            'displayMode' => $this->displayMode,
        ]);
    }
}
```

---

### 2. Excel Export View
**File:** `resources/views/arus_kas/export_excel.blade.php`

```blade
<table>
    <thead>
        <tr>
            <th colspan="8" style="font-size: 14pt; font-weight: bold;">
                LAPORAN ARUS KAS
            </th>
        </tr>
        <tr>
            <th colspan="8">
                Periode: {{ \Carbon\Carbon::parse($tanggalAwal)->format('d M Y') }} - 
                {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d M Y') }}
            </th>
        </tr>
    </thead>
    <tbody>
        @if($displayMode == 'source')
            {{-- Grouped by Source --}}
        @elseif($displayMode == 'account')
            {{-- Grouped by Account --}}
        @else
            {{-- Universal flat table --}}
        @endif
    </tbody>
</table>
```

---

### 3. PDF Export View
**File:** `resources/views/arus_kas/export_pdf.blade.php`

```blade
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px; }
        th { background-color: #f5f5f5; }
        .text-right { text-align: right; }
        .text-green { color: green; }
        .text-red { color: red; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <h2>LAPORAN ARUS KAS</h2>
    <p>Periode: {{ $tanggalAwal }} - {{ $tanggalAkhir }}</p>
    
    {{-- Same conditional rendering as Excel --}}
</body>
</html>
```

---

## File yang Dimodifikasi

### 1. Route
**File:** `routes/web.php`

```php
Route::get('/arus_kas/export', [ReportController::class, 'exportArusKas'])
    ->name('arus_kas.export');
```

---

### 2. Controller Method
**File:** `app/Http/Controllers/ReportController.php`

```php
public function exportArusKas(Request $request)
{
    $tanggalAwal = $request->start_date;
    $tanggalAkhir = $request->end_date;
    $displayMode = $request->display_mode ?? 'source';
    $format = $request->format ?? 'excel';

    // Get data using same logic as reportArusKas
    // ... (same data fetching logic) ...

    if ($format === 'pdf') {
        $pdf = PDF::loadView('arus_kas.export_pdf', compact(
            'rows', 'tanggalAwal', 'tanggalAkhir', 'displayMode'
        ));
        return $pdf->download('laporan-arus-kas.pdf');
    }

    // Excel export
    return Excel::download(
        new ArusKasExport($rows, $tanggalAwal, $tanggalAkhir, $displayMode),
        'laporan-arus-kas.xlsx'
    );
}
```

---

### 3. Report View - Export Buttons
**File:** `resources/views/arus_kas/report_arus_kas.blade.php`

```blade
<ul class="flex border-b mb-3 space-x-4 text-[10px] font-medium text-gray-600">
    <li>
        <a href="{{ route('arus_kas.export', [
                'start_date' => $tanggalAwal,
                'end_date' => $tanggalAkhir,
                'selected_accounts' => request('selected_accounts'),
                'display_mode' => $displayMode,
                'format' => 'excel',
            ]) }}"
            class="tab-link text-green-600 hover:text-green-800">
            <i class="fas fa-file-excel mr-1"></i> Export Excel
        </a>
    </li>
    <li>
        <a href="{{ route('arus_kas.export', [
                'start_date' => $tanggalAwal,
                'end_date' => $tanggalAkhir,
                'selected_accounts' => request('selected_accounts'),
                'display_mode' => $displayMode,
                'format' => 'pdf',
            ]) }}"
            class="tab-link text-red-600 hover:text-red-800">
            <i class="fas fa-file-pdf mr-1"></i> Export PDF
        </a>
    </li>
</ul>
```

---

## Testing Checklist
- [x] Export Excel berfungsi untuk mode Source
- [x] Export Excel berfungsi untuk mode Account
- [x] Export Excel berfungsi untuk mode Universal
- [x] Export PDF berfungsi untuk mode Source
- [x] Export PDF berfungsi untuk mode Account
- [x] Export PDF berfungsi untuk mode Universal
- [x] Total dan subtotal dihitung dengan benar
- [x] Format angka sesuai
