import openpyxl

wb = openpyxl.Workbook()
ws = wb.active
ws.title = "Journal"

# Headers
headers = ['source', 'tanggal', 'departemen', 'kode_akun', 'nama_akun', 'debit', 'kredit', 'comment_line', 'specpose']
ws.append(headers)

# Data
# 1. Baris Valid
ws.append(['SIMULASI-01', '2026-06-01', '-', '111-10', 'Kas BCA', 1000000, 0, 'Setoran Awal', '-'])
ws.append(['SIMULASI-01', '2026-06-01', '-', '311-10', 'Modal', 0, 1000000, 'Setoran Awal', '-'])

# 2. Baris Invalid (Kode Akun tidak ada di DB) -> SALAH-KODE
ws.append(['SIMULASI-02', '2026-06-02', '-', '999-99', 'Akun Siluman', 500000, 0, 'Pembelian Fiktif', '-'])
ws.append(['SIMULASI-02', '2026-06-02', '-', '111-10', 'Kas BCA', 0, 500000, 'Pembelian Fiktif', '-'])

# 3. Baris Invalid (Total Debit dan Kredit tidak balance)
ws.append(['SIMULASI-03', '2026-06-03', '-', '111-10', 'Kas BCA', 200000, 0, 'Tidak Balance', '-'])
ws.append(['SIMULASI-03', '2026-06-03', '-', '411-10', 'Pendapatan', 0, 150000, 'Tidak Balance', '-'])

wb.save('c:\\laragon\\www\\Ujicoba_RembulanCitraAbadi\\data file excel\\Simulasi_Import_Staging.xlsx')
print("File Simulasi_Import_Staging.xlsx berhasil dibuat di folder 'data file excel'.")
