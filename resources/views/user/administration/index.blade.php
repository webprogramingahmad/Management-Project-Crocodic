@extends('layouts.app')

@section('title')
    Administrations
@endsection

@section('css')

    <link rel="stylesheet" href="{{ asset('storage/css/main/administrations.css') }}">

@endsection

@section('content')
    <div class="card form-wrapper">
        <div class="mt-5 ms-5">
            <h4 class="fw-bold mb-4">Leave Submission</h4>
            <div class="mb-3">
                <label class="form-label">Leave Category</label>
                <select class="form-select">
                    <option selected>Leave Category</option>
                    <option value="1">Annual</option>
                    <option value="2">Sick</option>
                    <option value="3">Emergency</option>
                </select>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Date</label>
                    <input type="text" class="form-control" placeholder="Start" onfocus="this.type='date'">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <input type="text" class="form-control mt-md-4 mt-2" placeholder="End" onfocus="this.type='date'">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Description</label>
                <textarea class="form-control" rows="3" placeholder="description"></textarea>
            </div>
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Do you bring laptop? <br><small>(if there is a super urgent
                            matter)</small></label>
                    <div class="radio-container mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="bringLaptop" id="laptopYes">
                            <label class="form-check-label" for="laptopYes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="bringLaptop" id="laptopNo">
                            <label class="form-check-label" for="laptopNo">No</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Do you still be Contacted? <br><small>(if there is a super urgent
                            matter)</small></label>
                    <div class="radio-container mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="contacted" id="contactedYes">
                            <label class="form-check-label" for="contactedYes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="contacted" id="contactedNo">
                            <label class="form-check-label" for="contactedNo">No</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-center gap-2">
                <button class="btn btn-cancel">Cancel</button>
                <button class="btn btn-submit">Submit</button>
            </div>

        </div>
    </div>
@endsection