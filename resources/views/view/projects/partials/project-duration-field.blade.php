<div class="col-12 col-md-4 pe-md-0" data-project-duration-wrap>
    <label class="form-label text-muted small mb-1" for="project-duration-display">Duration</label>
    <input type="text"
        class="form-control form-control-sm bg-light"
        id="project-duration-display"
        value="{{ $initialLabel ?? '—' }}"
        readonly
        tabindex="-1"
        aria-live="polite"
        autocomplete="off"
        title="Calendar span from start date to end date (months and remaining days)">
</div>
