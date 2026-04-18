{{-- Light: logo.png | Dark: logo1.png — ukuran dibatasi CSS agar tetap di dalam header --}}
@php
    $variant = $variant ?? 'desktop';
@endphp
<span class="app-logo-wrap app-logo-wrap--{{ $variant }} d-inline-flex align-items-center">
    <img src="{{ asset('storage/images/logo.png') }}" class="app-logo app-logo-light" alt="Crocodic">
    <img src="{{ asset('storage/images/logo1.png') }}" class="app-logo app-logo-dark" alt="Crocodic">
</span>
