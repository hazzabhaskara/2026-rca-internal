import os, subprocess

base = r'D:\Hazza\REMBULAN CITRA ABADI\2026\2026-RCA-INTERNAL\web-internal'
phpunit = [r'C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe', 'vendor/bin/phpunit']

test_files = [
    'tests/Feature/AssignedWatchdogTier5AdversarialTest.php',
    'tests/Feature/BukuBesarDepartmentFilterTest.php',
    'tests/Feature/BukuBesarDynamicNavigationTest.php',
    'tests/Feature/ChallengerAdversarialTest.php',
    'tests/Feature/E2ER2AccountingTest.php',
    'tests/Feature/EloquentIsolationTest.php',
    'tests/Feature/ExampleTest.php',
    'tests/Feature/JournalEntryEditDepartmentTest.php',
    'tests/Feature/NavigationAndRbacTest.php',
    'tests/Feature/NeracaDepartemenTest.php',
    'tests/Feature/RekonsiliasiClosingTest.php',
    'tests/Feature/Tier1FeatureCoverageTest.php',
    'tests/Feature/Tier2BoundaryCornerCaseTest.php',
    'tests/Feature/Tier3CrossFeatureCombinationTest.php',
    'tests/Feature/Tier4RealWorldScenarioTest.php',
    'tests/Feature/UnassignedWatchdogTest.php',
    'tests/Unit/AnomalyDetectionEngineTest.php',
    'tests/Unit/DepartmentAssignmentServiceTest.php',
    'tests/Unit/ExampleTest.php',
]

for tf in test_files:
    res = subprocess.run(phpunit + [tf], cwd=base, capture_output=True, text=True, timeout=30)
    status = 'PASS' if res.returncode == 0 else 'FAIL'
    summary = [line for line in res.stdout.splitlines() if 'OK (' in line or 'ERRORS!' in line or 'FAILURES!' in line]
    sum_text = summary[0] if summary else ('Exit ' + str(res.returncode))
    print(f'{status:4s} | {tf} | {sum_text}')
