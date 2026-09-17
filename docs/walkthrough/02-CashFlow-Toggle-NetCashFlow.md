# Walkthrough: Cash Flow - Toggle & Net Cash Flow per Account

## Tanggal Implementasi
Desember 2024

## Tujuan
Menambahkan fitur pada mode "Per Account Kas/Bank":
1. **Collapsible Rows** - Baris detail bisa di-expand/collapse per akun
2. **Net Cash Flow per Account** - Menampilkan net cash flow di header setiap akun
3. **Global Toggle** - Tombol untuk expand/collapse semua sekaligus

---

## File yang Dimodifikasi

### Report View
**File:** `resources/views/arus_kas/report_arus_kas.blade.php`

---

## Implementasi

### 1. Alpine.js State Management

```html
<div x-data="{ 
    allExpanded: false,
    toggleAll() {
        this.allExpanded = !this.allExpanded;
        document.querySelectorAll('[data-detail-rows]').forEach(row => {
            row.style.display = this.allExpanded ? 'table-row' : 'none';
        });
        document.querySelectorAll('[data-toggle-icon]').forEach(icon => {
            icon.classList.toggle('fa-chevron-right', !this.allExpanded);
            icon.classList.toggle('fa-chevron-down', this.allExpanded);
        });
    }
}">
```

### 2. Global Toggle Button

```html
<button @click="toggleAll()" 
    class="text-xs px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
    <i class="fas" :class="allExpanded ? 'fa-compress-alt' : 'fa-expand-alt'"></i>
    <span x-text="allExpanded ? 'Collapse All' : 'Expand All'"></span>
</button>
```

### 3. Account Header Row dengan Net Cash Flow

```blade
@foreach ($groupedByAccount as $accountCode => $accountRows)
    @php
        $accountName = $accountRows->first()['akun_kas'];
        $totalCashIn = $accountRows->sum('cash_in');
        $totalCashOut = $accountRows->sum('cash_out');
        $netCashFlow = $totalCashIn - $totalCashOut;
    @endphp
    
    {{-- Header Row (Clickable) --}}
    <tr class="bg-blue-50 cursor-pointer font-semibold" 
        onclick="toggleAccountRows('{{ $accountCode }}')">
        <td class="border px-2 py-1" colspan="5">
            <i class="fas fa-chevron-right mr-2 text-gray-400" 
               data-toggle-icon data-account="{{ $accountCode }}"></i>
            {{ $accountName }}
        </td>
        {{-- Net Cash Flow Column --}}
        <td class="border px-2 py-1 text-right font-bold 
            {{ $netCashFlow >= 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ number_format($netCashFlow, 2) }}
        </td>
    </tr>
    
    {{-- Detail Rows (Hidden by default) --}}
    @foreach ($accountRows as $r)
        <tr data-detail-rows data-account="{{ $accountCode }}" 
            style="display: none;" class="hover:bg-gray-50">
            {{-- Detail columns --}}
        </tr>
    @endforeach
@endforeach
```

### 4. Toggle Function (JavaScript)

```javascript
<script>
    function toggleAccountRows(accountId) {
        const rows = document.querySelectorAll(`[data-detail-rows][data-account="${accountId}"]`);
        const icon = document.querySelector(`[data-toggle-icon][data-account="${accountId}"]`);
        const isHidden = rows[0]?.style.display === 'none';
        
        rows.forEach(row => row.style.display = isHidden ? 'table-row' : 'none');
        icon?.classList.toggle('fa-chevron-right', !isHidden);
        icon?.classList.toggle('fa-chevron-down', isHidden);
    }
</script>
```

---

## Hasil Visual

### State: Collapsed (Default)
```
┌─────────────────────────────────────────────────────────────┐
│ > 1102010 - BSI GIRO WADIAH_6655336657          +5,000,000  │
├─────────────────────────────────────────────────────────────┤
│ > 1102020 - MANDIRI_1310015467410               -2,500,000  │
├─────────────────────────────────────────────────────────────┤
│ > 1101001 - KAS BESAR                           +1,200,000  │
└─────────────────────────────────────────────────────────────┘
```

### State: Expanded
```
┌─────────────────────────────────────────────────────────────┐
│ ▼ 1102010 - BSI GIRO WADIAH_6655336657          +5,000,000  │
├─────────────────────────────────────────────────────────────┤
│   01/01/2024  JV-001  Pendapatan Sewa    +2,000,000    -    │
│   05/01/2024  JV-005  Pembayaran Gaji    -        1,500,000 │
│   10/01/2024  JV-010  Pendapatan Lain    +4,500,000    -    │
├─────────────────────────────────────────────────────────────┤
│ > 1102020 - MANDIRI_1310015467410               -2,500,000  │
└─────────────────────────────────────────────────────────────┘
```

---

## Testing Checklist
- [x] Click pada header akun toggle detail rows
- [x] Icon chevron berubah sesuai state
- [x] Net Cash Flow dihitung dengan benar (Cash In - Cash Out)
- [x] Warna Net Cash Flow: hijau positif, merah negatif
- [x] Toggle All button berfungsi
- [x] Default state adalah collapsed
