<script>
    (function () {
        var cfg = window.__tasksBoardSortable || {};
        if (cfg.role === "executive") {
            return;
        }

        var pendingSubmit = null;

        function csrfToken() {
            var m = document.querySelector('meta[name="csrf-token"]');
            return m ? m.getAttribute("content") : "";
        }

        function revertDrag(evt) {
            evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex] || null);
        }

        function applyStatusResponse(evt, data) {
            if (typeof window.refreshTaskRunningTimerAfterStatus === "function") {
                window.refreshTaskRunningTimerAfterStatus(
                    evt.item.querySelector(".task-link") || evt.item,
                    data.deadline_iso || null,
                    !!data.show_timer,
                    data.frozen_remain_ms != null ? data.frozen_remain_ms : null,
                    {
                        progress_balance_seconds: data.progress_balance_seconds ?? null,
                        revision_cycles: data.revision_cycles ?? [],
                    }
                );
            }
            var link = evt.item.querySelector(".task-link");
            if (link && data.has_submissions) {
                link.setAttribute("data-task-has-submissions", "1");
            }
        }

        function postStatus(evt, url, body) {
            return fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken(),
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify(body),
            })
                .then(function (res) {
                    return res.json().then(function (data) {
                        return { ok: res.ok, status: res.status, data: data };
                    });
                })
                .then(function (r) {
                    if (!r.ok || !r.data.success) {
                        alert((r.data && r.data.message) ? r.data.message : "Gagal update status");
                        revertDrag(evt);
                        return;
                    }
                    applyStatusResponse(evt, r.data);
                })
                .catch(function () {
                    alert("Error update status");
                    revertDrag(evt);
                });
        }

        function openSubmitReviewModal(evt, fromStatusClass) {
            pendingSubmit = {
                evt: evt,
                fromStatusClass: fromStatusClass,
            };
            var modalEl = document.getElementById("submit-review-modal");
            var form = document.getElementById("form-submit-review");
            if (!modalEl || !form) {
                revertDrag(evt);
                return;
            }
            var link = evt.item.querySelector(".task-link");
            var taskId = link ? link.dataset.taskId : evt.item.dataset.id;
            var tpl = window.__taskSubmitReviewUrlTemplate;
            if (!tpl || !taskId) {
                revertDrag(evt);
                return;
            }
            form.dataset.actionUrl = tpl.replace("__TASK_ID__", taskId);
            form.reset();
            if (window.__resetTaskPhotoUploader && window.__resetTaskPhotoUploader.submit_review_photos) {
                window.__resetTaskPhotoUploader.submit_review_photos();
            }
            var hint = document.getElementById("submit-review-hint");
            if (hint) {
                hint.textContent = fromStatusClass === "revision"
                    ? "Lengkapi hasil perbaikan revisi sebelum task dipindahkan ke Review."
                    : "Lengkapi hasil kerja sebelum task dipindahkan ke Review.";
            }
            revertDrag(evt);
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        var submitForm = document.getElementById("form-submit-review");
        if (submitForm) {
            submitForm.addEventListener("submit", function (e) {
                e.preventDefault();
                if (!pendingSubmit) return;
                var url = submitForm.dataset.actionUrl;
                if (!url) return;
                var btn = document.getElementById("submit-review-btn");
                if (btn) btn.disabled = true;
                var fd = new FormData(submitForm);
                fetch(url, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken(),
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: fd,
                })
                    .then(function (res) {
                        return res.json().then(function (data) {
                            return { ok: res.ok, data: data };
                        });
                    })
                    .then(function (r) {
                        if (!r.ok || !r.data.success) {
                            var msg = (r.data && r.data.message) ? r.data.message : "Gagal mengirim hasil kerja";
                            if (r.data && r.data.errors) {
                                var first = Object.values(r.data.errors)[0];
                                if (Array.isArray(first) && first[0]) msg = first[0];
                            }
                            alert(msg);
                            return;
                        }
                        var evt = pendingSubmit.evt;
                        var reviewCol = document.querySelector(
                            '.overflow-scroll-container[data-status="' + cfg.reviewId + '"]'
                        );
                        if (reviewCol) {
                            reviewCol.appendChild(evt.item);
                        }
                        applyStatusResponse(evt, r.data);
                        var link = evt.item.querySelector(".task-link");
                        if (link) {
                            link.dataset.taskStatus = "Review";
                            link.setAttribute("data-task-has-submissions", "1");
                        }
                        bootstrap.Modal.getInstance(document.getElementById("submit-review-modal"))?.hide();
                        pendingSubmit = null;
                    })
                    .catch(function () {
                        alert("Error mengirim hasil kerja");
                    })
                    .finally(function () {
                        if (btn) btn.disabled = false;
                    });
            });
        }

        document.querySelectorAll(".overflow-scroll-container").forEach(function (column) {
            new Sortable(column, {
                group: "tasks",
                animation: 150,
                onMove: function (evt) {
                    var to = evt.to.getAttribute("data-status");
                    var from = evt.from.getAttribute("data-status");
                    if (cfg.revisionId && to === cfg.revisionId) {
                        return false;
                    }
                    if (cfg.reviewId && cfg.completeId && from === cfg.reviewId && to === cfg.completeId) {
                        return false;
                    }
                    return true;
                },
                onAdd: function (evt) {
                    var link = evt.item.querySelector(".task-link");
                    var taskId = link ? link.dataset.taskId : evt.item.dataset.id;
                    var newStatus = evt.to.dataset.status;
                    var fromCol = evt.from;
                    var fromStatus = fromCol ? fromCol.getAttribute("data-status") : "";

                    if (cfg.reviewId && newStatus === cfg.reviewId) {
                        var fromIsProgress = cfg.progressId && fromStatus === cfg.progressId;
                        var fromIsRevision = cfg.revisionId && fromStatus === cfg.revisionId;
                        if (fromIsProgress || fromIsRevision) {
                            openSubmitReviewModal(
                                evt,
                                fromIsRevision ? "revision" : "progress"
                            );
                            return;
                        }
                    }

                    var url = cfg.statusUrlTemplate.replace("__TASK_ID__", taskId);
                    postStatus(evt, url, { id_status: newStatus });
                },
            });
        });
    })();
</script>
