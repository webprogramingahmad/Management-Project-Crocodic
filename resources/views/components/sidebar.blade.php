@php
    $role = Auth::user()->role->role;
@endphp

<style>
    .sidebar-rect-wrapper {
        position: relative;
        height: auto;
        overflow: visible;
        border-right: 1px solid var(--border-color, #e0e0e0ce);
        padding: 15px;
        box-sizing: border-box;
        background-color: var(--bg-sidebar, #ffffff);
    }

    .sidebar {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px; /* Space between icons, adjusted for vertical layout */
    }

    .sidebar .btn {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--bg-sidebar-btn, #efefef); /* Light gray background for inactive icons */
        color: var(--text-muted, #7D7D7D); /* Dark gray color for inactive icons */
        border: none;
        transition: all 0.3s ease;
        margin-bottom: 15px;/*mengatur jarak antar icon*/   
    }

    .sidebar .btn:hover {
        background-color: var(--bg-sidebar-btn-active, #6FAEC9); /* Slightly darker on hover for inactive */
        color: #ffffff
    }

    .sidebar .btn.active,
    .sidebar .btn.btn-primary {
        background-color: var(--bg-sidebar-btn-active, #6FAEC9); /* Blue background for active icons */
        color: #ffffff; /* White color for active icons */
    }

    .sidebar .btn i,
    .sidebar .btn span {
        font-size: 1.2rem; /* Adjust icon size if needed */
    }

    /* Label di samping ikon — pure CSS (tetap akurat saat html { zoom }) */
    .sidebar .btn[data-sidebar-tip] {
        position: relative;
    }

    .sidebar .btn[data-sidebar-tip]::after {
        content: attr(data-sidebar-tip);
        position: absolute;
        left: calc(100% + 10px);
        top: 50%;
        transform: translateY(-50%);
        padding: 3px 10px;
        font-size: 0.95rem;
        border-radius: 6px;
        white-space: nowrap;
        min-width: 80px;
        max-width: 180px;
        text-align: center;
        overflow: hidden;
        text-overflow: ellipsis;
        background-color: var(--bg-sidebar-btn-active, #6faec9);
        color: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 0.15s ease, visibility 0.15s ease;
        z-index: 20;
    }

    .sidebar .btn[data-sidebar-tip]:hover::after,
    .sidebar .btn[data-sidebar-tip]:focus-visible::after {
        opacity: 1;
        visibility: visible;
    }

</style>

<div class="sidebar-rect-wrapper">
    <nav class="sidebar d-flex flex-column align-items-center gap-2">
    @if ($role === 'staff')
        <a href="{{ route('staff.dashboard.index') }}"
            class="btn {{ request()->routeIs('staff.dashboard.index') ? 'active' : '' }}"
            data-sidebar-tip="Dashboard" aria-label="Dashboard">
            <i class="bi bi-columns-gap"></i>
        </a>

        <a href="{{ route('staff.projects.index') }}"
            class="btn {{ request()->routeIs('staff.projects.index') ? 'active' : '' }}"
            data-sidebar-tip="Project" aria-label="Project">
            <i class="bi bi-kanban-fill"></i>
        </a>

        <a href="{{ route('staff.tasks.index') }}"
            class="btn {{ request()->routeIs('staff.project.tasks.index', 'staff.tasks.index') ? 'active' : '' }}"
            data-sidebar-tip="Tasks" aria-label="Tasks">
            <i class="bi bi-file-earmark-check-fill"></i>
        </a>

        <a href="{{ route('staff.activity.index') }}"
            class="btn {{ request()->routeIs('staff.activity.index') ? 'active' : '' }}"
            data-sidebar-tip="Activity" aria-label="Activity">
            <i class="bi bi-activity"></i>
        </a>

        <a href="{{ route('staff.administration.index') }}"
            class="btn {{ request()->routeIs('staff.administration.index', 'staff.administration.create', 'staff.administration.show') ? 'active' : '' }}"
            data-sidebar-tip="Permission" aria-label="Permission">
            <i class="fa-solid fa-book"></i>
        </a>

    @elseif($role === 'director')
        <a href="{{ route('director.dashboard.index') }}"
            class="btn {{ request()->routeIs('director.dashboard.index') ? 'active' : '' }}"
            data-sidebar-tip="Dashboard" aria-label="Dashboard">
            <i class="bi bi-columns-gap"></i>
        </a>

        <a href="{{ route('director.projects.index') }}"
            class="btn {{ request()->routeIs('director.projects.index', 'director.project.edit', 'director.project.create') ? 'active' : '' }}"
            data-sidebar-tip="Project" aria-label="Project">
            <i class="bi bi-kanban-fill"></i>
        </a>

        <a href="{{ route('director.tasks.index') }}"
            class="btn {{ request()->routeIs('director.tasks.index', 'director.project.tasks.index') ? 'active' : '' }}"
            data-sidebar-tip="Tasks" aria-label="Tasks">
            <i class="bi bi-file-earmark-check-fill"></i>
        </a>

        <a href="{{ route('director.activity.index') }}"
            class="btn {{ request()->routeIs('director.activity.index') ? 'active' : '' }}"
            data-sidebar-tip="Activity" aria-label="Activity">
            <i class="bi bi-activity"></i>
        </a>

        <a href="{{ route('director.administration.index') }}"
            class="btn {{ request()->routeIs('director.administration.index', 'director.administration.create', 'director.administration.show') ? 'active' : '' }}"
            data-sidebar-tip="Permission" aria-label="Permission">
            <i class="fa-solid fa-book"></i>
        </a>
    @else
        <a href="{{ route('executive.dashboard.index') }}"
            class="btn {{ request()->routeIs('executive.dashboard.index') ? 'active' : '' }}"
            data-sidebar-tip="Dashboard" aria-label="Dashboard">
            <i class="bi bi-columns-gap"></i>
        </a>

        <a href="{{ route('executive.projects.index') }}"
            class="btn {{ request()->routeIs('executive.projects.index', 'executive.projects.create', 'executive.project.edit') ? 'active' : '' }}"
            data-sidebar-tip="Project" aria-label="Project">
            <i class="bi bi-kanban-fill"></i>
        </a>

        <a href="{{ route('executive.tasks.index') }}"
            class="btn {{ request()->routeIs('executive.tasks.index', 'executive.project.tasks.index') ? 'active' : '' }}"
            data-sidebar-tip="Tasks" aria-label="Tasks">
            <i class="bi bi-file-earmark-check-fill"></i>
        </a>

        <a href="{{ route('executive.activity.index') }}"
            class="btn {{ request()->routeIs('executive.activity.index') ? 'active' : '' }}"
            data-sidebar-tip="Activity" aria-label="Activity">
            <i class="bi bi-activity"></i>
        </a>

        <a href="{{ route('executive.administration.index') }}"
            class="btn {{ request()->routeIs('executive.administration.index', 'executive.administration.create', 'executive.administration.show') ? 'active' : '' }}"
            data-sidebar-tip="Permission" aria-label="Permission">
            <i class="fa-solid fa-book"></i>
        </a>

        <a href="{{ route('executive.accounts.index') }}"
            class="btn {{ request()->routeIs('executive.accounts.index', 'executive.accounts.edit', 'executive.accounts.create', 'executive.accounts.show') ? 'active' : '' }}"
            data-sidebar-tip="Account" aria-label="Account">
            <i class="fa-solid fa-user-shield"></i>
        </a>
    @endif
    </nav>
</div>
