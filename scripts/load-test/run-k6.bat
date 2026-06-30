@echo off
REM Load test k6 (read-only). Tidak mengubah kode aplikasi.
setlocal EnableDelayedExpansion

set "K6=k6"
where k6 >nul 2>&1
if errorlevel 1 (
    if exist "C:\Program Files\k6\k6.exe" (
        set "K6=C:\Program Files\k6\k6.exe"
    ) else (
        echo k6 tidak ditemukan. Restart terminal setelah install, atau tambahkan ke PATH.
        exit /b 1
    )
)

cd /d "%~dp0"

if /I "%~1"=="help" goto :help
if /I "%~1"=="/?" goto :help
if "%~1"=="" goto :help

set "SCRIPT=%~1"
set "VUS=%~2"
set "DURATION=%~3"
set "BASE_URL=%~4"
set "EMAIL=%~5"
set "PASSWORD=%~6"

if "%VUS%"=="" set "VUS=15"
if "%DURATION%"=="" set "DURATION=1m"
if "%BASE_URL%"=="" set "BASE_URL=http://127.0.0.1:9000"

if /I "%SCRIPT%"=="login" (
    set "K6_SCRIPT=k6\login-page.js"
    echo [k6] Halaman login - GET only
) else if /I "%SCRIPT%"=="staff" (
    set "K6_SCRIPT=k6\staff-readonly.js"
    echo [k6] Login staff + GET dashboard/tasks - read-only ^(1 akun^)
) else if /I "%SCRIPT%"=="staff-multi" (
    set "K6_SCRIPT=k6\staff-readonly-multi.js"
    echo [k6] Login staff multi-akun + GET dashboard/tasks - read-only
    echo Export akun staff dari database...
    call php "%~dp0export-staff-accounts.php" --limit=%VUS%
    if errorlevel 1 (
        echo Gagal export akun staff. Pastikan MySQL aktif dan ada user staff.
        exit /b 1
    )
) else (
    goto :help
)

echo VUS=%VUS%  DURATION=%DURATION%  BASE_URL=%BASE_URL%
if not "%EMAIL%"=="" echo EMAIL=%EMAIL%
echo.

set "ENV_ARGS=--env VUS=%VUS% --env DURATION=%DURATION% --env BASE_URL=%BASE_URL%"
if /I "%SCRIPT%"=="staff-multi" set "ENV_ARGS=!ENV_ARGS! --env ACCOUNTS_FILE=data/staff-accounts.json"
if not "%EMAIL%"=="" set "ENV_ARGS=!ENV_ARGS! --env EMAIL=%EMAIL%"
if not "%PASSWORD%"=="" set "ENV_ARGS=!ENV_ARGS! --env PASSWORD=%PASSWORD%"

echo.
echo Menjalankan k6 ^(tunggu ~2-3 menit untuk 50 VU^)...
echo.

"%K6%" run !ENV_ARGS! "%K6_SCRIPT%"
set "K6_EXIT=%ERRORLEVEL%"
if not "%K6_EXIT%"=="0" (
    echo.
    echo k6 gagal. Exit code: %K6_EXIT%
    echo Periksa: Apache/MySQL/Memurai aktif, password staff = password
)
exit /b %K6_EXIT%

:help
echo.
echo Penggunaan:
echo   run-k6.bat staff [VUS] [DURATION] [BASE_URL] [EMAIL] [PASSWORD]
echo   run-k6.bat staff-multi [VUS] [DURATION] [BASE_URL]
echo   run-k6.bat login [VUS] [DURATION] [BASE_URL]
echo.
echo Contoh:
echo   run-k6.bat staff 15 1m
echo   run-k6.bat staff-multi 50 2m
echo   run-k6.bat staff-multi 50 2m http://192.168.1.6:9000
echo   run-k6.bat staff 50 2m http://127.0.0.1:9000
echo   run-k6.bat login 30 1m
echo.
echo staff-multi = tiap VU pakai akun staff berbeda ^(export dari DB, password default: password^)
echo crocodic3@gmail.com otomatis pakai crocodic123 jika ada di DB.
echo.
exit /b 0
