# Diagram ERD (Mermaid)

## Untuk BAB IV — **satu diagram saja** (disarankan)

Relasi antar tabel detail bisa Anda letakkan di subbab berikutnya (perancangan tabel / relasi database).

| File | Output PNG |
|------|------------|
| `erd-laporan-tunggal-bab-iv.mmd` | `erd-laporan-tunggal-bab-iv.png` |

## Versi lama / cadangan (opsional)

- `erd-01-inti-proyek-dan-task.*` & `erd-02-sdm-admin-notifikasi.*` — dipisah agar tidak padat; boleh diabaikan jika laporan cuma pakai satu gambar di atas.
- `erd-sistem-manajemen-proyek.*` — satu lembar versi awal.

## Unduh / screenshot

1. Buka https://mermaid.live  
2. Paste isi `erd-sistem-manajemen-proyek.mmd`  
3. Menu **Actions → PNG/SVG**

## Render ke PNG (lokal)

```powershell
cd c:\xampp\htdocs\project-crocodic
npx --yes @mermaid-js/mermaid-cli -i docs/diagrams/erd-sistem-manajemen-proyek.mmd -o docs/diagrams/erd-sistem-manajemen-proyek.png -b white
```

## Catatan skema

- `users` punya dua FK ke `statussdms` (employment vs activity); digambarkan dua garis berlabel.
- `tasks.id_project` boleh NULL di migrasi terbaru (relasi opsional ke `projects`).
