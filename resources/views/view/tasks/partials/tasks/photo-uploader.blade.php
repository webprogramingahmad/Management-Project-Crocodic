<div class="mt-3">
    <label class="form-label" for="edit_task_photos">Work Evidence</label>

    <div class="task-photo-box d-flex align-items-center justify-content-between gap-2 rounded-3 px-3 py-2">
        <small class="task-photo-hint mb-0">Format JPG, maksimal 1MB per foto</small>
        <label for="edit_task_photos" class="task-photo-trigger btn btn-sm d-inline-flex align-items-center gap-1 mb-0">
            <i class="bi bi-upload"></i> Upload
        </label>
    </div>

    <input type="file" name="photos[]" id="edit_task_photos" class="d-none" accept=".jpg,.jpeg,image/jpeg" multiple>

    <div id="edit_task_photo_preview" class="task-photo-preview d-flex flex-wrap gap-2 mt-2"></div>
</div>

<style>
    .task-photo-box {
        border: 1px solid #ced4da;
        background-color: #f8f9fa;
    }

    .task-photo-trigger {
        border: 1px solid #ced4da;
        background-color: #fff;
        color: #495057;
        font-size: .8rem;
        line-height: 1.2;
    }

    .task-photo-trigger:hover {
        border-color: #0d6efd;
        background-color: #eef4ff;
        color: #0d6efd;
    }

    .task-photo-hint {
        font-size: .72rem;
        color: #6c757d;
    }

    .task-photo-preview .task-photo-thumb {
        position: relative;
        width: 52px;
        height: 52px;
        border-radius: .4rem;
        overflow: hidden;
        border: 1px solid #e0e0e0;
    }

    .task-photo-preview .task-photo-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .task-photo-preview .task-photo-remove {
        position: absolute;
        top: 1px;
        right: 1px;
        width: 18px;
        height: 18px;
        padding: 0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .65rem;
    }

    html[data-theme="dark"] .task-photo-box {
        border-color: rgba(161, 161, 170, 0.35);
        background-color: rgba(255, 255, 255, 0.03);
    }

    html[data-theme="dark"] .task-photo-trigger {
        border-color: rgba(161, 161, 170, 0.45);
        background-color: rgba(255, 255, 255, 0.06);
        color: #d4d4d8;
    }

    html[data-theme="dark"] .task-photo-trigger:hover {
        border-color: #6ea8fe;
        background-color: rgba(110, 168, 254, 0.12);
        color: #6ea8fe;
    }

    html[data-theme="dark"] .task-photo-preview .task-photo-thumb {
        border-color: rgba(161, 161, 170, 0.35);
    }
</style>

<script>
    (function () {
        function bytesToMb(b) {
            return b / (1024 * 1024);
        }

        function initTaskPhotoUploader() {
            var input = document.getElementById('edit_task_photos');
            var preview = document.getElementById('edit_task_photo_preview');
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
                    img.onload = function () {
                        URL.revokeObjectURL(url);
                    };

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
                        alert('Hanya file JPG yang diperbolehkan: ' + file.name);
                        return;
                    }
                    if (bytesToMb(file.size) > 1) {
                        alert('Ukuran maksimal 1MB per foto: ' + file.name);
                        return;
                    }
                    store.items.add(file);
                });
                input.files = store.files;
                render();
            });

            var modal = document.getElementById('edit-task');
            if (modal) {
                modal.addEventListener('hidden.bs.modal', function () {
                    store = new DataTransfer();
                    input.value = '';
                    input.files = store.files;
                    preview.innerHTML = '';
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initTaskPhotoUploader);
        } else {
            initTaskPhotoUploader();
        }
    })();
</script>
