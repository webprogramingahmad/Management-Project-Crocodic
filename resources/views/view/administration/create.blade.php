@extends('layouts.app')

@section('title')
    Create Administrations
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('build/css/main/administrations.css') }}">
@endsection

@php
    $role = Auth::user()->role->role;
    $durationInitial = \App\Support\LeaveDuration::label(old('start_date'), old('end_date'));
@endphp

@section('content')
    <div class="card form-wrapper">
        <div class="m-5">
                    @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

            <h4 class="fw-bold mb-4">Leave Submission</h4>
            <form @if ($role === 'executive') action="{{ route('executive.administration.store') }}" @elseif ($role === 'director') action="{{ route('director.administration.store') }}" @else
            action="{{ route('staff.administration.store') }}" @endif method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Leave Category</label>
                    <select name="id_category" class="form-select">
                        <option value="" selected disabled>Pilih Absent</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row mx-0 gx-2 mb-3 align-items-end">
                    <div class="col-12 col-md-4 ps-md-0">
                        <label class="form-label text-muted small mb-1">Start</label>
                        <input type="text" name="start_date" class="form-control form-control-sm project-date-picker"
                            placeholder="Start" value="{{ old('start_date') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label text-muted small mb-1">End</label>
                        <input type="text" name="end_date" class="form-control form-control-sm project-date-picker"
                            placeholder="End" value="{{ old('end_date') }}">
                    </div>
                    @include('view.projects.partials.project-duration-field', ['initialLabel' => $durationInitial])
                </div>
                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="3" placeholder="description"></textarea>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Do you bring laptop? <br><small>(if there is a super urgent
                                matter)</small></label>
                        <div class="radio-container mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="bring_laptop" id="laptopYes" value="1">
                                <label class="form-check-label" for="laptopYes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="bring_laptop" id="laptopNo" value="0">
                                <label class="form-check-label" for="laptopNo">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Do you still be Contacted? <br><small>(if there is a super urgent
                                matter)</small></label>
                        <div class="radio-container mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="contacted" id="contactedYes" value="1">
                                <label class="form-check-label" for="contactedYes">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="contacted" id="contactedNo" value="0">
                                <label class="form-check-label" for="contactedNo">No</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-center gap-2">
                    <a @if ($role === 'executive') href="{{ route('executive.administration.index') }}" @elseif ($role === 'director') href="{{ route('director.administration.index') }}" @else
                    href="{{ route('staff.administration.index') }}" @endif class="btn btn-cancel px-5 py-0 rounded-3" style="border: 1px solid #E0E0E0CE; height: 35px; line-height: 35px;">Cancel</a>
                    <button type="submit" class="btn btn-submit administration-create-submit px-5 py-0 rounded-3"
                        style="background-color: black; color: #ffffff; height: 35px; line-height: 35px;">Submit</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    @include('view.administration.partials.leave-duration-script')
    @include('view.projects.partials.project-date-picker-script')
@endsection