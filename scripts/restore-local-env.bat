@echo off
REM Kembalikan .env ke mode development lokal setelah uji LAN selesai.
cd /d "%~dp0.."

powershell -NoProfile -Command "(Get-Content .env) -replace 'APP_DEBUG=false', 'APP_DEBUG=true' -replace 'APP_URL=http://192.168.1.6:9000', 'APP_URL=http://127.0.0.1:9000' | Set-Content .env"

php artisan config:clear
php artisan cache:clear

echo.
echo .env dikembalikan ke http://127.0.0.1:9000 (APP_DEBUG=true)
pause
