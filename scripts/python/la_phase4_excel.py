"""
Phase 4: Generate Comprehensive Excel Output
Multi-sheet Excel with full classification, aged care focus
"""

import requests
import pandas as pd
import numpy as np
from datetime import datetime, date
from openpyxl import load_workbook
from openpyxl.styles import (PatternFill, Font, Alignment, Border, Side,
                              GradientFill)
from openpyxl.utils import get_column_letter
from openpyxl.chart import BarChart, Reference
from openpyxl.chart.series import DataPoint
import warnings
warnings.filterwarnings('ignore')

print("="*60)
print("PHASE 4: Generating Excel Output")
print("="*60)

# ============================================================
# LOAD PREPARED DATA FROM PHASE 1 & 2
# ============================================================
df = pd.read_csv(r'c:\laragon\www\Ujicoba_RembulanCitraAbadi\la_classified_temp.csv')
acila_df = pd.read_csv(r'c:\laragon\www\Ujicoba_RembulanCitraAbadi\acila_occupations_temp.csv')
dama_occ_df = pd.read_csv(r'c:\laragon\www\Ujicoba_RembulanCitraAbadi\dama_occupations_temp.csv')
today_str = date.today().strftime('%d %B %Y')

print(f"  Loaded {len(df)} company records")
print(f"  Loaded {len(acila_df)} ACILA occupation records")
print(f"  Loaded {len(dama_occ_df)} DAMA occupation records")

# ============================================================
# PREPARE DATAFRAMES FOR EACH SHEET
# ============================================================

# Sheet 2: All Companies (cleaned columns)
all_companies = df[[
    'companyname', 'latype', 'la_category', 'sector',
    'state_region', 'startdate', 'enddate', 'status', 'is_aged_care_related'
]].copy()
all_companies.columns = [
    'Company Name', 'LA Type', 'LA Category', 'Sector',
    'State / Region', 'Start Date', 'End Date', 'Status', 'Aged Care Related'
]
all_companies = all_companies.sort_values(['LA Category', 'Sector', 'Company Name'])

# Sheet 3: DAMA Companies
dama_companies = df[df['la_category'] == 'DAMA'][[
    'companyname', 'latype', 'sector', 'state_region',
    'startdate', 'enddate', 'status', 'is_aged_care_related'
]].copy()
dama_companies.columns = [
    'Company Name', 'DAMA Type', 'Sector', 'State / Region',
    'Start Date', 'End Date', 'Status', 'Aged Care Related'
]
dama_companies = dama_companies.sort_values(['DAMA Type', 'Sector', 'Company Name'])

# Sheet 4: Aged Care Companies
aged_care_df = df[df['is_aged_care_related'] == True][[
    'companyname', 'latype', 'la_category', 'sector',
    'state_region', 'startdate', 'enddate', 'status'
]].copy()
aged_care_df.columns = [
    'Company Name', 'LA Type', 'LA Category', 'Sector',
    'State / Region', 'Start Date', 'End Date', 'Status'
]
aged_care_df = aged_care_df.sort_values(['LA Category', 'State / Region', 'Company Name'])

# Summary stats
total = len(df)
total_active = len(df[df['status'] == 'Active'])
total_dama = len(df[df['la_category'] == 'DAMA'])
total_aged_care = len(df[df['is_aged_care_related'] == True])
total_acila = len(df[df['latype'] == 'Industry - Aged Care'])

sector_counts = df['sector'].value_counts().reset_index()
sector_counts.columns = ['Sector', 'Count']

la_type_counts = df['latype'].value_counts().reset_index()
la_type_counts.columns = ['LA Type', 'Count']

dama_aged_summary = df[df['is_aged_care_related'] == True].groupby('latype').size().reset_index(name='Count')
dama_aged_summary.columns = ['DAMA / LA Type', 'Aged Care Companies']
dama_aged_summary = dama_aged_summary.sort_values('Aged Care Companies', ascending=False)

# ============================================================
# EXCEL STYLES
# ============================================================
# Colors
DARK_BLUE    = "1F3864"
MED_BLUE     = "2E75B6"
LIGHT_BLUE   = "BDD7EE"
AGED_PURPLE  = "7030A0"
LIGHT_PURPLE = "E2CFEE"
DAMA_GREEN   = "375623"
LIGHT_GREEN  = "C6EFCE"
ORANGE       = "E26B0A"
LIGHT_ORANGE = "FCE4D6"
YELLOW_BG    = "FFFF00"
WHITE        = "FFFFFF"
DARK_GRAY    = "404040"
LIGHT_GRAY   = "F2F2F2"
RED          = "C00000"
ACTIVE_GRN   = "00B050"

def make_font(bold=False, color=WHITE, size=11):
    return Font(bold=bold, color=color, size=size, name='Calibri')

def make_fill(hex_color):
    return PatternFill("solid", fgColor=hex_color)

def make_border(thin=True):
    s = Side(style='thin' if thin else 'medium', color="AAAAAA")
    return Border(left=s, right=s, top=s, bottom=s)

def make_align(h='left', v='center', wrap=False):
    return Alignment(horizontal=h, vertical=v, wrap_text=wrap)

def style_header_row(ws, row, col_start, col_end, bg=MED_BLUE, fg=WHITE, size=11):
    for col in range(col_start, col_end + 1):
        cell = ws.cell(row=row, column=col)
        cell.font = make_font(bold=True, color=fg, size=size)
        cell.fill = make_fill(bg)
        cell.alignment = make_align('center', 'center', wrap=True)
        cell.border = make_border()

def auto_col_width(ws, min_w=12, max_w=50):
    for col in ws.columns:
        max_len = 0
        col_letter = get_column_letter(col[0].column)
        for cell in col:
            try:
                if cell.value:
                    max_len = max(max_len, len(str(cell.value)))
            except:
                pass
        ws.column_dimensions[col_letter].width = min(max(max_len + 2, min_w), max_w)

def write_table(ws, df_data, start_row, col_offset=1,
                header_bg=MED_BLUE, even_bg=LIGHT_BLUE, odd_bg=WHITE,
                zebra=True):
    """Write a DataFrame as a styled table, return next free row."""
    # Header
    for j, col_name in enumerate(df_data.columns):
        cell = ws.cell(row=start_row, column=col_offset + j, value=col_name)
        cell.font = make_font(bold=True, color=WHITE, size=10)
        cell.fill = make_fill(header_bg)
        cell.alignment = make_align('center', 'center', wrap=True)
        cell.border = make_border()

    # Rows
    for i, row_data in enumerate(df_data.itertuples(index=False), start=1):
        bg = even_bg if (i % 2 == 0 and zebra) else odd_bg
        for j, val in enumerate(row_data):
            cell = ws.cell(row=start_row + i, column=col_offset + j, value=val)
            cell.fill = make_fill(bg)
            cell.alignment = make_align('left', 'center', wrap=True)
            cell.border = make_border()
            cell.font = Font(name='Calibri', size=9, color=DARK_GRAY)

    return start_row + len(df_data) + 2  # next free row with 1 gap

# ============================================================
# CREATE WORKBOOK
# ============================================================
output_path = r'c:\laragon\www\Ujicoba_RembulanCitraAbadi\DAMA_Aged_Care_Research.xlsx'
writer = pd.ExcelWriter(output_path, engine='openpyxl')
writer.book  # Ensure workbook is initialized

# Write data to sheets first (pandas does the heavy lifting)
all_companies.to_excel(writer, sheet_name='All Companies', index=False, startrow=2)
dama_companies.to_excel(writer, sheet_name='DAMA Companies', index=False, startrow=2)
aged_care_df.to_excel(writer, sheet_name='Aged Care Companies', index=False, startrow=2)
acila_df.to_excel(writer, sheet_name='ACILA Occupation List', index=False, startrow=2)
dama_occ_df.to_excel(writer, sheet_name='DAMA Aged Care Occupations', index=False, startrow=2)

# Summary sheet (blank for now, will style with openpyxl)
pd.DataFrame().to_excel(writer, sheet_name='Summary Dashboard', index=False)

writer.close()

# ============================================================
# NOW STYLE WITH OPENPYXL
# ============================================================
wb = load_workbook(output_path)

# ==== SHEET 1: SUMMARY DASHBOARD ============================
print("  Styling: Summary Dashboard...")
ws_sum = wb['Summary Dashboard']
ws_sum.sheet_properties.tabColor = DARK_BLUE

# Title
ws_sum.merge_cells('A1:I1')
title_cell = ws_sum['A1']
title_cell.value = '🔍 DAMA Labour Agreement — Aged Care Research Dashboard'
title_cell.font = Font(name='Calibri', size=18, bold=True, color=WHITE)
title_cell.fill = make_fill(DARK_BLUE)
title_cell.alignment = make_align('center', 'center')
ws_sum.row_dimensions[1].height = 36

ws_sum.merge_cells('A2:I2')
sub = ws_sum['A2']
sub.value = f'Data as at 30 April 2026  |  Generated: {today_str}  |  Source: immi.homeaffairs.gov.au'
sub.font = Font(name='Calibri', size=10, italic=True, color="AAAAAA")
sub.fill = make_fill(DARK_GRAY)
sub.alignment = make_align('center', 'center')

# KPI Cards (Row 4-7)
kpis = [
    ('A', 'Total Labour\nAgreements', total, DARK_BLUE, WHITE),
    ('B', 'Active\nAgreements', total_active, ACTIVE_GRN, WHITE),
    ('C', 'Total DAMA\nCompanies', total_dama, MED_BLUE, WHITE),
    ('D', 'Aged Care\nRelated', total_aged_care, AGED_PURPLE, WHITE),
    ('E', 'Industry - Aged\nCare (ACILA)', total_acila, ORANGE, WHITE),
]

ws_sum.row_dimensions[4].height = 18
ws_sum.row_dimensions[5].height = 30
ws_sum.row_dimensions[6].height = 40
ws_sum.row_dimensions[7].height = 18

for col_letter, label, value, bg, fg in kpis:
    ws_sum.merge_cells(f'{col_letter}4:{col_letter}7')
    cell = ws_sum[f'{col_letter}4']
    cell.value = f'{label}\n\n{value:,}'
    cell.font = Font(name='Calibri', size=12, bold=True, color=fg)
    cell.fill = make_fill(bg)
    cell.alignment = Alignment(horizontal='center', vertical='center', wrap_text=True)

ws_sum.column_dimensions['A'].width = 20
ws_sum.column_dimensions['B'].width = 20
ws_sum.column_dimensions['C'].width = 20
ws_sum.column_dimensions['D'].width = 20
ws_sum.column_dimensions['E'].width = 20

# Sector breakdown table (col A, row 9)
ws_sum.merge_cells('A9:E9')
hdr = ws_sum['A9']
hdr.value = '📊 Labour Agreement by Sector'
hdr.font = Font(name='Calibri', size=13, bold=True, color=WHITE)
hdr.fill = make_fill(MED_BLUE)
hdr.alignment = make_align('left', 'center')
ws_sum.row_dimensions[9].height = 22

col_names = ['Sector', 'Count', '% Total']
sector_display = sector_counts.copy()
sector_display['% Total'] = (sector_display['Count'] / total * 100).round(1).astype(str) + '%'
write_table(ws_sum, sector_display, 10, col_offset=1,
            header_bg=MED_BLUE, even_bg=LIGHT_BLUE, odd_bg=WHITE)

# DAMA Aged Care summary table (col G, row 9)
ws_sum.column_dimensions['G'].width = 35
ws_sum.column_dimensions['H'].width = 15
ws_sum.merge_cells('G9:I9')
hdr2 = ws_sum['G9']
hdr2.value = '🏥 Aged Care Companies by LA/DAMA Type'
hdr2.font = Font(name='Calibri', size=13, bold=True, color=WHITE)
hdr2.fill = make_fill(AGED_PURPLE)
hdr2.alignment = make_align('left', 'center')
ws_sum.row_dimensions[9].height = 22

write_table(ws_sum, dama_aged_summary, 10, col_offset=7,
            header_bg=AGED_PURPLE, even_bg=LIGHT_PURPLE, odd_bg=WHITE)

# ==== SHEET 2: ALL COMPANIES ============================
print("  Styling: All Companies...")
ws_all = wb['All Companies']
ws_all.sheet_properties.tabColor = MED_BLUE

ws_all.merge_cells('A1:I1')
t = ws_all['A1']
t.value = f'All Labour Agreement Companies ({total:,} records) — as at 30 April 2026'
t.font = Font(name='Calibri', size=14, bold=True, color=WHITE)
t.fill = make_fill(MED_BLUE)
t.alignment = make_align('center', 'center')
ws_all.row_dimensions[1].height = 28

# Style header row (row 3 due to startrow=2)
style_header_row(ws_all, 3, 1, len(all_companies.columns), bg=MED_BLUE)

# Color rows by status and aged care flag
status_colors = {
    'Active': LIGHT_BLUE,
    'Expiring Soon (<6mo)': LIGHT_ORANGE,
    'Expired': 'FFCCCC',
}

for r_idx in range(4, 4 + len(all_companies)):
    status_val = ws_all.cell(row=r_idx, column=8).value
    aged_val = ws_all.cell(row=r_idx, column=9).value
    row_bg = status_colors.get(str(status_val), LIGHT_GRAY)
    if str(aged_val).lower() in ('true', '1', 'yes'):
        row_bg = LIGHT_PURPLE

    for c_idx in range(1, len(all_companies.columns) + 1):
        cell = ws_all.cell(row=r_idx, column=c_idx)
        cell.fill = make_fill(row_bg)
        cell.border = make_border()
        cell.font = Font(name='Calibri', size=9, color=DARK_GRAY)
        cell.alignment = make_align('left', 'center')

auto_col_width(ws_all, min_w=12, max_w=45)
ws_all.freeze_panes = 'A4'

# ==== SHEET 3: DAMA COMPANIES ============================
print("  Styling: DAMA Companies...")
ws_dama = wb['DAMA Companies']
ws_dama.sheet_properties.tabColor = DAMA_GREEN

ws_dama.merge_cells('A1:H1')
t = ws_dama['A1']
t.value = f'DAMA Companies ({total_dama:,} records) — All 13 DAMA Types'
t.font = Font(name='Calibri', size=14, bold=True, color=WHITE)
t.fill = make_fill(DAMA_GREEN)
t.alignment = make_align('center', 'center')
ws_dama.row_dimensions[1].height = 28

style_header_row(ws_dama, 3, 1, len(dama_companies.columns), bg=DAMA_GREEN)

for r_idx in range(4, 4 + len(dama_companies)):
    aged_val = ws_dama.cell(row=r_idx, column=8).value
    dama_type = str(ws_dama.cell(row=r_idx, column=2).value or '')
    row_bg = LIGHT_GREEN if (r_idx % 2 == 0) else WHITE
    if str(aged_val).lower() in ('true', '1', 'yes'):
        row_bg = LIGHT_PURPLE

    for c_idx in range(1, len(dama_companies.columns) + 1):
        cell = ws_dama.cell(row=r_idx, column=c_idx)
        cell.fill = make_fill(row_bg)
        cell.border = make_border()
        cell.font = Font(name='Calibri', size=9, color=DARK_GRAY)
        cell.alignment = make_align('left', 'center')

auto_col_width(ws_dama, min_w=14, max_w=45)
ws_dama.freeze_panes = 'A4'

# ==== SHEET 4: AGED CARE COMPANIES ============================
print("  Styling: Aged Care Companies...")
ws_ac = wb['Aged Care Companies']
ws_ac.sheet_properties.tabColor = AGED_PURPLE

ws_ac.merge_cells('A1:H1')
t = ws_ac['A1']
t.value = f'🏥 Aged Care Related Companies ({total_aged_care} total) — Industry ACILA + DAMA'
t.font = Font(name='Calibri', size=14, bold=True, color=WHITE)
t.fill = make_fill(AGED_PURPLE)
t.alignment = make_align('center', 'center')
ws_ac.row_dimensions[1].height = 28

style_header_row(ws_ac, 3, 1, len(aged_care_df.columns), bg=AGED_PURPLE)

la_cat_colors = {
    'Industry': LIGHT_PURPLE,
    'DAMA': LIGHT_GREEN,
    'Company Specific': LIGHT_ORANGE,
    'Skilled Refugee Pilot': LIGHT_BLUE,
}

for r_idx in range(4, 4 + len(aged_care_df)):
    la_cat = str(ws_ac.cell(row=r_idx, column=3).value or '')
    status_val = str(ws_ac.cell(row=r_idx, column=8).value or '')
    row_bg = la_cat_colors.get(la_cat, LIGHT_GRAY)
    if status_val == 'Expiring Soon (<6mo)':
        row_bg = LIGHT_ORANGE
    elif status_val == 'Expired':
        row_bg = 'FFCCCC'

    for c_idx in range(1, len(aged_care_df.columns) + 1):
        cell = ws_ac.cell(row=r_idx, column=c_idx)
        cell.fill = make_fill(row_bg)
        cell.border = make_border()
        cell.font = Font(name='Calibri', size=9, color=DARK_GRAY)
        cell.alignment = make_align('left', 'center')

auto_col_width(ws_ac, min_w=14, max_w=48)
ws_ac.freeze_panes = 'A4'

# ==== SHEET 5: ACILA OCCUPATION LIST ============================
print("  Styling: ACILA Occupation List...")
ws_occ = wb['ACILA Occupation List']
ws_occ.sheet_properties.tabColor = ORANGE

ws_occ.merge_cells('A1:G1')
t = ws_occ['A1']
t.value = '📋 Aged Care Industry Labour Agreement (ACILA) — Eligible Occupations'
t.font = Font(name='Calibri', size=14, bold=True, color=WHITE)
t.fill = make_fill(ORANGE)
t.alignment = make_align('center', 'center')
ws_occ.row_dimensions[1].height = 28

style_header_row(ws_occ, 3, 1, len(acila_df.columns), bg=ORANGE)

skill_colors = {'Skill Level 1': LIGHT_BLUE, 'Skill Level 2': LIGHT_GREEN, 'Skill Level 3': LIGHT_PURPLE}

for r_idx in range(4, 4 + len(acila_df)):
    skill_val = str(ws_occ.cell(row=r_idx, column=3).value or '')
    row_bg = skill_colors.get(skill_val, LIGHT_GRAY)
    for c_idx in range(1, len(acila_df.columns) + 1):
        cell = ws_occ.cell(row=r_idx, column=c_idx)
        cell.fill = make_fill(row_bg)
        cell.border = make_border()
        cell.font = Font(name='Calibri', size=9, color=DARK_GRAY)
        cell.alignment = make_align('left', 'center', wrap=True)

auto_col_width(ws_occ, min_w=16, max_w=55)

# Add legend
leg_row = 4 + len(acila_df) + 2
ws_occ.cell(row=leg_row, column=1).value = 'COLOUR LEGEND:'
ws_occ.cell(row=leg_row, column=1).font = Font(bold=True, size=10)
legends = [
    ('Skill Level 1 — Bachelor Degree + AHPRA Registration', LIGHT_BLUE),
    ('Skill Level 2 — Diploma / Advanced Diploma + Registration', LIGHT_GREEN),
    ('Skill Level 3 — Certificate III / IV', LIGHT_PURPLE),
]
for i, (lbl, bg) in enumerate(legends):
    c = ws_occ.cell(row=leg_row + 1 + i, column=1, value=lbl)
    c.fill = make_fill(bg)
    c.border = make_border()
    c.font = Font(size=9, italic=True)

# ==== SHEET 6: DAMA AGED CARE OCCUPATIONS ============================
print("  Styling: DAMA Aged Care Occupations...")
ws_dama_occ = wb['DAMA Aged Care Occupations']
ws_dama_occ.sheet_properties.tabColor = DAMA_GREEN

ws_dama_occ.merge_cells('A1:G1')
t = ws_dama_occ['A1']
t.value = '🗺️ DAMA Regions — Aged Care Occupations Available per Region'
t.font = Font(name='Calibri', size=14, bold=True, color=WHITE)
t.fill = make_fill(DAMA_GREEN)
t.alignment = make_align('center', 'center')
ws_dama_occ.row_dimensions[1].height = 28

style_header_row(ws_dama_occ, 3, 1, len(dama_occ_df.columns), bg=DAMA_GREEN)

dama_colors = {
    'Northern Territory DAMA': 'D9F0A3',
    'South Australia Regional DAMA': 'C6EFCE',
    'Far North Queensland DAMA': 'BDD7EE',
    'WA DAMA': 'DDEBF7',
    'South West DAMA': 'EAF4FB',
    'Orana DAMA': 'FFF2CC',
    'Townsville DAMA': 'FCE4D6',
    'Great South Coast DAMA': 'E2EFDA',
    'Goldfields DAMA': 'FFF2CC',
    'East Kimberley DAMA': 'EDEDED',
    'Goulburn Valley DAMA': 'F4E8F9',
}
for r_idx in range(4, 4 + len(dama_occ_df)):
    dama_name = str(ws_dama_occ.cell(row=r_idx, column=1).value or '')
    row_bg = dama_colors.get(dama_name, LIGHT_GRAY)
    for c_idx in range(1, len(dama_occ_df.columns) + 1):
        cell = ws_dama_occ.cell(row=r_idx, column=c_idx)
        cell.fill = make_fill(row_bg)
        cell.border = make_border()
        cell.font = Font(name='Calibri', size=9, color=DARK_GRAY)
        cell.alignment = make_align('left', 'center', wrap=True)

auto_col_width(ws_dama_occ, min_w=16, max_w=55)
ws_dama_occ.freeze_panes = 'A4'

# ============================================================
# REORDER SHEETS
# ============================================================
sheet_order = [
    'Summary Dashboard', 'Aged Care Companies', 'DAMA Companies',
    'All Companies', 'ACILA Occupation List', 'DAMA Aged Care Occupations'
]
for i, name in enumerate(sheet_order):
    if name in wb.sheetnames:
        wb.move_sheet(name, offset=(wb.sheetnames.index(name) - i) * -1)

# ============================================================
# SAVE
# ============================================================
wb.save(output_path)
print(f"\nExcel saved: {output_path}")
print(f"\nSUMMARY:")
print(f"  Total companies: {total:,}")
print(f"  Active agreements: {total_active:,}")
print(f"  DAMA companies: {total_dama:,}")
print(f"  Aged Care related: {total_aged_care:,}")
print(f"  Industry - Aged Care (ACILA): {total_acila:,}")
print(f"\nSheets in workbook: {wb.sheetnames}")
print("\n✅ PHASE 4 COMPLETE!")
