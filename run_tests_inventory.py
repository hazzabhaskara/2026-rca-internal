import os, subprocess, sys

base = r'D:\Hazza\REMBULAN CITRA ABADI\2026\2026-RCA-INTERNAL\web-internal'
phpunit = [r'C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe', 'vendor/bin/phpunit']

test_files = []
for root, dirs, files in os.walk(os.path.join(base, 'tests')):
    for f in sorted(files):
        if f.endswith('Test.php'):
            rel = os.path.relpath(os.path.join(root, f), base)
            test_files.append(rel)

print(f'=== Testing {len(test_files)} test files ===', flush=True)

results = {}
for tf in test_files:
    print(f'Running {tf}...', end=' ', flush=True)
    try:
        res = subprocess.run(phpunit + [tf], cwd=base, capture_output=True, text=True, timeout=120)
        status = 'PASS' if res.returncode == 0 else 'FAIL'
        summary = [line for line in res.stdout.splitlines() if 'OK (' in line or 'ERRORS!' in line or 'FAILURES!' in line]
        sum_text = summary[0] if summary else ('Exit ' + str(res.returncode))
        print(f'{status} ({sum_text})', flush=True)
        results[tf] = (status, sum_text, res.stdout, res.stderr)
    except subprocess.TimeoutExpired:
        print('TIMEOUT (>120s)', flush=True)
        results[tf] = ('TIMEOUT', 'Timeout >120s', '', '')

print('\n=== SUMMARY ===', flush=True)
for tf, (status, sum_text, _, _) in results.items():
    print(f'{status:7s} | {tf} | {sum_text}', flush=True)
