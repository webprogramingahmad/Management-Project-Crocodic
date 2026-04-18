@extends('layouts.app')

@section('title')
    Projects
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('storage/css/main/project.css') }}">

@endsection

@section('content')
    <div class="card m-3" style="height: 800px;">
        <div class="d-flex mt-4 mx-5 justify-content-between align-items-center mb-3 gap-2 flex-wrap">
            <div class="d-flex gap-2 align-items-center">
                <form class="d-flex align-items-center search border rounded" method="GET" action="">
                    <button class="btn btn-link p-0 ms-2 text-secondary" type="submit" aria-label="Search">
                        <i class="fas fa-search"></i>
                    </button>
                    <input class="form-control border-0 bg-transparent small" type="search" id="search" name="search"
                        autocomplete="off" placeholder="Search Project" aria-label="Search project" value="{{ request('search') }}" />
                </form>
                <div class="dropdown ms-2">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="bi bi-sliders me-1"></i> Filter
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">All</a></li>
                        <li><a class="dropdown-item" href="#">In Progress</a></li>
                        <li><a class="dropdown-item" href="#">Completed</a></li>
                        <li><a class="dropdown-item" href="#">Delayed</a></li>
                    </ul>
                </div>
            </div>
            <a href="{{ route('project.create') }}" class="btn btn-dark rounded d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> Create Project
            </a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead class="fw-semibold text-uppercase" style="height: 30px;">
                    <tr>
                        <th class="text-center" style="width: 8%;" scope="col-1">#</th>
                        <th style="width: 28%;" scope="col">Project Name</th>
                        <th class="text-center" style="width: 16%;" scope="col">Start</th>
                        <th class="text-center" style="width: 16%;" scope="col">Deadline</th>
                        <th class="text-center" style="width: 16%;" scope="col">Director</th>
                        <th class="text-center" style="width: 8%;" scope="col">Level</th>
                        <th class="text-center" style="width: 8%;" scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- <tr>
                        <td class="text-center fw-semibold" style="width: 8%;">1</td>
                        <td style="width: 28%;" class="fw-semibold">Wordpress Plugin Update</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Nov 4, 2024</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Des 25, 2024</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Athena Cyntia</td>
                        <td class="text-center " style="width: 8%;"><span class="badge bg-danger">High</span></td>
                        <td class="text-center" style="width: 8%;"><span class="badge bg-info text-white">Running</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center fw-semibold" style="width: 8%;">2</td>
                        <td style="width: 28%;" class="fw-semibold">Wordpress Plugin Update</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Nov 4, 2024</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Des 25, 2024</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Athena Cyntia</td>
                        <td class="text-center" style="width: 8%;"><span class="badge bg-danger">High</span></td>
                        <td class="text-center" style="width: 8%;">
                            <span class=" badge bg-warning text-white">Maintenance</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center fw-semibold" style="width: 8%;">3</td>
                        <td style="width: 28%;" class="fw-semibold">Wordpress Plugin Update</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Nov 4, 2024</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Des 25, 2024</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Athena Cyntia</td>
                        <td class="text-center" style="width: 8%;"><span class="badge bg-danger">High</span></td>
                        <td class="text-center" style="width: 8%;"><span class="badge bg-info text-white">Running</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center fw-semibold" style="width: 8%;">4</td>
                        <td style="width: 28%;" class="fw-semibold">Wordpress Plugin Update</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Nov 4, 2024</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Des 25, 2024</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Athena Cyntia</td>
                        <td class="text-center" style="width: 8%;"><span class="badge bg-danger">High</span></td>
                        <td class="text-center" style="width: 8%;">
                            <span class=" badge bg-warning text-white">Maintenance</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center fw-semibold" style="width: 8%;">5</td>
                        <td style="width: 28%;" class="fw-semibold">Wordpress Plugin Update</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Nov 4, 2024</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Des 25, 2024</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Athena Cyntia</td>
                        <td class="text-center " style="width: 8%;"><span class="badge bg-danger">High</span></td>
                        <td class="text-center" style="width: 8%;"><span class="badge bg-light text-white">To do</span></td>
                    </tr>
                    <tr>
                        <td class="text-center fw-semibold" style="width: 8%;">6</td>
                        <td style="width: 28%;" class="fw-semibold">Wordpress Plugin Update</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Nov 4, 2024</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Des 25, 2024</td>
                        <td class="text-center fw-semibold" style="width: 16%;">Athena Cyntia</td>
                        <td class="text-center " style="width: 8%;"><span class="badge bg-danger">High</span></td>
                        <td class="text-center" style="width: 8%;"><span class="badge bg-light text-white">To do</span></td>
                    </tr> --}}
                </tbody>
            </table>
        </div>
    </div>
@endsection