@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@section('css')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="{{ asset('build/css/main/dashboard.css') }}">
@endsection

@php
    $role = Auth::user()->role->role;
@endphp

@section('content')

<head>
<style>
    .btn-custom {
    background-color: #ffffff;      /* default putih */
    color: #000000;                 /* teks hitam */
    border: 1px solid #E0E0E0CE;      /* garis hitam */
    border-radius: 7px;            /* sudut membulat */
    font-weight: 500;           /* tebal sedang */
    transition: all 0.25s ease-in-out;      /* transisi halus */
    min-width: 100px;        /* lebar minimum 100px */
    min-height: 36px;
    padding: 0.375rem 0.9rem;
    line-height: 1.2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Hover */
.btn-custom:hover {
    background-color: #000000;      /* hitam */
    color: #ffffff;                 /* teks putih */
}

/* Active (terpilih) */
.btn-custom.active,
.btn-custom:active,
.btn-custom:focus {
    background-color: #000000; 
    color: #ffffff;
}

.card-title {
    font-size: 0.9rem;      /* ukuran nama */
    font-weight: 600;
    line-height: 1.2;
    color: #000;
}

.card-subtitle {
    font-size: 0.75rem;     /* ukuran divisi */
    font-weight: 400;
    color: #7D7D7D;
    line-height: 1.2;
}

.project-working {
    font-size: 1rem;     /* ukuran teks "Working on" */
    font-weight: 370;
    line-height: 1;
    color: #515151;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.project-name {
    font-size: 1rem;      /* ukuran nama project */
    font-weight: 390;       /* lebih tebal */
    color: #000;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.task-name {
    font-size: 1rem;
    font-weight: 400;
    color: #696969; /* abu gelap */
    line-height: 1.3;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Kategori Absent */
.card-text-strong {
    font-weight: 600;      /* tebal */
    color: #000000;        /* hitam */
    font-size: 0.9rem;
    margin-bottom: 2px;    /* jarak RAPAT */
    line-height: 1.25;
}

/* Deskripsi Absent */
.card-text-black {
    font-weight: 400;
    color: #6B7280;        /* abu-abu terang */
    font-size: 0.85rem;
    margin-bottom: 0;     /* hentikan jarak default <p> */
    line-height: 1.3;
}

/* Kartu task tab Ready (admin) & kartu project tab Project (user/director): isi di atas, kapsul kiri bawah */
.ready-task-card .card-body {
    min-height: 0;
    padding-bottom: 0.65rem !important;
}

.status-user-card .card-body {
    min-height: 0;
    padding-top: 0.75rem !important;
    padding-bottom: 0.75rem !important;
}

.status-user-card .card-body > .d-flex.align-items-center {
    margin: 0 !important;
}

/* Stand by / Not ready: nama user lebih tajam (Inter + bobot + kontras; hindari kesan "buram" dari zoom html + sans default) */
.status-user-card .card-title {
    font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
    font-size: 0.9375rem;
    font-weight: 700;
    color: #111827;
    line-height: 1.3;
}

.status-user-card .card-subtitle {
    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
    font-weight: 500;
    color: #57534e;
}

.ready-task-badges {
    flex-shrink: 0;
    width: fit-content;
    max-width: 100%;
}

/* Kapsul status/level task: pakai .task-meta-pill (global app.css), selaras permission */

/*
 * Kolom notifikasi (task / project / permission): kapsul lebih kompak & lebih membulat
 * (menimpa min-width 5rem dari app.css hanya di sini).
 */
.dashboard-notify-card span.task-meta-pill.btn {
    min-width: 0 !important;
    width: auto !important;
    max-width: 100%;
    padding: 0.12rem 0.5rem !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    line-height: 1.3 !important;
    border-radius: 9999px !important;
    text-align: center;
}

/* Notifikasi Task & Project: judul baris pertama + baris kedua rapat; ruang bawah sebelum kapsul */
.dashboard-notify-card .dashboard-project-notify-title {
    margin-bottom: 0.125rem !important;
    line-height: 1.3 !important;
}

.dashboard-notify-card .dashboard-project-notify-desc {
    margin-top: 0 !important;
    margin-bottom: 0 !important;
    line-height: 1.35 !important;
}

.dashboard-notify-card .dashboard-project-notify-top {
    margin-bottom: 0.75rem !important;
}

/* Samakan jarak atas-bawah konten card di semua tab kiri dashboard */
.dashboard-left-tab-panel .card.shadow-sm.h-100 .card-body {
    display: flex;
    flex-direction: column;
    min-height: 100%;
}

.dashboard-left-tab-panel .card.shadow-sm.h-100 .card-body > .d-flex.align-items-center {
    margin-bottom: 0.5rem !important;
    flex-shrink: 0;
}

.dashboard-left-tab-panel .card.shadow-sm.h-100 .card-body .card-text-strong {
    margin: 0 0 0.125rem 0 !important;
}

.dashboard-left-tab-panel .card.shadow-sm.h-100 .card-body .card-text,
.dashboard-left-tab-panel .card.shadow-sm.h-100 .card-body .card-text-black {
    margin: 0 !important;
    padding-bottom: 0.5rem;
}

.dashboard-left-tab-panel .card.shadow-sm.h-100 .card-body > .d-flex.gap-2 {
    margin-top: auto;
    padding-top: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}

/* Avatar stack: urut dari kanan dan saling overlap tipis */
.project-avatars {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    padding-left: 0;
    margin-left: 0;
}

.project-avatars .avatar-stack {
    margin-right: 0;
    border: 2px solid #fff;
}

.project-avatars .avatar-stack + .avatar-stack {
    margin-top: -13px; /* overlap vertikal ~40% dari 32px */
}

.project-avatars .avatar-stack:first-child {
    margin-right: 0;
}

.project-desc-clamp {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.project-badge-wrap {
    margin-top: -4px;
}

/* Elegant dark mode (referensi): hanya warna â€” tanpa mengubah dimensi/layout */
html[data-theme="dark"] .dashboard-elegant-dark .dashboard-left-card {
    background-color: #2d2d32 !important;
    border-color: #3f3f46 !important;
}

html[data-theme="dark"] .dashboard-elegant-dark .card.shadow-sm.h-100 {
    background-color: #36363c !important;
    border-color: #404048 !important;
    box-shadow: none !important;
}

html[data-theme="dark"] .dashboard-elegant-dark .btn-custom {
    background-color: rgba(255, 255, 255, 0.06);
    color: #e4e4e7;
    border-color: #52525b;
}

html[data-theme="dark"] .dashboard-elegant-dark .btn-custom:hover {
    background-color: rgba(255, 255, 255, 0.12);
    color: #fafafa;
    border-color: #71717a;
}

html[data-theme="dark"] .dashboard-elegant-dark .btn-custom.active,
html[data-theme="dark"] .dashboard-elegant-dark .btn-custom:active,
html[data-theme="dark"] .dashboard-elegant-dark .btn-custom:focus {
    background-color: #ffffff;
    color: #000000;
    border-color: #ffffff;
}

html[data-theme="dark"] .dashboard-elegant-dark .card-title {
    color: #f4f4f5 !important;
}

html[data-theme="dark"] .dashboard-elegant-dark .card-subtitle {
    color: #a1a1aa !important;
}

html[data-theme="dark"] .dashboard-elegant-dark .status-user-card .card-title {
    color: #fafafa !important;
}

html[data-theme="dark"] .dashboard-elegant-dark .status-user-card .card-subtitle {
    color: #d4d4d8 !important;
}

html[data-theme="dark"] .dashboard-elegant-dark .project-working {
    color: #a1a1aa !important;
}

html[data-theme="dark"] .dashboard-elegant-dark .project-name {
    color: #f4f4f5 !important;
}

html[data-theme="dark"] .dashboard-elegant-dark .task-name {
    color: #d4d4d8 !important;
}

html[data-theme="dark"] .dashboard-elegant-dark .card-text-strong {
    color: #f4f4f5 !important;
}

html[data-theme="dark"] .dashboard-elegant-dark .card-text-black {
    color: #a1a1aa !important;
}

html[data-theme="dark"] .dashboard-elegant-dark .card-text {
    color: #d4d4d8 !important;
}

html[data-theme="dark"] .dashboard-elegant-dark .empty-state .empty-text {
    color: #a1a1aa !important;
}

html[data-theme="dark"] .dashboard-elegant-dark .empty-state .bi,
html[data-theme="dark"] .dashboard-elegant-dark .empty-state .fa-solid,
html[data-theme="dark"] .dashboard-elegant-dark .empty-state .fas {
    color: #71717a !important;
}

/* Panel notifikasi (tema light): judul & empty putih (admin: biru #2E8EB5; user/director: hijau/orange) */
.dashboard-elegant-dark .dash-notify-heading,
.dashboard-elegant-dark .dash-notify-heading i,
.dashboard-elegant-dark .dash-notify-heading h2 {
    color: #ffffff !important;
}

.dashboard-elegant-dark .dash-notify-empty {
    color: #ffffff !important;
}

/* Panel notifikasi (tema dark): judul & empty di atas panel berwarna — tetap terang */
html[data-theme="dark"] .dashboard-elegant-dark .dash-notify-heading,
html[data-theme="dark"] .dashboard-elegant-dark .dash-notify-heading i,
html[data-theme="dark"] .dashboard-elegant-dark .dash-notify-heading h2 {
    color: #ffffff !important;
}

html[data-theme="dark"] .dashboard-elegant-dark .dash-notify-empty {
    color: rgba(255, 255, 255, 0.92) !important;
}

/* Kiri: kartu penuh viewport (lg+); kapsul tab diam; hanya isi tab yang scroll */
.dashboard-page {
    width: 100%;
    min-height: 0;
}

@media (min-width: 992px) {
    .dashboard-page {
        height: calc(var(--app-vh) - var(--app-header-height) - 2rem);
        max-height: calc(var(--app-vh) - var(--app-header-height) - 2rem);
        min-height: 240px;
        overflow: hidden;
        align-items: stretch;
    }
}

@supports (height: 100dvh) {
    @media (min-width: 992px) {
        .dashboard-page {
            height: calc(var(--app-dvh) - var(--app-header-height) - 2rem);
            max-height: calc(var(--app-dvh) - var(--app-header-height) - 2rem);
        }
    }
}

.dashboard-left-col {
    display: flex;
    flex-direction: column;
    min-height: 0;
}

@media (min-width: 992px) {
    .dashboard-left-col {
        height: 100%;
        max-height: 100%;
    }
}

.dashboard-left-card {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
    overflow: hidden;
    border-radius: 15px;
    border-color: #E0E0E0CE;
}

@media (min-width: 992px) {
    .dashboard-left-card {
        height: 100%;
    }
}

.dashboard-left-card-body {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
    overflow: hidden;
}

/* Tab rata kiri; jarak antar kapsul saja */
.dashboard-left-tab-btns {
    flex-shrink: 0;
    width: 100%;
    box-sizing: border-box;
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-items: center;
    justify-content: flex-start;
    column-gap: 1.5rem;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.dashboard-left-tab-btns::-webkit-scrollbar {
    display: none;
    width: 0;
    height: 0;
}

.dashboard-left-tab-btns .btn-custom {
    flex: 0 0 auto;
    white-space: nowrap;
}

.dashboard-left-tab-panels {
    flex: 1 1 auto;
    min-height: 0;
    position: relative;
    overflow: hidden;
}

/* Layar kecil: area scroll tetap terbatas agar kapsul tidak ikut bergulir */
@media (max-width: 991.98px) {
    .dashboard-left-tab-panels {
        max-height: min(65vh, 560px);
    }
}

.tab-container.dashboard-left-tab-panel {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
    box-sizing: border-box;
}

/*
 * Grid kartu isi tab (user/director): jarak antar kartu sama (gap), tepi rapi.
 * Tab admin Ready/Complete/Absent & Stand by/Not ready punya aturan grid tersendiri di bawah.
 */
.tab-container.dashboard-left-tab-panel > .row.g-3 {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 1rem;
    margin-left: 0;
    margin-right: 0;
    --bs-gutter-x: 0;
    --bs-gutter-y: 0;
}

@media (min-width: 576px) {
    .tab-container.dashboard-left-tab-panel > .row.g-3 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 768px) {
    .tab-container.dashboard-left-tab-panel > .row.g-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (min-width: 992px) {
    .tab-container.dashboard-left-tab-panel > .row.g-3 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1400px) {
    .tab-container.dashboard-left-tab-panel > .row.g-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

/*
 * Admin dashboard (SDM): grid mengikuti jumlah kartu per baris — bukan repeat(2) global di atas.
 * Ready / Complete / Absent → 3 kolom dari lg; Stand by / Not ready → 4 kolom dari lg.
 */
#tab-project.tab-container.dashboard-left-tab-panel > .row.g-3,
#tab-maintance.tab-container.dashboard-left-tab-panel > .row.g-3,
#tab-ready.tab-container.dashboard-left-tab-panel > .row.g-3,
#tab-complete.tab-container.dashboard-left-tab-panel > .row.g-3,
#tab-absent.tab-container.dashboard-left-tab-panel > .row.g-3 {
    grid-template-columns: minmax(0, 1fr);
}

@media (min-width: 576px) {
    #tab-project.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-maintance.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-ready.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-complete.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-absent.tab-container.dashboard-left-tab-panel > .row.g-3 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 768px) {
    #tab-project.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-maintance.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-ready.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-complete.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-absent.tab-container.dashboard-left-tab-panel > .row.g-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (min-width: 992px) {
    #tab-project.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-maintance.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-ready.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-complete.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-absent.tab-container.dashboard-left-tab-panel > .row.g-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (min-width: 1400px) {
    #tab-project.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-maintance.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-ready.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-complete.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-absent.tab-container.dashboard-left-tab-panel > .row.g-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

#tab-standby.tab-container.dashboard-left-tab-panel > .row.g-3,
#tab-notready.tab-container.dashboard-left-tab-panel > .row.g-3 {
    grid-template-columns: minmax(0, 1fr);
}

@media (min-width: 576px) {
    #tab-standby.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-notready.tab-container.dashboard-left-tab-panel > .row.g-3 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 768px) {
    #tab-standby.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-notready.tab-container.dashboard-left-tab-panel > .row.g-3 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 992px) {
    #tab-standby.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-notready.tab-container.dashboard-left-tab-panel > .row.g-3 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

@media (min-width: 1400px) {
    #tab-standby.tab-container.dashboard-left-tab-panel > .row.g-3,
    #tab-notready.tab-container.dashboard-left-tab-panel > .row.g-3 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

.tab-container.dashboard-left-tab-panel > .row.g-3 > [class*="col-"] {
    width: 100%;
    max-width: none;
    flex: none;
    padding-left: 0;
    padding-right: 0;
}

.tab-container.dashboard-left-tab-panel > .empty-state {
    margin-left: 0;
    margin-right: 0;
    padding-left: 0;
    padding-right: 0;
}

/* --- Kartu notifikasi kanan (Tasks / Project): tinggi fleksibel, scroll tanpa bilah --- */
.dashboard-notify-col {
    display: flex;
    flex-direction: column;
    min-width: 0;
    min-height: 0;
}

.dashboard-notify-stack {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    flex: 1 1 auto;
    min-height: 0;
    align-items: flex-start;
}

.dashboard-notify-row-top {
    display: flex;
    flex-wrap: nowrap;
    gap: 0.75rem;
    width: 100%;
    min-height: 0;
}

.dashboard-notify-row-top .dashboard-notify-card {
    flex: 1 1 0;
    min-width: 0;
    max-width: none;
    width: auto;
}

@media (min-width: 576px) and (max-width: 991.98px) {
    .dashboard-notify-stack {
        flex-direction: row;
        flex-wrap: wrap;
    }

    .dashboard-notify-stack--dual {
        flex-wrap: nowrap;
        align-items: flex-start;
    }

    .dashboard-notify-stack--dual .dashboard-notify-card {
        flex: 1 1 0;
        min-width: 0;
        max-width: none;
        align-self: flex-start;
    }
}

@media (min-width: 1400px) {
    .dashboard-notify-stack {
        flex-direction: row;
        flex-wrap: wrap;
        align-items: stretch;
    }
}

@media (min-width: 992px) {
    .dashboard-notify-col {
        height: 100%;
        max-height: 100%;
        align-self: stretch;
    }

    .dashboard-notify-stack {
        flex: 1 1 auto;
        min-height: 0;
        max-height: 100%;
        overflow: hidden;
        align-items: stretch;
    }

    .dashboard-notify-stack .dashboard-notify-card {
        width: 100%;
        max-width: none;
        flex: 1 1 0;
        min-height: 0;
        max-height: 100%;
    }

    /* User / director: tinggi kartu mengikuti isi sampai maks. 100% kolom; setelah itu scroll di dalam kartu */
    .dashboard-notify-col .dashboard-notify-stack--dual {
        flex: 1 1 auto;
        align-self: stretch;
        width: 100%;
        height: 100%;
        max-height: 100%;
        min-height: 0;
        overflow: hidden;
    }

    .dashboard-notify-stack--dual {
        flex-direction: row;
        flex-wrap: nowrap;
        align-items: flex-start;
        justify-content: flex-start;
        column-gap: 0.75rem;
    }

    .dashboard-notify-stack--dual .dashboard-notify-card {
        display: flex;
        flex-direction: column;
        flex: 1 1 0;
        min-width: 0;
        max-width: none;
        width: auto;
        align-self: flex-start;
        max-height: 100%;
        min-height: 0;
        overflow: hidden;
    }

    .dashboard-notify-stack--dual .dashboard-notify-section {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        min-width: 0;
        overflow: hidden;
    }

    .dashboard-notify-stack--dual .dashboard-notify-scroll {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
    }

    .dashboard-notify-stack--dual .dashboard-notify-scroll > .empty-state-right {
        flex: 0 0 auto;
        min-height: 0;
        padding-block: 0.75rem;
    }
}

.dashboard-notify-card {
    display: flex;
    flex-direction: column;
    min-width: 0;
    min-height: 0;
    overflow: hidden;
    width: 100%;
    max-width: 280px;
}

.dashboard-notify-section {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
}

.dashboard-notify-scroll {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
    border-radius: 10px;
}

/* Pesan kosong memenuhi area scroll agar vertikal terasa seperti kolom To do */
.dashboard-notify-scroll > .empty-state-right {
    flex: 1 1 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 0;
}

/* Layar kecil: sama pola batas tinggi seperti kolom task board (.tasks-board-card) */
@media (max-width: 991.98px) {
    .dashboard-notify-card {
        flex: 0 1 auto;
        max-height: min(70vh, 720px);
    }

    .dashboard-notify-scroll {
        max-height: min(70vh, 720px);
        /* Area kosong â‰ˆ satu kolom To do yang belum terisi */
        min-height: min(35vh, 360px);
    }

    /* Dual: batas tinggi = viewport âˆ’ header; kartu pakai nilai sama agar % orang tua tak perlu height tetap */
    .dashboard-notify-stack--dual {
        max-height: calc(var(--app-vh) - var(--app-header-height) - 4rem);
        min-height: 0;
    }

    .dashboard-notify-stack--dual .dashboard-notify-card {
        display: flex;
        flex-direction: column;
        flex: 1 1 0;
        min-width: 0;
        max-width: none;
        align-self: flex-start;
        max-height: calc(var(--app-vh) - var(--app-header-height) - 4rem);
        min-height: 0;
        overflow: hidden;
    }

    .dashboard-notify-stack--dual .dashboard-notify-section {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        min-width: 0;
        overflow: hidden;
    }

    .dashboard-notify-stack--dual .dashboard-notify-scroll {
        flex: 1 1 auto;
        min-height: 0;
        max-height: none;
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
    }

    .dashboard-notify-stack--dual .dashboard-notify-scroll > .empty-state-right {
        flex: 0 0 auto;
        min-height: 0;
        padding-block: 0.75rem;
    }
}

@supports (height: 100dvh) {
    @media (max-width: 991.98px) {
        .dashboard-notify-stack--dual {
            max-height: calc(var(--app-dvh) - var(--app-header-height) - 4rem);
        }

        .dashboard-notify-stack--dual .dashboard-notify-card {
            max-height: calc(var(--app-dvh) - var(--app-header-height) - 4rem);
        }
    }
}

/* Ponsel: dua kartu notifikasi bertumpuk (atasâ€“bawah) agar tetap terbaca */
@media (max-width: 575.98px) {
    .dashboard-notify-stack--dual {
        flex-direction: column;
        flex-wrap: nowrap;
        align-items: stretch;
    }

    .dashboard-notify-stack--dual .dashboard-notify-card {
        flex: 0 1 auto;
        width: 100%;
        max-width: none;
        align-self: stretch;
    }

    .dashboard-notify-row-top {
        flex-direction: column;
    }
}

.dashboard-notify-stack--triple {
    flex-direction: column !important;
    align-items: stretch !important;
    flex-wrap: nowrap !important;
}

/* Non-admin: baris atas (Tasks+Project) dan card Notification bawah seimbang */
.dashboard-notify-stack--triple > .dashboard-notify-row-top,
.dashboard-notify-stack--triple > .dashboard-notify-card {
    flex: 1 1 0;
    min-height: 0;
}

.dashboard-notify-stack--triple > .dashboard-notify-row-top .dashboard-notify-card {
    min-height: 0;
}

/* Admin: satu kartu pengajuan izin mengisi tinggi kolom kanan */
@media (min-width: 992px) {
    .dashboard-notify-stack--admin-solo .dashboard-notify-card {
        flex: 1 1 0;
        min-height: 0;
        max-width: none;
        width: 100%;
    }
}

.dashboard-notify-card--admin-feed .dashboard-notify-section {
    min-height: 0;
}

/* Notifikasi admin: 2 kolom agar kartu tidak memanjang vertikal berlebihan */
.dashboard-notify-items-2col {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.5rem;
    align-content: start;
}

.dashboard-notify-items-2col .dashboard-notify-grid-item {
    min-width: 0;
}

.dashboard-notify-items-2col .dashboard-notify-grid-item > .bg-white {
    min-height: 100%;
}

@media (max-width: 575.98px) {
    .dashboard-notify-items-2col {
        grid-template-columns: 1fr;
    }
}

</style>
</head>
    <div class="row gx-0 px-1 py-1 m-0 dashboard-elegant-dark dashboard-page">
        {{-- Bagian Kiri --}}
        <div class="col-lg-8 dashboard-left-col">
            <div class="card dashboard-left-card">
                <div class="card-body dashboard-left-card-body">
                    @if (false && ($role === 'staff' || $role === 'director'))
                        <div class="tab-btns dashboard-left-tab-btns mb-4">
                            <button type="button" class="btn btn-custom"
                                data-target="#tab-project">Project</button>
                            <button type="button" class="btn btn-custom"
                                data-target="#tab-maintance">Maintence</button>
                            <button type="button" class="btn btn-custom"
                                data-target="#tab-complete">Complate</button>
                        </div>

                        <div class="dashboard-left-tab-panels">
                        <div id="tab-project" class="tab-container dashboard-left-tab-panel">
                            @if($projects->isEmpty())
                                @include('components.empty-state', ['icon' => 'bi bi-file-earmark-fill', 'text' => 'No project yet'])
                            @else
                                <div class="row g-3">
                                    @foreach ($projects as $project)
                                        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xxl-4">
                                            <div class="card shadow-sm h-100 ready-task-card">
                                                <div class="card-body p-3 d-flex flex-column h-100">
                                                    <div class="d-flex align-items-center mb-2 gap-3 flex-shrink-0">
                                                        @if ($project->director?->avatar)
                                                            <img id="avatarPreview" alt="Foto Profil" class="user-avatar rounded-circle"
                                                                src="{{ asset('storage/avatars/' . $project->director->avatar) }}"
                                                                style="width: 25px; height: 25px; object-fit: cover;" />
                                                        @else
                                                            <div id="avatarPreview"
                                                                class="rounded-circle d-flex align-items-center justify-content-center fw-semibold"
                                                                style="width: 25px; height: 25px; font-size: 10px; background-color: #0D8ABC; color: white;">
                                                                {{ $project->director?->name ? strtoupper(substr($project->director->name, 0, 2)) : '' }}
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <p class="card-title m-0">
                                                                {{ ucwords($project->director->name ?? '-') }}
                                                            </p>
                                                            <p class="card-subtitle m-0">
                                                                {{ ucwords($project->director->division->divisi ?? '-') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 d-flex flex-column">
                                                        <p class="card-text-strong m-0">Working on {{ ucwords($project->name) }} :</p>
                                                        <p class="card-text-black mb-0 pb-2">{{ ucwords($project->description ?? '') }}</p>
                                                    </div>
                                                    <div class="ready-task-badges mt-auto pt-2 d-flex flex-wrap gap-2 justify-content-start align-items-center">
                                                        @if($project->status)
                                                            <span class="btn btn-sm rounded-2 border-0 task-meta-pill"
                                                                style="background-color: {{ $project->status->background_color }}; color: {{ $project->status->text_color }};">
                                                                {{ ucwords($project->status->status) }}
                                                            </span>
                                                        @endif
                                                        @if($project->difficulty)
                                                            <span class="btn btn-sm rounded-2 border-0 task-meta-pill"
                                                                style="background-color: {{ $project->difficulty->background_color }}; color: {{ $project->difficulty->text_color }};">
                                                                {{ ucwords($project->difficulty->difficulty) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div id="tab-maintance" class="tab-container dashboard-left-tab-panel d-none">
                            @if($main->isEmpty())
                                @include('components.empty-state', ['icon' => 'bi bi-file-earmark-fill', 'text' => 'No project yet'])
                            @else
                                <div class="row g-3">
                                    @foreach ($main as $project)
                                        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xxl-4">
                                            <div class="card shadow-sm h-100">
                                                <div class="card-body p-3">
                                                    <div class="d-flex align-items-center mb-2 gap-3">
                                                        @if ($project->user?->avatar)
                                                            <img id="avatarPreview" alt="Foto Profil" class="user-avatar"
                                                                src="{{ asset('storage/avatars' . $project->user->avatar) }}"
                                                                style="width: 25px; height: 25px; object-fit: cover;" />
                                                        @else
                                                            <div id="avatarPreview"
                                                                class="rounded-circle d-flex align-items-center justify-content-center fw-semibold"
                                                                style="width: 25px; height: 25px; font-size: 10px; background-color: #0D8ABC; color: white;">
                                                                {{ $project->director->name ? strtoupper(substr($project->director->name, 0, 2)) : '' }}
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <p class="card-title m-0">
                                                                {{ ucwords($project->director->name) }}
                                                            </p>
                                                            <p class="card-subtitle m-0">
                                                                {{ ucwords($project->director->division->divisi) }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <p class="card-text-strong">Working on {{ ucwords($project->name) }} :</p>
                                                    <p class="card-text mb-2">{{ ucwords($project->description) }}</p>
                                                    <div class="d-flex gap-2">
                                                        <span class="btn btn-sm rounded-2 border-0 task-meta-pill"
                                                            style="background-color: {{ $project->status->background_color }}; color: {{ $project->status->text_color }};">
                                                            {{ ucwords($project->status->status) }}
                                                        </span>
                                                        <span class='btn btn-sm rounded-2 border-0 task-meta-pill'
                                                            style="background-color: {{ $project->difficulty->background_color }}; color: {{ $project->difficulty->text_color }};">
                                                            {{ ucwords($project->difficulty->difficulty) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div id="tab-complete" class="tab-container dashboard-left-tab-panel d-none">
                            @if($complete->isEmpty())
                                @include('components.empty-state', ['icon' => 'bi bi-clipboard-x', 'text' => 'No completed'])
                            @else
                                <div class="row g-3">
                                    @foreach($complete as $project)
                                        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xxl-4">
                                            <div class="card shadow-sm h-100">
                                                <div class="card-body p-3">
                                                    <div class="d-flex align-items-center mb-2 gap-3">
                                                        @if ($project->user?->avatar)
                                                            <img id="avatarPreview" alt="Foto Profil" class="user-avatar"
                                                                src="{{ asset('storage/avatars' . $project->user->avatar) }}"
                                                                style="width: 25px; height: 25px; object-fit: cover;" />
                                                        @else
                                                            <div id="avatarPreview"
                                                                class="rounded-circle d-flex align-items-center justify-content-center fw-semibold"
                                                                style="width: 25px; height: 25px; font-size: 10px; background-color: #0D8ABC; color: white;">
                                                                {{ $project->director->name ? strtoupper(substr($project->director->name, 0, 2)) : '' }}
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <p class="card-title m-0">
                                                                {{ ucwords($project->director->name) }}
                                                            </p>
                                                            <p class="card-subtitle m-0">
                                                                {{ ucwords($project->director->division->divisi) }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <p class="card-text-strong">
                                                        Working on {{ ucwords($project->name) }} :</p>
                                                    <p class="card-text mb-2">{{ ucwords($project->description) }}

                                                    </p>
                                                    <div class="d-flex gap-2">
                                                        <span class="btn btn-sm rounded-2 border-0 task-meta-pill"
                                                            style="background-color: {{ $project->status->background_color }}; color: {{ $project->status->text_color }};">
                                                            {{ ucwords($project->status->status) }}
                                                        </span>
                                                        <span class='btn btn-sm rounded-2 border-0 task-meta-pill'
                                                            style="background-color: {{ $project->difficulty->background_color }}; color: {{ $project->difficulty->text_color }};">
                                                            {{ ucwords($project->difficulty->difficulty) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        </div>
                    @else
                        <div class="tab-btns dashboard-left-tab-btns mb-4">
                            <button type="button" class="btn btn-custom" data-target="#tab-ready">Ready</button>
                            <button type="button" class="btn btn-custom" data-target="#tab-standby">Stand By</button>
                            <button type="button" class="btn btn-custom" data-target="#tab-notready">Not Ready</button>
                            <button type="button" class="btn btn-custom"
                                data-target="#tab-complete">Complete</button>
                            <button type="button" class="btn btn-custom"
                                data-target="#tab-absent">Absent</button>
                        </div>

                        <div class="dashboard-left-tab-panels">
                        <div id="tab-ready" class="tab-container dashboard-left-tab-panel">
                            @if($ready->isEmpty())
                                @include('components.empty-state', ['icon' => 'bi bi-file-earmark-check-fill', 'text' => 'No ready tasks'])
                            @else
                                <div class="row g-3">
                                    @foreach($ready as $task)
                                        <div class="col-12 col-sm-6 col-lg-4">
                                            <div class="card shadow-sm h-100 ready-task-card">
                                                <div class="card-body p-3 d-flex flex-column h-100">
                                                    <div class="d-flex align-items-center mb-2 gap-3 flex-shrink-0">
                                                        @if ($task->user?->avatar)
                                                            <img id="avatarPreview" alt="Foto Profil" class="user-avatar rounded-circle"
                                                                src="{{ asset('storage/avatars/' . $task->user->avatar) }}"
                                                                style="width: 25px; height: 25px; object-fit: cover;" />
                                                        @else
                                                            <div id="avatarPreview"
                                                                class="rounded-circle d-flex align-items-center justify-content-center fw-semibold"
                                                                style="width: 25px; height: 25px; font-size: 10px; background-color: #0D8ABC; color: white;">
                                                                {{ $task->user?->name ? strtoupper(substr($task->user->name, 0, 2)) : '' }}
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <p class="card-title m-0">
                                                                {{ ucwords($task->user->name ?? '-') }}
                                                            </p>
                                                            <p class="card-subtitle m-0">
                                                                @if (($task->user?->role?->role ?? null) === 'staff')
                                                                    {{ ucwords($task->user?->division?->divisi ?? '-') }}
                                                                @else
                                                                    {{ \App\Support\RoleDisplay::label($task->user?->role?->role ?? null) }}
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 d-flex flex-column">
                                                        <p class="card-text-strong m-0">Working on {{ ucwords($task->project->name ?? 'Stand By') }} :</p>
                                                        <p class="card-text-black mb-0 pb-2">{{ ucwords($task->name) }}</p>
                                                    </div>
                                                    <div class="ready-task-badges mt-auto pt-2 d-flex flex-wrap gap-2 justify-content-start align-items-center">
                                                        @if($task->status)
                                                            <span class="btn btn-sm rounded-2 border-0 task-meta-pill"
                                                                style="background-color: {{ $task->status->background_color }}; color: {{ $task->status->text_color }};">
                                                                {{ ucwords($task->status->status) }}
                                                            </span>
                                                        @endif
                                                        @if($task->difficulty)
                                                            <span class="btn btn-sm rounded-2 border-0 task-meta-pill"
                                                                style="background-color: {{ $task->difficulty->background_color }}; color: {{ $task->difficulty->text_color }};">
                                                                {{ ucwords($task->difficulty->difficulty) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div id="tab-standby" class="tab-container dashboard-left-tab-panel d-none">
                            @if($standby->isEmpty())
                                @include('components.empty-state', ['icon' => 'fa-solid fa-users', 'text' => 'No staff in Stand By'])
                            @else
                                <div class="row g-3">
                                    @foreach($standby as $user)
                                        <div class="col-12 col-sm-6 col-lg-3">
                                            <div class="card shadow-sm h-100 status-user-card">
                                                <div class="card-body p-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        @if ($user->avatar)
                                                            <img id="avatarPreview" alt="Foto Profil" class="user-avatar rounded-circle"
                                                                src="{{ asset('storage/avatars/' . $user->avatar) }}"
                                                                style="width: 35px; height: 35px; object-fit: cover;" />
                                                        @else
                                                            <div id="avatarPreview"
                                                                class="rounded-circle d-flex align-items-center justify-content-center fw-semibold"
                                                                style="width: 35px; height: 35px; font-size: 10px; background-color: #0D8ABC; color: white;">
                                                                {{ $user->name ? strtoupper(substr($user->name, 0, 2)) : '' }}
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <p class="card-title m-0">
                                                                {{ ucwords($user->name) }}
                                                            </p>
                                                            <p class="card-subtitle m-0">
                                                                @if (($user->role?->role ?? null) === 'staff')
                                                                    {{ ucwords($user->division?->divisi ?? '-') }}
                                                                @else
                                                                    {{ \App\Support\RoleDisplay::label($user->role?->role ?? null) }}
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div id="tab-notready" class="tab-container dashboard-left-tab-panel d-none">
                            @if($notready->isEmpty())
                                @include('components.empty-state', ['icon' => 'fa-solid fa-users', 'text' => 'No staff in Not Ready'])
                            @else
                                <div class="row g-3">
                                    @foreach ($notready as $user)
                                        <div class="col-12 col-sm-6 col-lg-3">
                                            <div class="card shadow-sm h-100 status-user-card">
                                                <div class="card-body p-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        @if ($user->avatar)
                                                            <img id="avatarPreview" alt="Foto Profil" class="user-avatar rounded-circle"
                                                                src="{{ asset('storage/avatars/' . $user->avatar) }}"
                                                                style="width: 35px; height: 35px; object-fit: cover;" />
                                                        @else
                                                            <div id="avatarPreview"
                                                                class="rounded-circle d-flex align-items-center justify-content-center fw-semibold"
                                                                style="width: 35px; height: 35px; font-size: 10px; background-color: #0D8ABC; color: white;">
                                                                {{ $user->name ? strtoupper(substr($user->name, 0, 2)) : '' }}
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <p class="card-title m-0">
                                                                {{ ucwords($user->name) }}
                                                            </p>
                                                            <p class="card-subtitle m-0">
                                                                @if (($user->role?->role ?? null) === 'staff')
                                                                    {{ ucwords($user->division?->divisi ?? '-') }}
                                                                @else
                                                                    {{ \App\Support\RoleDisplay::label($user->role?->role ?? null) }}
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div id="tab-complete" class="tab-container dashboard-left-tab-panel d-none">
                            @if($complete->isEmpty())
                                <div class="empty-state d-flex align-items-center justify-content-center">
                                    <div class="d-block">
                                        <div class="empty-icon mb-2 d-flex align-items-center justify-content-center">
                                            <i class="bi bi-clipboard-x fs-1"></i>
                                        </div>
                                        <div class="empty-text">No completed tasks</div>
                                    </div>
                                </div>
                            @else
                                <div class="row g-3">
                                    @foreach ($complete as $task)
                                        <div class="col-12 col-sm-6 col-lg-4">
                                            <div class="card shadow-sm h-100 ready-task-card">
                                                <div class="card-body p-3 d-flex flex-column h-100">
                                                    <div class="d-flex align-items-center mb-2 gap-3 flex-shrink-0">
                                                        @if ($task->user?->avatar)
                                                            <img id="avatarPreview" alt="Foto Profil" class="user-avatar rounded-circle"
                                                                src="{{ asset('storage/avatars/' . $task->user->avatar) }}"
                                                                style="width: 25px; height: 25px; object-fit: cover;" />
                                                        @else
                                                            <div id="avatarPreview"
                                                                class="rounded-circle d-flex align-items-center justify-content-center fw-semibold"
                                                                style="width: 25px; height: 25px; font-size: 10px; background-color: #0D8ABC; color: white;">
                                                                {{ $task->user?->name ? strtoupper(substr($task->user->name, 0, 2)) : '' }}
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <p class="card-title m-0">
                                                                {{ ucwords($task->user->name ?? '-') }}
                                                            </p>
                                                            <p class="card-subtitle m-0">
                                                                @if (($task->user?->role?->role ?? null) === 'staff')
                                                                    {{ ucwords($task->user?->division?->divisi ?? '-') }}
                                                                @else
                                                                    {{ \App\Support\RoleDisplay::label($task->user?->role?->role ?? null) }}
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 d-flex flex-column">
                                                        <p class="card-text-strong m-0">Working on {{ ucwords($task->project->name ?? 'Stand By') }} :</p>
                                                        <p class="card-text-black mb-0 pb-2">{{ ucwords($task->name) }}</p>
                                                    </div>
                                                    <div class="ready-task-badges mt-auto pt-2 d-flex flex-wrap gap-2 justify-content-start align-items-center">
                                                        @if($task->status)
                                                            <span class="btn btn-sm rounded-2 border-0 task-meta-pill"
                                                                style="background-color: {{ $task->status->background_color }}; color: {{ $task->status->text_color }};">
                                                                {{ ucwords($task->status->status) }}
                                                            </span>
                                                        @endif
                                                        @if($task->difficulty)
                                                            <span class="btn btn-sm rounded-2 border-0 task-meta-pill"
                                                                style="background-color: {{ $task->difficulty->background_color }}; color: {{ $task->difficulty->text_color }};">
                                                                {{ ucwords($task->difficulty->difficulty) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div id="tab-absent" class="tab-container dashboard-left-tab-panel d-none">
                            @if($absent->isEmpty())
                                @include('components.empty-state', ['icon' => 'fas fa-users-slash', 'text' => 'No absent staff today'])
                            @else
                                <div class="row g-3">
                                    @foreach ($absent as $user)
                                        <div class="col-12 col-sm-6 col-lg-4">
                                            <div class="card shadow-sm h-100">
                                                <div class="card-body p-3">
                                                    <div class="d-flex align-items-center mb-3 gap-3">
                                                        @if ($user->avatar)
                                                            <img id="avatarPreview" alt="Foto Profil" class="user-avatar rounded-circle"
                                                                src="{{ asset('storage/avatars/' . $user->avatar) }}"
                                                                style="width: 35px; height: 35px; object-fit: cover;" />
                                                        @else
                                                            <div id="avatarPreview"
                                                                class="rounded-circle d-flex align-items-center justify-content-center fw-semibold"
                                                                style="width: 35px; height: 35px; font-size: 10px; background-color: #0D8ABC; color: white;">
                                                                {{ $user->name ? strtoupper(substr($user->name, 0, 2)) : '' }}
                                                            </div>
                                                        @endif

                                                        @php $adm = $user->administrations->first(); @endphp

                                                        <div>
                                                            <p class="card-title m-0">{{ ucwords($user->name) }}</p>
                                                            <p class="card-subtitle m-0">
                                                                @if ($adm)
                                                                    {{ \Carbon\Carbon::parse($adm->start_date)->diffInDays(\Carbon\Carbon::parse($adm->end_date)) + 1 }}
                                                                    hari
                                                                @else
                                                                    Absent
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <p class="card-text-strong">
                                                        {{ ucwords($user->administrations->first()->category->name ?? '') }}
                                                    </p>
                                                    <p class="card-text-black">
                                                        {{ ucwords($user->administrations->first()->description ?? '') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Bagian Kanan --}}
        <div class="col-lg-4 mt-2 mt-lg-0 dashboard-notify-col ps-3">
            <div class="dashboard-notify-stack @if ($role === 'executive') dashboard-notify-stack--admin-solo @else dashboard-notify-stack--triple @endif">
                @if ($role === 'executive')
                    {{-- Admin: izin pending + project completed (30 hari terakhir), urut waktu --}}
                    <div class="card dashboard-notify-card dashboard-notify-card--admin-feed p-0 m-0"
                        style="border-radius:15px; background-color:#2E8EB5 !important; border: none;">
                        <section class="project-section dashboard-notify-section p-3 rounded-4 d-flex flex-column h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2 dash-notify-heading flex-shrink-0">
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-bell-fill" style="font-size:22px" aria-hidden="true"></i>
                                    <h2 class="fs-6 fw-semibold m-0" style="font-family:montserrat">Notification</h2>
                                </div>
                                @if (($dashboardNotificationBadgeCount ?? 0) > 0)
                                    <span class="badge rounded-pill bg-white text-dark fw-semibold">{{ $dashboardNotificationBadgeCount }}</span>
                                @endif
                            </div>
                            <div class="dashboard-notify-scroll dashboard-notify-scroll--admin-2col flex-grow-1" style="background-color:#2E8EB5;">
                                @if (($dashboardNotifications ?? collect())->isEmpty())
                                    <div class="empty-state-right d-flex align-items-center justify-content-center text-center px-2">
                                        <div class="empty-text dash-notify-empty">Tidak ada notifikasi</div>
                                    </div>
                                @else
                                    <div class="dashboard-notify-items-2col">
                                    @foreach ($dashboardNotifications as $note)
                                        @if ($note->kind === 'administration')
                                            @php
                                                $adm = $note->administration;
                                                $admTotalDays = ($adm->start_date && $adm->end_date)
                                                    ? $adm->start_date->diffInDays($adm->end_date) + 1
                                                    : null;
                                            @endphp
                                            <a href="{{ route('executive.administration.show', $adm->id) }}"
                                                class="dashboard-notify-grid-item text-decoration-none text-reset d-block">
                                                <div class="bg-white rounded-2 p-2 text-black h-100 d-flex flex-column">
                                                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                                        <p class="fw-semibold small mb-0 text-truncate flex-grow-1 min-w-0" title="{{ ucwords($adm->user->name ?? '-') }}">
                                                            {{ ucwords($adm->user->name ?? '-') }}
                                                        </p>
                                                        <span class="btn btn-sm rounded-4 border-0 task-meta-pill flex-shrink-0 align-self-start px-2 py-0"
                                                            style="background-color:#FFB42E;color:#fff;min-width:auto;line-height:1.3;">Pending</span>
                                                    </div>
                                                    <p class="small mb-1" style="line-height:1.35;">
                                                        <span class="text-muted">Permission :</span>
                                                        <span class="text-break">{{ ucwords($adm->category->name ?? 'â€”') }}</span>
                                                    </p>
                                                    <p class="small mb-1" style="line-height:1.35;">
                                                        <span class="text-muted">Division :</span>
                                                        <span class="text-break">{{ ucwords($adm->user?->division->divisi ?? 'â€”') }}</span>
                                                    </p>
                                                    <p class="small mb-1" style="line-height:1.35;">
                                                        <span class="text-muted">Long leave :</span>
                                                        @if ($admTotalDays !== null)
                                                            <span>{{ $admTotalDays }} hari</span>
                                                        @else
                                                            <span>-</span>
                                                        @endif
                                                    </p>
                                                    <p class="small text-muted mb-0" style="line-height:1.35;">
                                                        <span class="text-muted">Tanggal :</span>
                                                        {{ $adm->start_date?->format('d/m/Y') }} - {{ $adm->end_date?->format('d/m/Y') }}
                                                    </p>
                                                </div>
                                            </a>
                                        @elseif ($note->kind === 'project_completed')
                                            @php $proj = $note->project; @endphp
                                            <a href="{{ route('executive.project.edit', $proj->id) }}"
                                                class="dashboard-notify-grid-item text-decoration-none text-reset d-block">
                                                <div class="bg-white rounded-2 p-2 text-black h-100 d-flex flex-column">
                                                    <p class="fw-semibold small mb-1 text-truncate" title="{{ ucwords($proj->name) }}">{{ ucwords($proj->name) }}</p>
                                                    <p class="small text-secondary mb-0" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                                        @if ($proj->director)
                                                            Director: {{ ucwords($proj->director->name) }}
                                                        @endif
                                                    </p>
                                                    <p class="small text-muted mb-2 mt-1">
                                                        {{ $proj->updated_at?->format('d/m/Y H:i') }}
                                                    </p>
                                                    <span class="btn btn-sm rounded-4 border-0 task-meta-pill flex-shrink-0 align-self-start px-2 py-0 mt-auto"
                                                        style="background-color:#7DB546;color:#fff;min-width:auto;line-height:1.3;">Completed</span>
                                                </div>
                                            </a>
                                        @endif
                                    @endforeach
                                    </div>
                                @endif
                            </div>
                        </section>
                    </div>
                @else
                    {{-- User / Director: ringkasan task (hijau) & project (orange) --}}
                    <div class="dashboard-notify-row-top">
                    <div class="card dashboard-notify-card p-0 m-0" style="border-radius:15px; background-color:#7DB546 !important; border: none;">
                        <section class="task-section dashboard-notify-section p-3 rounded-4">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2 dash-notify-heading flex-shrink-0">
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-file-earmark-check-fill" style="font-size:22px"></i>
                                    <h2 class="fs-6 fw-semibold m-0" style="font-family:montserrat">Tasks</h2>
                                </div>
                                @if ($tasks->count() > 0)
                                    <span class="badge rounded-pill bg-white text-dark fw-semibold">{{ $tasks->count() }}</span>
                                @endif
                            </div>
                            <div class="dashboard-notify-scroll" style="background-color:#7DB546;">
                                @if($tasks->isEmpty())
                                    <div class="empty-state-right d-flex align-items-center justify-content-center">
                                        <div class="empty-text dash-notify-empty">You have 0 tasks</div>
                                    </div>
                                @else
                                    @foreach ($tasks as $task)
                                        <div class="bg-white rounded-2 p-2 mb-2 text-black">
                                            <div class="dashboard-project-notify-top">
                                                <p class="fw-semibold small mb-0 dashboard-project-notify-title">{{ ucwords($task->project->name ?? 'Stand By') }}</p>
                                                <p class="small text-secondary dashboard-project-notify-desc">{{ ucwords($task->name) }}</p>
                                            </div>
                                            <div class="d-flex gap-2">
                                                @if($task->status)
                                                    <span class="btn btn-sm rounded-2 border-0 task-meta-pill"
                                                        style="background-color: {{ $task->status->background_color }}; color: {{ $task->status->text_color }};">
                                                        {{ ucwords($task->status->status) }}
                                                    </span>
                                                @endif
                                                @if($task->difficulty)
                                                    <span class="btn btn-sm rounded-2 border-0 task-meta-pill"
                                                        style="background-color: {{ $task->difficulty->background_color }}; color: {{ $task->difficulty->text_color }};">
                                                        {{ ucwords($task->difficulty->difficulty) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </section>
                    </div>

                    <div class="card dashboard-notify-card p-0 m-0" style="border-radius:15px; background-color:#FFB42E; border: none;">
                        <section class="project-section dashboard-notify-section p-3 rounded-4">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2 dash-notify-heading flex-shrink-0">
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-kanban-fill" style="font-size:22px"></i>
                                    <h2 class="fs-6 fw-semibold m-0" style="font-family:montserrat">Project</h2>
                                </div>
                                @if ($projects->count() > 0)
                                    <span class="badge rounded-pill bg-white text-dark fw-semibold">{{ $projects->count() }}</span>
                                @endif
                            </div>
                            <div class="dashboard-notify-scroll" style="background-color:#FFB42E;">
                                @if($projects->isEmpty())
                                    <div class="empty-state-right d-flex align-items-center justify-content-center">
                                        <div class="empty-text dash-notify-empty">There's 0 project</div>
                                    </div>
                                @else
                                    @foreach ($projects as $project)
                                        <div class="bg-white rounded-2 p-2 mb-2 text-black">
                                            <div class="d-flex justify-content-between align-items-start w-100 dashboard-project-notify-top">
                                                <div class="pe-0 flex-grow-1 me-2">
                                                    <p class="fw-semibold small mb-0 dashboard-project-notify-title">{{ ucwords($project->name) }}</p>
                                                    <p class="small text-secondary project-desc-clamp dashboard-project-notify-desc">{{ ucfirst($project->description)}}</p>
                                                </div>
                                                <div class="project-avatars flex-shrink-0 ps-0">
                                                    @foreach($project->sdms->take(4) as $member)
                                                        <img src="{{ $member->avatar ? asset('storage/avatars/' . $member->avatar) : 'https://ui-avatars.com/api/?name=' . $member->name . '&background=random&color=fff' }}"
                                                            class="rounded-circle avatar-stack" width="32" height="32" alt="{{ $member->name }}" />
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column align-items-start gap-2 mb-0 mt-0 pt-0 project-badge-wrap">
                                                <div class="d-flex gap-2">
                                                    @if($project->status)
                                                        <span class="btn btn-sm rounded-2 border-0 task-meta-pill"
                                                            style="background-color: {{ $project->status->background_color }}; color: {{ $project->status->text_color }};">
                                                            {{ ucwords($project->status->status) }}
                                                        </span>
                                                    @endif
                                                    @if($project->difficulty)
                                                        <span class="btn btn-sm rounded-2 border-0 task-meta-pill"
                                                            style="background-color: {{ $project->difficulty->background_color }}; color: {{ $project->difficulty->text_color }};">
                                                            {{ ucwords($project->difficulty->difficulty) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </section>
                    </div>
                    </div>

                    <div class="card dashboard-notify-card dashboard-notify-card--admin-feed p-0 m-0"
                        style="border-radius:15px; background-color:#2E8EB5 !important; border: none;">
                        <section class="project-section dashboard-notify-section p-3 rounded-4 d-flex flex-column h-100">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2 dash-notify-heading flex-shrink-0">
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-bell-fill" style="font-size:22px" aria-hidden="true"></i>
                                    <h2 class="fs-6 fw-semibold m-0" style="font-family:montserrat">Notification</h2>
                                </div>
                                @if (($dashboardNotificationBadgeCount ?? 0) > 0)
                                    <span class="badge rounded-pill bg-white text-dark fw-semibold">{{ $dashboardNotificationBadgeCount }}</span>
                                @endif
                            </div>
                            <div class="dashboard-notify-scroll dashboard-notify-scroll--admin-2col flex-grow-1" style="background-color:#2E8EB5;">
                                @if (($dashboardNotifications ?? collect())->isEmpty())
                                    <div class="empty-state-right d-flex align-items-center justify-content-center text-center px-2">
                                        <div class="empty-text dash-notify-empty">Tidak ada notifikasi</div>
                                    </div>
                                @else
                                    <div class="dashboard-notify-items-2col">
                                    @foreach ($dashboardNotifications as $note)
                                        @if ($note->kind === 'administration')
                                            @php
                                                $adm = $note->administration;
                                                $admTotalDays = ($adm->start_date && $adm->end_date)
                                                    ? $adm->start_date->diffInDays($adm->end_date) + 1
                                                    : null;
                                                $admShowRoute = $role === 'director'
                                                    ? route('director.administration.show', $adm->id, false)
                                                    : route('staff.administration.show', $adm->id, false);
                                                $admStatusName = strtolower($adm->status->name ?? 'pending');
                                                $admStatusLabel = match ($admStatusName) {
                                                    'accept' => 'Accepted',
                                                    'reject' => 'Rejected',
                                                    default => 'Pending',
                                                };
                                                $admStatusStyle = match ($admStatusName) {
                                                    'accept' => 'background-color:#7DB546;color:#fff;min-width:auto;line-height:1.3;',
                                                    'reject' => 'background-color:#C2410C;color:#fff;min-width:auto;line-height:1.3;',
                                                    default => 'background-color:#FFB42E;color:#fff;min-width:auto;line-height:1.3;',
                                                };
                                                $admNotificationOpenUrl = route('dashboard.notifications.open', [
                                                    'key' => $note->read_key,
                                                    'to' => $admShowRoute,
                                                ]);
                                            @endphp
                                            <a href="{{ $admNotificationOpenUrl }}"
                                                class="dashboard-notify-grid-item text-decoration-none text-reset d-block">
                                                <div class="bg-white rounded-2 p-2 text-black h-100 d-flex flex-column">
                                                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                                        <p class="fw-semibold small mb-0 text-truncate flex-grow-1 min-w-0" title="{{ ucwords($adm->user->name ?? '-') }}">
                                                            {{ ucwords($adm->user->name ?? '-') }}
                                                        </p>
                                                        <span class="btn btn-sm rounded-4 border-0 task-meta-pill flex-shrink-0 align-self-start px-2 py-0"
                                                            style="{{ $admStatusStyle }}">{{ $admStatusLabel }}</span>
                                                    </div>
                                                    <p class="small mb-1" style="line-height:1.35;">
                                                        <span class="text-muted">Permission :</span>
                                                        <span class="text-break">{{ ucwords($adm->category->name ?? '-') }}</span>
                                                    </p>
                                                    <p class="small mb-1" style="line-height:1.35;">
                                                        <span class="text-muted">Division :</span>
                                                        <span class="text-break">{{ ucwords($adm->user?->division->divisi ?? '-') }}</span>
                                                    </p>
                                                    <p class="small mb-1" style="line-height:1.35;">
                                                        <span class="text-muted">Long leave :</span>
                                                        @if ($admTotalDays !== null)
                                                            <span>{{ $admTotalDays }} hari</span>
                                                        @else
                                                            <span>-</span>
                                                        @endif
                                                    </p>
                                                    <p class="small text-muted mb-0" style="line-height:1.35;">
                                                        <span class="text-muted">Tanggal :</span>
                                                        {{ $adm->start_date?->format('d/m/Y') }} - {{ $adm->end_date?->format('d/m/Y') }}
                                                    </p>
                                                </div>
                                            </a>
                                        @elseif ($note->kind === 'project_completed')
                                            @php
                                                $proj = $note->project;
                                                $projRoute = $role === 'director'
                                                    ? route('director.project.edit', $proj->id, false)
                                                    : route('staff.project.tasks.index', $proj->id, false);
                                                $projNotificationOpenUrl = route('dashboard.notifications.open', [
                                                    'key' => $note->read_key,
                                                    'to' => $projRoute,
                                                ]);
                                            @endphp
                                            <a href="{{ $projNotificationOpenUrl }}"
                                                class="dashboard-notify-grid-item text-decoration-none text-reset d-block">
                                                <div class="bg-white rounded-2 p-2 text-black h-100 d-flex flex-column">
                                                    <p class="fw-semibold small mb-1 text-truncate" title="{{ ucwords($proj->name) }}">{{ ucwords($proj->name) }}</p>
                                                    <p class="small text-secondary mb-0" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                                        @if ($proj->director)
                                                            Director: {{ ucwords($proj->director->name) }}
                                                        @endif
                                                    </p>
                                                    <p class="small text-muted mb-2 mt-1">
                                                        {{ $proj->updated_at?->format('d/m/Y H:i') }}
                                                    </p>
                                                    <span class="btn btn-sm rounded-4 border-0 task-meta-pill flex-shrink-0 align-self-start px-2 py-0 mt-auto"
                                                        style="background-color:#7DB546;color:#fff;min-width:auto;line-height:1.3;">Completed</span>
                                                </div>
                                            </a>
                                        @elseif ($note->kind === 'task_review')
                                            @php
                                                $task = $note->task;
                                                $taskRoute = route('director.tasks.index', ['project_id' => $task->id_project], false);
                                                $taskNotificationOpenUrl = route('dashboard.notifications.open', [
                                                    'key' => $note->read_key,
                                                    'to' => $taskRoute,
                                                ]);
                                            @endphp
                                            <a href="{{ $taskNotificationOpenUrl }}"
                                                class="dashboard-notify-grid-item text-decoration-none text-reset d-block">
                                                <div class="bg-white rounded-2 p-2 text-black h-100 d-flex flex-column">
                                                    <p class="fw-semibold small mb-1 text-truncate" title="{{ ucwords($task->name ?? '-') }}">
                                                        {{ ucwords($task->name ?? '-') }}
                                                    </p>
                                                    <p class="small text-secondary mb-0 text-break" style="line-height:1.35;">
                                                        {{ ucwords($task->project?->name ?? 'Stand By') }}
                                                    </p>
                                                    <p class="small text-secondary mb-0 text-break" style="line-height:1.35;">
                                                        {{ ucwords($task->user?->name ?? '-') }}
                                                    </p>
                                                    <p class="small text-muted mb-2 mt-1" style="line-height:1.35;">
                                                        {{ ($task->running_review_at ?? $task->updated_at)?->format('d/m/Y H:i') }}
                                                    </p>
                                                    <div class="d-flex gap-1 flex-wrap align-items-center mt-auto">
                                                        <span class="btn btn-sm rounded-4 border-0 task-meta-pill flex-shrink-0 align-self-start px-2 py-0"
                                                            style="background-color:#FFB42E;color:#fff;min-width:auto;line-height:1.3;">Pending Review</span>
                                                    </div>
                                                </div>
                                            </a>
                                        @elseif ($note->kind === 'task_revision')
                                            @php
                                                $task = $note->task;
                                                $taskRoute = route('staff.tasks.index', ['project_id' => $task->id_project], false);
                                                $taskNotificationOpenUrl = route('dashboard.notifications.open', [
                                                    'key' => $note->read_key,
                                                    'to' => $taskRoute,
                                                ]);
                                            @endphp
                                            <a href="{{ $taskNotificationOpenUrl }}"
                                                class="dashboard-notify-grid-item text-decoration-none text-reset d-block">
                                                <div class="bg-white rounded-2 p-2 text-black h-100 d-flex flex-column">
                                                    <p class="fw-semibold small mb-1 text-truncate" title="{{ ucwords($task->name ?? '-') }}">
                                                        {{ ucwords($task->name ?? '-') }}
                                                    </p>
                                                    <p class="small text-secondary mb-0 text-break" style="line-height:1.35;">
                                                        {{ ucwords($task->project?->name ?? 'Stand By') }}
                                                    </p>
                                                    <p class="small text-secondary mb-0 text-break" style="line-height:1.35;">
                                                        {{ ucwords($task->user?->name ?? '-') }}
                                                    </p>
                                                    <p class="small text-muted mb-2 mt-1" style="line-height:1.35;">
                                                        {{ $task->updated_at?->format('d/m/Y H:i') }}
                                                    </p>
                                                    <div class="d-flex gap-1 flex-wrap align-items-center mt-auto">
                                                        <span class="btn btn-sm rounded-4 border-0 task-meta-pill flex-shrink-0 align-self-start px-2 py-0"
                                                            style="background-color:#C2410C;color:#fff;min-width:auto;line-height:1.3;">Revision Requested</span>
                                                    </div>
                                                </div>
                                            </a>
                                        @elseif ($note->kind === 'task_deadline_alert')
                                            @php
                                                $task = $note->task;
                                                $taskRoute = $role === 'director'
                                                    ? route('director.tasks.index', ['project_id' => $task->id_project])
                                                    : route('staff.tasks.index', ['project_id' => $task->id_project]);
                                                $balanceSeconds = (int) ($note->balance_seconds ?? 0);
                                                $isOverdue = $balanceSeconds < 0;
                                                $absSeconds = abs($balanceSeconds);
                                                $h = str_pad((string) floor($absSeconds / 3600), 2, '0', STR_PAD_LEFT);
                                                $m = str_pad((string) floor(($absSeconds % 3600) / 60), 2, '0', STR_PAD_LEFT);
                                                $s = str_pad((string) ($absSeconds % 60), 2, '0', STR_PAD_LEFT);
                                                $runningText = ($isOverdue ? '-' : '') . $h . ':' . $m . ':' . $s;
                                                $alertLabel = $isOverdue ? 'Overdue' : 'Due < 30m';
                                                $alertStyle = $isOverdue
                                                    ? 'background-color:#C2410C;color:#fff;min-width:auto;line-height:1.3;'
                                                    : 'background-color:#FFB42E;color:#fff;min-width:auto;line-height:1.3;';
                                            @endphp
                                            <a href="{{ $taskRoute }}"
                                                class="dashboard-notify-grid-item text-decoration-none text-reset d-block">
                                                <div class="bg-white rounded-2 p-2 text-black h-100 d-flex flex-column">
                                                    <p class="fw-semibold small mb-1 text-truncate" title="{{ ucwords($task->name ?? '-') }}">
                                                        {{ ucwords($task->name ?? '-') }}
                                                    </p>
                                                    <p class="small text-secondary mb-0 text-break" style="line-height:1.35;">
                                                        {{ ucwords($task->project?->name ?? 'Stand By') }}
                                                    </p>
                                                    <p class="small text-muted mb-2 mt-1" style="line-height:1.35;">
                                                        Running: <span class="fw-semibold">{{ $runningText }}</span>
                                                    </p>
                                                    <div class="d-flex gap-1 flex-wrap align-items-center mt-auto">
                                                        <span class="btn btn-sm rounded-4 border-0 task-meta-pill flex-shrink-0 align-self-start px-2 py-0"
                                                            style="{{ $alertStyle }}">{{ $alertLabel }}</span>
                                                    </div>
                                                </div>
                                            </a>
                                        @elseif ($note->kind === 'project_timeline_alert')
                                            @php
                                                $proj = $note->project;
                                                $projRoute = $role === 'director'
                                                    ? route('director.project.edit', $proj->id)
                                                    : route('staff.project.tasks.index', $proj->id);
                                                $balanceSeconds = (int) ($note->balance_seconds ?? 0);
                                                $isOverdue = $balanceSeconds < 0;
                                                $absSeconds = abs($balanceSeconds);
                                                $h = str_pad((string) floor($absSeconds / 3600), 2, '0', STR_PAD_LEFT);
                                                $m = str_pad((string) floor(($absSeconds % 3600) / 60), 2, '0', STR_PAD_LEFT);
                                                $s = str_pad((string) ($absSeconds % 60), 2, '0', STR_PAD_LEFT);
                                                $runningText = ($isOverdue ? '-' : '') . $h . ':' . $m . ':' . $s;
                                                $phase = $note->phase ?? 'end';
                                                $isStartPhase = $phase === 'start';
                                                if ($isStartPhase) {
                                                    $alertLabel = $isOverdue ? 'Start Overdue' : 'Starts < 24h';
                                                } else {
                                                    $alertLabel = $isOverdue ? 'Deadline Overdue' : 'Deadline < 24h';
                                                }
                                                $alertStyle = $isOverdue
                                                    ? 'background-color:#C2410C;color:#fff;min-width:auto;line-height:1.3;'
                                                    : 'background-color:#FFB42E;color:#fff;min-width:auto;line-height:1.3;';
                                                $timeLabel = $isStartPhase ? 'Start in' : 'Ends in';
                                            @endphp
                                            <a href="{{ $projRoute }}"
                                                class="dashboard-notify-grid-item text-decoration-none text-reset d-block">
                                                <div class="bg-white rounded-2 p-2 text-black h-100 d-flex flex-column">
                                                    <p class="fw-semibold small mb-1 text-truncate" title="{{ ucwords($proj->name ?? '-') }}">
                                                        {{ ucwords($proj->name ?? '-') }}
                                                    </p>
                                                    <p class="small text-secondary mb-0 text-break" style="line-height:1.35;">
                                                        Director: {{ ucwords($proj->director?->name ?? '-') }}
                                                    </p>
                                                    <p class="small text-muted mb-2 mt-1" style="line-height:1.35;">
                                                        {{ $timeLabel }}: <span class="fw-semibold">{{ $runningText }}</span>
                                                    </p>
                                                    <div class="d-flex gap-1 flex-wrap align-items-center mt-auto">
                                                        <span class="btn btn-sm rounded-4 border-0 task-meta-pill flex-shrink-0 align-self-start px-2 py-0"
                                                            style="{{ $alertStyle }}">{{ $alertLabel }}</span>
                                                    </div>
                                                </div>
                                            </a>
                                        @endif
                                    @endforeach
                                    </div>
                                @endif
                            </div>
                        </section>
                    </div>
                @endif
            </div>
        </div>

            {{-- Activity 
            <div class="card rounded-4 p-3 mt-4" style="max-width: full; border-color: #E0E0E0CE;">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-secondary text-white rounded-3 d-flex justify-content-center align-items-center"
                        style="width: 30px; height: 30px;">
                        <i class="bi bi-activity"></i>
                    </div>
                    <h5 class="ms-2 mb-0">Activity</h5>
                </div>
                <canvas id="activityChart" width="500" height="200"></canvas>
            </div> --}}
        </div>
    </div>
@endsection

@section('js')
    <script src=" {{ asset('build/js/main/dashboard.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabBtnsContainer = document.querySelectorAll('.tab-btns');

            tabBtnsContainer.forEach(container => {
                const buttons = Array.from(container.querySelectorAll('button'));
                if (buttons.length === 0) return;

                // Set first button active by default
                const firstBtn = buttons[0];
                firstBtn.classList.add('active');
                // Show its target
                const defaultTarget = firstBtn.getAttribute('data-target');
                if (defaultTarget) {
                    document.querySelectorAll('.tab-container').forEach(tc => tc.classList.add('d-none'));
                    const el = document.querySelector(defaultTarget);
                    if (el) el.classList.remove('d-none');
                }

                // Click handling: activate clicked button and show related tab
                buttons.forEach(btn => {
                    btn.addEventListener('click', function () {
                        buttons.forEach(b => b.classList.remove('active'));
                        this.classList.add('active');

                        const target = this.getAttribute('data-target');
                        if (!target) return;
                        document.querySelectorAll('.tab-container').forEach(tc => tc.classList.add('d-none'));
                        const targetEl = document.querySelector(target);
                        if (targetEl) targetEl.classList.remove('d-none');
                    });
                });
            });
        });
    </script>
@endsection

