@php
    $role = Auth::user()->role->role;
@endphp

<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar">
    <div class="offcanvas-header border-bottom border-2 border-gray-200">
        <div class="d-flex align-items-center gap-2">
            @include('components.app-logo', ['variant' => 'sidebar'])
        </div>
        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body sidebar-mobile">
        @if ($role === 'staff')
            <a href="{{ route('staff.dashboard.index') }}" class="{{ request()->routeIs('staff.dashboard.index') ? 'active' : '' }}">
                <i class="bi bi-columns-gap"></i>
                <h5 class="mt-2 ms-1">Dashboard</h5>
            </a>

            <a href="{{ route('staff.projects.index') }}"
                class="{{ request()->routeIs('staff.projects.index') ? 'active' : '' }}">
                <i class="bi bi-kanban-fill"></i>
                <h5 class="mt-2 ms-1">Projects</h5>
            </a>

            <a href="{{ route('staff.tasks.index') }}"
                class="{{ request()->routeIs('staff.project.tasks.index', 'staff.tasks.index') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-check-fill"></i>
                <h5 class="mt-2 ms-1">Tasks</h5>
            </a>

            <a href="{{ route('staff.administration.index') }}"
                class="{{ request()->routeIs('staff.administration.index', 'staff.administration.create', 'staff.administration.show') ? 'active' : '' }}">
                <i class="fa-solid fa-book"></i>
                <h5 class="mt-2 ms-1">Administrations</h5>
            </a>

        @elseif($role === 'director')
            <a href="{{ route('director.dashboard.index') }}"
                class="{{ request()->routeIs('director.dashboard.index') ? 'active' : '' }}">
                <i class="bi bi-columns-gap"></i>
                <h5 class="mt-2 ms-1">Dashboard</h5>
            </a>

            <a href="{{ route('director.projects.index') }}"
                class="{{ request()->routeIs('director.projects.index', 'director.project.edit', 'director.project.create') ? 'active' : '' }}">
                <i class="bi bi-kanban-fill"></i>
                <h5 class="mt-2 ms-1">Projects</h5>
            </a>

            <a href="{{ route('director.tasks.index') }}"
                class="{{ request()->routeIs('director.tasks.index', 'director.project.tasks.index') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-check-fill"></i>
                <h5 class="mt-2 ms-1">Tasks</h5>
            </a>

            <a href="{{ route('director.administration.index') }}"
                class="{{ request()->routeIs('director.administration.index', 'director.administration.create', 'director.administration.show') ? 'active' : '' }}">
                <i class="fa-solid fa-book"></i>
                <h5 class="mt-2 ms-1">Administrations</h5>
            </a>

        @else
            <a href="{{ route('executive.dashboard.index') }}"
                class="{{ request()->routeIs('executive.dashboard.index') ? 'active' : '' }}">
                <i class="bi bi-columns-gap"></i>
                <h5 class="mt-2 ms-1">Dashboard</h5>
            </a>

            <a href="{{ route('executive.projects.index') }}"
                class="{{ request()->routeIs('executive.projects.index', 'executive.projects.create', 'executive.project.edit') ? 'active' : '' }}">
                <i class="bi bi-kanban-fill"></i>
                <h5 class="mt-2 ms-1">Project</h5>
            </a>

            <a href="{{ route('executive.tasks.index') }}"
                class="{{ request()->routeIs('executive.tasks.index', 'executive.project.tasks.index') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-check-fill"></i>
                <h5 class="mt-2 ms-1">Tasks</h5>
            </a>

            <a href="{{ route('executive.activity.index') }}"
                class="{{ request()->routeIs('executive.activity.index') ? 'active' : '' }}">
                <i class="bi bi-activity"></i>
                <h5 class="mt-2 ms-1">Activity</h5>
            </a>

            <a href="{{ route('executive.administration.index') }}"
                class="{{ request()->routeIs('executive.administration.index', 'executive.administration.create', 'executive.administration.show') ? 'active' : '' }}">
                <i class="fa-solid fa-book"></i>
                <h5 class="mt-2 ms-1">Administrations</h5>
            </a>

            <a href="{{ route('executive.accounts.index') }}"
                class="{{ request()->routeIs('executive.accounts.index', 'executive.accounts.edit', 'executive.accounts.create', 'executive.accounts.show') ? 'active' : '' }}">
                <i class="fa-solid fa-user-shield"></i>
                <h5 class="mt-2 ms-1">Executive</h5>
            </a>
        @endif
    </div>
</div>