# FIX-10: Mode Account Cash Flow - Toggle & Net Cash Flow

**Tanggal:** 2025-12-08  
**Commit:** b2695a1  
**File Diubah:** 1 file

---

## Ringkasan
Menambahkan fungsi collapse/expand baris dan Net Cash Flow per akun untuk mode tampilan "Per Account Kas/Bank".

## Perubahan

### Penyempurnaan View Laporan
**File:** `resources/views/arus_kas/report_arus_kas.blade.php`

---

### Fitur 1: Tombol Toggle Global
Menambahkan tombol "Expand All / Collapse All" di atas tabel.

```blade
<div class="mb-2 flex gap-2" x-data="{ allExpanded: false }">
    <button type="button" 
        @click="allExpanded = !allExpanded; 
                document.querySelectorAll('[data-detail-rows]').forEach(el => 
                    el.style.display = allExpanded ? 'table-row' : 'none')"
        class="px-3 py-1 text-[10px] bg-blue-600 text-white rounded">
        <span x-text="allExpanded ? 'Collapse All' : 'Expand All'">Expand All</span>
    </button>
</div>
```

---

### Fitur 2: Baris Akun yang Dapat Di-collapse
- Status default: **Tertutup** (hanya header yang terlihat)
- Klik baris header untuk membuka/menutup baris detail
- Ikon berubah: `fa-chevron-right` ↔ `fa-chevron-down`

```blade
{{-- Header Akun (Dapat Diklik) --}}
<tr class="bg-blue-100 font-semibold cursor-pointer hover:bg-blue-200" 
    onclick="toggleAccountRows({{ $accountIndex }})">
    <td colspan="4" class="px-2 py-1 text-left">
        <i class="fas fa-chevron-right mr-1" data-toggle-icon data-account="{{ $accountIndex }}"></i>
        <i class="fas fa-wallet mr-1"></i>{{ $akunKas }}
    </td>
    <td class="border px-2 py-1 text-right">{{ number_format($subtotalIn, 2) }}</td>
    <td class="border px-2 py-1 text-right">{{ number_format($subtotalOut, 2) }}</td>
    <td class="border px-2 py-1 text-right">{{ number_format($netCashFlow, 2) }}</td>
</tr>

{{-- Baris detail tersembunyi secara default --}}
<tr data-detail-rows data-account="{{ $accountIndex }}" style="display: none;">
    ...
</tr>
```

---

### Fitur 3: Net Cash Flow Per Akun
Menambahkan kolom "Net" yang menampilkan `Cash In - Cash Out` untuk setiap akun.

```php
$netCashFlow = $subtotalIn - $subtotalOut;
```

Pewarnaan:
- **Hijau** untuk net positif
- **Merah** untuk net negatif

---

### Fungsi JavaScript Toggle
```javascript
function toggleAccountRows(accountId) {
    const rows = document.querySelectorAll(`[data-detail-rows][data-account="${accountId}"]`);
    const icon = document.querySelector(`[data-toggle-icon][data-account="${accountId}"]`);
    const isHidden = rows[0]?.style.display === 'none';
    
    rows.forEach(row => row.style.display = isHidden ? 'table-row' : 'none');
    icon?.classList.toggle('fa-chevron-right', !isHidden);
    icon?.classList.toggle('fa-chevron-down', isHidden);
}
```

---

## Performa
- Toggle murni client-side (tidak ada request ke server)
- Kompleksitas O(1) per klik
- Tidak ada query database tambahan

---

## Pengujian
1. Buka laporan Cash Flow dengan mode "Per Account Kas/Bank"
2. Verifikasi status default tertutup (collapsed)
3. Klik header akun untuk membuka detail
4. Klik "Expand All" untuk menampilkan semua detail
5. Verifikasi nilai Net benar per akun
