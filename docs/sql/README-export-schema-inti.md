# Export struktur tabel inti ke database MySQL baru (untuk diagram / laporan)

Gunakan ini jika Anda ingin database **kosong** dengan **hanya struktur** tabel yang relevan, supaya phpMyAdmin Designer tidak penuh tabel lain.

> **Penting:** Ini **bukan** pengganti database aplikasi. Database Laravel tetap dipakai untuk development; database baru ini **opsional** untuk dokumentasi atau latihan layout diagram.

## Prasyarat

- MySQL/XAMPP berjalan.
- Anda tahu **nama database** aplikasi Anda (cek `.env`: `DB_DATABASE`).
- Path `mysqldump` dan `mysql` biasanya di XAMPP:

  `C:\xampp\mysql\bin\mysqldump.exe`  
  `C:\xampp\mysql\bin\mysql.exe`

## Tabel yang ikut (supaya foreign key tidak putus)

Urutan tidak penting untuk `mysqldump`; yang penting **semua induk FK ikut**.

**Inti laporan:** `users`, `projects`, `project_user`, `tasks`, `task_revision_cycles`, `administrations`, `notification_reads`

**Master yang direferensi kolom di atas:**  
`roles`, `divisions`, `statussdms`, `last_graduates`,  
`status_projects`, `project_difficulties`,  
`status_tasks`, `task_difficulties`,  
`category_administrations`, `status_administrations`

*(Jika Anda benar-benar ingin **sangat minimal**, Anda bisa export hanya tabel inti — tetapi harus men-drop FK di phpMyAdmin atau membuat DB tanpa constraint; cara di atas lebih aman.)*

## Langkah (PowerShell)

Ganti `nama_database_project_anda` dengan nilai `DB_DATABASE` di file `.env`.

Anda akan diminta password MySQL **dua kali** (dump + import).

```powershell
$MYSQL = "C:\xampp\mysql\bin"
$DB_ASLI = "nama_database_project_anda"

# 1) Buat database baru (misalnya nama ini — bisa diubah)
& "$MYSQL\mysql.exe" -u root -p -e "CREATE DATABASE IF NOT EXISTS ta_pmis_struktur_inti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2) Salin STRUKTUR saja (--no-data) ke database baru
& "$MYSQL\mysqldump.exe" -u root -p --no-data --single-transaction $DB_ASLI `
  roles divisions statussdms last_graduates users `
  status_projects project_difficulties projects project_user `
  status_tasks task_difficulties tasks task_revision_cycles `
  category_administrations status_administrations administrations `
  notification_reads `
| & "$MYSQL\mysql.exe" -u root -p ta_pmis_struktur_inti
```

Setelah itu buka phpMyAdmin → database **`ta_pmis_struktur_inti`** → **Designer**: isinya hanya tabel di atas (tanpa data), lebih mudah dibaca.

**Catatan:** Jika nama tabel di project Anda berbeda atau ada tabel tambahan yang direferensi FK, sesuaikan daftar di `mysqldump`.

## Alternatif tanpa database baru

Untuk laporan TA, diagram relasi bisa dari:

- File Mermaid di `docs/diagrams/` (sudah ada), atau  
- Gambar ERD manual — **tidak wajib** punya database kedua.
