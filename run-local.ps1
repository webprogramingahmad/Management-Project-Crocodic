param(
    [switch]$Dev,
    [switch]$NoBuild,
    [switch]$Serve,
    [int]$Port = 9000
)

$ErrorActionPreference = "Stop"
$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path

Set-Location $projectRoot

function Step([string]$Message) {
    Write-Host ""
    Write-Host "==> $Message" -ForegroundColor Cyan
}

function Ensure-Command([string]$Name) {
    if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
        throw "Command '$Name' is not available in PATH."
    }
}

function Test-MySqlConnection {
    $mysqlExe = "C:\xampp\mysql\bin\mysql.exe"
    if (-not (Test-Path $mysqlExe)) {
        throw "MySQL client not found at $mysqlExe."
    }

    & $mysqlExe -u root -e "SELECT 1;" *> $null
    if ($LASTEXITCODE -ne 0) {
        throw "MySQL is not reachable. Start MySQL from XAMPP first."
    }
}

Step "Checking dependencies"
Ensure-Command "php"
Ensure-Command "composer"
Ensure-Command "npm"

if (-not (Test-Path ".\vendor\autoload.php")) {
    Step "Installing PHP dependencies (composer install)"
    composer install
}

if (-not (Test-Path ".\node_modules")) {
    Step "Installing JS dependencies (npm install)"
    npm install
}

Step "Checking MySQL connection"
Test-MySqlConnection

Step "Preparing Laravel app"
if (-not (Test-Path ".\.env")) {
    if (Test-Path ".\.env.example") {
        Copy-Item ".\.env.example" ".\.env"
    } else {
        throw ".env not found and .env.example is unavailable."
    }
}

php artisan key:generate --ansi
php artisan optimize:clear
php artisan migrate --force

if (-not $Dev) {
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
}

if ($Dev) {
    Step "Starting Vite dev server"
    Start-Process -FilePath "npm" -ArgumentList "run dev" -WorkingDirectory $projectRoot
    Write-Host "Vite dev server started in new process." -ForegroundColor Green
} elseif (-not $NoBuild) {
    Step "Building frontend assets"
    npm run build
}

Step "Starting Laravel server"
if ($Serve) {
    Write-Host "Open: http://127.0.0.1:$Port" -ForegroundColor Yellow
    php artisan serve --host=127.0.0.1 --port=$Port

    if ($LASTEXITCODE -ne 0 -and $Port -eq 9000) {
        $fallback = 9001
        Write-Host "Port 9000 busy. Retrying on $fallback..." -ForegroundColor Yellow
        Write-Host "Open: http://127.0.0.1:$fallback" -ForegroundColor Yellow
        php artisan serve --host=127.0.0.1 --port=$fallback
    }
} else {
    Write-Host "Setup complete." -ForegroundColor Green
    Write-Host "Run server manually:" -ForegroundColor Yellow
    Write-Host "php artisan serve --host=127.0.0.1 --port=$Port" -ForegroundColor Yellow
}

