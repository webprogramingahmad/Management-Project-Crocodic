@echo off
REM Buka port 9000 agar perangkat lain di Wi-Fi/LAN bisa akses aplikasi.
REM Jalankan file ini: klik kanan -> Run as administrator

echo.
echo Menambahkan aturan firewall untuk port TCP 9000...
netsh advfirewall firewall delete rule name="Laravel Crocodic 9000" >nul 2>&1
netsh advfirewall firewall add rule name="Laravel Crocodic 9000" dir=in action=allow protocol=TCP localport=9000 profile=private,domain

if errorlevel 1 (
    echo.
    echo Gagal. Pastikan Anda menjalankan sebagai Administrator.
    pause
    exit /b 1
)

echo.
echo Berhasil. Port 9000 terbuka untuk jaringan Private/Domain.
echo User lain bisa akses: http://192.168.1.6:9000
echo.
pause
