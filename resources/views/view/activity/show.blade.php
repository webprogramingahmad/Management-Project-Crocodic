@extends('layouts.app')

@section('title')
    Activity - {{ ucwords($me->name) }}
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/activity.css') }}">
    <style>
        .activity-card {
            display: flex;
            flex-direction: column;
            height: calc(var(--app-vh) - var(--app-header-height) - 2rem);
            max-height: calc(var(--app-vh) - var(--app-header-height) - 2rem);
            min-height: 240px;
            overflow: hidden;
        }

        @supports (height: 100dvh) {
            .activity-card {
                height: calc(var(--app-dvh) - var(--app-header-height) - 2rem);
                max-height: calc(var(--app-dvh) - var(--app-header-height) - 2rem);
            }
        }

        .activity-card .activity-filter-bar {
            flex-shrink: 0;
        }

        .activity-content-scroll {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .activity-content-scroll::-webkit-scrollbar {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="card activity-card gx-0 py-2 px-2" style="border-radius:15px; border-color:#E0E0E0CE">
        <div class="activity-filter-bar mt-2 mx-2 d-flex align-items-center justify-content-between gap-2 flex-wrap">
            <a href="{{ $backRoute }}" class="btn btn-outline-secondary rounded-3 px-3 py-0 d-inline-flex align-items-center gap-2"
                style="height: 35px; line-height:35px;">
                <i class="bi bi-arrow-left"></i> Back
            </a>

            <form method="GET" class="d-flex align-items-center">
                <select name="month" class="form-select rounded-3 me-2 px-2 py-0" style="width: 150px; height: 35px; line-height:35px;">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>

                <select name="year" class="form-select rounded-3 me-2 px-2 py-0" style="width: 120px; height: 35px; line-height:35px;">
                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>

                <button type="submit" class="btn btn-dark rounded-3 px-3 py-0" style="height: 35px; line-height:35px">Filter</button>
            </form>
        </div>
        <div class="tab-container-fluid mb-2 activity-content-scroll">
            @include('view.activity.partials.self', ['me' => $me])
        </div>
    </div>
@endsection

@section('js')
    <script src=" {{ asset('storage/js/main/activity.js') }}"></script>
@endsection
