# Simulasi user login lalu hanya GET (dashboard + task board). Tidak POST/create/update data.
param(
    [string]$BaseUrl = "http://127.0.0.1:9000",
    [int]$VirtualUsers = 15, # 15, 30 , 50
    [int]$Iterations = 3, # 3, 3 , 2
    [string]$Email = "crocodic3@gmail.com",
    [string]$Password = "crocodic123"
)

Write-Host ""
Write-Host "=== Load test: login + GET dashboard/tasks (read-only) ===" -ForegroundColor Cyan
Write-Host "URL          : $BaseUrl"
Write-Host "Virtual users: $VirtualUsers"
Write-Host "Iterations   : $Iterations per user"
Write-Host "Total flows  : $($VirtualUsers * $Iterations)"
Write-Host ""

$jobs = 1..$VirtualUsers | ForEach-Object {
    $userIndex = $_
    Start-Job -Name "vu-$userIndex" -ScriptBlock {
        param($Base, $UserEmail, $UserPassword, $Loops)

        function Get-CsrfToken {
            param([string]$Html)
            if ($Html -match 'name="_token"\s+value="([^"]+)"') { return $Matches[1] }
            if ($Html -match 'name="_token"\s+content="([^"]+)"') { return $Matches[1] }
            throw "CSRF token tidak ditemukan."
        }

        $out = @()
        for ($n = 1; $n -le $Loops; $n++) {
            try {
                $session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
                $loginPage = Invoke-WebRequest -Uri "$Base/" -WebSession $session -UseBasicParsing
                $token = Get-CsrfToken -Html $loginPage.Content

                $null = Invoke-WebRequest -Uri "$Base/" -Method POST -WebSession $session -UseBasicParsing -Body @{
                    _token   = $token
                    email    = $UserEmail
                    password = $UserPassword
                }

                $sw = [System.Diagnostics.Stopwatch]::StartNew()
                $dashboard = Invoke-WebRequest -Uri "$Base/staff/dashboard" -WebSession $session -UseBasicParsing
                $dashboardMs = $sw.ElapsedMilliseconds

                $sw.Restart()
                $tasks = Invoke-WebRequest -Uri "$Base/staff/tasks" -WebSession $session -UseBasicParsing
                $tasksMs = $sw.ElapsedMilliseconds

                $out += [pscustomobject]@{
                    DashboardStatus = $dashboard.StatusCode
                    TasksStatus     = $tasks.StatusCode
                    DashboardMs     = $dashboardMs
                    TasksMs         = $tasksMs
                    TotalMs         = $dashboardMs + $tasksMs
                    Ok              = ($dashboard.StatusCode -eq 200 -and $tasks.StatusCode -eq 200)
                    Error           = $null
                }
            } catch {
                $out += [pscustomobject]@{
                    DashboardStatus = 0
                    TasksStatus     = 0
                    DashboardMs     = 0
                    TasksMs         = 0
                    TotalMs         = 0
                    Ok              = $false
                    Error           = $_.Exception.Message
                }
            }
        }

        return $out
    } -ArgumentList $BaseUrl, $Email, $Password, $Iterations
}

Wait-Job -Job $jobs | Out-Null
$results = $jobs | Receive-Job
$jobs | Remove-Job

$flat = @($results | ForEach-Object { $_ })
$success = @($flat | Where-Object { $_.Ok })
$failed = @($flat | Where-Object { -not $_.Ok })

Write-Host "Hasil:" -ForegroundColor Green
Write-Host ("  Berhasil : {0}/{1}" -f $success.Count, $flat.Count)
Write-Host ("  Gagal    : {0}" -f $failed.Count)

if ($success.Count -gt 0) {
    $avgDash = [math]::Round(($success | Measure-Object -Property DashboardMs -Average).Average, 0)
    $avgTasks = [math]::Round(($success | Measure-Object -Property TasksMs -Average).Average, 0)
    $avgTotal = [math]::Round(($success | Measure-Object -Property TotalMs -Average).Average, 0)
    $maxTotal = ($success | Measure-Object -Property TotalMs -Maximum).Maximum

    Write-Host ""
    Write-Host "Waktu respon (ms) - hanya GET setelah login:"
    Write-Host "  Dashboard rata-rata : $avgDash ms"
    Write-Host "  Tasks rata-rata     : $avgTasks ms"
    Write-Host "  Total rata-rata     : $avgTotal ms"
    Write-Host "  Total terlama       : $maxTotal ms"
}

if ($failed.Count -gt 0) {
    Write-Host ""
    Write-Host "Contoh error:" -ForegroundColor Yellow
    $failed | Select-Object -First 3 | ForEach-Object { Write-Host ("  - {0}" -f $_.Error) }
}

Write-Host ""
