@props([
    'user',
    'statussdms',
    'lastgraduates',
    'formAction',
])

@php
    $employmentStatusIds = $statussdms->pluck('id')->all();
    $defaultEmploymentId = in_array($user->id_status_sdm, $employmentStatusIds, true) ? $user->id_status_sdm : '';
    $oldInput = session()->get('_old_input', []);
    if (array_key_exists('id_status_sdm', $oldInput)) {
        $selectedEmploymentId = $oldInput['id_status_sdm'];
    } else {
        $selectedEmploymentId = $defaultEmploymentId;
    }
@endphp

<form action="{{ $formAction }}" method="POST">
    @csrf
    @method('PUT')

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Validasi gagal.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="text-secondary mb-1">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ form_old('email', $user->email) }}">
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 position-relative">
            <label class="text-secondary mb-1">Password</label>
            <input type="password" name="password"
                class="form-control @error('password') is-invalid @enderror pr-5" placeholder="Kosongkan jika tidak diubah"
                id="password" autocomplete="new-password">
            <i class="bi bi-eye-slash position-absolute" id="togglePassword"
                style="top: 35px; right: 20px; cursor: pointer;"></i>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="text-secondary mb-1">NIK</label>
            <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror"
                value="{{ form_old('nik', $user->nik) }}">
            @error('nik')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="text-secondary mb-1">Link Telegram</label>
            <input type="text" name="link_tele" class="form-control @error('link_tele') is-invalid @enderror"
                value="{{ form_old('link_tele', $user->link_tele) }}">
            @error('link_tele')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="text-secondary mb-1">Status SDM</label>
            <select name="id_status_sdm" class="form-select @error('id_status_sdm') is-invalid @enderror">
                <option value=""
                    {{ $selectedEmploymentId === '' || $selectedEmploymentId === null ? 'selected' : '' }}>--pilih status--
                </option>
                @foreach ($statussdms as $status)
                    <option value="{{ $status->id }}"
                        {{ (string) $selectedEmploymentId === (string) $status->id ? 'selected' : '' }}>
                        {{ $status->status_sdm }}</option>
                @endforeach
            </select>
            @error('id_status_sdm')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="text-secondary mb-1">Alamat</label>
            <input type="text" name="alamat" class="form-control @error('alamat') is-invalid @enderror"
                value="{{ form_old('alamat', $user->alamat) }}">
            @error('alamat')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="text-secondary mb-1">No. HP</label>
            <input type="text" name="no_telp" class="form-control @error('no_telp') is-invalid @enderror"
                value="{{ form_old('no_telp', $user->no_telp) }}">
            @error('no_telp')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="text-secondary mb-1">Tanggal Lahir</label>
            <input type="date" name="tgl_lahir" class="form-control @error('tgl_lahir') is-invalid @enderror"
                value="{{ form_old('tgl_lahir', $user->tgl_lahir ? $user->tgl_lahir->format('Y-m-d') : '') }}">
            @error('tgl_lahir')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="text-secondary mb-1">Tanggal Masuk</label>
            <input type="date" name="tgl_masuk" class="form-control @error('tgl_masuk') is-invalid @enderror"
                value="{{ form_old('tgl_masuk', $user->tgl_masuk ? $user->tgl_masuk->format('Y-m-d') : '') }}">
            @error('tgl_masuk')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="text-secondary mb-1">Pendidikan Terakhir</label>
            <select name="id_graduate" class="form-select @error('id_graduate') is-invalid @enderror">
                @foreach ($lastgraduates as $graduate)
                    <option value="{{ $graduate->id }}"
                        {{ (string) form_old('id_graduate', $user->id_graduate) === (string) $graduate->id ? 'selected' : '' }}>
                        {{ $graduate->graduate }}</option>
                @endforeach
            </select>
            @error('id_graduate')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 text-center mt-4">
            <button type="submit" class="btn btn-dark px-5">Simpan</button>
        </div>
    </div>
</form>
