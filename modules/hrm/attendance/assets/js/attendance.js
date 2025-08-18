;(function($) {
    'use strict';

    Vue.config.debug = !!(wpErpAttendance.scriptDebug);

    var WeDevs_ERP_Attendance = {

    	initialize: function() {

            $( 'body').on( 'change', '#att-filter-duration', this.attendance.customFilter );

            // Attendance Employee Serlf Service
            this.attendance.updateClockPerSecond();

            // Print Attendance Summary
            $( 'body' ).on( 'click', '.att-summary-print', this.attendance.printSummary );

            // HR Reporting
            $( 'body').on( 'change', '#att-reporting-query', this.attendance.filterReporingHR );

            this.initTimePicker();
            this.initDatePicker();
        },

        initDatePicker: function() {
            $( '.attendance-date-field' ).datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+0',
            });
        },

        initTimePicker: function () {
            $( '.attendance-time-field' ).timepicker({
                'scrollDefault' : 'now',
                'step'          : 1,
                'timeFormat'    : 'H:i'
            });
        },

        attendance: {
            printSummary: function(e) {
                e.preventDefault();

                window.print();
            },

            reload: function() {
                $( '.erp-hr-attendance' ).load( window.location.href + ' .erp-hr-attendance' );
            },

            customFilter: function () {
                if ( 'custom' != this.value ) {
                    $( '#custom-input' ).remove();
                } else {
                    var element = '<span id="custom-input"><span>From </span><input name="start" class="attendance-date-field" type="text">&nbsp;<span>To </span><input name="end" class="attendance-date-field" type="text"></span>&nbsp;';
                    $( '#att-filter-duration' ).after( element );
                    WeDevs_ERP_Attendance.initDatePicker();
                }
            },

            filterReporingHR: function() {
                if ( 'custom' != this.value ) {
                    $( '#custom-input' ).remove();
                } else {
                    var element = '<span id="custom-input"><span>From </span><input name="start" class="attendance-date-field" type="text">&nbsp;<span>To </span><input name="end" class="attendance-date-field" type="text"></span>&nbsp;';
                    $( '#att-reporting-query' ).after( element );
                    WeDevs_ERP_Attendance.initDatePicker();
                }
            },

            getCurrentTime: function () {
                var currentTime = new Date ( );
                var currentHours = currentTime.getHours ( );
                var currentMinutes = currentTime.getMinutes ( );
                var currentSeconds = currentTime.getSeconds ( );

                currentMinutes = ( currentMinutes < 10 ? "0" : "" ) + currentMinutes;
                currentSeconds = ( currentSeconds < 10 ? "0" : "" ) + currentSeconds;

                var timeOfDay = ( currentHours < 12 ) ? "AM" : "PM";

                currentHours = ( currentHours > 12 ) ? currentHours - 12 : currentHours;
                currentHours = ( currentHours == 0 ) ? 12 : currentHours;

                var currentTimeString = currentHours + ":" + currentMinutes + ":" + currentSeconds + " " + timeOfDay;

                return currentTimeString;
            },

            updateClockPerSecond: function () {

                setInterval( function() {
                    var currentTimeString = WeDevs_ERP_Attendance.attendance.getCurrentTime();
                    $("#self-service-clock").html(currentTimeString);
                }, 1000 );
            }
        }
    }

    WeDevs_ERP_Attendance.initialize();

    Vue.directive('erp-datepicker', {
        params: ['exclude'],

        bind: function() {
            var settings = {
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+0',
            };

            switch(this.params.exclude) {
                case 'prev':
                    settings.minDate = 0;
                    break;

                case 'next':
                    settings.maxDate = 0;
                    break;

                default:
                    break;
            }

            $(this.el).datepicker(settings);
        }
    });

    Vue.directive('erp-timepicker', {
        params: ['scrollDefault', 'step', 'timeFormat', 'minTime', 'maxTime'],

        bind: function() {
            var settings = $.extend({
                scrollDefault: 'now',
                step: 15,
                timeFormat: 'H:i',
                minTime: '12:00am',
                maxTime: '24 hours after minTime'
            }, this.params);

            $(this.el).timepicker(settings);
        }
    });

    Vue.filter('shorttime', function (value) {
        return value.split(':').splice(0, 2).join(':');
    });

    if ($('#erp-new-attendance').length) {
        var hrInput = new Vue({
            el: '#erp-new-attendance',
            data: {
                date: '',
                attendance: [],
                allPresent: false,
                allAbsent: false,
                searchQuery: '',
                isFetchingResult: true
            },
            methods: {
                makeAllPresent: function() {
                    this.attendance.map(function(x) {
                        x.present = 'yes';
                        return x
                    });

                    this.allAbsent = false;
                },
                makeAllAbsent: function() {
                    this.attendance.map(function(x) {
                        x.present = 'no';
                        return x
                    });

                    this.allPresent = false;
                },
                getAttendance: function () {
                    return this.attendance.map(function (entry) {
                        var data = {
                            user_id:         entry.user_id,
                            dshift_id:       entry.dshift_id,
                            checkin_id:      entry.checkin_id,
                            checkout_id:     entry.checkout_id,
                            employee_id:     entry.employee_id,
                            employee_name:   entry.employee_name,
                            department_name: entry.department_name,
                            present:         entry.present,
                            shift:           entry.shift,
                            checkin:         entry.checkin,
                            checkout:        entry.checkout,
                            worktime:        entry.worktime
                        }

                        if ( entry.id ) {
                            data.id = entry.id;
                        }

                        return data;
                    });
                },
                submitForm: function (e) {
                    e.preventDefault();

                    if (!this.validateForm()) {
                        alert('You must entry the checkin time for all present employees');

                        return;
                    }

                    var data = {
                        action: 'erp_att_save_hr_input',
                        attendance: JSON.stringify(hrInput.getAttendance()),
                        date: hrInput.date,
                        nonce: wpErpAttendance.nonce
                    };

                    $('.spinner').addClass('is-active');

                    $.post(ajaxurl, data).done(function(data){
                        $('.spinner').removeClass('is-active');
                        window.location.replace(wpErpAttendance.att_main_url);
                    });
                },
                validateForm: function () {
                    var isValid = true;

                    this.attendance.forEach(function (record, i) {
                        if ('yes' === record.present && !record.checkin) {
                            isValid = false;
                            return;
                        }
                    });

                    return isValid;
                }
            },
            ready: function() {
                this.date = wpErpAttendance.current_date;
            },
            watch: {
                date: function() {
                    var data = {
                        action: 'erp_get_att_by_date',
                        date: this.date,
                        nonce: wpErpAttendance.nonce
                    };

                    var self = this;

                    self.isFetchingResult = true;

                    $.post(ajaxurl, data).done(function(data){
                        hrInput.attendance = data.data;
                        self.isFetchingResult = false;
                    });
                }
            }
        });
    }


    if ($('#erp-edit-attendance').length) {
        var hrInputEdit = new Vue({
            el: '#erp-edit-attendance',
            data: {
                date: '',
                attendance: [],
                allPresent: false,
                allAbsent: false,
                searchQuery: ''
            },
            methods: {
                makeAllPresent: function() {
                    this.attendance.map(function(x) {
                        x.present = 'yes';
                        return x;
                    });

                    this.allAbsent = false;
                },
                makeAllAbsent: function() {
                    this.attendance.map(function(x) {
                        x.present = 'no';
                        return x;
                    });

                    this.allPresent = false;
                },
                getAttendance: function () {                    
                    return this.attendance.map(function (entry) {
                        var data = {
                            user_id:         entry.user_id,
                            dshift_id:       entry.dshift_id,
                            checkin_id:      entry.checkin_id,
                            checkout_id:     entry.checkout_id,
                            employee_id:     entry.employee_id,
                            employee_name:   entry.employee_name,
                            department_name: entry.department_name,
                            present:         entry.present,
                            shift:           entry.shift,
                            checkin:         entry.checkin,
                            checkout:        entry.checkout,
                            worktime:        entry.worktime
                        }

                        if (entry.id) {
                            data.id = entry.id;
                        }

                        return data;
                    });
                },
                submitForm: function (e) {
                    e.preventDefault();

                    if (!this.validateForm()) {
                        alert('You must entry the checkin time for all present employees');

                        return;
                    }

                    var data = {
                        action: 'erp_att_save_hr_input',
                        attendance: JSON.stringify(hrInputEdit.getAttendance()),
                        date: hrInputEdit.date,
                        nonce: wpErpAttendance.nonce
                    };

                    $('.spinner').addClass('is-active');

                    $.post(ajaxurl, data).done(function(data){
                        $('.spinner').removeClass('is-active');
                        window.location.replace(wpErpAttendance.att_main_url);
                    });
                },
                validateForm: function () {
                    var isValid = true;

                    this.attendance.forEach(function (record, i) {
                        if ('yes' === record.present && !record.checkin) {
                            isValid = false;
                            return;
                        }
                    });

                    return isValid;
                }
            },
            ready: function() {
                this.date = wpErpAttendance.current_date;
            },
            watch: {
                date: function() {
                    var data = {
                        action: 'erp_get_att_by_date_for_edit',
                        date: this.date,
                        nonce: wpErpAttendance.nonce
                    };

                    $.post(ajaxurl, data).done(function(data){
                        hrInputEdit.attendance = data.data;
                    });
                }
            }
        });
    }

})(jQuery);