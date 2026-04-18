@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@section('css')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="{{ asset('storage/css/main/dashboard.css') }}">
@endsection

@section('content')
    <div class="row m-3">
        {{-- Bagian Kiri --}}
        <div class="col-lg-7">
            <div class="card" style="height: 800px;">
                <div class="card-body">
                    <div class="tab-btns d-flex mb-4 gap-2 overflow-x-auto flex-nowrap" style="white-space: nowrap;">
                        <button type="button" class="btn btn-primary active mb-1"
                            data-target="#tab-project">Project</button>
                        <button type="button" class="btn btn-outline-secondary ms-3 mb-1"
                            data-target="#tab-maintance">Maintence</button>
                        <button type="button" class="btn btn-outline-secondary ms-3 mb-1"
                            data-target="#tab-complete">Complate</button>
                    </div>

                    <div id="tab-project" class="tab-container overflow-scroll-container">
                        @include('components.empty-state', ['icon' => 'bi bi-file-earmark-fill', 'text' => 'No project yet'])
                        {{-- <div class="row g-3">
                            <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xxl-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center mb-2 gap-3">
                                            <img src="https://storage.googleapis.com/a1aa/image/a9420cc7-e438-4a16-1d83-5485344bca45.jpg"
                                                alt="Athena Cyntia, female UX Designer with brown hair and smiling face"
                                                class="user-avatar" />
                                            <div>
                                                <p class="card-title m-0">Athena Cyntia</p>
                                                <p class="card-subtitle m-0">UX Designer</p>
                                            </div>
                                        </div>
                                        <p class="card-text-strong">Working on Farm App :</p>
                                        <p class="card-text mb-2">Design landing & prototype page farm app</p>
                                        <div class="d-flex gap-2">
                                            <span class="card-priority priority-review">Review</span>
                                            <span class="card-priority priority-medium">Medium</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                    </div>

                    <div id="tab-maintance" class="tab-container overflow-scroll-container d-none">
                        @include('components.empty-state', ['icon' => 'bi bi-file-earmark-fill', 'text' => 'No maintance'])
                        {{-- <div class="row g-3">
                            <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xxl-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center mb-2 gap-3">
                                            <img src="https://storage.googleapis.com/a1aa/image/a9420cc7-e438-4a16-1d83-5485344bca45.jpg"
                                                alt="Athena Cyntia, female UX Designer with brown hair and smiling face"
                                                class="user-avatar" />
                                            <div>
                                                <p class="card-title m-0">Athena Cyntia</p>
                                                <p class="card-subtitle m-0">UX Designer</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                    </div>

                    <div id="tab-complete" class="tab-container overflow-scroll-container d-none">
                        @include('components.empty-state', ['icon' => 'bi bi-clipboard-x', 'text' => 'No completed'])
                        {{-- <div class="row g-3">
                            <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xxl-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center mb-2 gap-3">
                                            <img src="https://storage.googleapis.com/a1aa/image/d9867667-fcec-46be-22a9-dfcc03987e7a.jpg"
                                                alt="Kylo Finn, male Back End Developer with short hair and smiling face"
                                                class="user-avatar" />
                                            <div>
                                                <p class="card-title m-0">Kylo Finn</p>
                                                <p class="card-subtitle m-0">Back End Developer</p>
                                            </div>
                                        </div>
                                        <p class="card-text-strong">Working on Web Codelab :</p>
                                        <p class="card-text">Fix the wordpress in Project A : there's some bug
                                            when click account</p>
                                        <span class="card-priority priority-complete">Complete</span>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>

        {{-- Bagian Kanan --}}
        <div class="col-lg-5 mt-4 mt-lg-0 d-flex flex-column scroll-lg">
            <div class="d-sm-flex d-lg-block d-xxl-flex gap-3">
                {{-- Tasks --}}
                <section class="task-section p-3 rounded-4 mb-3 mb-lg-0 mb-lg-3 mb-xxl-0">
                    <div class="d-flex align-items-center gap-1 mb-2">
                        <i class="bi bi-file-earmark-check-fill"></i>
                        <h2 class="fs-6 fw-semibold m-0">Tasks</h2>
                    </div>
                    <div class="empty-state-right d-flex align-items-center justify-content-center">
                        <div class="empty-text">You have 0 tasks</div>
                    </div>
                    {{-- <div class="overflow-y-scroll">
                        <div class="bg-white rounded-2 p-2 mb-2 text-black">
                            <p class="fw-semibold small mb-1">Create filter to find data resource</p>
                            <p class="small mb-1 text-secondary">create button and id data sets will show</p>
                            <span class="task-badge-low">Low</span>
                        </div>
                        <div class="bg-white rounded-2 p-2 text-black">
                            <p class="fw-semibold small mb-1">Displaying and merging data</p>
                            <p class="small mb-1 text-secondary">merging data in web codelab, to make easy
                                access and more</p>
                            <span class="task-badge-medium">Medium</span>
                        </div>
                    </div> --}}
                </section>

                {{-- Project --}}
                <section class="project-section p-3 rounded-4">
                    <div class="d-flex align-items-center gap-1 mb-2">
                        <i class="bi bi-kanban-fill"></i>
                        <h2 class="fs-6 fw-semibold m-0">Project</h2>
                    </div>
                    <div class="empty-state-right d-flex align-items-center justify-content-center">
                        <div class="empty-text">There's 0 project</div>
                    </div>
                    {{-- <div class="overflow-y-scroll">
                        <div class="bg-white rounded-2 p-2 mb-2 text-black">
                            <p class="fw-semibold small mb-2">CODESHOP</p>
                            <p class="small mb-3">Create a web, to buy mod game GTA San Andreas must use
                                Dana/Paypal/Steam</p>
                            <div class="d-flex align-items-center justify-content-between mb-21">
                                <button class="btn-create text-center" type="button">On create</button>
                                <div class="project-avatars d-flex mb-2 justify-content-end">
                                    <img src="https://storage.googleapis.com/a1aa/image/9ffe32a0-5a05-4efc-ef74-995bb224b0f9.jpg"
                                        alt="User avatar 1 for project members" />
                                    <img src="https://storage.googleapis.com/a1aa/image/f9c4441f-182b-4186-b82a-e0aa04a8ca53.jpg"
                                        alt="User avatar 2 for project members" />
                                    <img src="https://storage.googleapis.com/a1aa/image/50a76d56-b51b-45c4-9bb8-39bbb6fabd30.jpg"
                                        alt="User avatar 3 for project members" />
                                    <img src="https://storage.googleapis.com/a1aa/image/469b4cea-186e-40ab-bbe5-ab172291d657.jpg"
                                        alt="User avatar 4 for project members" />
                                </div>
                            </div>
                        </div>
                    </div> --}}
                </section>

            </div>

            {{-- Activity --}}
            <div class="card shadow-sm rounded-4 p-3 mt-4" style="max-width: full;">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-secondary text-white rounded-3 d-flex justify-content-center align-items-center"
                        style="width: 30px; height: 30px;">
                        <i class="bi bi-activity"></i>
                    </div>
                    <h5 class="ms-2 mb-0">Activity</h5>
                </div>
                <canvas id="activityChart" width="500" height="200"></canvas>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src=" {{ asset('storage/js/main/dashboard.js') }}"></script>
@endsection