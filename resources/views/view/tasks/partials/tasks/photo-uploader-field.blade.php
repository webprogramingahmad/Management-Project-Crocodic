@php
    $inputId = $inputId ?? 'task_photos';
    $previewId = $previewId ?? 'task_photo_preview';
    $resetModalId = $resetModalId ?? null;
@endphp
<div class="mt-2">
    <label class="form-label" for="{{ $inputId }}">{{ $label ?? 'Lampiran foto (opsional)' }}</label>
    <div class="task-photo-box d-flex align-items-center justify-content-between gap-2 rounded-3 px-3 py-2">
        <small class="task-photo-hint mb-0">Format JPG, maksimal 1MB per foto</small>
        <label for="{{ $inputId }}" class="task-photo-trigger btn btn-sm d-inline-flex align-items-center gap-1 mb-0">
            <i class="bi bi-upload"></i> Upload
        </label>
    </div>
    <input type="file" name="photos[]" id="{{ $inputId }}" class="d-none" accept=".jpg,.jpeg,image/jpeg" multiple>
    <div id="{{ $previewId }}" class="task-photo-preview d-flex flex-wrap gap-2 mt-2"></div>
</div>

<script>
    (function () {
        var inputId = @json($inputId);
        var previewId = @json($previewId);
        var resetModalId = @json($resetModalId);

        function initUploader() {
            var input = document.getElementById(inputId);
            var preview = document.getElementById(previewId);
            if (!input || !preview || input.dataset.bound === '1') return;
            input.dataset.bound = '1';

            var store = new DataTransfer();

            function render() {
                preview.innerHTML = '';
                Array.prototype.forEach.call(store.files, function (file, idx) {
                    var url = URL.createObjectURL(file);
                    var thumb = document.createElement('div');
                    thumb.className = 'task-photo-thumb';
                    var img = document.createElement('img');
                    img.src = url;
                    img.alt = file.name;
                    img.onload = function () { URL.revokeObjectURL(url); };
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'task-photo-remove btn btn-danger';
                    btn.innerHTML = '<i class="bi bi-x"></i>';
                    btn.addEventListener('click', function () {
                        var dt = new DataTransfer();
                        Array.prototype.forEach.call(store.files, function (f, i) {
                            if (i !== idx) dt.items.add(f);
                        });
                        store = dt;
                        input.files = store.files;
                        render();
                    });
                    thumb.appendChild(img);
                    thumb.appendChild(btn);
                    preview.appendChild(thumb);
                });
            }

            input.addEventListener('change', function () {
                Array.prototype.forEach.call(input.files, function (file) {
                    var isJpg = file.type === 'image/jpeg' || /\.jpe?g$/i.test(file.name);
                    if (!isJpg) {
                        alert('Hanya file JPG: ' + file.name);
                        return;
                    }
                    if (file.size / (1024 * 1024) > 1) {
                        alert('Maksimal 1MB: ' + file.name);
                        return;
                    }
                    store.items.add(file);
                });
                input.files = store.files;
                render();
            });

            window.__resetTaskPhotoUploader = window.__resetTaskPhotoUploader || {};
            window.__resetTaskPhotoUploader[inputId] = function () {
                store = new DataTransfer();
                input.value = '';
                input.files = store.files;
                preview.innerHTML = '';
            };

            if (resetModalId) {
                var modal = document.getElementById(resetModalId);
                if (modal) {
                    modal.addEventListener('hidden.bs.modal', function () {
                        if (window.__resetTaskPhotoUploader[inputId]) {
                            window.__resetTaskPhotoUploader[inputId]();
                        }
                    });
                }
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initUploader);
        } else {
            initUploader();
        }
    })();
</script>
