<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        (function () {
            try {
                var t = localStorage.getItem('theme');
                if (t === 'dark' || t === 'light') {
                    document.documentElement.setAttribute('data-theme', t);
                } else {
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
    @yield('css')
    <link rel="stylesheet" href="{{ asset('build/css/global.css') }}">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
    @include('components.search')
    <div class="app-shell">
        <div class="app-shell__header">
            @include('components.header')
        </div>
        <div class="app-shell__body d-flex align-items-start w-100">
            <aside class="app-shell__sidebar d-none d-lg-flex flex-column flex-shrink-0">
                @include('components.sidebar')
            </aside>
            @include('components.sidebar-mobile')
            <main class="app-shell__main flex-grow-1 min-w-0 py-3 px-3">
                @yield('content')
            </main>
        </div>
    </div>
    {{-- Modal di luar <main> agar z-index di atas .modal-backdrop (main punya stacking context z-index:0) --}}
    @stack('modals')
    <script src="{{ asset('build/js/global.js') }}"></script>
    @yield('js')
</body>

</html>