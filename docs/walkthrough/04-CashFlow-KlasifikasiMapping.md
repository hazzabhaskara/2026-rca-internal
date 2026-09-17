# Walkthrough: Cash Flow - Klasifikasi Akun Mapping

## Tanggal Implementasi
Desember 2024

## Tujuan
Memetakan seluruh Chart of Account ke klasifikasi akun yang tepat untuk mendukung Laporan Arus Kas Metode Langsung. Setiap akun harus memiliki `klasifikasi_id` yang benar untuk pengelompokan ke aktivitas Operasional, Investasi, atau Pendanaan.

---

## File yang Dibuat

### 1. Seeder
**File:** `database/seeds/KlasifikasiAkunMappingSeeder.php`

```php
<?php

use Illuminate\Database\Seeder;
use App\ChartOfAccount;
use App\KlasifikasiAkun;

class KlasifikasiAkunMappingSeeder extends Seeder
{
    public function run()
    {
        // Get klasifikasi IDs
        $klasifikasi = KlasifikasiAkun::pluck('id', 'nama_klasifikasi')->toArray();
        
        // STEP 1: Reset klasifikasi untuk akun beban (5xxxxxx)
        $resetCount = ChartOfAccount::where('kode_akun', '>=', '5000000')
            ->where('kode_akun', '<=', '5999999')
            ->whereIn('level_akun', ['SUB ACCOUNT', 'ACCOUNT'])
            ->update(['klasifikasi_id' => null]);
        
        // STEP 1b: Reset klasifikasi untuk akun piutang (1105xxx dan 1106xxx)
        $resetPiutang = ChartOfAccount::whereBetween('kode_akun', ['1105000', '1106999'])
            ->whereIn('level_akun', ['SUB ACCOUNT', 'ACCOUNT'])
            ->update(['klasifikasi_id' => null]);
        
        // STEP 2: Mapping berdasarkan range kode akun
        $mappings = [
            // ASET LANCAR
            ['1101000', '1101999', 'Cash'],
            ['1102000', '1102999', 'Bank'],
            ['1103000', '1103999', 'Accounts Receivable'],
            ['1104000', '1104999', 'Other Receivables'],
            ['1105000', '1105999', 'Other Receivables'],  // Piutang Pihak Ketiga
            ['1106000', '1106999', 'Other Receivables'],  // Piutang Pihak Istimewa
            ['1107000', '1107999', 'Other Receivables'],  // Piutang Yayasan
            ['1108000', '1108999', 'Inventory'],
            ['1109000', '1109999', 'Other Current Asset'],
            ['1110000', '1110999', 'Other Current Asset'],
            ['1111000', '1111999', 'Other Current Asset'],
            
            // ASET TETAP
            ['1201000', '1201999', 'Capital Asset'],
            ['1202000', '1204999', 'Capital Asset'],
            
            // ASET LAIN
            ['1301000', '1304999', 'Other Non-Current Asset'],
            
            // KEWAJIBAN LANCAR
            ['2101000', '2101999', 'Accounts Payable'],
            ['2102000', '2109999', 'Other Payable'],
            
            // KEWAJIBAN JANGKA PANJANG
            ['2201000', '2203999', 'Long Term Debt'],
            
            // EKUITAS
            ['3101000', '3105999', 'Share Capital'],
            
            // PENDAPATAN
            ['4100000', '4199999', 'Operating Revenue'],
            ['4200000', '4999999', 'Other Revenue'],
            
            // BEBAN (DETAIL)
            ['5201000', '5201999', 'Payroll Expense'],
            ['5202000', '5202999', 'General & Admin. Expense'],
            ['5203000', '5208999', 'Operating Expense'],
            ['5300000', '5302999', 'General & Admin. Expense'],
            ['5303000', '5304999', 'Amort./Depreciation Expense'],
            ['5305000', '5305999', 'Non-Operating Expense'],
            ['5306000', '5306999', 'Interest Expense'],
            ['5307000', '5307999', 'Income Tax Expense'],
            ['5308000', '5308999', 'General & Admin. Expense'],
            ['5309000', '5309999', 'Loss'],
            ['5800000', '5899999', 'Income Tax Expense'],
        ];

        // Apply mappings
        foreach ($mappings as [$start, $end, $klasifikasiName]) {
            $klasifikasiId = $klasifikasi[$klasifikasiName] ?? null;
            if ($klasifikasiId) {
                ChartOfAccount::whereBetween('kode_akun', [$start, $end])
                    ->whereIn('level_akun', ['SUB ACCOUNT', 'ACCOUNT'])
                    ->whereNull('klasifikasi_id')
                    ->update(['klasifikasi_id' => $klasifikasiId]);
            }
        }
    }
}
```

---

### 2. SQL Script (Alternatif)
**File:** `database/sql/update_klasifikasi_akun.sql`

Script SQL untuk dijalankan langsung di phpMyAdmin jika seeder tidak bisa digunakan.

---

## Mapping Klasifikasi ke Aktivitas Arus Kas

### Aktivitas Operasional
| Klasifikasi | Kode Akun | Keterangan |
|-------------|-----------|------------|
| Operating Revenue | 4100xxx | Pendapatan operasional |
| Other Revenue | 4200xxx | Pendapatan lain-lain |
| Accounts Receivable | 1103xxx | Piutang usaha |
| Other Receivables | 1104-1107xxx | Piutang lain-lain |
| Accounts Payable | 2101xxx | Hutang usaha |
| Other Payable | 2102-2109xxx | Hutang lain-lain |
| Payroll Expense | 5201xxx | Beban gaji |
| Operating Expense | 5203-5208xxx | Beban operasional |
| General & Admin. Expense | 5202, 5300-5302, 5308xxx | Beban administrasi |
| Income Tax Expense | 5307, 5800xxx | Beban pajak |
| Non-Operating Expense | 5305xxx | Beban non-operasional |

### Aktivitas Investasi
| Klasifikasi | Kode Akun | Keterangan |
|-------------|-----------|------------|
| Capital Asset | 1201-1204xxx | Aset tetap |
| Other Non-Current Asset | 1301-1304xxx | Aset tidak lancar lainnya |
| Amort./Depreciation Expense | 5303-5304xxx | Beban penyusutan |

### Aktivitas Pendanaan
| Klasifikasi | Kode Akun | Keterangan |
|-------------|-----------|------------|
| Share Capital | 3101-3105xxx | Modal |
| Long Term Debt | 2201-2203xxx | Hutang jangka panjang |
| Interest Expense | 5306xxx | Beban bunga |

---

## Cara Menjalankan

### Via Artisan Seeder
```bash
php artisan db:seed --class=KlasifikasiAkunMappingSeeder
```

### Via SQL (phpMyAdmin)
```sql
-- Contoh: Update akun Kas
UPDATE chart_of_accounts 
SET klasifikasi_id = (SELECT id FROM klasifikasi_akuns WHERE nama_klasifikasi = 'Cash')
WHERE kode_akun BETWEEN '1101000' AND '1101999'
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT');
```

---

## Testing Checklist
- [x] Semua akun Kas (1101xxx) -> Cash
- [x] Semua akun Bank (1102xxx) -> Bank
- [x] Semua akun Piutang (1103-1107xxx) -> Receivables
- [x] Semua akun Beban Gaji (5201xxx) -> Payroll Expense
- [x] Semua akun Beban Operasional -> Operating Expense / General & Admin
- [x] Semua akun Aset Tetap -> Capital Asset
- [x] Semua akun Hutang JK Panjang -> Long Term Debt
