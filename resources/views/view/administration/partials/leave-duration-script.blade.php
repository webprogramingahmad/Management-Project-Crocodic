<script>
(function () {
    function parseISODate(s) {
        if (!s || typeof s !== 'string' || !/^\d{4}-\d{2}-\d{2}$/.test(s.trim())) {
            return null;
        }
        var p = s.trim().split('-').map(Number);
        return new Date(p[0], p[1] - 1, p[2]);
    }

    function startOfDay(d) {
        return new Date(d.getFullYear(), d.getMonth(), d.getDate());
    }

    function inclusiveDays(a, b) {
        var ms = 86400000;
        return Math.round((b.getTime() - a.getTime()) / ms) + 1;
    }

    function durationLabel(startStr, endStr) {
        var s = parseISODate(startStr);
        var e = parseISODate(endStr);
        if (!s || !e || e < s) {
            return '—';
        }
        s = startOfDay(s);
        e = startOfDay(e);
        var days = inclusiveDays(s, e);
        return days === 1 ? '1 day' : days + ' days';
    }

    function updateDuration() {
        var wrap = document.querySelector('[data-project-duration-wrap]');
        if (!wrap) return;
        var form = wrap.closest('form');
        if (!form) return;
        var startEl = form.querySelector('[name="start_date"]');
        var endEl = form.querySelector('[name="end_date"]');
        var out = document.getElementById('project-duration-display');
        if (!startEl || !endEl || !out) return;
        out.value = durationLabel(startEl.value, endEl.value);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var wrap = document.querySelector('[data-project-duration-wrap]');
        if (!wrap) return;
        var form = wrap.closest('form');
        if (!form) return;
        ['change', 'input', 'blur'].forEach(function (ev) {
            form.querySelectorAll('[name="start_date"],[name="end_date"]').forEach(function (el) {
                el.addEventListener(ev, updateDuration);
            });
        });
        updateDuration();
    });
})();
</script>
