import "./bootstrap";
import * as bootstrap from "bootstrap";

const THEME_STORAGE_KEY = "theme";

function initBootstrapComponents() {
    // Pastikan komponen Bootstrap tersedia sebagai global untuk inline scripts.
    if (!window.bootstrap) {
        window.bootstrap = bootstrap;
    }

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
        var editTaskIdInput = document.getElementById("edit_task_id");
        var openedTaskId = editTaskIdInput ? editTaskIdInput.value : "";
        var cardTaskId = card.getAttribute("data-task-id") || "";
        if (openedTaskId && cardTaskId && openedTaskId === cardTaskId) {
            setModalTimeTrackingFromCard(card);
        }
    }
}

function syncEditTaskModalFromCard(card) {
    var normal = document.getElementById("edit-task-normal-fields");
    var mainSubmit = document.getElementById("edit-task-submit-btn");
    if (normal) {
        normal.classList.remove("d-none");
    }
    if (mainSubmit) {
        mainSubmit.classList.remove("d-none");
    }
}

function setModalRevisionNoteFromCard(card) {
    const noteRow = document.querySelector(".modal-revision-note-row");
    const noteEl = document.querySelector(".modal-revision-note");
    if (!noteRow || !noteEl) return;
    const attr = card.getAttribute("data-task-revision-note");
    let note = null;
    if (attr != null && attr !== "") {
        try {
            note = JSON.parse(attr);
        } catch (e) {
            note = null;
        }
    }
    const text = note != null ? String(note).trim() : "";
    if (text) {
        noteEl.textContent = text;
        noteRow.classList.remove("d-none");
    } else {
        noteEl.textContent = "";
        noteRow.classList.add("d-none");
    }
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

document.addEventListener("DOMContentLoaded", function () {
    initBootstrapComponents();
    initThemeToggles();
    initProfilePopups();
    document.querySelectorAll('.task-link').forEach(function (card) {
        card.addEventListener('click', function () {
            window.__lastTaskCardForEdit = this;
            var taskId = card.dataset.taskId || '';
            // Title
            document.querySelector('.task-title').textContent = card.dataset.taskTitle || '-';
            // Status
            var statusText = card.dataset.taskStatus || '-';
            var statusBadge = document.querySelector('.status-todo');
            statusBadge.textContent = statusText;
            let statusColor = '#6c757d';
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

            var editTaskIdInput = document.getElementById('edit_task_id');
            if (editTaskIdInput) {
                editTaskIdInput.value = taskId;
            }
            var editTaskNameInput = document.getElementById('edit_task_name');
            if (editTaskNameInput) {
                editTaskNameInput.value = card.dataset.taskTitle || '';
            }
            var editDiffId = card.dataset.taskDiffid;
            if (editDiffId) {
                var diffRadio = document.getElementById('edit_diff_' + editDiffId);
                if (diffRadio) diffRadio.checked = true;
            }

            var editTaskDesc = document.getElementById("edit_task_description");
            if (editTaskDesc) {
                var descAttr = card.getAttribute("data-task-description-json");
                var descVal = "";
                if (descAttr != null && descAttr !== "") {
                    try {
                        var parsed = JSON.parse(descAttr);
                        descVal = parsed != null ? String(parsed) : "";
                    } catch (e) {
                        descVal = "";
                    }
                }
                editTaskDesc.value = descVal;
            }

            setModalTaskDescriptionFromCard(card);
            setModalRevisionNoteFromCard(card);
            setModalTimeTrackingFromCard(card);

            var editLink = document.getElementById("edit-task-link");
            if (editLink) {
                var r = card.dataset.taskRole;
                var st = (card.dataset.taskStatus || "").toLowerCase();
                var hideEdit =
                    r === "executive" || (r === "staff" && st === "review");
                editLink.classList.toggle("d-none", hideEdit);
            }

            var rdFooter = document.getElementById("detail-task-review-footer");
            var rdBtn = document.getElementById("detail-review-decision-btn");
            if (rdFooter && rdBtn) {
                var isReview = statusText.toLowerCase() === "review";
                rdFooter.classList.toggle("d-none", !isReview);
                rdBtn.classList.toggle("d-none", !isReview);
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
                (document.getElementById("edit_task_id") || {}).value ||
                (window.__lastTaskCardForEdit &&
                    window.__lastTaskCardForEdit.dataset.taskId) ||
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

    /*
     * View task → Edit: tutup modal detail dulu, lalu buka edit (satu modal aktif).
     * Menghindari dua tombol X sekaligus — klik X sering menutup detail (bukan edit) sehingga semua overlay hilang.
     */
    var editFromDetailLink = document.getElementById("edit-task-link");
    if (editFromDetailLink) {
        function openEditAfterDetailClosed() {
            var detailEl = document.getElementById("detail-task");
            var editEl = document.getElementById("edit-task");
            if (!detailEl || !editEl) {
                return;
            }
            window.__taskEditOpenedFromDetail = true;
            var detailModal =
                bootstrap.Modal.getInstance(detailEl) ||
                bootstrap.Modal.getOrCreateInstance(detailEl);
            function onDetailHidden() {
                detailEl.removeEventListener("hidden.bs.modal", onDetailHidden);
                bootstrap.Modal.getOrCreateInstance(editEl).show();
            }
            detailEl.addEventListener("hidden.bs.modal", onDetailHidden, { once: true });
            detailModal.hide();
        }
        editFromDetailLink.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (editFromDetailLink.classList.contains("d-none")) {
                return;
            }
            openEditAfterDetailClosed();
        });
        editFromDetailLink.addEventListener("keydown", function (e) {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                openEditAfterDetailClosed();
            }
        });
    }

    var editTaskModalEl = document.getElementById("edit-task");
    if (editTaskModalEl) {
        editTaskModalEl.addEventListener("show.bs.modal", function () {
            var c = window.__lastTaskCardForEdit;
            if (c) {
                syncEditTaskModalFromCard(c);
            }
        });
        editTaskModalEl.addEventListener("hidden.bs.modal", function () {
            if (!window.__taskEditOpenedFromDetail) {
                return;
            }
            window.__taskEditOpenedFromDetail = false;
            var detailEl = document.getElementById("detail-task");
            if (!detailEl) {
                return;
            }
            setTimeout(function () {
                bootstrap.Modal.getOrCreateInstance(detailEl).show();
            }, 400);
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
