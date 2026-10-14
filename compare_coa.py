import subprocess

def get_coas(db):
    res = subprocess.run([r'C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe', '-u', 'root', db, '-e', 'SELECT kode_akun, nama_akun FROM chart_of_accounts;'], capture_output=True, text=True)
    lines = res.stdout.strip().splitlines()[1:]
    return {l.split('\t')[0]: l.split('\t')[1] for l in lines if '\t' in l}

c25 = get_coas('rembulancitraabadi_2025_db')
c26 = get_coas('rembulancitraabadi_2026_db')

print('Total 2025 accounts:', len(c25))
print('Total 2026 accounts:', len(c26))

diff_names = {}
for k in c25:
    if k in c26 and c25[k] != c26[k]:
        diff_names[k] = (c25[k], c26[k])

print(f'Different names for same code ({len(diff_names)}):')
for k, v in list(diff_names.items())[:20]:
    print(f'  {k}: 2025="{v[0]}" vs 2026="{v[1]}"')

only_25 = set(c25.keys()) - set(c26.keys())
only_26 = set(c26.keys()) - set(c25.keys())
print(f'Codes in 2025 only ({len(only_25)}):', list(only_25)[:10])
print(f'Codes in 2026 only ({len(only_26)}):', list(only_26)[:10])
