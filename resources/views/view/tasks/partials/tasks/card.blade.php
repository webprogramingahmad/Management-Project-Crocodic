@php
    $role = Auth::user()->role->role;
    $initial = $task->user?->name ? strtoupper(substr($task->user->name, 0, 2)) : '';
    $task->loadMissing(['status', 'difficulty', 'project', 'pendingOwnershipTransferRequest.toUser', 'pendingOwnershipTransferRequest.requestedBy']);
    $deadline = \App\Support\TaskRunningTimer::deadlineFor($task);
    $showRunningTimer = \App\Support\TaskRunningTimer::shouldShowTimer($task);
    $liveTimer = \App\Support\TaskRunningTimer::shouldShowLiveTimer($task);
    $frozenRemainMs = \App\Support\TaskRunningTimer::frozenRemainMsForReview($task);
    $timerChipClass = 'task-running-timer px-2 py-1';
    $progressBalanceSeconds = \App\Support\TaskRunningTimer::progressBalanceSeconds($task);
    $revisionCycleBalances = \App\Support\TaskRunningTimer::revisionCycleBalances($task);
    $latestRevisionNote = optional($task->revisionCycles->sortByDesc('cycle_number')->first())->notes;
    $pendingTransfer = $task->pendingOwnershipTransferRequest;
    $canRequestOwnership = \App\Support\TaskBoardAccess::canRequestOwnershipTransfer(Auth::user(), $task) && ! $pendingTransfer;
    $canDirectReassign = \App\Support\TaskBoardAccess::canDirectReassignOwnership(Auth::user(), $task) && ! $pendingTransfer;
    $canReviewOwnership = $pendingTransfer && \App\Support\TaskBoardAccess::canReviewOwnershipRequest(Auth::user(), $task);
    $isPendingOwnershipApplicant = $pendingTransfer
        && (string) $pendingTransfer->from_user_id === (string) Auth::id();
    $showOwnershipCardBadge = $pendingTransfer && ($canReviewOwnership || $isPendingOwnershipApplicant);
    if ($showRunningTimer) {
        if ($frozenRemainMs !== null) {
            $timerChipClass .= $frozenRemainMs > 0 ? ' task-running-timer--ok' : ' task-running-timer--late';
        } else {
            $timerChipClass .= ' task-running-timer--ok';
        }
    } else {
        $timerChipClass .= ' d-none';
    }
@endphp
<a href="#" class="task-link" data-bs-toggle="modal" data-bs-target="#detail-task" data-task-id="{{ $task->id }}"
    data-task-title="{{ ucwords($task->name) }}" data-task-status="{{ ucwords($task->status->status) }}"
    data-task-color="{{ $task->status->class ?? '' }}" data-task-projectname="{{ ucwords($task->project?->name ?? 'Stand By') }}"
    data-task-timeline="{{ $task->created_at?->format('d M Y') }}" data-task-user="{{ ucwords($task->user?->name) }}"
    data-task-avatar="{{ $task->user?->avatar ? asset('storage/avatars/' . $task->user->avatar) : $initial }}"
    data-task-difficulty="{{ ucwords($task->difficulty->difficulty) }}"
    data-task-colordiff="{{ $task->difficulty->class }}" data-task-project="{{ $task->project?->id ?? '' }}"
    data-task-role="{{ $role === 'staff' ? 'staff' : ($role === 'executive' ? 'executive' : 'director') }}"
    data-task-level="{{ $task->difficulty->difficulty }}" data-task-diffid="{{ $task->difficulty->id }}"
    data-task-description-json='@json($task->description)'
    data-task-revision-note='@json($latestRevisionNote)'
    data-task-owner-id="{{ $task->id_user }}"
    data-task-has-submissions="{{ $task->submissions->isNotEmpty() ? '1' : '0' }}"
    data-task-progress-balance-seconds="{{ $progressBalanceSeconds !== null ? $progressBalanceSeconds : '' }}"
    data-task-revision-cycles-json='@json($revisionCycleBalances)'
    data-task-can-request-ownership="{{ $canRequestOwnership ? '1' : '0' }}"
    data-task-can-direct-reassign="{{ $canDirectReassign ? '1' : '0' }}"
    data-task-can-review-ownership="{{ $canReviewOwnership ? '1' : '0' }}"
    data-task-pending-transfer-id="{{ $pendingTransfer?->id ?? '' }}"
    data-task-pending-transfer-to-user="{{ $pendingTransfer?->toUser ? ucwords($pendingTransfer->toUser->name) : '' }}"
    data-task-pending-transfer-to-user-id="{{ $pendingTransfer?->to_user_id ?? '' }}"
    data-task-pending-transfer-reason="{{ $pendingTransfer?->reason ?? '' }}"
    data-task-pending-transfer-requester="{{ $pendingTransfer?->requestedBy ? ucwords($pendingTransfer->requestedBy->name) : '' }}">
    <div class="d-flex justify-content-between align-items-center mb-2 gap-2">
        <h6 class="fw-bold mb-0 text-truncate flex-grow-1 min-w-0">{{ Str::limit(ucwords($task->name), 25) }}</h6>
        <div class="d-flex align-items-center gap-1 flex-shrink-0 ms-1">
            @if ($showOwnershipCardBadge)
                @if ($canReviewOwnership)
                    <span class="task-ownership-card-badge task-ownership-card-badge--review">Transfer review</span>
                @elseif ($isPendingOwnershipApplicant)
                    <span class="task-ownership-card-badge task-ownership-card-badge--waiting">Pending approval</span>
                @endif
            @endif
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
    </div>
    <p class="mb-2 text-muted small">{{ ucwords($task->project?->name ?? 'Stand By') }}</p>
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center small gap-2 flex-wrap">
            <div class="d-flex align-items-center text-muted gap-1">
                <i class="bi bi-calendar3"></i>
                <span>{{ $task->created_at->format('M d, Y') }}</span>
            </div>
            <span
                class="{{ $timerChipClass }}"
                @if ($liveTimer && $deadline) data-deadline="{{ $deadline->toIso8601String() }}" @endif
                @if ($frozenRemainMs !== null) data-frozen-ms="{{ $frozenRemainMs }}" @endif>--:--:--</span>
        </div>
        <span class="btn btn-sm rounded-2 border-0 task-meta-pill"
            style="background-color: {{ $task->difficulty->background_color }}; color: {{ $task->difficulty->text_color }};">{{ $task->difficulty->difficulty }}</span>
    </div>
</a>