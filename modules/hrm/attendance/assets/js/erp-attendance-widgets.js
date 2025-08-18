;
(function($) {
    'use strict';

    Vue.config.debug = !!(erpAttendanceWidgets.scriptDebug);

    $('.erp-attendance-status-widget').each(function() {
        var widget = this;

        new Vue({
            el: widget,

            data: {
                i18n: erpAttendanceWidgets.i18n,
                filter: 'this_month',
                attendance_data: [],
                doingAjax: false
            },

            ready: function() {
                var self = this;

                this.getAttendanceData();

                // this event triggers fron self service widget
                $(window).on('erp-att-refresh-status-widget', function(e) {
                    self.getAttendanceData();
                })
            },

            methods: {

                getAttendanceData: function() {
                    var self = this;

                    var data = {
                        query: self.filter || 'today',
                        action: 'erp_att_get_attendance_data',
                        _wpnonce: erpAttendanceWidgets.nonce
                    };

                    self.doingAjax = true;

                    $.get(erpAttendanceWidgets.ajaxurl, data, function(response) {
                        self.doingAjax = false;

                        self.attendance_data = response.data;

                        if (self.attendance_data.length) {
                            setTimeout(function() {
                                self.drawChart();
                            }, 400);
                        }

                    });
                },

                drawChart: function() {
                    $.plot('#erp-attendance-status-chart-' + this._uid, this.attendance_data, {
                        series: {
                            pie: {
                                show: true,
                                radius: 1,
                                innerRadius: 0.4,
                                label: {
                                    show: true,
                                    radius: 2 / 3,
                                    formatter: function(label, series) {
                                        return '<div class="erp-flot-pie-label">' + series.data[0][1] + '</div>';
                                    },
                                }
                            },
                            grid: {
                                hoverable: true
                            },
                            tooltip: {
                                show: true
                            }
                        }
                    });
                }
            },

            watch: {
                filter: function() {
                    this.getAttendanceData();
                }
            }
        });
    });

    $('.erp-att-self-service-widget').each(function() {
        var widget = this;

        new Vue({
            el: widget,

            data: {
                i18n: erpAttendanceWidgets.i18n,
                attendance: {
                    ds_id        : 0,
                    log_id       : 0,
                    shift_title  : '',
                    min_checkin  : '',
                    max_checkout : ''
                },
                isReady: false,
                doingAjax: false,
                digital_clock: '',
                shift_time: '',
                workingTime: 0,
                totalSeconds: 0,
                shift_assigned: true,
                disable_checking_button: false,
                is_timer_running: false,
                last_checkin_to_current_diff: 0
            },

            ready: function() {
                var self = this;

                this.getTimeDetails();
                this.getAttendanceUserLog();
                this.updateClockPerSecond();
                
                /*window.onbeforeunload = function(event) {
                    self.setAttendanceUserLogEverySec();
                };*/
            },

            methods: {
                getAttendanceUserLog: function() {
                    var self = this;

                    var data = {
                        action: 'erp_att_get_attendance_user_log',
                        _wpnonce: erpAttendanceWidgets.nonce
                    };

                    self.doingAjax = true;

                    $.get(erpAttendanceWidgets.ajaxurl, data, function(res) {
                        self.doingAjax = false;

                        if ( res.data.max_checkout == '0000-00-00 00:00:00' ) {
                            self.last_checkin_to_current_diff = res.data.curnt_timestamp - res.data.max_checkin ;
                        }

                        if (! $.isEmptyObject[res] ) {
                            var checkout = moment( res.data.max_checkout );

                            if ( res.data.min_checkin && ! checkout.isValid() ) {
                                self.disable_checking_button = true;
                            }
                            
                        }

                    });
                },

                setAttendanceUserLogEverySec: function() {
                    var self = this;

                    if ( self.disable_checking_button && ( self.totalSeconds > self.attendance.log_time ) ) {
                        window.localStorage.runningTime = this.totalSeconds ;
                    }

                },

                saveAttendance: function( type ) {
                    var self = this;

                    self.doingAjax = false;

                    var data = {
                        _wpnonce: erpAttendanceWidgets.nonce,
                        action: 'erp_att_save_self_attendance'
                    };

                    $.post(erpAttendanceWidgets.ajaxurl, data).done(function(response) {


                        if ( ! response.success) {
                            swal({
                                type: 'warning',
                                title: 'Warning',
                                text: response.data,
                                footer: ''
                            });
                            return;
                        }

                        $(window).trigger('erp-att-refresh-status-widget');

                        if ( type == 'checkin' ) {
                            self.disable_checking_button = true;
                            swal({
                                type: 'success',
                                title: 'Success',
                                text: self.i18n.thanksForCheckin,
                                footer: ''
                            });
                            self.getTimeDetails();
                        } else {
                            self.disable_checking_button = false;
                            swal({
                                type: 'success',
                                title: 'Success',
                                text: self.i18n.thanksForCheckout,
                                footer: ''
                            });
                            self.getTimeDetails();
                            self.totalSeconds = 0;
                            self.workingTime = 0;
                            self.last_checkin_to_current_diff = 0;
                            clearInterval(self.is_timer_running);
                            window.localStorage.removeItem('runningTime');
                        }

                    }).always(function() {
                        self.doingAjax = false;
                    });
                },

                updateClockPerSecond: function() {
                    var self = this,
                        currentTime = new Date(),
                        currentHours = currentTime.getHours(),
                        currentMinutes = currentTime.getMinutes(),
                        currentSeconds = currentTime.getSeconds();

                    currentMinutes = (currentMinutes < 10 ? '0' : '') + currentMinutes;
                    currentSeconds = (currentSeconds < 10 ? '0' : '') + currentSeconds;

                    var timeOfDay = (currentHours < 12) ? '<span>AM</span>' : '<span>PM</span>';

                    currentHours = (currentHours > 12) ? currentHours - 12 : currentHours;
                    currentHours = (currentHours == 0) ? 12 : currentHours;

                    this.digital_clock = currentHours + ':' + currentMinutes + ':' + currentSeconds + ' ' + timeOfDay;

                    setTimeout(function() {
                        self.updateClockPerSecond();
                    }, 1000);
                },

                zeroPad: function(val) {
                    return val > 9 ? val : '0' + val;
                },

                countTimer: function() {

                    ++this.totalSeconds;

                    var hour = this.zeroPad(Math.floor(this.totalSeconds / 3600));
                    var minute = this.zeroPad(Math.floor((this.totalSeconds - hour * 3600) / 60));
                    var seconds = this.zeroPad(this.totalSeconds - (hour * 3600 + minute * 60));

                    this.workingTime = hour + ':' + minute + ':' + seconds;
                },

                timeDiffBetween: function(from, to) {

                    var date1 = new Date("01/01/2015 " + from);
                    var date2 = new Date("01/01/2015 " + to);

                    var diff = date2.getTime() - date1.getTime();

                    var msec = diff;
                    var hh = Math.floor(msec / 1000 / 60 / 60);
                    msec -= hh * 1000 * 60 * 60;
                    var mm = Math.floor(msec / 1000 / 60);
                    msec -= mm * 1000 * 60;
                    var ss = Math.floor(msec / 1000);
                    msec -= ss * 1000;

                    return hh + ':' + mm + ':' + ss;
                },

                timeConvFrom24: function(time24) {
                    var H = +time24.substr(0, 2);
                    var h = (H % 12) || 12;
                    h = (h < 10) ? ('0' + h) : h;
                    var ampm = H < 12 ? ' AM' : ' PM';
                    var ts = h + time24.substr(2, 3) + ampm;

                    return ts;
                },

                getTimeDetails : function() {

                    var self = this,
                        data = {
                            action: 'erp_att_get_employee_attendance_data',
                            _wpnonce: erpAttendanceWidgets.nonce
                        };
                    this.doingAjax = true;

                    $.get( erpAttendanceWidgets.ajaxurl, data).done(function(response) {
                        if (response.data.attendance.ds_id) {

                            if ( response.data.attendance.log_time == null ) {
                                response.data.attendance.log_time = 0;
                            }

                            self.$set('attendance', response.data.attendance);
                            if ( self.attendance.min_checkin && self.attendance.log_id && self.attendance.max_checkout === '00:00:00' ) {
                                self.totalSeconds = self.attendance.log_time;
                                var last_check_find = setInterval(function(){
                                    if( self.disable_checking_button &&
                                        self.last_checkin_to_current_diff &&
                                        self.last_checkin_to_current_diff > 0
                                    ){
                                        console.log('Log - ', self.attendance.log_time);
                                        console.log('Diff - ', self.last_checkin_to_current_diff);
                                        self.totalSeconds = parseInt(self.last_checkin_to_current_diff) + parseInt(self.attendance.log_time);
                                        self.last_checkin_to_current_diff = 0;
                                        clearInterval(last_check_find);
                                    }
                                },1000);
                                self.is_timer_running = setInterval(self.countTimer, 1000);
                            }
                        } else {
                            self.shift_assigned = false;

                            self.$set('attendance', {
                                ds_id        : 0,
                                log_id       : 0,
                                shift_title  : '',
                                min_checkin  : '',
                                max_checkout : ''
                            });
                        }

                        self.$set('shift_time', response.data.shift_time);
                        self.$set('doingAjax', false);
                        self.$set('isReady', true);
                    });
                }

            },
        });
    });

})(jQuery);