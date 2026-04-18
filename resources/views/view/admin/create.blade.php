@extends('layouts.app')

@section('title')
    Create Account
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/admin-create.css') }}">
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
                            <label for="division" class="form-label mb-1">Division</label>
                            <select name="id_divisi" id="division" class="form-select mb-2 rounded-3">
                                <option selected disabled>Enter division</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->divisi }}</option>
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
                            <select name="id_role" id="role" class="form-select mb-2 rounded-3">
                                <option selected disabled>Enter role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ \App\Support\RoleDisplay::label($role->role ?? null) }}</option>
                                @endforeach
                            </select>
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