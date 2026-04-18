@extends('layouts.app')

@section('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('build/css/main/profile-detail.css') }}">
@endsection

@section('content')
    <div class="container">
        <button class="btn btn-primary" id="openAvatarModalBtn">Change Avatar</button>


        <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const uploadUrl = '{{ route("profile.avatar.update") }}';
                const MAX_UPSCALE = 2.0;
                const OUTPUT_SIZE = 800;
                const OUTPUT_TYPE = 'image/jpeg';
                const OUTPUT_QUALITY = 0.9;

                const openBtn = document.getElementById('openAvatarModalBtn');
                const inputModalEl = document.getElementById('avatarInputModal');
                const avatarFileInput = document.getElementById('avatarFileInput');
                const avatarPreviewSmall = document.getElementById('avatarPreviewSmall');
                const btnUploadAvatar = document.getElementById('btnUploadAvatar');

                const cropperModalEl = document.getElementById('cropperModal');
                const cropperContainer = document.getElementById('cropper-container');
                const imageToCrop = document.getElementById('imageToCrop');
                const btnCropUse = document.getElementById('btnCropUse');

                const bsInputModal = new bootstrap.Modal(inputModalEl);
                const bsCropperModal = new bootstrap.Modal(cropperModalEl);

                let cropper = null;
                let croppedBlob = null;
                let lastObjectUrl = null;

                function revokeLastObjectURL() {
                    if (lastObjectUrl) {
                        try { URL.revokeObjectURL(lastObjectUrl); } catch (e) { }
                        lastObjectUrl = null;
                    }
                }

                function resetInputModalState() {
                    avatarFileInput.value = '';
                    croppedBlob = null;
                    btnUploadAvatar.disabled = true;
                }

                openBtn?.addEventListener('click', () => {
                    resetInputModalState();
                    bsInputModal.show();
                });

                avatarFileInput?.addEventListener('change', function (e) {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;

                    revokeLastObjectURL();

                    lastObjectUrl = URL.createObjectURL(file);
                    openCropper(lastObjectUrl);
                });

                function openCropper(objectUrl) {
                    imageToCrop.src = objectUrl;

                    bsCropperModal.show();

                    cropperModalEl.addEventListener('shown.bs.modal', function onShown() {
                        cropperModalEl.removeEventListener('shown.bs.modal', onShown);

                        if (!imageToCrop.complete || !imageToCrop.naturalWidth) {
                            imageToCrop.addEventListener('load', function onLoad() {
                                imageToCrop.removeEventListener('load', onLoad);
                                initCropper();
                            }, { once: true });
                        } else {
                            initCropper();
                        }
                    }, { once: true });
                }

                function initCropper() {
                    if (cropper) {
                        try { cropper.destroy(); } catch (e) { }
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

                                const safeScale = Math.min(Math.max(targetScale, 1), MAX_UPSCALE);

                                if (safeScale > 1.01) {
                                    try { cropper.zoomTo(safeScale); } catch (err) { }
                                }

                                try { cropper.center(); } catch (e) { }
                            } catch (err) {
                                console.warn('Cropper ready calculation failed', err);
                            }
                        }
                    });
                }

                btnCropUse?.addEventListener('click', function () {
                    if (!cropper) { alert('Cropper belum siap'); return; }

                    const canvas = cropper.getCroppedCanvas({
                        width: OUTPUT_SIZE,
                        height: OUTPUT_SIZE,
                        imageSmoothingQuality: 'high'
                    });

                    if (!canvas) { alert('Gagal membuat gambar'); return; }

                    canvas.toBlob(function (blob) {
                        if (!blob) { alert('Gagal mengonversi hasil crop'); return; }

                        revokeLastObjectURL();

                        croppedBlob = blob;

                        const previewUrl = URL.createObjectURL(blob);
                        lastObjectUrl = previewUrl;
                        if (avatarPreviewSmall) avatarPreviewSmall.src = previewUrl;

                        if (btnUploadAvatar) btnUploadAvatar.disabled = false;

                        try { cropper.destroy(); } catch (e) { }
                        cropper = null;

                        const bsCrop = bootstrap.Modal.getInstance(cropperModalEl);
                        if (bsCrop) bsCrop.hide();

                        bsInputModal.show();
                    }, OUTPUT_TYPE, OUTPUT_QUALITY);
                });

                btnUploadAvatar?.addEventListener('click', async function () {
                    if (!croppedBlob) {
                        alert('Pilih dan crop gambar terlebih dahulu.');
                        return;
                    }

                    btnUploadAvatar.disabled = true;
                    const originalText = btnUploadAvatar.textContent;
                    btnUploadAvatar.textContent = 'Uploading...';

                    try {
                        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                        const csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : null;

                        const file = new File([croppedBlob], 'avatar.jpg', { type: croppedBlob.type });

                        const fd = new FormData();
                        fd.append('avatar', file);

                        const res = await fetch(uploadUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: fd,
                            credentials: 'same-origin'
                        });

                        if (!res.ok) {
                            let errText = 'Upload failed';
                            try {
                                const errJson = await res.json();
                                errText = errJson?.message || JSON.stringify(errJson);
                            } catch (e) {
                                errText = await res.text().catch(() => res.statusText || 'Upload failed');
                            }
                            throw new Error(errText);
                        }

                        const data = await res.json();

                        if (avatarPreviewSmall) avatarPreviewSmall.src = data.url + '?t=' + Date.now();

                        document.querySelectorAll('.user-avatar-img').forEach(img => {
                            img.src = data.url + '?t=' + Date.now();
                        });

                        revokeLastObjectURL();
                        croppedBlob = null;
                        btnUploadAvatar.textContent = originalText;

                        bsInputModal.hide();

                        alert('Avatar berhasil diupload');

                    } catch (err) {
                        console.error(err);
                        alert('Upload error: ' + (err.message || 'Unknown'));
                        btnUploadAvatar.disabled = false;
                        btnUploadAvatar.textContent = originalText;
                    }
                });

                cropperModalEl?.addEventListener('hidden.bs.modal', function () {
                    if (cropper) {
                        try { cropper.destroy(); } catch (e) { }
                        cropper = null;
                    }
                    if (imageToCrop && imageToCrop.src) {
                        try { URL.revokeObjectURL(imageToCrop.src); } catch (e) { }
                        imageToCrop.src = '';
                    }
                });

                inputModalEl?.addEventListener('hidden.bs.modal', function () {
                    if (lastObjectUrl && lastObjectUrl.startsWith('blob:')) {
                        try { URL.revokeObjectURL(lastObjectUrl); } catch (e) { }
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
                        console.warn('To change upload URL, edit the script uploadUrl variable in source.');
                    }
                };
            });
        </script>

@endsection