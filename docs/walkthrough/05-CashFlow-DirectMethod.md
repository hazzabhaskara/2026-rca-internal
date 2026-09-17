# Walkthrough: Cash Flow - Metode Langsung (Direct Method)

## Tanggal Implementasi
Desember 2024

## Tujuan
Menambahkan mode tampilan ke-4 "Metode Langsung" yang mengelompokkan arus kas berdasarkan aktivitas:
1. **Aktivitas Operasional** - Penerimaan dan pengeluaran dari kegiatan utama
2. **Aktivitas Investasi** - Pembelian/penjualan aset tetap
3. **Aktivitas Pendanaan** - Penerimaan/pembayaran modal dan pinjaman

Fitur tambahan:
- Drill-down per klasifikasi (klik untuk lihat detail transaksi)
- Sorting berdasarkan urutan klasifikasi akun
- Summary box per aktivitas

---

## File yang Dimodifikasi

### 1. Controller - Mapping Aktivitas
**File:** `app/Http/Controllers/ReportController.php`

#### Konstanta AKTIVITAS_MAPPING

```php
const AKTIVITAS_MAPPING = [
    // ===== AKTIVITAS OPERASIONAL =====
    'Cash' => 'Operasional',
    'Bank' => 'Operasional',
    'Operating Revenue' => 'Operasional',
    'Other Revenue' => 'Operasional',
    'Accounts Receivable' => 'Operasional',
    'Other Receivables' => 'Operasional',
    'Accounts Payable' => 'Operasional',
    'Other Payable' => 'Operasional',
    'Inventory' => 'Operasional',
    'Other Current Asset' => 'Operasional',
    'Payroll Expense' => 'Operasional',
    'Operating Expense' => 'Operasional',
    'General & Admin. Expense' => 'Operasional',
    'Income Tax Expense' => 'Operasional',
    'Non-Operating Expense' => 'Operasional',
    // ... more items
    
    // ===== AKTIVITAS INVESTASI =====
    'Capital Asset' => 'Investasi',
    'Accum. Amort./Depreciation' => 'Investasi',
    'Other Non-Current Asset' => 'Investasi',
    'Gain' => 'Investasi',
    'Loss' => 'Investasi',
    
    // ===== AKTIVITAS PENDANAAN =====
    'Share Capital' => 'Pendanaan',
    'Retained Earnings' => 'Pendanaan',
    'Long Term Debt' => 'Pendanaan',
    'Interest Expense' => 'Pendanaan',
    'Owner/Partner Withdrawals' => 'Pendanaan',
];

const NON_CASH_KLASIFIKASI = [
    'Amort./Depreciation Expense',  // Non-cash item
    'Bad Debt Expense',              // Non-cash item
];
```

#### Method groupByAktivitas()

```php
private function groupByAktivitas($rows)
{
    // Get klasifikasi id for sorting
    $klasifikasiUrutan = \App\KlasifikasiAkun::pluck('id', 'nama_klasifikasi')->toArray();
    
    $aktivitas = [
        'Operasional' => [
            'label' => 'Arus Kas dari Aktivitas Operasional',
            'items' => [],
            'cash_in' => 0,
            'cash_out' => 0,
        ],
        'Investasi' => [
            'label' => 'Arus Kas dari Aktivitas Investasi',
            'items' => [],
            'cash_in' => 0,
            'cash_out' => 0,
        ],
        'Pendanaan' => [
            'label' => 'Arus Kas dari Aktivitas Pendanaan',
            'items' => [],
            'cash_in' => 0,
            'cash_out' => 0,
        ],
    ];
    
    // Group by klasifikasi within each aktivitas
    foreach ($rows as $row) {
        $akt = $row['aktivitas'];
        $klasifikasi = $row['klasifikasi_akun'];
        
        if (!isset($aktivitas[$akt]['items'][$klasifikasi])) {
            $aktivitas[$akt]['items'][$klasifikasi] = [
                'label' => $klasifikasi,
                'urutan' => $klasifikasiUrutan[$klasifikasi] ?? 999,
                'cash_in' => 0,
                'cash_out' => 0,
            ];
        }
        
        $aktivitas[$akt]['items'][$klasifikasi]['cash_in'] += $row['cash_in'];
        $aktivitas[$akt]['items'][$klasifikasi]['cash_out'] += $row['cash_out'];
        $aktivitas[$akt]['cash_in'] += $row['cash_in'];
        $aktivitas[$akt]['cash_out'] += $row['cash_out'];
    }
    
    // Sort items within each aktivitas by urutan
    foreach ($aktivitas as &$akt) {
        uasort($akt['items'], function($a, $b) {
            return ($a['urutan'] ?? 999) <=> ($b['urutan'] ?? 999);
        });
    }
    
    return $aktivitas;
}
```

---

### 2. Filter View - Radio Button
**File:** `resources/views/arus_kas/filter_arus_kas.blade.php`

```blade
<label class="inline-flex items-center cursor-pointer">
    <input type="radio" name="display_mode" value="direct"
        class="form-radio text-green-600 focus:ring-green-500">
    <span class="ml-2 text-sm text-gray-700 font-semibold">Metode Langsung</span>
</label>
```

---

### 3. Report View - Direct Method Section
**File:** `resources/views/arus_kas/report_arus_kas.blade.php`

#### Struktur Utama

```blade
@if ($displayMode === 'direct')
    @php
        $grandCashIn = collect($rows)->sum('cash_in');
        $grandCashOut = collect($rows)->sum('cash_out');
        $netCashFlow = $grandCashIn - $grandCashOut;
        $rowsByKlasifikasi = collect($rows)->groupBy('klasifikasi_akun');
    @endphp

    <div class="overflow-x-auto" x-data="{ expandedItems: {} }">
        {{-- Toggle All Button --}}
        <button @click="...">Toggle Semua Detail</button>
        
        <table>
            @foreach ($aktivitasData as $aktKey => $akt)
                {{-- Header Aktivitas --}}
                <tr class="bg-blue-50">
                    <td colspan="4">{{ $akt['label'] }}</td>
                </tr>
                
                {{-- Items per Klasifikasi (Clickable) --}}
                @foreach ($akt['items'] as $klasifikasiKey => $item)
                    <tr @click="expand/collapse">
                        <td>{{ $item['label'] }} ({{ count }} transaksi)</td>
                        <td>{{ $itemNet }}</td>
                    </tr>
                    
                    {{-- Detail Rows (Hidden by default) --}}
                    <tr x-show="expanded">
                        <td colspan="4">
                            <table><!-- Detail transactions --></table>
                        </td>
                    </tr>
                @endforeach
                
                {{-- Subtotal Aktivitas --}}
                <tr class="bg-gray-100 font-semibold">
                    <td>Arus Kas Bersih dari {{ $akt['label'] }}</td>
                    <td>{{ $aktNetFlow }}</td>
                </tr>
            @endforeach
            
            {{-- Grand Total --}}
            <tr class="bg-green-100 font-bold">
                <td>KENAIKAN/PENURUNAN BERSIH KAS</td>
                <td>{{ $netCashFlow }}</td>
            </tr>
        </table>
    </div>
    
    {{-- Summary Box --}}
    <div class="grid grid-cols-3 gap-4">
        @foreach ($aktivitasData as $aktKey => $akt)
            <div class="{{ $akt positif ? 'bg-green-50' : 'bg-red-50' }}">
                <div>{{ $aktKey }}</div>
                <div>Rp {{ $aktNetFlow }}</div>
            </div>
        @endforeach
    </div>
@endif
```

---

## Hasil Visual

### Format Output Metode Langsung

```
LAPORAN ARUS KAS - METODE LANGSUNG
Periode: 01 Jan 2024 - 31 Dec 2024

📁 Arus Kas dari Aktivitas Operasional
├── > Operating Revenue (440 transaksi)           10,828,200,684
├── > Other Revenue (20 transaksi)                     4,926,806
├── > Other Receivables (373 transaksi)           (3,469,670,376)
├── > Other Current Asset (174 transaksi)         (2,725,541,954)
├── > Accounts Payable (37 transaksi)               (521,743,600)
├── > Other Payable (160 transaksi)                 (891,051,713)
├── > Operating Expense (124 transaksi)             (636,589,507)
├── > General & Admin. Expense (37 transaksi)       (201,694,144)
├── > Payroll Expense (233 transaksi)             (2,341,709,815)
├── > Income Tax Expense (3 transaksi)              (288,009,739)
└── > Non-Operating Expense (226 transaksi)         (320,971,897)
    Arus Kas Bersih dari Aktivitas Operasional    (563,855,255)

📁 Arus Kas dari Aktivitas Investasi
└── [Tidak ada transaksi]
    Arus Kas Bersih dari Aktivitas Investasi                  0

📁 Arus Kas dari Aktivitas Pendanaan
└── [Tidak ada transaksi]
    Arus Kas Bersih dari Aktivitas Pendanaan                  0

═══════════════════════════════════════════════════════════════
KENAIKAN/PENURUNAN BERSIH KAS                     (563,855,255)
═══════════════════════════════════════════════════════════════

┌─────────────────────────────────────────────────────────────┐
│  Ringkasan Arus Kas                                         │
├───────────────┬───────────────┬───────────────┬────────────┤
│  Operasional  │   Investasi   │   Pendanaan   │            │
│ (563,855,255) │       0       │       0       │            │
└───────────────┴───────────────┴───────────────┴────────────┘
```

---

## Drill-Down Detail

Ketika klasifikasi diklik, akan menampilkan detail transaksi:

```
▼ Operating Revenue (440 transaksi)                10,828,200,684
┌──────────┬────────────┬─────────────────────┬──────────────┬──────────────┐
│ Tanggal  │ Source     │ Lawan Akun          │ Cash In      │ Cash Out     │
├──────────┼────────────┼─────────────────────┼──────────────┼──────────────┤
│ 02/01/24 │ MDR_7410-1 │ PEND. SEWA TANAH    │ 17,000,000   │              │
│ 02/01/24 │ MDR_7410-2 │ PEND. SEWA TANAH    │  8,500,000   │              │
│ ...      │ ...        │ ...                 │ ...          │ ...          │
└──────────┴────────────┴─────────────────────┴──────────────┴──────────────┘
```

---

## Testing Checklist
- [x] Radio button "Metode Langsung" berfungsi
- [x] Pengelompokan aktivitas sesuai mapping
- [x] Sorting klasifikasi berdasarkan kode_klasifikasi
- [x] Drill-down per klasifikasi berfungsi
- [x] Toggle All button berfungsi
- [x] Subtotal per aktivitas dihitung dengan benar
- [x] Grand total (Kenaikan/Penurunan Bersih Kas) benar
- [x] Summary box menampilkan 3 aktivitas
- [x] Warna: hijau positif, merah negatif
- [x] Non-cash items (Depreciation, Bad Debt) di-exclude
