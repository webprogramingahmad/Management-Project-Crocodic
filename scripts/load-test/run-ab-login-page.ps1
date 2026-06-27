# Read-only load test: halaman login (GET saja, tidak mengubah data).
param(
    [string]$BaseUrl = "http://127.0.0.1:9000/",
    [int]$TotalRequests = 300, # 300, 600 , 1000
    [int]$Concurrency = 15 # 15, 30 , 50
)

$ab = "C:\xampp\apache\bin\ab.exe"
if (-not (Test-Path $ab)) {
    Write-Error "Apache Bench tidak ditemukan di $ab"
    exit 1
}

Write-Host ""
Write-Host "=== Load test: halaman login (GET, read-only) ===" -ForegroundColor Cyan
Write-Host "URL         : $BaseUrl"
Write-Host "Requests    : $TotalRequests"
Write-Host "Concurrency : $Concurrency (simulasi user bersamaan)"
Write-Host ""

& $ab -n $TotalRequests -c $Concurrency -q $BaseUrl
