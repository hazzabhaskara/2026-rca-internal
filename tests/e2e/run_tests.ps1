# PowerShell E2E Automation Wrapper for Neraca Departemen
param (
    [string]$env = "both",
    [string]$tier = "all",
    [switch]$verbose = $false
)

$phpPath = "C:\laragon\bin\php\php-7.4.33-Win32-vc15-x64\php.exe"
if (-not (Test-Path $phpPath)) {
    $phpPath = "php"
}

Write-Host "================================================================================" -ForegroundColor Cyan
Write-Host "  EXECUTING E2E TEST SUITE FOR NERACA DEPARTEMEN                                " -ForegroundColor Cyan
Write-Host "================================================================================" -ForegroundColor Cyan
Write-Host " Environment: $env"
Write-Host " Tier:        $tier"
Write-Host " PHP Path:    $phpPath"
Write-Host " Timestamp:   $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
Write-Host "================================================================================"

$scriptPath = Join-Path $PSScriptRoot "test_neraca_departemen.php"
$argsList = @($scriptPath, "--env=$env", "--tier=$tier")
if ($verbose) {
    $argsList += "--verbose"
}

& $phpPath $argsList
$exitCode = $LASTEXITCODE

if ($exitCode -eq 0) {
    Write-Host "`n[SUCCESS] All E2E tests completed successfully." -ForegroundColor Green
} else {
    Write-Host "`n[FAILED] One or more E2E tests failed (Exit Code: $exitCode)." -ForegroundColor Red
}

exit $exitCode
