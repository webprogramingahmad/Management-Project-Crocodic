<script>
(function () {
    function openDatePicker(el) {
        if (el.type === 'text') {
            el.type = 'date';
        }
        if (typeof el.showPicker === 'function') {
            try {
                el.showPicker();
                return;
            } catch (e) {
                /* fall through */
            }
        }
        el.focus();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input.project-date-picker').forEach(function (el) {
            el.addEventListener('focus', function () {
                if (el.type === 'text') {
                    el.type = 'date';
                }
            });
            el.addEventListener('dblclick', function (e) {
                e.preventDefault();
                openDatePicker(el);
            });
        });
    });
})();
</script>
