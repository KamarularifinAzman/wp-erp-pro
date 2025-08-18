export default {
    ready() {
        jQuery('.erp-date-field').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            yearRange: '-100:+0',
        });

        jQuery('.erp-time-field').timepicker({
            'scrollDefault': 'now',
            'step': 15
        });
    }
}