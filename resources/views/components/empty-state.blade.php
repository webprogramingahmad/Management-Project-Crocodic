@props(['icon' => 'bi bi-file-earmark-fill', 'text' => 'No items'])

<div class="empty-state d-flex align-items-center justify-content-center">
    <div class="d-block text-center">
        <div class="empty-icon mb-2 d-flex align-items-center justify-content-center">
            <i class="{{ $icon }} empty-icon-i" aria-hidden="true"></i>
        </div>
        <div class="empty-text">{{ $text }}</div>
    </div>
</div>
