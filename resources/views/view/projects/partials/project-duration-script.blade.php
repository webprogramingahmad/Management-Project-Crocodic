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

    function addCalendarMonth(d) {
        var y = d.getFullYear();
        var mo = d.getMonth();
        var day = d.getDate();
        var next = new Date(y, mo + 1, day);
        if (next.getDate() !== day) {
            next.setDate(0);
        }
        return startOfDay(next);
    }

    function daysBetween(a, b) {
        var ms = 86400000;
        return Math.round((b.getTime() - a.getTime()) / ms);
    }

    function durationLabel(startStr, endStr) {
        var s = parseISODate(startStr);
        var e = parseISODate(endStr);
        if (!s || !e || e < s) {
            return '—';
        }
        s = startOfDay(s);
        e = startOfDay(e);
        var months = 0;
        var cursor = new Date(s.getTime());
        while (true) {
            var next = addCalendarMonth(cursor);
            if (next > e) {
                break;
            }
            cursor = next;
            months++;
        }
        var days = daysBetween(cursor, e);
        if (months === 0 && days === 0) {
            return '1 day';
        }
        var parts = [];
        if (months > 0) {
            parts.push(months === 1 ? '1 month' : months + ' months');
        }
        if (days > 0) {
            parts.push(days === 1 ? '1 day' : days + ' days');
        }
        return parts.length ? parts.join(', ') : '0 days';
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
