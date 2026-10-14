import subprocess

sql = "INSERT INTO chart_of_accounts (id, kode_akun, nama_akun, tipe_akun, level_akun, is_income_tax, aktif, omit_zero_balance, allow_project_allocation) VALUES (1, '1000000_ROOT', 'ASSET', 'Aset', 'HEADER', 0, 1, 0, 0);"
res = subprocess.run([r'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe', '-u', 'root', 'rembulancitraabadi_2026_db', '-e', sql], capture_output=True, text=True)
print('Return code:', res.returncode)
print('Stdout:', res.stdout)
print('Stderr:', res.stderr)
