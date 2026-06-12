@extends('layouts.app')

@section('title')
    Task Report — {{ ucwords($task->name) }}
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/project.css') }}">
    <style>
        .report-photo-thumb {
            width: 120px;
            height: 90px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.15s ease;
        }

        .report-photo-thumb:hover {
            transform: scale(1.03);
        }

        html[data-theme="dark"] .report-photo-thumb {
            border-color: rgba(255, 255, 255, 0.15);
        }

        .report-info-label {
            color: #6c757d;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 0.15rem;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid gx-0 px-1 py-1">
        <div class="mb-3">
            <a href="{{ route($reportRoute, $project->id) }}" class="btn btn-outline-secondary rounded-3 px-3 py-0 d-inline-flex align-items-center gap-2"
                style="height: 35px; line-height:35px;">
                <i class="bi bi-arrow-left"></i> Back to Project Report
            </a>
        </div>

        <div class="card border rounded-3 p-3 p-md-4 mb-3" style="border-color:#E0E0E0CE">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                <div>
                    <h4 class="fw-bold mb-1">{{ ucwords($task->name) }}</h4>
                    <div class="text-muted small">{{ ucwords($project->name) }}</div>
                </div>
                <div class="text-end">
                    <span class="badge bg-secondary rounded-pill mb-1">{{ $task->status?->status ?? '-' }}</span>
                    <div class="small text-muted">Level: {{ $task->difficulty?->difficulty ?? '-' }}</div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="report-info-label">Assignee</div>
                    <div class="fw-semibold">{{ ucwords($task->user?->name ?? '-') }}</div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="report-info-label">Created by</div>
                    <div class="fw-semibold">{{ ucwords($task->creator?->name ?? '-') }}</div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="report-info-label">On-time delivery</div>
                    <div class="fw-semibold">
                        @if ($onTime === true)
                            <span class="text-success"><i class="bi bi-check-circle me-1"></i>On time</span>
                        @elseif ($onTime === false)
                            <span class="text-danger"><i class="bi bi-x-circle me-1"></i>Late</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <div class="card border rounded-3 p-3 h-100" style="border-color:#E0E0E0CE">
                    <h6 class="fw-bold mb-2"><i class="bi bi-card-text me-1"></i>Description</h6>
                    <div class="small text-break" style="white-space: pre-wrap;">{{ trim((string) $task->description) !== '' ? $task->description : '—' }}</div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card border rounded-3 p-3 h-100" style="border-color:#E0E0E0CE">
                    <h6 class="fw-bold mb-2"><i class="bi bi-clock-history me-1"></i>Timeline</h6>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2">
                            <span class="text-muted">Created:</span>
                            {{ $task->created_at?->translatedFormat('d F Y, H:i') ?? '—' }}
                        </li>
                        <li class="mb-2">
                            <span class="text-muted">Started (In Progress):</span>
                            {{ $task->running_started_at?->translatedFormat('d F Y, H:i') ?? '—' }}
                        </li>
                        <li class="mb-2">
                            <span class="text-muted">Submitted to Review:</span>
                            {{ $task->running_review_at?->translatedFormat('d F Y, H:i') ?? '—' }}
                        </li>
                        <li>
                            <span class="text-muted">Last updated:</span>
                            {{ $task->updated_at?->translatedFormat('d F Y, H:i') ?? '—' }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-12">
                <div class="card border rounded-3 p-3" style="border-color:#E0E0E0CE">
                    <h6 class="fw-bold mb-2"><i class="bi bi-images me-1"></i>Work Evidence</h6>
                    @if ($task->photos->isEmpty())
                        <div class="text-muted small">Belum ada foto bukti kerja.</div>
                    @else
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($task->photos as $photo)
                                <a href="{{ $photo->url }}" target="_blank" rel="noopener noreferrer" title="{{ $photo->original_name }}">
                                    <img src="{{ $photo->url }}" alt="{{ $photo->original_name }}" class="report-photo-thumb">
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @if ($task->revisionCycles->isNotEmpty())
                <div class="col-12">
                    <div class="card border rounded-3 p-3" style="border-color:#E0E0E0CE">
                        <h6 class="fw-bold mb-2"><i class="bi bi-arrow-counterclockwise me-1"></i>Revision History</h6>
                        <div class="d-flex flex-column gap-2">
                            @foreach ($task->revisionCycles as $cycle)
                                <div class="border rounded-3 p-2 px-3">
                                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-1">
                                        <span class="fw-semibold small">Cycle {{ $cycle->cycle_number }}</span>
                                        <span class="text-muted small">
                                            {{ $cycle->entered_revision_at?->translatedFormat('d M Y, H:i') }}
                                            @if ($cycle->revision_hours)
                                                · {{ $cycle->revision_hours }}h allowance
                                            @endif
                                        </span>
                                    </div>
                                    @if ($cycle->notes)
                                        <div class="small text-break" style="white-space: pre-wrap;">{{ $cycle->notes }}</div>
                                    @else
                                        <div class="text-muted small">No revision notes.</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
