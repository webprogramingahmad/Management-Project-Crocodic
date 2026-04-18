@extends('layouts.app')

@section('title')
    Activity
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/activity.css') }}">

@endsection

@section('content')
    <div class="card gx-0 py-2 px-2" style="height: 800px; border-radius:15px; border-color:#E0E0E0CE">
        <div class="dropdown mt-2 mx-2">
            <form method="GET" class="d-flex align-items-center">
                <select name="month" class="form-select rounded-3 me-2 px-2 py-0" style="width: 150px; height: 35px; line-height:35px;">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>

                <select name="year" class="form-select rounded-3 me-2 px-2 py-0 " style="width: 120px; height: 35px; line-height:35px;">
                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>

                <button type="submit" class="btn btn-dark rounded-3 px-3 py-0" style="height: 35px; line-height:35px">Filter</button>
            </form>
        </div>
        <div class="tab-container-fluid mb-2 overflow-x-scroll">
            @if ($users->isEmpty())
                <div class="tab-container-fluid mb-2">
                    <div class="empty-state d-flex align-items-center justify-content-center">
                        <div class="d-block">
                            <div class="empty-icon mb-2 d-flex align-items-center justify-content-center">
                                <i class="bi bi-clipboard-x fs-1"></i>
                            </div>
                            <div class="empty-text">No employee yet</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row g-3 mt-1 mx-1">
                    @foreach ($users as $user)
                        <div class="col-12 col-md-6 col-xxl-4">
                            <div class="card border rounded-3 p-3 h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="{{ $user->avatar ? asset('storage/avatars/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=0D8ABC&color=fff' }}"
                                        class="rounded-circle me-3" width="60" height="60" alt="Avatar">
                                    <div>
                                        <h5 class="mb-0">{{ ucwords($user->name) }}</h5>
                                        <small class="text-muted">{{ ucwords($user->division->divisi) }}</small>
                                    </div>
                                </div>
                                <div class="row text-center mb-3">
                                    <div class="col-3">
                                        <div class="fw-semibold">Project</div>
                                        <div class="border rounded py-1">{{ $user->projects_joined_count }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="fw-semibold">Tasks done</div>
                                        <div class="border rounded py-1">{{ $user->completed_task_count }}</div>
                                    </div>
                                    <div class="col-5 border-start">
                                        <div class="fw-semibold">Leave entitlement</div>
                                        <div class="border rounded py-1">{{ $user->accepted_absent_count }}</div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="fw-semibold mb-1">Work hours</div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="progress position-relative"
                                            style="height: 20px; width: 75%; border-radius: 20px; overflow: hidden;">
                                            <div class="progress-bar bg-dark d-flex align-items-end justify-content-center pe-2"
                                                style="width: 0%; border-radius: 20px; padding-right: 8px;">
                                                <small class="text-white">0%</small>
                                            </div>
                                        </div>
                                        {{-- <div class="ms-2 d-flex align-items-center">
                                            <i class="bi bi-exclamation-circle-fill text-black-50 me-1" title="Over work"></i>
                                            <small class="text-muted">Over work</small>
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

@section('js')
    <script src=" {{ asset('storage/js/main/activity.js') }}"></script>
@endsection