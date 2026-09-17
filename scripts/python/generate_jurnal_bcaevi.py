"""
Generate Jurnal Import GL dari file mentah KINGS - Bank BCA Evi 2025
Format source: BCAEVI-YYMMDD-XXX (no urut reset setiap hari)

Kolom E-I  = Pemasukan (masuk ke bank BCA EVI)
  Debit  : 1102038 BCA EVI - PUSAT BANDUNG
  Kredit : 1104008 PIUTANG KINGS - PUSAT BANDUNG

Kolom J = Pengeluaran (keluar dari bank BCA EVI)
  Debit  : 1104008 PIUTANG KINGS - PUSAT BANDUNG
  Kredit : 1102038 BCA EVI - PUSAT BANDUNG
"""

import openpyxl
from openpyxl.styles import Font, PatternFill, Alignment, Border, Side
from datetime import datetime, date
import os

# ─────────────────────────────────────────────────────────────────────────────
# KONFIGURASI
# ─────────────────────────────────────────────────────────────────────────────
INPUT_FILE  = r'C:\laragon\www\rembulancitraabadi.annamlproject.id\data file excel\Data Mentah\KINGS-  Bank BCA Evi 2025.xlsx'
OUTPUT_FILE = r'C:\laragon\www\rembulancitraabadi.annamlproject.id\data file excel\Import GL BCAEVI 2025.xlsx'

DEPT        = 'PUSAT BANDUNG'
KD_BANK     = 1102038
NM_BANK     = 'BCA EVI'
KD_PIUTANG  = 1104008
NM_PIUTANG  = 'PIUTANG KINGS'

# Header kolom sesuai format Import GL 2024
HEADERS = ['source', 'tanggal', 'comment_line', 'departemen', 'kode_akun', 'nama_akun', 'debit', 'kredit', 'specpose']

# ─────────────────────────────────────────────────────────────────────────────
# BACA FILE MENTAH
# ─────────────────────────────────────────────────────────────────────────────
print(f"Membaca file: {INPUT_FILE}")
wb_in  = openpyxl.load_workbook(INPUT_FILE, data_only=True)
ws_in  = wb_in.active
print(f"Sheet aktif: {ws_in.title}, Total baris: {ws_in.max_row}")

# ─────────────────────────────────────────────────────────────────────────────
# PROSES DATA
# ─────────────────────────────────────────────────────────────────────────────
journal_rows = []   # list of dict
current_month_date = None   # datetime dari kolom A (bulan marker)
source_counter = {}          # {date_str: count}  – reset setiap hari

def get_source(tgl: date) -> str:
    """Hasilkan source BCAEVI-YYMMDD-XXX, counter reset setiap hari."""
    key = tgl.strftime('%y%m%d')
    source_counter[key] = source_counter.get(key, 0) + 1
    return f"BCAEVI-{key}-{source_counter[key]:03d}"

def make_journal_pair(source, tgl, comment, amount, is_income):
    """
    Buat pasangan jurnal debit/kredit.
    is_income=True  → Debit BCA EVI, Kredit PIUTANG KINGS
    is_income=False → Debit PIUTANG KINGS, Kredit BCA EVI
    """
    if is_income:
        debit_akun,  debit_nama  = KD_BANK,    NM_BANK
        kredit_akun, kredit_nama = KD_PIUTANG, NM_PIUTANG
    else:
        debit_akun,  debit_nama  = KD_PIUTANG, NM_PIUTANG
        kredit_akun, kredit_nama = KD_BANK,    NM_BANK

    row_debit = {
        'source':      source,
        'tanggal':     tgl,
        'comment_line': comment,
        'departemen':  DEPT,
        'kode_akun':   debit_akun,
        'nama_akun':   debit_nama,
        'debit':       amount,
        'kredit':      0,
        'specpose':    None,
    }
    row_kredit = {
        'source':      source,
        'tanggal':     tgl,
        'comment_line': comment,
        'departemen':  DEPT,
        'kode_akun':   kredit_akun,
        'nama_akun':   kredit_nama,
        'debit':       0,
        'kredit':      amount,
        'specpose':    None,
    }
    return row_debit, row_kredit

# Kolom di spreadsheet (0-indexed):
# A=0  B=1  C=2  D=3  E=4  F=5  G=6  H=7  I=8  J=9
COL_A, COL_C, COL_D = 0, 2, 3
COL_E, COL_F, COL_G, COL_H, COL_I = 4, 5, 6, 7, 8
COL_J = 9

current_day = None   # int: hari dari kolom C (terakhir valid)
skipped = 0
processed = 0

# Baris terakhir yang valid (sebelum baris total/summary di row 1014)
MAX_DATA_ROW = 1013
# Sanity check: nilai transaksi per baris maks (untuk menyaring baris total)
MAX_AMOUNT_PER_TXN = 50_000_000  # 50 juta

for row_idx, row in enumerate(ws_in.iter_rows(min_row=4, max_row=MAX_DATA_ROW, values_only=True), start=4):
    # ── Update bulan jika kolom A berisi tanggal ──────────────────────────
    if row[COL_A] is not None:
        val = row[COL_A]
        if isinstance(val, datetime):
            current_month_date = val
        elif isinstance(val, (int, float)):
            # Excel serial number – biarkan openpyxl menangani, harusnya sudah datetime
            pass

    # ── Update hari jika kolom C berisi angka ─────────────────────────────
    if row[COL_C] is not None:
        try:
            current_day = int(row[COL_C])
        except (ValueError, TypeError):
            pass

    # ── Ambil nilai transaksi ─────────────────────────────────────────────
    e_val = row[COL_E]
    f_val = row[COL_F]
    g_val = row[COL_G]
    h_val = row[COL_H]
    i_val = row[COL_I]
    j_val = row[COL_J]

    # Helper: safely convert to float
    def to_num(v):
        try:
            f = float(v)
            return f if f > 0 else None
        except (TypeError, ValueError):
            return None

    # Filter: hanya baris yang punya nilai transaksi di E-J (angka positif)
    income_vals  = [n for n in [to_num(e_val), to_num(f_val), to_num(g_val), to_num(h_val), to_num(i_val)] if n is not None]
    expense_vals = [n for n in [to_num(j_val)] if n is not None]

    if not income_vals and not expense_vals:
        continue

    # ── Pastikan kita punya tanggal yang valid ────────────────────────────
    if current_month_date is None:
        skipped += 1
        continue

    # Bangun tanggal transaksi
    if current_day is not None:
        try:
            tgl = date(current_month_date.year, current_month_date.month, current_day)
        except ValueError:
            # Tanggal tidak valid (misal: hari 31 di bulan Feb), gunakan hari terakhir bulan
            import calendar
            last_day = calendar.monthrange(current_month_date.year, current_month_date.month)[1]
            tgl = date(current_month_date.year, current_month_date.month, last_day)
    else:
        # Gunakan tanggal bulan (hari 1) jika tidak ada hari spesifik
        tgl = date(current_month_date.year, current_month_date.month, 1)

    # ── Ambil comment dari kolom D ────────────────────────────────────────
    comment = str(row[COL_D]).strip() if row[COL_D] is not None else ''

    # ── Buat jurnal untuk setiap transaksi pemasukan (E–I) ──────────────
    for val in income_vals:
        amount = round(float(val), 2)
        if amount <= 0:
            continue
        source = get_source(tgl)
        dr, kr = make_journal_pair(source, tgl, comment, amount, is_income=True)
        journal_rows.extend([dr, kr])
        processed += 1

    # ── Buat jurnal untuk setiap transaksi pengeluaran (J) ───────────────
    for val in expense_vals:
        amount = round(float(val), 2)
        if amount <= 0:
            continue
        source = get_source(tgl)
        dr, kr = make_journal_pair(source, tgl, comment, amount, is_income=False)
        journal_rows.extend([dr, kr])
        processed += 1

print(f"\nTotal transaksi diproses : {processed}")
print(f"Total baris jurnal       : {len(journal_rows)}")
print(f"Baris dilewati (no date) : {skipped}")

# ─────────────────────────────────────────────────────────────────────────────
# TULIS OUTPUT EXCEL
# ─────────────────────────────────────────────────────────────────────────────
print(f"\nMenulis output ke: {OUTPUT_FILE}")
wb_out = openpyxl.Workbook()
ws_out = wb_out.active
ws_out.title = 'GL BCAEVI 2025'

# ── Style header ─────────────────────────────────────────────────────────────
hdr_fill  = PatternFill('solid', fgColor='1F4E79')
hdr_font  = Font(bold=True, color='FFFFFF', size=11)
hdr_align = Alignment(horizontal='center', vertical='center', wrap_text=True)
thin_border = Border(
    left=Side(style='thin'), right=Side(style='thin'),
    top=Side(style='thin'), bottom=Side(style='thin')
)

ws_out.row_dimensions[1].height = 30

for col_idx, col_name in enumerate(HEADERS, start=1):
    cell = ws_out.cell(row=1, column=col_idx, value=col_name)
    cell.font  = hdr_font
    cell.fill  = hdr_fill
    cell.alignment = hdr_align
    cell.border = thin_border

# ── Lebar kolom ──────────────────────────────────────────────────────────────
col_widths = [25, 14, 60, 18, 12, 25, 18, 18, 12]
for col_idx, width in enumerate(col_widths, start=1):
    ws_out.column_dimensions[ws_out.cell(1, col_idx).column_letter].width = width

# ── Isi data ─────────────────────────────────────────────────────────────────
date_fill_even = PatternFill('solid', fgColor='DEEAF1')
date_fill_odd  = PatternFill('solid', fgColor='FFFFFF')

num_fmt  = '#,##0.00'
date_fmt = 'DD-MMM-YYYY'

for r_idx, jrow in enumerate(journal_rows, start=2):
    fill = date_fill_even if (r_idx % 4 < 2) else date_fill_odd  # group per pair (2 baris per transaksi)

    vals = [
        jrow['source'],
        jrow['tanggal'],
        jrow['comment_line'],
        jrow['departemen'],
        jrow['kode_akun'],
        jrow['nama_akun'],
        jrow['debit'],
        jrow['kredit'],
        jrow['specpose'],
    ]

    for c_idx, val in enumerate(vals, start=1):
        cell = ws_out.cell(row=r_idx, column=c_idx, value=val)
        cell.border  = thin_border
        cell.alignment = Alignment(vertical='center')

        if c_idx == 2 and isinstance(val, date):  # tanggal
            cell.number_format = date_fmt
        elif c_idx in (7, 8) and val is not None:  # debit/kredit angka
            cell.number_format = num_fmt

# ── Freeze pane & auto filter ────────────────────────────────────────────────
ws_out.freeze_panes = 'A2'
ws_out.auto_filter.ref = ws_out.dimensions

# ── Simpan ───────────────────────────────────────────────────────────────────
wb_out.save(OUTPUT_FILE)
print(f"File berhasil disimpan!")
print(f"\nRingkasan:")
print(f"  File output  : {OUTPUT_FILE}")
print(f"  Total baris  : {len(journal_rows) + 1}  (termasuk header)")
print(f"  Transaksi    : {processed}")

# ── Preview 10 baris pertama ─────────────────────────────────────────────────
print(f"\nPreview 10 baris pertama jurnal:")
print(f"{'source':<25} {'tanggal':<14} {'comment':<40} {'akun':<10} {'debit':>15} {'kredit':>15}")
print("-" * 125)
for jrow in journal_rows[:10]:
    print(f"{jrow['source']:<25} {str(jrow['tanggal']):<14} {jrow['comment_line'][:38]:<40} {jrow['kode_akun']:<10} {jrow['debit']:>15,.2f} {jrow['kredit']:>15,.2f}")
