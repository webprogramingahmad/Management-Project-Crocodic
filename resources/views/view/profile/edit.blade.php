@extends('layouts.app')

@section('title')
    Edit Profile {{ $user->name }}
@endsection

@section('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('build/css/main/profile-detail.css') }}">
@endsection

@section('content')
    <div class="container-fluid p-1">
        <div class="card" style="border-radius: 15px; border-color: #E0E0E0CE;">
            <div class= "card-body p-5">
                <div class="profile-card">
                    <div class="d-flex mb-5" >
                        <img src="{{ $user->avatar ? asset('storage/avatars/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=0D8ABC&color=fff' }}"
                            class="profile-avatar" alt="User Photo" style="height: 200px; width: 200px; border-radius: 15px">
                        <div class="font-montserrat mx-3">
                            <h5 class="mb-0 fw-bold fs-4">{{ Str::ucfirst($user->name) }}</h5>
                        <p class="fw-normal text-secondary mb-1 fs-6">
                                                    @if ($role === 'staff')
                                                        {{ Str::ucfirst(Auth::user()->division->divisi) }}
                                                    @else
                                                        {{ \App\Support\RoleDisplay::label(Auth::user()->role->role ?? null) }}
                                                    @endif
                                </p>
                            <div class="d-flex ">
                                <button class="btn btn-sm btn-primary mb-1 mb-md-0 me-md-2" style="height: 30px" id="openAvatarModalBtn">Upload
                                    Picture</button>
                                @if ($role === 'executive')
                                <form action="{{ route('executive.accounts.destroy.avatar', $user->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin hapus avatar?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-light border" style="height: 30px">Deleted Picture</button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>

            <form @if ($role === 'staff') action="{{ route('staff.profile.update', $user->id) }}" @elseif($role === 'executive')
            action="{{ route('executive.profile.update', $user->id) }}" @else
                action="{{ route('director.profile.update', $user->id) }}" @endif method="POST">
                @csrf
                @method('PUT')
                
                @php
                    $oldInput = session()->get('_old_input', []);
                    $emailValue = array_key_exists('email', $oldInput) ? $oldInput['email'] : $user->email;
                    $nikValue = array_key_exists('nik', $oldInput) ? $oldInput['nik'] : $user->nik;
                    $linkTeleValue = array_key_exists('link_tele', $oldInput) ? $oldInput['link_tele'] : $user->link_tele;
                    $alamatValue = array_key_exists('alamat', $oldInput) ? $oldInput['alamat'] : $user->alamat;
                    $noTelpValue = array_key_exists('no_telp', $oldInput) ? $oldInput['no_telp'] : $user->no_telp;
                    $tglLahirValue = array_key_exists('tgl_lahir', $oldInput) ? $oldInput['tgl_lahir'] : ($user->tgl_lahir ? $user->tgl_lahir->format('Y-m-d') : '');
                    $tglMasukValue = array_key_exists('tgl_masuk', $oldInput) ? $oldInput['tgl_masuk'] : ($user->tgl_masuk ? $user->tgl_masuk->format('Y-m-d') : '');
                    $graduateValue = array_key_exists('id_graduate', $oldInput) ? $oldInput['id_graduate'] : $user->id_graduate;
                @endphp
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-secondary mb-1">Email</label>
                        <input type="email" name='email' class="form-control @error('email') is-invalid @enderror"
                            value="{{ $emailValue }}">
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 position-relative">
                        <label class="text-secondary mb-1">Password</label>
                        <input type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror pr-5" placeholder="********"
                            id="password">
                        <i class="bi bi-eye-slash position-absolute" id="togglePassword"
                            style="top: 35px; right: 20px; cursor: pointer;"></i>
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="text-secondary mb-1">NIK</label>
                        <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror"
                            value="{{ $nikValue }}">
                        @error('nik')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="text-secondary mb-1">Link Telegram</label>
                        <input type="text" name="link_tele"
                            class="form-control @error('link_tele') is-invalid @enderror"
                            value="{{ $linkTeleValue }}">
                        @error('link_tele')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    @php
                        $employmentStatusIds = $statussdms->pluck('id')->all();
                        $defaultEmploymentId = in_array($user->id_status_sdm, $employmentStatusIds, true) ? $user->id_status_sdm : '';
                        $selectedEmploymentId = array_key_exists('id_status_sdm', $oldInput) ? $oldInput['id_status_sdm'] : $defaultEmploymentId;
                    @endphp
                    <div class="col-md-6">
                        <label class="text-secondary mb-1">Status SDM</label>
                        <select name="id_status_sdm" class="form-select @error('id_status_sdm') is-invalid @enderror">
                            <option value="" {{ $selectedEmploymentId === '' || $selectedEmploymentId === null ? 'selected' : '' }}>--pilih status--</option>
                            @foreach($statussdms as $status)
                                <option value="{{ $status->id }}" {{ (string) $selectedEmploymentId === (string) $status->id ? 'selected' : '' }}>{{ $status->status_sdm }}</option>
                            @endforeach
                        </select>
                        @error('id_status_sdm')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="text-secondary mb-1">Alamat</label>
                        <input type="text" name="alamat" class="form-control @error('alamat') is-invalid @enderror"
                            value="{{ $alamatValue }}">
                        @error('alamat')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="text-secondary mb-1">No. HP</label>
                        <input type="text" name="no_telp" class="form-control @error('no_telp') is-invalid @enderror"
                            value="{{ $noTelpValue }}">
                        @error('no_telp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="text-secondary mb-1">Tanggal Lahir</label>
                        <input type="date" name="tgl_lahir"
                            class="form-control @error('tgl_lahir') is-invalid @enderror"
                            value="{{ $tglLahirValue }}">
                        @error('tgl_lahir')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="text-secondary mb-1">Tanggal Masuk</label>
                        <input type="date" name="tgl_masuk"
                            class="form-control @error('tgl_masuk') is-invalid @enderror"
                            value="{{ $tglMasukValue }}">
                        @error('tgl_masuk')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="text-secondary mb-1">Pendidikan Terakhir</label>
                        <select name="id_graduate" class="form-select @error('id_graduate') is-invalid @enderror">
                            @foreach($lastgraduates as $graduate)
                                <option value="{{ $graduate->id }}" {{ (string) $graduateValue === (string) $graduate->id ? 'selected' : '' }}>
                                    {{ $graduate->graduate }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_graduate')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 text-center mt-4">
                        <button type="submit" class="btn btn-dark px-5 profile-edit-submit">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>
</div>
@endsection

{{-- Modal di luar <main> (layouts/app @stack('modals')) agar file input & backdrop tidak terblokir stacking context --}}
@push('modals')
    <div class="modal fade" id="avatarInputModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Avatar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <img id="avatarPreviewSmall"
                            src="{{ $user->avatar ? asset('storage/avatars/' . $user->avatar) : 'https://via.placeholder.com/150' }}"
                            alt="{{ ucwords($user->name) }}"
                            style="width:150px; height:150px; object-fit:cover; border-radius:8px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="avatarFileInput">Choose image</label>
                        <input type="file" id="avatarFileInput" accept="image/*" class="form-control">
                    </div>

                    <div class="alert alert-info small">
                        Setelah memilih gambar → kamu akan dibawa ke layar crop. Setelah crop, kembali ke modal ini
                        untuk upload.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" id="btnUploadAvatar" class="btn btn-primary" disabled>Upload</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cropperModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crop Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="cropper-container"
                        style="width:100%; height:min(65vh, 700px); min-height:320px; display:flex; align-items:center; justify-content:center; background:#f8f9fa; overflow:hidden;">
                        <img id="imageToCrop" style="display:block;">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" id="btnCropUse" class="btn btn-primary">Crop & Use Image</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endpush

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <script src=" {{ asset('build/js/main/profile-detail.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const uploadUrl = '{{ route("upload.avatar", $user->id) }}';
            const MAX_UPSCALE = 2.0;
            const OUTPUT_SIZE = 800;
            const OUTPUT_TYPE = "image/jpeg";
            const OUTPUT_QUALITY = 0.9;

            const openBtn = document.getElementById("openAvatarModalBtn");
            const inputModalEl = document.getElementById("avatarInputModal");
            const avatarFileInput = document.getElementById("avatarFileInput");
            const avatarPreviewSmall = document.getElementById("avatarPreviewSmall");
            const btnUploadAvatar = document.getElementById("btnUploadAvatar");

            const cropperModalEl = document.getElementById("cropperModal");
            const cropperContainer = document.getElementById("cropper-container");
            const imageToCrop = document.getElementById("imageToCrop");
            const btnCropUse = document.getElementById("btnCropUse");

            const bsInputModal = new bootstrap.Modal(inputModalEl);
            const bsCropperModal = new bootstrap.Modal(cropperModalEl);

            let cropper = null;
            let croppedBlob = null;
            let lastObjectUrl = null;

            function revokeLastObjectURL() {
                if (lastObjectUrl) {
                    try {
                        URL.revokeObjectURL(lastObjectUrl);
                    } catch (e) { }
                    lastObjectUrl = null;
                }
            }

            function resetInputModalState() {
                avatarFileInput.value = "";
                croppedBlob = null;
                btnUploadAvatar.disabled = true;
            }

            openBtn?.addEventListener("click", () => {
                resetInputModalState();
                bsInputModal.show();
            });

            avatarFileInput?.addEventListener("change", function (e) {
                const file = e.target.files && e.target.files[0];
                if (!file) return;

                revokeLastObjectURL();

                lastObjectUrl = URL.createObjectURL(file);
                openCropper(lastObjectUrl);
            });

            function openCropper(objectUrl) {
                imageToCrop.src = objectUrl;

                bsCropperModal.show();

                cropperModalEl.addEventListener(
                    "shown.bs.modal",
                    function onShown() {
                        cropperModalEl.removeEventListener("shown.bs.modal", onShown);

                        if (!imageToCrop.complete || !imageToCrop.naturalWidth) {
                            imageToCrop.addEventListener(
                                "load",
                                function onLoad() {
                                    imageToCrop.removeEventListener("load", onLoad);
                                    initCropper();
                                },
                                { once: true }
                            );
                        } else {
                            initCropper();
                        }
                    },
                    { once: true }
                );
            }

            function initCropper() {
                if (cropper) {
                    try {
                        cropper.destroy();
                    } catch (e) { }
                    cropper = null;
                }

                cropper = new Cropper(imageToCrop, {
                    aspectRatio: 1,
                    viewMode: 2,
                    autoCropArea: 1,
                    responsive: true,
                    background: false,
                    movable: true,
                    zoomable: true,
                    cropBoxResizable: true,
                    ready() {
                        try {
                            const containerData = cropper.getContainerData();
                            const naturalW = imageToCrop.naturalWidth || 1;
                            const naturalH = imageToCrop.naturalHeight || 1;

                            const scaleX = containerData.width / naturalW;
                            const scaleY = containerData.height / naturalH;
                            const targetScale = Math.max(scaleX, scaleY);

                            const safeScale = Math.min(
                                Math.max(targetScale, 1),
                                MAX_UPSCALE
                            );

                            if (safeScale > 1.01) {
                                try {
                                    cropper.zoomTo(safeScale);
                                } catch (err) { }
                            }

                            try {
                                cropper.center();
                            } catch (e) { }
                        } catch (err) {
                            console.warn("Cropper ready calculation failed", err);
                        }
                    },
                });
            }

            btnCropUse?.addEventListener("click", function () {
                if (!cropper) {
                    alert("Cropper belum siap");
                    return;
                }

                const canvas = cropper.getCroppedCanvas({
                    width: OUTPUT_SIZE,
                    height: OUTPUT_SIZE,
                    imageSmoothingQuality: "high",
                });

                if (!canvas) {
                    alert("Gagal membuat gambar");
                    return;
                }

                canvas.toBlob(
                    function (blob) {
                        if (!blob) {
                            alert("Gagal mengonversi hasil crop");
                            return;
                        }

                        revokeLastObjectURL();

                        croppedBlob = blob;

                        const previewUrl = URL.createObjectURL(blob);
                        lastObjectUrl = previewUrl;
                        if (avatarPreviewSmall) avatarPreviewSmall.src = previewUrl;

                        if (btnUploadAvatar) btnUploadAvatar.disabled = false;

                        try {
                            cropper.destroy();
                        } catch (e) { }
                        cropper = null;

                        const bsCrop = bootstrap.Modal.getInstance(cropperModalEl);
                        if (bsCrop) bsCrop.hide();

                        bsInputModal.show();
                    },
                    OUTPUT_TYPE,
                    OUTPUT_QUALITY
                );
            });

            btnUploadAvatar?.addEventListener("click", async function () {
                if (!croppedBlob) {
                    alert("Pilih dan crop gambar terlebih dahulu.");
                    return;
                }

                btnUploadAvatar.disabled = true;
                const originalText = btnUploadAvatar.textContent;
                btnUploadAvatar.textContent = "Uploading...";

                try {
                    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                    const csrfToken = tokenMeta
                        ? tokenMeta.getAttribute("content")
                        : null;

                    const file = new File([croppedBlob], "avatar.jpg", {
                        type: croppedBlob.type,
                    });

                    const fd = new FormData();
                    fd.append("avatar", file);

                    const res = await fetch(uploadUrl, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrfToken,
                            Accept: "application/json",
                        },
                        body: fd,
                        credentials: "same-origin",
                    });

                    if (!res.ok) {
                        let errText = "Upload failed";
                        try {
                            const errJson = await res.json();
                            errText = errJson?.message || JSON.stringify(errJson);
                        } catch (e) {
                            errText = await res
                                .text()
                                .catch(() => res.statusText || "Upload failed");
                        }
                        throw new Error(errText);
                    }

                    const data = await res.json();

                    if (avatarPreviewSmall)
                        avatarPreviewSmall.src = data.url + "?t=" + Date.now();

                    document.querySelectorAll(".user-avatar-img").forEach((img) => {
                        img.src = data.url + "?t=" + Date.now();
                    });

                    revokeLastObjectURL();
                    croppedBlob = null;
                    btnUploadAvatar.textContent = originalText;

                    bsInputModal.hide();

                    location.reload();
                } catch (err) {
                    console.error(err);
                    alert("Upload error: " + (err.message || "Unknown"));
                    btnUploadAvatar.disabled = false;
                    btnUploadAvatar.textContent = originalText;
                }
            });

            cropperModalEl?.addEventListener("hidden.bs.modal", function () {
                if (cropper) {
                    try {
                        cropper.destroy();
                    } catch (e) { }
                    cropper = null;
                }
                if (imageToCrop && imageToCrop.src) {
                    try {
                        URL.revokeObjectURL(imageToCrop.src);
                    } catch (e) { }
                    imageToCrop.src = "";
                }
            });

            inputModalEl?.addEventListener("hidden.bs.modal", function () {
                if (lastObjectUrl && lastObjectUrl.startsWith("blob:")) {
                    try {
                        URL.revokeObjectURL(lastObjectUrl);
                    } catch (e) { }
                    lastObjectUrl = null;
                }
            });

            window.__avatarUploadHelpers = {
                openCropperWithFile: function (file) {
                    revokeLastObjectURL();
                    lastObjectUrl = URL.createObjectURL(file);
                    openCropper(lastObjectUrl);
                },
                setUploadUrl: function (url) {
                    console.warn(
                        "To change upload URL, edit the script uploadUrl variable in source."
                    );
                },
            };
        });
    </script>
@endsection