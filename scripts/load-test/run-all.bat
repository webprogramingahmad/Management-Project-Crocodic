@echo off
REM Load test aman (read-only). Tidak mengubah kode aplikasi atau database.
cd /d "%~dp0"

echo.
echo [1/2] Tes halaman login (GET) - 15 user virtual, 300 request...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0run-ab-login-page.ps1" -TotalRequests 300 -Concurrency 15
if errorlevel 1 exit /b 1

echo.
echo [2/2] Tes login + dashboard/tasks (GET saja) - 15 user x 3 iterasi...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0run-readonly-authenticated.ps1" -VirtualUsers 15 -Iterations 3

echo.
echo Selesai. Lihat ringkasan di atas.
pause
