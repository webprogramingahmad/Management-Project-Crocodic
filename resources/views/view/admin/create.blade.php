@extends('layouts.app')

@section('title')
    Create Account
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/admin-create.css') }}">
    <style>
        @media (prefers-color-scheme: dark) {
            #join-date::-webkit-calendar-picker-indicator {
                filter: invert(0.85);
                opacity: 0.9;
            }
        }

        html[data-theme="dark"] #join-date::-webkit-calendar-picker-indicator {
            filter: invert(0.85);
            opacity: 0.9;
        }
    </style>
@endsection

@section('content')
    <div class="card form-wrapper">
        <div class="mt-5 ms-5 mb-3">
            <form method="POST" action="{{ route('executive.accounts.store') }}">
                @csrf
                <h4 class="fw-bold mb-4">Create New Account</h4>
                <div class="row g-0 g-md-4 mb-5">
                    <div class="col-12 col-md-6 mb-0">
                        <div>
                            <label for="username" class="form-label mb-1">Username</label>
                            <input type="text" name="name" class="form-control mb-2 rounded-3" id="username"
                                placeholder="Santiago">
                        </div>
                        <div>
                            <label for="email" class="form-label mb-1">Email</label>
                            <input type="email" name="email" class="form-control mb-2 rounded-3" id="email"
                                placeholder="Santiago@email.com">
                        </div>
                        <div>
                            <label for="nik" class="form-label mb-1">NIK</label>
                            <input type="text" name="nik" class="form-control mb-2 rounded-3" id="nik"
                                placeholder="Enter NIK">
                        </div>
                        <div>
                            <label for="join-date" class="form-label mb-1">Join Date</label>
                            <input type="date" name="tgl_masuk" class="form-control mb-2 rounded-3" id="join-date">
                        </div>
                        <div>
                            <label for="employment-status" class="form-label mb-1">Employment Status</label>
                            <select name="id_status_sdm" id="employment-status" class="form-select mb-2 rounded-3" required>
                                <option value="" selected disabled>Select employment status</option>
                                @foreach ($employmentStatuses as $employmentStatus)
                                    <option value="{{ $employmentStatus->id }}">{{ $employmentStatus->status_sdm }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="position-relative">
                            <label for="password" class="form-label mb-1">Password</label>
                            <input type="password" name="password" class="form-control mb-2 rounded-3" id="password"
                                placeholder="Enter password">
                            <i class="bi bi-eye-slash position-absolute" id="togglePassword"
                                style="top: 35px; right: 20px; cursor: pointer;"></i>
                        </div>
                        <div class="position-relative">
                            <label for="confirm-password" class="form-label mb-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control mb-2 rounded-3"
                                id="confirm-password" placeholder="Confirm Password">
                            <i class="bi bi-eye-slash position-absolute" id="toggleConfirmPassword"
                                style="top: 35px; right: 20px; cursor: pointer;"></i>
                        </div>
                        <div>
                            <label for="role" class="form-label mb-1">Role</label>
                            <select name="id_role" id="role" class="form-select mb-2 rounded-3" required>
                                <option value="" selected disabled>Enter role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" data-role-key="{{ $role->role }}">{{ \App\Support\RoleDisplay::label($role->role ?? null) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="division-field-wrapper">
                            <label for="division" class="form-label mb-1">Division</label>
                            <select name="id_divisi" id="division" class="form-select mb-2 rounded-3" disabled
                                aria-disabled="true" title="Select Staff role to enable division">
                                <option value="">Select division</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->divisi }}</option>
                                @endforeach
                            </select>
                            <p id="division-field-hint" class="small text-muted mb-0">Select role first. Division is only required for staff (engineer).</p>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-dark admin-create-account-submit w-20 px-4 py-1 rounded-3">Create Account</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.getElementById('role');
        const divisionWrapper = document.getElementById('division-field-wrapper');
        const divisionSelect = document.getElementById('division');
        const divisionHint = document.getElementById('division-field-hint');
        function syncDivisionField() {
            if (!roleSelect || !divisionWrapper || !divisionSelect) return;
            const opt = roleSelect.options[roleSelect.selectedIndex];
            const key = opt ? (opt.getAttribute('data-role-key') || '') : '';
            if (key === 'staff') {
                divisionSelect.disabled = false;
                divisionSelect.removeAttribute('aria-disabled');
                divisionSelect.removeAttribute('title');
                divisionSelect.setAttribute('required', 'required');
                divisionSelect.classList.remove('opacity-50', 'bg-light');
                if (divisionHint) {
                    divisionHint.textContent = 'Required for staff (engineer).';
                }
            } else {
                divisionSelect.value = '';
                divisionSelect.disabled = true;
                divisionSelect.setAttribute('aria-disabled', 'true');
                divisionSelect.setAttribute('title', 'Executive/Director roles do not use operational division');
                divisionSelect.removeAttribute('required');
                divisionSelect.classList.add('opacity-50', 'bg-light');
                if (divisionHint) {
                    if (!key) {
                        divisionHint.textContent = 'Select role first. Division is only required for staff (engineer).';
                    } else {
                        divisionHint.textContent = 'Executive & Director do not select division. This field is disabled.';
                    }
                }
            }
        }
        if (roleSelect) {
            roleSelect.addEventListener('change', syncDivisionField);
            syncDivisionField();
        }

        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        if (togglePassword && password) {
            togglePassword.addEventListener('click', function () {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('bi-eye');
                this.classList.toggle('bi-eye-slash');
            });
        }

        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPassword = document.getElementById('confirm-password');
        if (toggleConfirmPassword && confirmPassword) {
            toggleConfirmPassword.addEventListener('click', function () {
                const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPassword.setAttribute('type', type);
                this.classList.toggle('bi-eye');
                this.classList.toggle('bi-eye-slash');
            });
        }
    });
    </script>
@endsection