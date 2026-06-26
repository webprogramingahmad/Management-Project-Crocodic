import "./bootstrap";
import * as bootstrap from "bootstrap";

// Tersedia sebelum DOMContentLoaded agar inline script (profile edit, dll.) bisa pakai bootstrap.Modal.
if (!window.bootstrap) {
    window.bootstrap = bootstrap;
}

const THEME_STORAGE_KEY = "theme";

function initBootstrapComponents() {
    // Inisialisasi eksplisit dropdown agar tidak bergantung penuh pada Data API.
    document
        .querySelectorAll('[data-bs-toggle="dropdown"]')
        .forEach((toggleEl) => {
            bootstrap.Dropdown.getOrCreateInstance(toggleEl);
        });
}

function applyTheme(mode) {
    const t = mode === "dark" ? "dark" : "light";
    document.documentElement.setAttribute("data-theme", t);
    try {
        localStorage.setItem(THEME_STORAGE_KEY, t);
    } catch (e) {
        /* ignore */
    }
    document.querySelectorAll("[data-theme-toggle]").forEach((el) => {
        el.checked = t === "dark";
    });
}

function initThemeToggles() {
    document.querySelectorAll("[data-theme-toggle]").forEach((el) => {
        el.addEventListener("change", () => {
            applyTheme(el.checked ? "dark" : "light");
        });
    });
    const current =
        document.documentElement.getAttribute("data-theme") === "dark"
            ? "dark"
            : "light";
    document.querySelectorAll("[data-theme-toggle]").forEach((el) => {
        el.checked = current === "dark";
    });
}

function initProfilePopups() {
    const canHover = window.matchMedia("(hover: hover)").matches;

    document.querySelectorAll("[data-profile-popup]").forEach((host) => {
        const trigger = host.querySelector(".profile-trigger");
        if (!trigger) {
            return;
        }

        const open = () => host.classList.add("is-open");
        const close = () => host.classList.remove("is-open");

        if (canHover) {
            host.addEventListener("mouseenter", open);
            host.addEventListener("mouseleave", close);
        }

        trigger.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (!canHover) {
                host.classList.toggle("is-open");
            }
        });

        if (!canHover) {
            document.addEventListener("click", (e) => {
                if (!host.contains(e.target)) {
                    close();
                }
            });
        }
    });
}

function escapeHtml(text) {
    if (text == null) return "";
    const d = document.createElement("div");
    d.textContent = String(text);
    return d.innerHTML;
}

/** Keterangan task: teks di-escape; URL http(s) jadi tautan. */
function taskDescriptionToHtml(raw) {
    if (raw == null || String(raw).trim() === "") {
        return "";
    }
    const s = String(raw);
    const parts = s.split(/(https?:\/\/\S+)/giu);
    let out = "";
    for (let j = 0; j < parts.length; j++) {
        const part = parts[j];
        if (!part) continue;
        if (/^https?:\/\//iu.test(part)) {
            const url = part.replace(/[.,;:!?'"@\]]+$/u, "");
            try {
                const u = new URL(url);
                if (u.protocol === "http:" || u.protocol === "https:") {
                    const safe = escapeHtml(url);
                    out +=
                        '<a href="' +
                        safe +
                        '" target="_blank" rel="noopener noreferrer">' +
                        safe +
                        "</a>";
                    if (url.length < part.length) {
                        out += escapeHtml(part.slice(url.length));
                    }
                    continue;
                }
            } catch (e) {
                /* ignore */
            }
        }
        out += escapeHtml(part);
    }
    return out.replace(/\r\n|\r|\n/g, "<br>");
}

function formatBalanceSeconds(seconds) {
    if (seconds == null || Number.isNaN(Number(seconds))) {
        return "-";
    }
    const value = Number(seconds);
    const abs = Math.abs(value);
    const h = Math.floor(abs / 3600);
    const m = Math.floor((abs % 3600) / 60);
    const s = abs % 60;
    const hms =
        String(h).padStart(2, "0") +
        ":" +
        String(m).padStart(2, "0") +
        ":" +
        String(s).padStart(2, "0");
    if (value < 0) {
        return "-" + hms;
    }
    return hms;
}

function setModalTimeTrackingFromCard(card) {
    const progressEl = document.querySelector(".modal-progress-time");
    const revisionsWrap = document.querySelector(".modal-revision-times");
    if (!progressEl || !revisionsWrap) return;

    const progressRaw = card.getAttribute("data-task-progress-balance-seconds");
    progressEl.textContent = formatBalanceSeconds(
        progressRaw !== null && progressRaw !== "" ? Number(progressRaw) : null
    );

    let cycles = [];
    const cyclesAttr = card.getAttribute("data-task-revision-cycles-json");
    if (cyclesAttr) {
        try {
            const parsed = JSON.parse(cyclesAttr);
            if (Array.isArray(parsed)) {
                cycles = parsed;
            }
        } catch (e) {
            cycles = [];
        }
    }

    if (cycles.length === 0) {
        revisionsWrap.innerHTML =
            '<div class="small"><span class="text-muted">Revision:</span> <span class="fw-semibold">-</span></div>';
        return;
    }

    revisionsWrap.innerHTML = cycles
        .map((c) => {
            const n =
                c && c.cycle_number != null && !Number.isNaN(Number(c.cycle_number))
                    ? Number(c.cycle_number)
                    : 0;
            const balance =
                c && c.balance_seconds != null && !Number.isNaN(Number(c.balance_seconds))
                    ? Number(c.balance_seconds)
                    : null;
            return (
                '<div class="small mb-1"><span class="text-muted">Revision ' +
                n +
                ':</span> <span class="fw-semibold">' +
                escapeHtml(formatBalanceSeconds(balance)) +
                "</span></div>"
            );
        })
        .join("");
}

function updateTaskTrackingDataset(card, payload) {
    if (!card || !payload) return;
    if (Object.prototype.hasOwnProperty.call(payload, "progress_balance_seconds")) {
        const v = payload.progress_balance_seconds;
        card.setAttribute(
            "data-task-progress-balance-seconds",
            v == null ? "" : String(v)
        );
    }
    if (Object.prototype.hasOwnProperty.call(payload, "revision_cycles")) {
        card.setAttribute(
            "data-task-revision-cycles-json",
            JSON.stringify(Array.isArray(payload.revision_cycles) ? payload.revision_cycles : [])
        );
    }

    // Jika modal detail sedang buka untuk task ini, refresh data tracking realtime.
    var modal = document.getElementById("detail-task");
    if (modal && modal.classList.contains("show")) {
        var openedCard = window.__lastTaskCardForDetail;
        var cardTaskId = card.getAttribute("data-task-id") || "";
        if (openedCard && (openedCard.getAttribute("data-task-id") || "") === cardTaskId) {
            setModalTimeTrackingFromCard(card);
        }
    }
}

function updateTaskWorkResultsButton(card) {
    const btn = document.getElementById("btn-view-work-results");
    if (!btn || !card) return;
    btn.classList.remove("d-none");
}

function formatDurationSeconds(seconds) {
    if (seconds == null || Number.isNaN(Number(seconds))) {
        return "-";
    }
    const abs = Math.abs(Math.floor(Number(seconds)));
    const h = Math.floor(abs / 3600);
    const m = Math.floor((abs % 3600) / 60);
    const s = abs % 60;
    return (
        String(h).padStart(2, "0") +
        ":" +
        String(m).padStart(2, "0") +
        ":" +
        String(s).padStart(2, "0")
    );
}

function renderWorkResultPhotos(photos) {
    if (!photos || !photos.length) {
        return "";
    }
    return (
        '<div class="work-result-photos">' +
        photos
            .map(function (p) {
                return (
                    '<a href="' +
                    escapeHtml(p.url) +
                    '" target="_blank" rel="noopener" class="modal-photo-thumb"><img src="' +
                    escapeHtml(p.url) +
                    '" alt=""></a>'
                );
            })
            .join("") +
        "</div>"
    );
}

function renderWorkResultField(label, icon, rawContent) {
    if (rawContent == null || String(rawContent).trim() === "") {
        return "";
    }
    return (
        '<div class="work-result-field">' +
        '<div class="work-result-field-label"><i class="bi bi-' +
        icon +
        ' me-1"></i>' +
        escapeHtml(label) +
        "</div>" +
        '<div class="work-result-field-value">' +
        taskDescriptionToHtml(rawContent) +
        "</div>" +
        "</div>"
    );
}

function renderWorkResultTiming(timing, allocationHint) {
    if (!timing || timing.used_seconds == null) {
        return "";
    }
    let html = "";
    if (allocationHint) {
        html +=
            '<p class="work-result-allocation-hint mb-2">' +
            escapeHtml(allocationHint) +
            "</p>";
    }
    html += '<div class="work-result-timing">';
    html += '<div class="work-result-timing-item">';
    html +=
        '<span class="work-result-timing-label"><i class="bi bi-stopwatch me-1"></i>Waktu pengerjaan</span>';
    html +=
        '<span class="work-result-timing-value">' +
        escapeHtml(formatDurationSeconds(timing.used_seconds)) +
        "</span>";
    html += "</div>";
    if (timing.is_overdue && timing.overdue_seconds != null) {
        html += '<div class="work-result-timing-item work-result-timing-item--overdue">';
        html +=
            '<span class="work-result-timing-label"><i class="bi bi-exclamation-circle me-1"></i>Kelebihan waktu</span>';
        html +=
            '<span class="work-result-timing-value">' +
            escapeHtml(formatDurationSeconds(timing.overdue_seconds)) +
            "</span>";
        html += "</div>";
    } else if (
        timing.balance_seconds != null &&
        Number(timing.balance_seconds) > 0
    ) {
        html += '<div class="work-result-timing-item work-result-timing-item--ok">';
        html +=
            '<span class="work-result-timing-label"><i class="bi bi-check-circle me-1"></i>Tepat waktu</span>';
        html +=
            '<span class="work-result-timing-value">Sisa ' +
            escapeHtml(formatDurationSeconds(timing.balance_seconds)) +
            "</span>";
        html += "</div>";
    }
    html += "</div>";
    return html;
}

function renderWorkResultSubmissionBlock(submission, timing, allocationHint) {
    if (!submission) {
        return '<p class="small text-muted mb-0">Belum ada hasil dari staff.</p>';
    }
    let html = renderWorkResultTiming(timing, allocationHint);
    html += renderWorkResultField("Deskripsi", "card-text", submission.notes);
    html += renderWorkResultField("Link", "link-45deg", submission.links);
    html += renderWorkResultPhotos(submission.photos);
    return html;
}

function renderWorkResultDirectorBlock(director) {
    if (!director) {
        return "";
    }
    const hasContent =
        director.notes ||
        director.links ||
        director.revision_hours ||
        (director.photos && director.photos.length);
    if (!hasContent) {
        return "";
    }
    let html = '<div class="work-result-subsection">';
    html += '<div class="work-result-subsection-label">Instruksi revisi</div>';
    if (director.revision_hours) {
        html +=
            '<p class="work-result-allocation-hint">Batas waktu revisi: <strong>' +
            escapeHtml(String(director.revision_hours)) +
            " jam</strong></p>";
    }
    html += renderWorkResultField("Catatan", "chat-left-text", director.notes);
    html += renderWorkResultField("Link", "link-45deg", director.links);
    html += renderWorkResultPhotos(director.photos);
    html += "</div>";
    return html;
}

function renderWorkResultsMeta(meta) {
    if (!meta) {
        return "";
    }
    return (
        '<div class="work-results-meta">' +
        '<div class="work-results-meta-title">' +
        escapeHtml(meta.task_name || "-") +
        "</div>" +
        '<div class="work-results-meta-row">' +
        '<span class="text-muted"><i class="bi bi-person me-1"></i>Pemilik</span>' +
        '<span class="fw-semibold">' +
        escapeHtml(meta.owner_name || "-") +
        "</span>" +
        "</div>" +
        '<div class="work-results-meta-row">' +
        '<span class="text-muted"><i class="bi bi-kanban me-1"></i>Project</span>' +
        '<span class="fw-semibold">' +
        escapeHtml(meta.project_name || "-") +
        "</span>" +
        "</div>" +
        "</div>"
    );
}

function renderWorkResultsPayload(json) {
    const body = document.getElementById("task-work-results-body");
    if (!body) return;

    const data = json && json.data ? json.data : json;
    if (!data) {
        body.innerHTML = '<p class="text-muted mb-0">Belum ada hasil kerja.</p>';
        return;
    }

    let out = renderWorkResultsMeta(data.meta);

    if (data.work_submission) {
        out += '<div class="work-result-section">';
        out += '<div class="work-result-section-title">Hasil kerja</div>';
        out += renderWorkResultSubmissionBlock(
            data.work_submission,
            data.work_submission.timing || null,
            data.work_submission.timing &&
                data.work_submission.timing.allocated_seconds != null
                ? "Batas waktu level task: " +
                      formatDurationSeconds(
                          data.work_submission.timing.allocated_seconds
                      )
                : null
        );
        out += "</div>";
    }

    (data.revision_cycles || []).forEach(function (cycle) {
        const cycleNum = cycle.cycle_number || "-";
        out += '<div class="work-result-section">';
        out +=
            '<div class="work-result-section-title">Revisi ' +
            escapeHtml(String(cycleNum)) +
            "</div>";
        out += renderWorkResultDirectorBlock(cycle.director);
        out += '<div class="work-result-subsection">';
        out += '<div class="work-result-subsection-label">Hasil perbaikan</div>';
        const allocHint =
            cycle.director && cycle.director.revision_hours
                ? "Batas waktu revisi: " + cycle.director.revision_hours + " jam"
                : null;
        out += renderWorkResultSubmissionBlock(
            cycle.staff_submission,
            cycle.timing || null,
            allocHint
        );
        out += "</div>";
        out += "</div>";
    });

    body.innerHTML =
        out || '<p class="text-muted mb-0">Belum ada hasil kerja.</p>';
}

function openTaskWorkResultsModal(card) {
    const tpl = window.__taskSubmissionsUrlTemplate;
    const modalEl = document.getElementById("task-work-results-modal");
    const body = document.getElementById("task-work-results-body");
    if (!tpl || !modalEl || !card) return;
    const taskId = card.dataset.taskId || "";
    if (!taskId) return;
    if (body) body.innerHTML = '<div class="text-center text-muted py-4">Memuat...</div>';
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
    fetch(tpl.replace("__TASK_ID__", taskId), {
        headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
    })
        .then(function (r) {
            if (!r.ok) throw new Error("failed");
            return r.json();
        })
        .then(function (json) {
            renderWorkResultsPayload(json);
        })
        .catch(function () {
            if (body) body.innerHTML = '<p class="text-danger mb-0">Gagal memuat hasil kerja.</p>';
        });
}

function setModalTaskDescriptionFromCard(card) {
    const descRow = document.querySelector(".modal-task-description-row");
    const descEl = document.querySelector(".modal-task-description");
    if (!descRow || !descEl) return;
    const attr = card.getAttribute("data-task-description-json");
    let raw = null;
    if (attr != null && attr !== "") {
        try {
            raw = JSON.parse(attr);
        } catch (e) {
            raw = null;
        }
    }
    const html = taskDescriptionToHtml(raw);
    if (html) {
        descEl.innerHTML = html;
        descRow.classList.remove("d-none");
    } else {
        descEl.innerHTML = "";
        descRow.classList.add("d-none");
    }
}

function initTaskFormSubmitGuard() {
    document.querySelectorAll("form").forEach(function (form) {
        if (form.dataset.taskSubmitGuard === "1") {
            return;
        }
        const hasGuardedSubmit = form.querySelector("[data-task-form-submit]");
        if (!hasGuardedSubmit) {
            return;
        }
        form.dataset.taskSubmitGuard = "1";
        form.addEventListener("submit", function (e) {
            if (form.dataset.submitting === "1") {
                e.preventDefault();
                return;
            }
            form.dataset.submitting = "1";
            form.querySelectorAll("[data-task-form-submit], button[type='submit']").forEach(function (btn) {
                btn.disabled = true;
            });
        });
    });
}

function initCreateTaskModalForms() {
    ["create-task", "create-taskproject"].forEach(function (modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            return;
        }
        const form = modal.querySelector("form");
        if (!form) {
            return;
        }
        const standbyRadio = modal.querySelector("#standby-level");
        const taskNameGroup = modal.querySelector("#task-name-group");
        const projectGroup = modal.querySelector("#project-group");
        const descriptionGroup = modal.querySelector("#description-group");
        const descriptionField = modal.querySelector(
            "#create-task-description, #create-project-task-description"
        );
        const levelRadios = form.querySelectorAll('input[name="id_difficulty"]');

        function toggleStandbyFields() {
            const isStandby = standbyRadio && standbyRadio.checked;
            if (taskNameGroup) {
                taskNameGroup.style.display = isStandby ? "none" : "";
                const nameInput = taskNameGroup.querySelector("input");
                if (nameInput) {
                    if (isStandby) {
                        nameInput.removeAttribute("required");
                    } else {
                        nameInput.setAttribute("required", "required");
                    }
                }
            }
            if (projectGroup) {
                projectGroup.style.display = isStandby ? "none" : "";
                const projectSelect = projectGroup.querySelector("select");
                if (projectSelect) {
                    if (isStandby) {
                        projectSelect.removeAttribute("required");
                    } else {
                        projectSelect.setAttribute("required", "required");
                    }
                }
            }
            if (descriptionGroup) {
                descriptionGroup.style.display = isStandby ? "none" : "";
            }
            if (descriptionField) {
                if (isStandby) {
                    descriptionField.removeAttribute("required");
                    descriptionField.value = "";
                } else {
                    descriptionField.setAttribute("required", "required");
                }
            }
        }

        levelRadios.forEach(function (radio) {
            radio.addEventListener("change", toggleStandbyFields);
        });
        toggleStandbyFields();
    });
}

document.addEventListener("DOMContentLoaded", function () {
    initBootstrapComponents();
    initThemeToggles();
    initProfilePopups();
    initTaskFormSubmitGuard();
    initCreateTaskModalForms();
    document.querySelectorAll('.task-link').forEach(function (card) {
        card.addEventListener('click', function () {
            window.__lastTaskCardForDetail = this;
            var taskId = card.dataset.taskId || '';
            // Title
            document.querySelector('.task-title').textContent = card.dataset.taskTitle || '-';
            // Status
            var statusText = card.dataset.taskStatus || '-';
            var statusBadge = document.querySelector('.status-todo');
            statusBadge.textContent = statusText;
            let statusColor = '#EA4949';
            switch (statusText.toLowerCase()) {
                case 'todo': statusColor = '#EA4949'; break;
                case 'in progress': statusColor = '#FFB42E'; break;
                case 'review': statusColor = '#6FAEC9'; break;
                case 'revision': statusColor = '#C2410C'; break;
                case 'complete': statusColor = '#7DB546'; break;
            }
            statusBadge.style.backgroundColor = statusColor;
            statusBadge.style.color = '#fff';
            // Project
            document.querySelector('.modal-project').textContent = card.dataset.taskProjectname || '-';
            // Timeline
            document.querySelector('.modal-timeline').textContent = card.dataset.taskTimeline || '-';
            // Assignee
            var userName = card.dataset.taskUser || '-';
            var avatar = card.dataset.taskAvatar;
            var initial = userName ? userName.substring(0,2).toUpperCase() : '-';
            var avatarImg = document.querySelector('.modal-user img.avatar-sm');
            var avatarInitial = document.querySelector('.modal-user-initial');
            var usernameSpan = document.querySelector('.modal-username');
            if (avatar && !avatar.endsWith(initial)) {
                avatarImg.src = avatar;
                avatarImg.classList.remove('d-none');
                avatarInitial.classList.add('d-none');
            } else {
                avatarInitial.textContent = initial;
                avatarInitial.classList.remove('d-none');
                avatarImg.classList.add('d-none');
            }
            usernameSpan.textContent = userName;
            usernameSpan.classList.remove('d-none');
            // Difficulty/Level as label
            var diffText = card.dataset.taskDifficulty || '-';
            var diffBadge = document.querySelector('.modal-difficulty');
            diffBadge.textContent = diffText;
            let diffColor = '#6c757d';
            switch (diffText.toLowerCase()) {
                case 'low': diffColor = '#6FAEC9'; break;
                case 'medium': diffColor = '#FFB42E'; break;
                case 'high': diffColor = '#EA4949'; break;
            }
            diffBadge.style.backgroundColor = diffColor;
            diffBadge.style.color = '#fff';

            setModalTaskDescriptionFromCard(card);
            setModalTimeTrackingFromCard(card);
            updateTaskWorkResultsButton(card);

            var rdBtn = document.getElementById("detail-review-decision-btn");
            if (rdBtn) {
                var isReview = statusText.toLowerCase() === "review";
                rdBtn.classList.toggle("d-none", !isReview);
            }

            if (typeof window.updateTaskDetailOwnershipUi === "function") {
                window.updateTaskDetailOwnershipUi(card);
            }
        });
    });

    var reviewDecisionModalForm = document.getElementById(
        "form-review-decision-modal"
    );
    var reviewDecisionModalEl = document.getElementById("review-decision-modal");

    if (reviewDecisionModalEl) {
        reviewDecisionModalEl.addEventListener("hidden.bs.modal", function () {
            if (window.__taskReviewDecisionSubmitting) {
                window.__taskReviewDecisionSubmitting = false;
                window.__taskReviewDecisionOpenedFromDetail = false;
                return;
            }
            if (!window.__taskReviewDecisionOpenedFromDetail) {
                return;
            }
            window.__taskReviewDecisionOpenedFromDetail = false;
            var detailEl = document.getElementById("detail-task");
            if (!detailEl) {
                return;
            }
            setTimeout(function () {
                bootstrap.Modal.getOrCreateInstance(detailEl).show();
            }, 400);
        });
    }

    if (reviewDecisionModalForm) {
        reviewDecisionModalForm.addEventListener("submit", function () {
            window.__taskReviewDecisionSubmitting = true;
        });
        reviewDecisionModalForm.addEventListener("change", function (e) {
            if (e.target && e.target.name === "decision") {
                var isRevision = e.target.value === "revision";
                var wrap = document.getElementById("rd-revision-hours-wrap");
                if (wrap) {
                    wrap.classList.toggle("d-none", !isRevision);
                }
                var notes = document.getElementById("rd_revision_notes");
                if (notes) {
                    notes.required = isRevision;
                }
            }
        });
    }

    var rdModalBtn = document.getElementById("detail-review-decision-btn");
    if (rdModalBtn) {
        function prepareReviewDecisionForm() {
            var tpl = window.REVIEW_DECISION_URL_TEMPLATE;
            var form = document.getElementById("form-review-decision-modal");
            if (!tpl || !form) {
                return false;
            }
            var tid =
                (window.__lastTaskCardForDetail &&
                    window.__lastTaskCardForDetail.dataset.taskId) ||
                "";
            if (!tid) {
                return false;
            }
            form.action = tpl.replace("__TASK_ID__", tid);
            var dc = document.getElementById("rd_decision_complete");
            var dr = document.getElementById("rd_decision_revision");
            if (dc) {
                dc.checked = true;
            }
            if (dr) {
                dr.checked = false;
            }
            var rw = document.getElementById("rd-revision-hours-wrap");
            if (rw) {
                rw.classList.add("d-none");
            }
            var sel = document.getElementById("rd_revision_hours");
            if (sel) {
                sel.value = "2";
            }
            var notes = document.getElementById("rd_revision_notes");
            if (notes) {
                notes.value = "";
                notes.required = false;
            }
            var links = document.getElementById("rd_revision_links");
            if (links) links.value = "";
            if (window.__resetTaskPhotoUploader && window.__resetTaskPhotoUploader.rd_revision_photos) {
                window.__resetTaskPhotoUploader.rd_revision_photos();
            }
            return true;
        }

        function openReviewDecisionAfterDetailClosed() {
            var detailEl = document.getElementById("detail-task");
            var reviewEl = document.getElementById("review-decision-modal");
            if (!detailEl || !reviewEl) {
                return;
            }
            if (!prepareReviewDecisionForm()) {
                return;
            }
            window.__taskReviewDecisionOpenedFromDetail = true;
            var detailModal =
                bootstrap.Modal.getInstance(detailEl) ||
                bootstrap.Modal.getOrCreateInstance(detailEl);
            function onDetailHidden() {
                detailEl.removeEventListener("hidden.bs.modal", onDetailHidden);
                bootstrap.Modal.getOrCreateInstance(reviewEl).show();
            }
            detailEl.addEventListener("hidden.bs.modal", onDetailHidden, {
                once: true,
            });
            detailModal.hide();
        }

        rdModalBtn.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (rdModalBtn.classList.contains("d-none")) {
                return;
            }
            openReviewDecisionAfterDetailClosed();
        });
        rdModalBtn.addEventListener("keydown", function (e) {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                if (rdModalBtn.classList.contains("d-none")) {
                    return;
                }
                openReviewDecisionAfterDetailClosed();
            }
        });
    }

    var workResultsBtn = document.getElementById("btn-view-work-results");
    if (workResultsBtn) {
        workResultsBtn.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (window.__lastTaskCardForDetail) {
                openTaskWorkResultsModal(window.__lastTaskCardForDetail);
            }
        });
    }

    initTaskRunningTimers();
});

function formatTaskRunningCountdown(ms) {
    const totalSec = Math.floor(Math.abs(ms) / 1000);
    const h = Math.floor(totalSec / 3600);
    const m = Math.floor((totalSec % 3600) / 60);
    const s = totalSec % 60;
    const hms =
        String(h).padStart(2, "0") +
        ":" +
        String(m).padStart(2, "0") +
        ":" +
        String(s).padStart(2, "0");
    // Sebelum deadline: sisa waktu. Setelah habis: lanjut hitung kelebihan waktu (molor), dengan prefiks -
    if (ms > 0) {
        return hms;
    }
    return "-" + hms;
}

function updateOneTaskRunningTimer(el) {
    const frozenRaw = el.getAttribute("data-frozen-ms");
    if (frozenRaw != null && frozenRaw !== "") {
        const ms = parseInt(frozenRaw, 10);
        if (!Number.isNaN(ms)) {
            el.textContent = formatTaskRunningCountdown(ms);
            el.classList.toggle("task-running-timer--ok", ms > 0);
            el.classList.toggle("task-running-timer--late", ms <= 0);
        }
        return;
    }
    const raw = el.getAttribute("data-deadline");
    if (!raw) {
        return;
    }
    const end = Date.parse(raw);
    if (Number.isNaN(end)) {
        return;
    }
    const remain = end - Date.now();
    el.textContent = formatTaskRunningCountdown(remain);
    const overdue = remain <= 0;
    el.classList.toggle("task-running-timer--ok", !overdue);
    el.classList.toggle("task-running-timer--late", overdue);
}

function initTaskRunningTimers() {
    function tick() {
        document
            .querySelectorAll(
                ".task-running-timer[data-deadline], .task-running-timer[data-frozen-ms]"
            )
            .forEach(updateOneTaskRunningTimer);
    }
    tick();
    if (window.__taskRunningTimerInterval) {
        clearInterval(window.__taskRunningTimerInterval);
    }
    window.__taskRunningTimerInterval = setInterval(tick, 1000);
}

window.refreshTaskRunningTimerAfterStatus = function (
    columnItemEl,
    deadlineIso,
    showTimer,
    frozenRemainMs,
    trackingPayload
) {
    if (!columnItemEl) {
        return;
    }
    const el = columnItemEl.querySelector(".task-running-timer");
    if (!el) {
        return;
    }
    const frozenNum =
        frozenRemainMs != null && frozenRemainMs !== ""
            ? Number(frozenRemainMs)
            : NaN;
    const hasFrozen = showTimer && !Number.isNaN(frozenNum);
    const hasLive = showTimer && deadlineIso;

    if (hasFrozen) {
        el.classList.remove("d-none");
        el.removeAttribute("data-deadline");
        el.setAttribute("data-frozen-ms", String(frozenNum));
        updateOneTaskRunningTimer(el);
        updateTaskTrackingDataset(columnItemEl, trackingPayload);
        return;
    }
    if (hasLive) {
        el.classList.remove("d-none");
        el.removeAttribute("data-frozen-ms");
        el.setAttribute("data-deadline", deadlineIso);
        updateOneTaskRunningTimer(el);
        updateTaskTrackingDataset(columnItemEl, trackingPayload);
        return;
    }
    el.classList.add("d-none");
    el.removeAttribute("data-deadline");
    el.removeAttribute("data-frozen-ms");
    el.textContent = "--:--:--";
    el.classList.remove("task-running-timer--ok", "task-running-timer--late");

    updateTaskTrackingDataset(columnItemEl, trackingPayload);
};

// Fungsi untuk update data-task-status pada card dan status di modal detail
function updateTaskCardStatus(idTask, newStatus) {
    // Update data-task-status pada card
    var card = document.querySelector('.task-link[data-task-id="' + idTask + '"]');
    if (card) {
        card.dataset.taskStatus = newStatus;
        // Jika modal detail sedang terbuka dan menampilkan task ini, update juga status di modal
        var modal = document.getElementById('detail-task');
        if (modal && modal.classList.contains('show')) {
            // Cek apakah modal sedang menampilkan task yang sama
            var currentTitle = document.querySelector('.task-title').textContent;
            if (currentTitle === card.dataset.taskTitle) {
                var statusBadge = document.querySelector('.status-todo');
                statusBadge.textContent = newStatus;
                let statusColor = '#6c757d';
                switch (newStatus.toLowerCase()) {
                    case 'todo': statusColor = '#EA4949'; break;
                    case 'in progress': statusColor = '#FFB42E'; break;
                    case 'review': statusColor = '#6FAEC9'; break;
                    case 'revision': statusColor = '#C2410C'; break;
                    case 'complete': statusColor = '#7DB546'; break;
                }
                statusBadge.style.backgroundColor = statusColor;
                statusBadge.style.color = '#fff';
            }
        }
    }
}
// Contoh pemakaian:
// updateTaskCardStatus('id-task-uuid', 'In Progress');

function populateOwnershipRecipientSelect(selectEl, projectId, ownerId, selectedId) {
    if (!selectEl) return;
    const projects = window.__taskBoardProjects || [];
    const project = projects.find((p) => String(p.id) === String(projectId));
    selectEl.innerHTML = '<option value="" disabled selected>Pilih SDM</option>';
    if (!project || !project.sdms) return;
    project.sdms.forEach((sdm) => {
        if (ownerId && String(sdm.id) === String(ownerId)) return;
        const opt = document.createElement("option");
        opt.value = sdm.id;
        const isAbsent = Boolean(sdm.is_absent_now);
        const returnLabel = sdm.absent_returns_on_label
            ? ` — kembali ${sdm.absent_returns_on_label}`
            : "";
        opt.textContent = isAbsent
            ? `${sdm.name} (Absent${returnLabel})`
            : sdm.name;
        if (isAbsent) opt.disabled = true;
        if (selectedId && String(sdm.id) === String(selectedId)) {
            opt.selected = true;
        }
        selectEl.appendChild(opt);
    });
}

window.updateTaskDetailOwnershipUi = function (card) {
    const ownershipSection = document.getElementById("detail-ownership-section");
    if (!ownershipSection || !card) return;

    const canRequest = card.dataset.taskCanRequestOwnership === "1";
    const canDirect = card.dataset.taskCanDirectReassign === "1";
    const canReview = card.dataset.taskCanReviewOwnership === "1";
    const pendingId = card.dataset.taskPendingTransferId || "";
    const hasPending = Boolean(pendingId);

    const showOwnershipButtons = !hasPending && (canRequest || canDirect);
    ownershipSection.classList.toggle("d-none", !showOwnershipButtons);
    ownershipSection.classList.toggle("d-flex", showOwnershipButtons);

    const pendingWrap = document.getElementById("detail-ownership-pending");
    const pendingText = document.getElementById("detail-ownership-pending-text");
    const pendingTo = document.getElementById("detail-ownership-pending-to");
    const pendingReason = document.getElementById("detail-ownership-pending-reason");
    const btnRequest = document.getElementById("btn-request-ownership-transfer");
    const btnDirect = document.getElementById("btn-direct-ownership-reassign");

    btnRequest?.classList.toggle("d-none", !canRequest);
    btnDirect?.classList.toggle("d-none", !canDirect);

    if (hasPending) {
        pendingWrap?.classList.remove("d-none");
        if (pendingText) {
            pendingText.textContent = canReview
                ? "Pengajuan alih kepemilikan menunggu persetujuan Anda."
                : "Pengajuan alih kepemilikan menunggu persetujuan director.";
        }
        if (pendingTo) pendingTo.textContent = card.dataset.taskPendingTransferToUser || "-";
        if (pendingReason) pendingReason.textContent = card.dataset.taskPendingTransferReason || "-";
        const reviewActions = document.getElementById("detail-ownership-review-actions");
        reviewActions?.classList.toggle("d-none", !canReview);

        const approveForm = document.getElementById("form-ownership-approve");
        const rejectForm = document.getElementById("form-ownership-reject");
        const taskId = card.dataset.taskId || "";
        const routes = window.__ownershipDirectorRoutes || {};
        if (canReview && routes.approve && routes.reject) {
            if (approveForm) {
                approveForm.action = routes.approve
                    .replace("__TASK__", taskId)
                    .replace("__REQUEST__", pendingId);
            }
            if (rejectForm) {
                rejectForm.action = routes.reject
                    .replace("__TASK__", taskId)
                    .replace("__REQUEST__", pendingId);
            }
            populateOwnershipRecipientSelect(
                document.getElementById("ownership-approve-to-user"),
                card.dataset.taskProject || "",
                card.dataset.taskOwnerId || "",
                card.dataset.taskPendingTransferToUserId || ""
            );
        }
    } else {
        pendingWrap?.classList.add("d-none");
        document.getElementById("detail-ownership-review-actions")?.classList.add("d-none");
    }

    updateTaskWorkResultsButton(card);
};

function initTaskOwnershipTransferUi() {
    const transferModalEl = document.getElementById("ownership-transfer-modal");
    if (!transferModalEl) return;

    const transferForm = document.getElementById("ownership-transfer-form");
    const transferTitle = document.getElementById("ownership-transfer-modal-title");
    const transferTaskName = document.getElementById("ownership-transfer-task-name");
    const transferReason = document.getElementById("ownership-transfer-reason");
    const transferReasonHint = document.getElementById("ownership-transfer-reason-hint");
    const transferSubmit = document.getElementById("ownership-transfer-submit");
    const transferSelect = document.getElementById("ownership-transfer-to-user");
    const routes = window.__ownershipTransferRoutes || {};
    let transferMode = "request";

    function openTransferModal(card, mode) {
        if (!card || !transferForm) return;
        transferMode = mode;
        const taskId = card.dataset.taskId || "";
        const taskTitle = card.dataset.taskTitle || "-";
        const ownerId = card.dataset.taskOwnerId || "";
        const projectId = card.dataset.taskProject || "";

        if (transferTaskName) transferTaskName.textContent = taskTitle;
        populateOwnershipRecipientSelect(transferSelect, projectId, ownerId, null);

        if (mode === "direct" && routes.directorReassign) {
            transferForm.action = routes.directorReassign.replace("__TASK__", taskId);
            if (transferTitle) transferTitle.textContent = "Alihkan kepemilikan";
            if (transferSubmit) transferSubmit.textContent = "Alihkan";
            if (transferReason) transferReason.removeAttribute("required");
            if (transferReasonHint) transferReasonHint.textContent = "Opsional.";
        } else if (routes.staffRequest) {
            transferForm.action = routes.staffRequest.replace("__TASK__", taskId);
            if (transferTitle) transferTitle.textContent = "Ajukan alih kepemilikan";
            if (transferSubmit) transferSubmit.textContent = "Kirim pengajuan";
            if (transferReason) transferReason.setAttribute("required", "required");
            if (transferReasonHint) transferReasonHint.textContent = "Wajib diisi — jelaskan alasan pengajuan.";
        }

        const detailModal = document.getElementById("detail-task");
        if (detailModal) {
            const detailInst = bootstrap.Modal.getInstance(detailModal);
            if (detailInst) detailInst.hide();
        }

        setTimeout(function () {
            bootstrap.Modal.getOrCreateInstance(transferModalEl).show();
        }, 300);
    }

    document.getElementById("btn-request-ownership-transfer")?.addEventListener("click", function () {
        const card = window.__lastTaskCardForDetail;
        openTransferModal(card, "request");
    });

    document.getElementById("btn-direct-ownership-reassign")?.addEventListener("click", function () {
        const card = window.__lastTaskCardForDetail;
        openTransferModal(card, "direct");
    });

    transferModalEl.addEventListener("hidden.bs.modal", function () {
        if (transferForm) transferForm.reset();
        const detailModal = document.getElementById("detail-task");
        if (detailModal && window.__lastTaskCardForDetail) {
            setTimeout(function () {
                bootstrap.Modal.getOrCreateInstance(detailModal).show();
                window.updateTaskDetailOwnershipUi(window.__lastTaskCardForDetail);
            }, 200);
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    initTaskOwnershipTransferUi();

    const detailModal = document.getElementById("detail-task");
    if (detailModal) {
        detailModal.addEventListener("show.bs.modal", function (ev) {
            const card = ev.relatedTarget || window.__lastTaskCardForDetail;
            if (card && typeof window.updateTaskDetailOwnershipUi === "function") {
                window.updateTaskDetailOwnershipUi(card);
            }
            if (card && typeof updateTaskWorkResultsButton === "function") {
                updateTaskWorkResultsButton(card);
            }
        });
    }
});
