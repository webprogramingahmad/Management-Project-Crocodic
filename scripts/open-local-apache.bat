@echo off
REM Jalankan Laravel via Apache XAMPP (port 9000), bukan "php artisan serve".
REM Pastikan Apache + MySQL sudah Start di XAMPP Control Panel.

echo.
echo [1/2] Cek konfigurasi Apache...
C:\xampp\apache\bin\httpd.exe -t
if errorlevel 1 (
    echo Gagal: konfigurasi Apache tidak valid.
    pause
    exit /b 1
)

echo.
echo [2/2] Buka http://localhost:9000
echo.
echo Catatan:
echo - Hentikan "php artisan serve" jika masih berjalan (port 9000 bentrok).
echo - Port 80 / project lain di htdocs tidak diubah.
echo.
start http://localhost:9000
