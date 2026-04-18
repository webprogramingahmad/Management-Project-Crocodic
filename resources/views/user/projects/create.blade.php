@extends('layouts.app')

@section('title')
    Project Create
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('storage/css/main/project-create.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
    <div class="container">
        <form action="">
            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <div class="form-container h-100">
                        <h5 class="fw-bold mb-4">New Project</h5>
                        <div class="mb-3">
                            <label class="form-label text-muted">Project</label>
                            <input type="text" class="form-control" placeholder="Project name...">
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label text-muted">Date</label>
                                <input type="text" class="form-control" placeholder="Start" onfocus="type='date'">
                            </div>
                            <div class="col d-flex align-items-end">
                                <input type="text" class="form-control" placeholder="END" onfocus="type='date'">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Level Project</label>
                            <input type="hidden" name="level" id="input-priority" value="medium">
                            <div class="dropdown">
                                <button class="form-select text-start" type="button" id="dropdownLevel"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <span id="selected-priority">-- Select Level --</span>
                                </button>
                                <ul class="dropdown-menu w-100" aria-labelledby="dropdownLevel">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="setPriority('low')">
                                            <span class="card-priority priority-low">Low</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="setPriority('medium')">
                                            <span class="card-priority priority-medium">Medium</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="setPriority('high')">
                                            <span class="card-priority priority-high">High</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div>
                            <label class="form-label text-muted">About Project</label>
                            <textarea class="form-control" rows="4" placeholder="About project..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="form-container h-100">
                        <h5 class="fw-bold mb-4">SDM</h5>
                        <div class="mb-3">
                            <label class="form-label text-muted">Project Director</label>
                            <select class="form-select">
                                <option disabled selected>-- Pilih --</option>
                                <option>Athena</option>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label text-muted">Engineer Web</label>
                                <select class="form-select">
                                    <option selected disabled>-- Pilih --</option>
                                    <option>Bagas</option>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label text-muted">Analis</label>
                                <select class="form-select">
                                    <option selected disabled>-- Pilih --</option>
                                    <option>Septian</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label text-muted">Engineer Android</label>
                                <select class="form-select">
                                    <option selected disabled>-- Pilih --</option>
                                    <option>Ahmad</option>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label text-muted">Content Creator</label>
                                <select class="form-select">
                                    <option selected disabled>-- Pilih --</option>
                                    <option>Bobi</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label text-muted">Engineer IOS</label>
                                <select class="form-select">
                                    <option selected disabled>-- Pilih --</option>
                                    <option>Riko</option>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label text-muted">Copywriter</label>
                                <select class="form-select">
                                    <option selected disabled>-- Pilih --</option>
                                    <option>Agus</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">UI/UX</label>
                            <select class="form-select">
                                <option selected disabled>-- Pilih --</option>
                                <option>Riko</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted">Tester</label>
                            <select class="form-select">
                                <option selected disabled>-- Pilih --</option>
                                <option>Rani</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-3">
                    <a href="{{ route('projects') }}" class="btn btn-cancel px-4 py-2 rounded-3">Cancel</a>
                    <button type="submit" class="btn btn-create px-4 py-2 rounded-3">Create</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('js')
    <script src=" {{ asset('storage/js/main/project-create.js') }}"></script>
@endsection