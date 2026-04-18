@extends('layouts.app')

@section('title')
    Leave detail
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/administrations.css') }}">
    <style>
        span.adm-status-pill {
            display: inline-block;
            pointer-events: none;
            cursor: default;
            min-width: 5.5rem;
            text-align: center;
            font-weight: 500;
            font-size: 0.8125rem;
            line-height: 1.5;
            padding: 0.35rem 0.75rem;
        }

        span.adm-status-pill.adm-status--accept {
            background-color: #7DB546 !important;
            color: #ffffff !important;
        }

        span.adm-status-pill.adm-status--reject {
            background-color: #EA4949 !important;
            color: #ffffff !important;
        }

        span.adm-status-pill.adm-status--pending {
            background-color: #FFB42E !important;
            color: #ffffff !important;
        }

        span.adm-status-pill.adm-status--default {
            background-color: #6c757d !important;
            color: #ffffff !important;
        }

        .btn-adm-accept {
            background-color: #7DB546 !important;
            color: #ffffff !important;
            border: none !important;
            font-weight: 500 !important;
            font-size: 0.8125rem !important;
            line-height: 1.5 !important;
            padding: 0.35rem 0.75rem !important;
            border-radius: 0.375rem !important;
            box-shadow: none !important;
            text-shadow: none !important;
        }

        .btn-adm-accept:hover {
            background-color: #6fa03a !important;
            color: #ffffff !important;
        }

        .btn-adm-reject {
            background-color: #EA4949 !important;
            color: #ffffff !important;
            border: none !important;
            font-weight: 500 !important;
            font-size: 0.8125rem !important;
            line-height: 1.5 !important;
            padding: 0.35rem 0.75rem !important;
            border-radius: 0.375rem !important;
            box-shadow: none !important;
            text-shadow: none !important;
        }

        .btn-adm-reject:hover {
            background-color: #d63d3f !important;
            color: #ffffff !important;
        }

        .adm-detail-meta {
            border-radius: 12px;
            border: 1px solid #E0E0E0CE;
            background: #fafafa;
        }

        html[data-theme="dark"] .adm-detail-meta {
            background: rgba(255, 255, 255, 0.04);
            border-color: #3f3f46;
        }

        .adm-detail-yesno {
            font-size: 0.8125rem;
            font-weight: 600;
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
        }

        .adm-detail-yesno--yes {
            background: rgba(125, 181, 70, 0.15);
            color: #5a8a32;
        }

        .adm-detail-yesno--no {
            background: rgba(108, 117, 125, 0.12);
            color: #495057;
        }

        html[data-theme="dark"] .adm-detail-yesno--yes {
            background: rgba(125, 181, 70, 0.2);
            color: #a3d66a;
        }

        html[data-theme="dark"] .adm-detail-yesno--no {
            background: rgba(255, 255, 255, 0.06);
            color: #a1a1aa;
        }

        .adm-back-link {
            color: #495057;
            font-size: 0.9375rem;
            transition: color 0.15s ease;
        }

        .adm-back-link:hover {
            color: #111;
        }

        .adm-back-link__icon {
            width: 2.125rem;
            height: 2.125rem;
            border-radius: 10px;
            background: #f4f4f5;
            border: 1px solid #E0E0E0CE;
            font-size: 1rem;
            line-height: 1;
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .adm-back-link:hover .adm-back-link__icon {
            background: #ececed;
            border-color: #d0d0d0;
        }

        html[data-theme="dark"] .adm-back-link {
            color: #a1a1aa;
        }

        html[data-theme="dark"] .adm-back-link:hover {
            color: #fafafa;
        }

        html[data-theme="dark"] .adm-back-link__icon {
            background: rgba(255, 255, 255, 0.06);
            border-color: #3f3f46;
        }

        html[data-theme="dark"] .adm-back-link:hover .adm-back-link__icon {
            background: rgba(255, 255, 255, 0.1);
        }

        .adm-view-heading {
            color: #212529;
        }

        html[data-theme="dark"] .adm-view-heading {
            color: #f4f4f5 !important;
        }

        html[data-theme="dark"] .adm-view-subtitle {
            color: #a1a1aa !important;
        }
    </style>
@endsection

@php
    use App\Support\ProjectDuration;

    $role = Auth::user()->role->role;
    $administrationIndexUrl = $role === 'executive'
        ? route('executive.administration.index')
        : ($role === 'director'
            ? route('director.administration.index')
            : route('staff.administration.index'));
    $statusKey = strtolower((string) ($administration->status->name ?? ''));
    $statusMod = in_array($statusKey, ['accept', 'reject', 'pending'], true) ? $statusKey : 'default';
    $durationLabel = ProjectDuration::label(
        $administration->start_date?->format('Y-m-d'),
        $administration->end_date?->format('Y-m-d')
    );
    $submitterName = $administration->user?->name ?? '-';
    $submitterRole = $administration->user?->role?->role ?? '-';
@endphp

@section('content')
    <div class="container-fluid px-2 py-3">
        <div class="mx-auto" style="max-width: 880px;">
            <div class="card border-0 shadow-sm"
                style="border-radius: 15px; border: 1px solid #E0E0E0CE !important;">
                <div class="card-body p-4 p-md-5">
                    <a href="{{ $administrationIndexUrl }}"
                        class="adm-back-link d-inline-flex align-items-center gap-2 text-decoration-none mb-4">
                        <span class="adm-back-link__icon d-inline-flex align-items-center justify-content-center">
                            <i class="bi bi-arrow-left" aria-hidden="true"></i>
                        </span>
                        <span class="fw-semibold">Permission list</span>
                    </a>

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 pb-4 mb-2 border-bottom"
                        style="border-color: #E0E0E0CE !important;">
                        <div>
                            <h1 class="h4 fw-bold mb-1 adm-view-heading">Leave submission</h1>
                            <p class="text-muted small mb-0 adm-view-subtitle">Detail izin / absent</p>
                        </div>
                        <span
                            class="btn btn-sm rounded-2 border-0 adm-status-pill adm-status--{{ $statusMod }}">{{ ucfirst($administration->status->name ?? 'Pending') }}</span>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="adm-detail-meta p-3 h-100 d-flex gap-3">
                                <div class="text-secondary pt-1"><i class="bi bi-person-badge fs-5"></i></div>
                                <div class="min-w-0">
                                    <div class="text-muted text-uppercase small fw-semibold mb-1"
                                        style="font-size: 0.7rem; letter-spacing: 0.04em;">Submitted by</div>
                                    <div class="fw-semibold text-break">{{ ucwords($submitterName) }}</div>
                                    <div class="text-muted small">{{ \App\Support\RoleDisplay::label($submitterRole === '-' ? null : $submitterRole) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="adm-detail-meta p-3 h-100 d-flex gap-3">
                                <div class="text-secondary pt-1"><i class="bi bi-tag fs-5"></i></div>
                                <div class="min-w-0">
                                    <div class="text-muted text-uppercase small fw-semibold mb-1"
                                        style="font-size: 0.7rem; letter-spacing: 0.04em;">Category</div>
                                    <div class="fw-semibold">{{ $administration->category->name ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="adm-detail-meta p-3 h-100 d-flex gap-3">
                                <div class="text-secondary pt-1"><i class="bi bi-calendar-event fs-5"></i></div>
                                <div>
                                    <div class="text-muted text-uppercase small fw-semibold mb-1"
                                        style="font-size: 0.7rem; letter-spacing: 0.04em;">Start</div>
                                    <div class="fw-semibold">
                                        {{ $administration->start_date ? \Carbon\Carbon::parse($administration->start_date)->translatedFormat('d F Y') : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="adm-detail-meta p-3 h-100 d-flex gap-3">
                                <div class="text-secondary pt-1"><i class="bi bi-calendar-check fs-5"></i></div>
                                <div>
                                    <div class="text-muted text-uppercase small fw-semibold mb-1"
                                        style="font-size: 0.7rem; letter-spacing: 0.04em;">End</div>
                                    <div class="fw-semibold">
                                        {{ $administration->end_date ? \Carbon\Carbon::parse($administration->end_date)->translatedFormat('d F Y') : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="adm-detail-meta p-3 h-100 d-flex gap-3">
                                <div class="text-secondary pt-1"><i class="bi bi-hourglass-split fs-5"></i></div>
                                <div>
                                    <div class="text-muted text-uppercase small fw-semibold mb-1"
                                        style="font-size: 0.7rem; letter-spacing: 0.04em;">Duration</div>
                                    <div class="fw-semibold">{{ $durationLabel }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="text-muted text-uppercase small fw-semibold mb-2"
                            style="font-size: 0.7rem; letter-spacing: 0.04em;">Description</div>
                        <div class="rounded-3 p-3 border text-secondary"
                            style="border-color: #E0E0E0CE !important; min-height: 4rem; line-height: 1.55;">
                            @if ($administration->description)
                                {!! nl2br(e($administration->description)) !!}
                            @else
                                —
                            @endif
                        </div>
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 border-bottom"
                                style="border-color: #f1f3f5 !important;">
                                <span class="text-muted small fw-semibold"><i
                                        class="bi bi-laptop me-2"></i>Bring laptop</span>
                                <span
                                    class="adm-detail-yesno {{ $administration->bring_laptop ? 'adm-detail-yesno--yes' : 'adm-detail-yesno--no' }}">{{ $administration->bring_laptop ? 'Yes' : 'No' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 border-bottom"
                                style="border-color: #f1f3f5 !important;">
                                <span class="text-muted small fw-semibold"><i
                                        class="bi bi-chat-dots me-2"></i>Contactable</span>
                                <span
                                    class="adm-detail-yesno {{ $administration->contacted ? 'adm-detail-yesno--yes' : 'adm-detail-yesno--no' }}">{{ $administration->contacted ? 'Yes' : 'No' }}</span>
                            </div>
                        </div>
                    </div>

                    @if ($role === 'executive' && strtolower((string) ($administration->status->name ?? '')) === 'pending')
                        <div class="d-flex flex-wrap gap-2 justify-content-end pt-4 mt-2 border-top"
                            style="border-color: #E0E0E0CE !important;">
                            <form action="{{ route('executive.administrations.updateStatus', $administration->id) }}"
                                method="POST" class="d-inline flex-grow-1 flex-sm-grow-0">
                                @csrf
                                <input type="hidden" name="id_status" value="{{ $idAccept }}">
                                <button type="submit" class="btn btn-adm-accept w-100 rounded-2">Accept</button>
                            </form>
                            <form action="{{ route('executive.administrations.updateStatus', $administration->id) }}"
                                method="POST" class="d-inline flex-grow-1 flex-sm-grow-0">
                                @csrf
                                <input type="hidden" name="id_status" value="{{ $idReject }}">
                                <button type="submit" class="btn btn-adm-reject w-100 rounded-2">Reject</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
