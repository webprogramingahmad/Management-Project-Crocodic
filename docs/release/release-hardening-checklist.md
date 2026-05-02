# Release Hardening Checklist (Tahap 9)

Checklist ini dipakai sebelum rilis agar alur utama stabil untuk role `executive`, `director`, dan `staff`.

---

## A. Persiapan UAT

- [ ] 1 akun uji `executive` siap login
- [ ] 1 akun uji `director` siap login
- [ ] 1 akun uji `staff` siap login
- [ ] Minimal 1 project aktif dengan director + staff terpasang
- [ ] Data status task tersedia (`To Do`, `In progress`, `Review`, `Revision`, `Complete`)
- [ ] Data status SDM tersedia (`Ready`, `Stand By`, `Not Ready`, `Absent`)

---

## B. UAT Role Executive (Monitor-only)

- [ ] Bisa login dan membuka dashboard tanpa error
- [ ] Tab kiri dashboard tampil: `Ready`, `Stand By`, `Not Ready`, `Complete`, `Absent`
- [ ] Tidak bisa drag/update status task (harus ditolak)
- [ ] Halaman task tetap bisa dimonitor (read-only behavior tetap benar)
- [ ] Halaman accounts tampil normal (search/filter/action view-delete)

Catatan hasil:
- Observasi: ...
- Isu: ...

---

## C. UAT Role Director

### C1. Task milik sendiri
- [ ] Bisa create task normal (non-standby)
- [ ] Bisa update status task sendiri sesuai rule
- [ ] `Review -> Complete` untuk task sendiri berjalan sesuai rule

### C2. Task staff dalam project milik director
- [ ] Bisa transfer task ke staff pada project miliknya
- [ ] Hanya bisa review decision saat task staff berstatus `Review`
- [ ] Bisa putuskan `Review -> Revision` dengan `revision_hours` valid
- [ ] Bisa putuskan `Review -> Complete`
- [ ] Tidak bisa review decision untuk task di luar scope project

Catatan hasil:
- Observasi: ...
- Isu: ...

---

## D. UAT Role Staff

- [ ] Bisa create task sendiri pada project yang diizinkan
- [ ] Bisa update task sendiri `To Do -> In progress -> Review`
- [ ] Bisa update `Revision -> Review` untuk task sendiri
- [ ] Tidak bisa ubah task milik user lain
- [ ] Tidak bisa ubah status ke `Complete`
- [ ] Tidak bisa create task pada project yang bukan scope-nya

Catatan hasil:
- Observasi: ...
- Isu: ...

---

## E. UAT Dashboard & Status SDM

- [ ] Empty state dashboard tampil dengan teks konsisten
- [ ] Subtitle card user konsisten (staff tampil division, non-staff tampil role)
- [ ] Perubahan task aktif mempengaruhi status SDM sesuai rule
- [ ] Prioritas `Absent` bekerja benar
- [ ] `Stand By` harian tidak bocor ke hari berikutnya

Catatan hasil:
- Observasi: ...
- Isu: ...

---

## F. Smoke Test Teknis

- [ ] Jalankan test feature utama:
  - `php artisan test tests/Feature/StatusSdm tests/Feature/Task`
- [ ] Semua test lulus
- [ ] Tidak ada error kritikal di log setelah smoke run

Output ringkas:
- Hasil test: ...
- Durasi: ...

---

## G. Operasional & Scheduler

- [ ] `php artisan schedule:list` menampilkan:
  - `sdm:reset-not-ready` (00:00)
  - `sdm:health-check --warn-threshold=90` (00:10)
- [ ] Jalankan manual:
  - `php artisan sdm:health-check`
- [ ] Ringkasan status SDM terlihat wajar (tidak anomali ekstrem)

Catatan hasil:
- Observasi: ...
- Isu: ...

---

## H. Observability / Logging

Sampling minimal sekali untuk tiap event:

- [ ] `task_create` tercatat
- [ ] `task_transfer` tercatat
- [ ] `task_update_status` tercatat
- [ ] `task_review_decision` tercatat
- [ ] Kasus reject/forbidden mencatat `reason` yang sesuai

Lokasi log:
- `storage/logs/laravel.log`

Catatan hasil:
- Observasi: ...
- Isu: ...

---

## I. UI Consistency Final Check

- [ ] Istilah user-facing konsisten pakai `Staff`
- [ ] Tombol modal create/transfer konsisten (`Cancel` + aksi utama)
- [ ] Action button alignment rapi (icon + text center)
- [ ] Tidak ada debug artifact di UI (contoh: `dd()` / dump)

Catatan hasil:
- Observasi: ...
- Isu: ...

---

## J. Sign-off Rilis

- [ ] Semua blocker = 0
- [ ] Semua critical issue = 0
- [ ] Semua high issue sudah ditangani / ada mitigasi
- [ ] Release note internal selesai
- [ ] Disetujui untuk rilis

Approval:
- QA/Tester: ...
- PIC Teknis: ...
- Tanggal: ...

