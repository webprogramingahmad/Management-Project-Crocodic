import "./bootstrap";
import * as bootstrap from "bootstrap";

const THEME_STORAGE_KEY = "theme";

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
    initThemeToggles();
    initProfilePopups();
    document.querySelectorAll('.task-link').forEach(function (card) {
        card.addEventListener('click', function () {
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
        });
    });

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
    if (ms <= 0) {
        return "00:00:00";
    }
    const totalSec = Math.floor(ms / 1000);
    const h = Math.floor(totalSec / 3600);
    const m = Math.floor((totalSec % 3600) / 60);
    const s = totalSec % 60;
    return (
        String(h).padStart(2, "0") +
        ":" +
        String(m).padStart(2, "0") +
        ":" +
        String(s).padStart(2, "0")
    );
}

function updateOneTaskRunningTimer(el) {
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
            .querySelectorAll(".task-running-timer[data-deadline]")
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
    showTimer
) {
    if (!columnItemEl) {
        return;
    }
    const el = columnItemEl.querySelector(".task-running-timer");
    if (!el) {
        return;
    }
    if (showTimer && deadlineIso) {
        el.classList.remove("d-none");
        el.setAttribute("data-deadline", deadlineIso);
        updateOneTaskRunningTimer(el);
    } else {
        el.classList.add("d-none");
        el.removeAttribute("data-deadline");
        el.textContent = "--:--:--";
        el.classList.remove("task-running-timer--ok", "task-running-timer--late");
    }
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
