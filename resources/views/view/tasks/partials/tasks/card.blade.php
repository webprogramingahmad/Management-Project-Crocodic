@php
    $role = Auth::user()->role->role;
    $initial = $task->user?->name ? strtoupper(substr($task->user->name, 0, 2)) : '';
    $task->loadMissing(['status', 'difficulty']);
    $deadline = \App\Support\TaskRunningTimer::deadlineFor($task);
    $showRunningTimer = \App\Support\TaskRunningTimer::shouldShowTimer($task);
@endphp
<style>
    /* Hilangkan style default link pada card task */
.task-link {
    text-decoration: none;      /* hilangkan garis bawah */
    color: inherit;             /* jangan pakai warna link */
    display: block;
}

/* Judul task */
.task-link h6 {
    color: #000000;             /* hitam */
    text-decoration: none;      /* pastikan tidak ada underline */
}

html[data-theme="dark"] .task-link h6 {
    color: #f4f4f5 !important;
}

html[data-theme="dark"] .task-link .text-muted {
    color: #a1a1aa !important;
}

/* Timer: chip metadata halus (selaras kartu putih — ref. Linear/Notion-style tags) */
.task-running-timer {
    font-variant-numeric: tabular-nums;
    min-width: 5.25rem;
    text-align: center;
    font-size: 0.6875rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    line-height: 1.35;
    border: 1px solid transparent;
    border-radius: 9999px;
    transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}
.task-running-timer--ok {
    background-color: #f1f5f9;
    border-color: #e2e8f0;
    color: #475569;
}
.task-running-timer--late {
    background-color: #fef2f2;
    border-color: #fecaca;
    color: #b91c1c;
}
html[data-theme="dark"] .task-running-timer--ok {
    background-color: rgba(255, 255, 255, 0.06);
    border-color: #3f3f46;
    color: #d4d4d8;
}
html[data-theme="dark"] .task-running-timer--late {
    background-color: rgba(220, 38, 38, 0.12);
    border-color: #7f1d1d;
    color: #fca5a5;
}

</style>
<a href="#" class="task-link" data-bs-toggle="modal" data-bs-target="#detail-task" data-task-id="{{ $task->id }}"
    data-task-title="{{ ucwords($task->name) }}" data-task-status="{{ ucwords($task->status->status) }}"
    data-task-color="{{ $task->status->class ?? '' }}" data-task-projectname="{{ ucwords($task->project?->name ?? 'Stand By') }}"
    data-task-timeline="{{ $task->created_at?->format('d M Y') }}" data-task-user="{{ ucwords($task->user?->name) }}"
    data-task-avatar="{{ $task->user?->avatar ? asset('storage/avatars/' . $task->user->avatar) : $initial }}"
    data-task-difficulty="{{ ucwords($task->difficulty->difficulty) }}"
    data-task-colordiff="{{ $task->difficulty->class }}" data-task-project="{{ $task->project?->id ?? '' }}"
    data-task-role="{{ $role === 'staff' ? 'staff' : ($role === 'executive' ? 'executive' : 'director') }}"
    data-task-level="{{ $task->difficulty->difficulty }}" data-task-diffid="{{ $task->difficulty->id }}"
    data-task-description-json='@json($task->description)'>
    <div class="d-flex justify-content-between align-items-start mb-2">
        <h6 class="fw-bold mb-1">{{ Str::limit(ucwords($task->name), 25) }}</h6>
        @if ($task->user?->avatar)
            <img id="avatarPreview" alt="Foto Profil" class="profile-pic rounded-circle"
                src="{{ asset('storage/avatars/' . $task->user->avatar) }}"
                style="width: 30px; height: 30px; object-fit: cover;" />
        @else
            <div id="avatarPreview" class="rounded-circle d-flex align-items-center justify-content-center fw-semibold"
                style="width: 30px; height: 30px; font-size: 10px; background-color: #0D8ABC; color: white;">
                {{ $initial }}
            </div>
        @endif
    </div>
    <p class="mb-2 text-muted small">{{ ucwords($task->project?->name ?? 'Stand By') }}</p>
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center small gap-2 flex-wrap">
            <div class="d-flex align-items-center text-muted gap-1">
                <i class="bi bi-calendar3"></i>
                <span>{{ $task->created_at->format('M d, Y') }}</span>
            </div>
            <span
                class="task-running-timer px-2 py-1 @unless ($showRunningTimer && $deadline) d-none @endunless @if ($showRunningTimer && $deadline) task-running-timer--ok @endif"
                @if ($showRunningTimer && $deadline) data-deadline="{{ $deadline->toIso8601String() }}" @endif>--:--:--</span>
        </div>
        <span class="btn btn-sm rounded-2 border-0 task-meta-pill"
            style="background-color: {{ $task->difficulty->background_color }}; color: {{ $task->difficulty->text_color }};">{{ $task->difficulty->difficulty }}</span>
    </div>
</a>