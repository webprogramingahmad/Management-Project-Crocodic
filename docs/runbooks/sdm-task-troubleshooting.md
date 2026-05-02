# Runbook SDM & Task Troubleshooting

Panduan cepat ketika status SDM atau status task terlihat tidak sesuai di dashboard.

## 1) Cek cepat status operasional SDM

Jalankan:

```bash
php artisan sdm:health-check
```

Yang perlu dicek:

- apakah ada total user operasional (`Total operational users with activity status`)
- apakah distribusi status masuk akal (`Ready`, `Stand By`, `Not Ready`, `Absent`)
- apakah muncul warning dominasi status (indikasi reset atau alur task perlu dicek)

## 2) Cek reset harian berjalan

Command reset:

```bash
php artisan sdm:reset-not-ready
```

Schedule harian:

- `00:00` -> `sdm:reset-not-ready`
- `00:10` -> `sdm:health-check --warn-threshold=90`

Jika hasil reset tidak sesuai, cek scheduler aktif:

```bash
php artisan schedule:list
```

## 3) Cek jejak aksi task dari log

Event utama yang sudah dilog:

- `task_create`
- `task_transfer`
- `task_update_status`
- `task_review_decision`

Context penting di log:

- `result` (`success` / `forbidden`)
- `actor_id`, `actor_role`
- `task_id`, `project_id`
- `from_status`, `to_status`
- `reason` (jika ditolak)

Lokasi log:

- `storage/logs/laravel.log`

Contoh filter (PowerShell):

```powershell
Select-String -Path "storage/logs/laravel.log" -Pattern "task_update_status|task_review_decision|task_create|task_transfer"
```

## 4) Cek rule akses per role (jika muncul 403)

- `executive`: monitor-only (tidak boleh update status)
- `director`:
  - bisa update task milik sendiri
  - untuk task staff: keputusan review via endpoint review decision
- `staff`:
  - hanya task milik sendiri
  - tidak boleh ke `Complete`
  - tidak boleh ubah task milik user lain

Jika terjadi 403, cocokkan `reason` di log dengan rule di atas.

## 5) Cek rule status SDM (jika tab kiri dashboard terasa janggal)

Prioritas utama:

1. `Absent` (administration approved aktif hari ini)
2. `Ready` (punya task aktif kelas `todo/progress/review/revision`, non-standby)
3. `Stand By` harian (punya task difficulty `Stand By` yang dibuat hari ini)
4. `Stand By` karena semua task kerja non-standby sudah `complete` di hari berjalan
5. default `Not Ready`

## 6) Verifikasi database minimal (query cepat)

Gunakan Tinker:

```bash
php artisan tinker
```

Contoh cek user tertentu:

```php
$u = \App\Models\User::with(['role','activityStatussdm'])->find('USER_ID');
$u?->role?->role;
$u?->activityStatussdm?->status_sdm;
```

Contoh cek task aktif user:

```php
\App\Models\Task::with(['status','difficulty'])
    ->where('id_user', 'USER_ID')
    ->get(['id','id_status','id_difficulty','created_at']);
```

## 7) Setelah perbaikan, wajib re-check

- jalankan `php artisan sdm:health-check`
- uji 1 skenario manual di UI (create/update/review task)
- pastikan log event keluar dengan `result=success`

