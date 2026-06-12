<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Project Report — {{ $project->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
            line-height: 1.45;
            margin: 0;
            padding: 24px;
        }
        h1 { font-size: 18px; margin: 0 0 4px; }
        h2 { font-size: 13px; margin: 18px 0 8px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        h3 { font-size: 11px; margin: 0 0 6px; }
        .muted { color: #666; font-size: 9px; }
        .header-meta { margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #f3f3f3; font-size: 9px; text-transform: uppercase; }
        .text-center { text-align: center; }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .kpi-table td { text-align: center; font-weight: bold; font-size: 14px; }
        .kpi-table th { text-align: center; font-size: 9px; }
        .task-block {
            margin-top: 14px;
            padding: 10px;
            border: 1px solid #ddd;
            page-break-inside: avoid;
        }
        .task-block + .task-block { page-break-before: auto; }
        .photo-row img {
            width: 100px;
            height: 75px;
            object-fit: cover;
            border: 1px solid #ccc;
            margin: 0 6px 6px 0;
        }
        .revision-box {
            border: 1px solid #eee;
            padding: 6px;
            margin-bottom: 6px;
            background: #fafafa;
        }
        .label { font-size: 8px; color: #888; text-transform: uppercase; margin-bottom: 2px; }
        .progress-bar {
            background: #e9ecef;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 4px;
        }
        .progress-fill {
            background: #198754;
            height: 8px;
        }
    </style>
</head>
<body>
    <div class="header-meta">
        <h1>Project Report</h1>
        <div style="font-size: 14px; font-weight: bold;">{{ ucwords($project->name) }}</div>
        <div class="muted">
            Generated: {{ $generatedAt->translatedFormat('d F Y, H:i') }}
            &nbsp;|&nbsp; Director: {{ ucwords($project->director?->name ?? '-') }}
            &nbsp;|&nbsp; Period: {{ $project->start_date?->translatedFormat('d M Y') }} – {{ $project->end_date?->translatedFormat('d M Y') }}
        </div>
        <div class="muted">
            Status: {{ $project->status?->status ?? '-' }}
            &nbsp;|&nbsp; Level: {{ $project->difficulty?->difficulty ?? '-' }}
            &nbsp;|&nbsp; Completion: {{ $progressPercent }}% ({{ $completedCount }}/{{ $totalTasks }} tasks)
        </div>
        <div class="progress-bar" style="max-width: 300px;">
            <div class="progress-fill" style="width: {{ $progressPercent }}%;"></div>
        </div>
    </div>

    <h2>Summary</h2>
    <table class="kpi-table">
        <thead>
            <tr>
                <th>Total Tasks</th>
                <th>Complete</th>
                <th>In Progress</th>
                <th>To Do</th>
                <th>On-time Rate</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $totalTasks }}</td>
                <td class="text-success">{{ $completedCount }}</td>
                <td>{{ $activeCount }}</td>
                <td>{{ $countByClass['todo'] }}</td>
                <td>{{ $onTimePercent }}%</td>
            </tr>
        </tbody>
    </table>

    <h2>Team Contribution</h2>
    @if ($memberStats->isEmpty())
        <p class="muted">No tasks on this project.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Member</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Complete</th>
                    <th class="text-center">Active</th>
                    <th class="text-center">On time</th>
                    <th class="text-center">Late</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($memberStats as $stat)
                    <tr>
                        <td>
                            <strong>{{ ucwords($stat->user?->name ?? 'Unassigned') }}</strong><br>
                            <span class="muted">{{ ucwords($stat->user?->division?->divisi ?? ($stat->user?->role?->role ?? '-')) }}</span>
                        </td>
                        <td class="text-center">{{ $stat->total }}</td>
                        <td class="text-center">{{ $stat->completed }}</td>
                        <td class="text-center">{{ $stat->active }}</td>
                        <td class="text-center text-success">{{ $stat->on_time }}</td>
                        <td class="text-center text-danger">{{ $stat->late }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Task Overview</h2>
    @if ($tasks->isEmpty())
        <p class="muted">No tasks.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Assignee</th>
                    <th>Created by</th>
                    <th>Status</th>
                    <th>Level</th>
                    <th class="text-center">On time</th>
                    <th class="text-center">Evidence</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tasks as $task)
                    @php $timing = \App\Support\TaskRunningTimer::reviewedOnTime($task); @endphp
                    <tr>
                        <td><strong>{{ ucwords($task->name) }}</strong></td>
                        <td>{{ ucwords($task->user?->name ?? '-') }}</td>
                        <td>{{ ucwords($task->creator?->name ?? '-') }}</td>
                        <td>{{ $task->status?->status ?? '-' }}</td>
                        <td>{{ $task->difficulty?->difficulty ?? '-' }}</td>
                        <td class="text-center">
                            @if ($timing === true) Yes
                            @elseif ($timing === false) No
                            @else —
                            @endif
                        </td>
                        <td class="text-center">{{ $task->photos->count() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2 style="page-break-before: always;">Task Details</h2>
    @foreach ($tasks as $task)
        @php
            $onTime = \App\Support\TaskRunningTimer::reviewedOnTime($task);
        @endphp
        <div class="task-block">
            <h3>{{ ucwords($task->name) }}</h3>
            <table style="margin-bottom: 8px;">
                <tr>
                    <td width="25%"><span class="label">Assignee</span><br>{{ ucwords($task->user?->name ?? '-') }}</td>
                    <td width="25%"><span class="label">Created by</span><br>{{ ucwords($task->creator?->name ?? '-') }}</td>
                    <td width="25%"><span class="label">Status</span><br>{{ $task->status?->status ?? '-' }}</td>
                    <td width="25%"><span class="label">On-time</span><br>
                        @if ($onTime === true)<span class="text-success">On time</span>
                        @elseif ($onTime === false)<span class="text-danger">Late</span>
                        @else — @endif
                    </td>
                </tr>
            </table>

            <div class="label">Description</div>
            <p style="margin: 0 0 8px; white-space: pre-wrap;">{{ trim((string) $task->description) !== '' ? $task->description : '—' }}</p>

            <div class="label">Timeline</div>
            <table>
                <tr>
                    <td>Created: {{ $task->created_at?->translatedFormat('d M Y, H:i') ?? '—' }}</td>
                    <td>Started: {{ $task->running_started_at?->translatedFormat('d M Y, H:i') ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Review: {{ $task->running_review_at?->translatedFormat('d M Y, H:i') ?? '—' }}</td>
                    <td>Updated: {{ $task->updated_at?->translatedFormat('d M Y, H:i') ?? '—' }}</td>
                </tr>
            </table>

            <div class="label" style="margin-top: 6px;">Work Evidence</div>
            @if ($task->photos->isEmpty())
                <p class="muted">No work evidence photos.</p>
            @else
                <div class="photo-row">
                    @foreach ($task->photos as $photo)
                        @php $dataUri = \App\Support\ReportPhotoEmbed::dataUri($photo->path); @endphp
                        @if ($dataUri)
                            <img src="{{ $dataUri }}" alt="{{ $photo->original_name }}">
                        @endif
                    @endforeach
                </div>
            @endif

            @if ($task->revisionCycles->isNotEmpty())
                <div class="label" style="margin-top: 6px;">Revision History</div>
                @foreach ($task->revisionCycles as $cycle)
                    <div class="revision-box">
                        <strong>Cycle {{ $cycle->cycle_number }}</strong>
                        <span class="muted">
                            — {{ $cycle->entered_revision_at?->translatedFormat('d M Y, H:i') }}
                            @if ($cycle->revision_hours) ({{ $cycle->revision_hours }}h allowance) @endif
                        </span>
                        <div style="margin-top: 4px; white-space: pre-wrap;">{{ $cycle->notes ?: 'No revision notes.' }}</div>
                    </div>
                @endforeach
            @endif
        </div>
    @endforeach
</body>
</html>
