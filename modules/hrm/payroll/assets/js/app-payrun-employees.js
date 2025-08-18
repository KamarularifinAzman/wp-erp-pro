(function ( $ ) {
    Vue.filter( 'custom_currency', function(value){
        currency = wpErpPayroll.currency_symbol;
        return currency + "" + parseFloat(value).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
    });

    // Vue directive for Date picker
    Vue.directive( 'datepicker', {
        params: [ 'datedisable' ],
        twoWay: true,
        bind: function () {
            var vm = this.vm;
            var key = this.expression;

            if ( this.params.datedisable == 'previous' ) {
                $( this.el ).datepicker( {
                    minDate: 0,
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    yearRange: '-100:+0',
                    onClose: function ( date ) {
                        vm.$set( key, date );
                    }
                } );
            } else if ( this.params.datedisable == 'upcomming' ) {
                $( this.el ).datepicker( {
                    maxDate: 0,
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    yearRange: '-100:+0',
                    onClose: function ( date ) {
                        vm.$set( key, date );
                    }
                } );
            } else {
                $( this.el ).datepicker( {
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    yearRange: '-100:+0',
                    onClose: function ( date ) {
                        if ( date.match( /^(0?[1-9]|[12][0-9]|3[01])[\/\-\.](0?[1-9]|1[012])[\/\-\.]\d{4}$/ ) )
                            vm.$set( key, date );
                        else {
                            vm.$set( key, "" );
                        }
                    }
                } );
            }
        },
        update: function ( val ) {
            $( this.el ).datepicker( 'setDate', val );
        }
    } );

    if ( $( '#pay-run-wrapper-employees' ).length > 0 ) {
        var payrun_wrapper_spinner = $( '.spinner' );
        Vue.config.debug = wpErpPayroll.scriptDebug;
        var payRun_obj = new Vue( {
            el: '#pay-run-wrapper-employees',

            data: {
                payrunid         : '',
                payment_date     : '',
                from_date        : '',
                to_date          : '',
                employeelist     : [],
                cal_info         : [],
                empidlist        : [],
                paycalid         : '',
                specify_pay_item : false,
                dateVerified     : false,
            },

            ready: function () {
                var self = this;
                var payment_date = self.getParameterByName('payment_date');
                self.payrunid = self.getParameterByName('prid') || 0;
                self.paycalid = self.getParameterByName('calid') || 0;
                //set current date as payment date
                var today = new Date();
                var dd = today.getDate();
                var mm = today.getMonth()+1; //January is 0!
                var yyyy = today.getFullYear();
                if ( payment_date == '' ) {
                    self.payment_date = yyyy + '-' + mm + '-' + dd;
                } else {
                    self.payment_date = payment_date;
                }
                //self.setPaymentDate(calid);
                // self.getEmployeeList();
                self.getCalendarInfo();
                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                
                jQuery( 'body' ).on( 'click', '#is_specific_field', function(event){
                    if( event.target.checked ) {
                        jQuery( '.select_pay_item_class' ).css( 'display', 'block' );
                        jQuery( '#pay_item_dropdown' ).select2();
                        self.specify_pay_item = true;
                    } else {
                        jQuery( '.select_pay_item_class' ).css( 'display', 'none' );
                        self.specify_pay_item = false;
                    }
                } )
            },

            computed: {
                totalBasic: function(){
                    var edata = this.employeelist;
                    var total = 0;
                    for ( var key in edata ) {
                        total = total + parseFloat( edata[ key ].pay_basic )
                    }
                    return total;
                },

                totalPayment: function(){
                    var edata = this.employeelist;
                    var total = 0;
                    for ( var key in edata ) {
                        total = total + parseFloat( edata[ key ].payment )
                    }
                    return total;
                },

                totalDeduction: function(){
                    var edata = this.employeelist;
                    var total = 0;
                    for ( var key in edata ) {
                        total = total + parseFloat( edata[ key ].deduction )
                    }
                    return total;
                },

                totalTax: function(){
                    var edata = this.employeelist;
                    var total = 0;
                    for ( var key in edata ) {
                        total = total + parseFloat( edata[ key ].tax )
                    }
                    return total;
                },

                totalNetTotal: function(){
                    return  (this.totalBasic +  this.totalPayment) - this.totalDeduction - this.totalTax;
                },

                approve_status: function(){
                    if ( this.cal_info[ 0 ] ) {
                        if ( this.cal_info[ 0 ].approve_status == '0' ) {
                            return wpErpPayroll.not_approved;
                        } else {
                            return wpErpPayroll.approved;
                        }
                    } else {
                        return wpErpPayroll.not_approved;
                    }
                }
            },

            watch: {
                payment_date: function(){
                    $.post( ajaxurl, { action: 'erp_payroll_set_payment_date', payment_date: this.payment_date, _wpnonce: wpErpPayroll.nonce },
                        function ( response ) {
                            if ( response.success === true ) {}
                        }
                    );
                }
            },

            methods: {
                getEmployeeList: function () {
                    var self = this;
                    self.$set( 'employeelist', [] );
                    self.$set( 'empidlist', [] );

                    payrun_wrapper_spinner.css( { 'visibility': 'visible' } );

                    $.get( ajaxurl, {
                        action    : 'erp_payroll_get_employee_list_by_calid',
                        prid      : self.payrunid,
                        calid     : self.paycalid,
                        from_date : self.from_date,
                        to_date   : self.to_date,
                        _wpnonce  : wpErpPayroll.nonce
                    }, function ( response ) {
                        if ( response.success === true ) {
                            self.$set( 'employeelist', response.data );

                            for ( var key in self.employeelist ) {
                                self.empidlist.push( {
                                    id : self.employeelist[ key ].empid,
                                    pay_basic : self.employeelist[ key ].pay_basic
                                } );
                            }

                        } else {
                            swal({
                                title: 'Caution!',
                                text: response.data,
                                type: 'error'
                            });
                        }
                    });
                },

                getCalendarInfo: function () {
                    var self = this;
                    payrun_wrapper_spinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, {
                            action: 'erp_payroll_get_calendar_info',
                            calid: self.paycalid,
                            prid: self.payrunid,
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'cal_info', response.data );
                                //set from date and to date
                                self.from_date = ( self.cal_info[ 0 ] ? self.cal_info[ 0 ].from_date : '' );
                                self.to_date = ( self.cal_info[ 0 ] ? self.cal_info[ 0 ].to_date : self.payment_date );
                                self.payment_date = ( self.cal_info[ 0 ] ? self.cal_info[ 0 ].payment_date : self.payment_date );
                            }
                        }
                    );
                },

                totalRowNetPay: function(basic, payment, deduction, tax){
                    return  (parseFloat( basic ) +  parseFloat( payment )) - parseFloat( deduction ) - parseFloat(tax);
                },

                setPaymentDate: function(calid){
                    $.post( ajaxurl, { action: 'erp_payroll_set_payment_date', payment_date: this.payment_date, _wpnonce: wpErpPayroll.nonce },
                        function ( response ) {
                            if ( response.success === true ) {}
                        }
                    );
                },

                verifyDate: function() {
                    var self      = this,
                        caltype   = self.cal_info[ 0 ].pay_calendar_type,
                        error_msg = '',
                        dateRangeCheck = self.dateRangeValidation( caltype, self.from_date, self.to_date, self.payment_date );
                    
                    self.dateVerified = false;
                    
                    if ( self.cal_info[ 0 ].approve_status == '0' && dateRangeCheck !== 'pass' ) {
                        error_msg = dateRangeCheck;
                    }

                    if ( self.cal_info[ 0 ].approve_status == '0' && self.paymendDateValidation( self.from_date, self.payment_date ) == "fail" ) {
                        error_msg = wpErpPayroll.validation_message.pay_date;
                    }

                    if ( self.from_date == '' ) {
                        error_msg = wpErpPayroll.validation_message.null_from_date;
                    }

                    if ( self.to_date == '' ) {
                        error_msg = wpErpPayroll.validation_message.null_to_date;
                    }

                    if ( self.payment_date == '' ) {
                        error_msg = wpErpPayroll.validation_message.null_pay_date;
                    }

                    if ( '' != error_msg ) {
                        swal( {
                            title: 'Oops',
                            text: error_msg,
                            type: 'error',
                            timer: 9000
                        } );
                    } else {
                        self.getEmployeeList();
                        self.dateVerified = true;
                    }
                },

                updateDateandGoNextStep: function(){
                    var self      = this,
                        caltype   = self.cal_info[ 0 ].pay_calendar_type,
                        error_msg = '',
                        tab       = 'variable_input',
                        dateRangeCheck = self.dateRangeValidation( caltype, self.from_date, self.to_date, self.payment_date );

                    if ( self.cal_info[ 0 ].approve_status == '0' && dateRangeCheck !== 'pass' ) {
                        error_msg = dateRangeCheck;
                    }

                    if ( self.cal_info[ 0 ].approve_status == '0' && self.paymendDateValidation( self.from_date, self.payment_date ) == "fail" ) {
                        error_msg = wpErpPayroll.validation_message.pay_date;
                    }

                    if ( self.from_date == '' ) {
                        error_msg = wpErpPayroll.validation_message.null_from_date;
                    }

                    if ( self.to_date == '' ) {
                        error_msg = wpErpPayroll.validation_message.null_to_date;
                    }

                    if ( self.payment_date == '' ) {
                        error_msg = wpErpPayroll.validation_message.null_pay_date;
                    }

                    if ( error_msg == '' ) {
                        payrun_wrapper_spinner.css( { 'visibility': 'visible' } );
                        wp.ajax.send( 'erp_payroll_start_variable_input', {
                            data: {
                                payrunid: self.payrunid,
                                calid: self.paycalid,
                                from_date: self.from_date,
                                to_date: self.to_date,
                                payment_date: self.payment_date,
                                empidlist: self.empidlist,
                                specify_pay_item:self.specify_pay_item,
                                selected_pay_items : JSON.stringify( $('.pay_item_dropdown').val() ),
                                _wpnonce: wpErpPayroll.nonce
                            },
                            success: function ( response ) {
                                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                                var prunid = response.prun;
                                location.href = wpErpPayroll.payrun_url + '&tab=' + tab + '&prid=' + prunid;
                            },
                            error: function ( error ) {
                                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                                swal( {
                                    title: 'Oops',
                                    text: error,
                                    type: 'error',
                                    timer: 9000
                                } );
                            }
                        } );
                    } else {
                        swal( {
                            title: 'Oops',
                            text: error_msg,
                            type: 'error',
                            timer: 9000
                        } );
                    }
                },

                dateRangeValidation: function(caltype, fromdate, todate, paymentdate){
                    var from_date = new Date(fromdate);
                    var to_date = new Date(todate);
                    var payment_date = new Date(paymentdate);
                    var timeDiff = Math.abs( to_date.getTime() - from_date.getTime() );
                    var diffDays = Math.ceil( timeDiff / (1000 * 3600 * 24) + 1 );

                    var timeDiffPayment = Math.abs( payment_date.getTime() - from_date.getTime() );
                    var diffDaysPayment = Math.ceil( timeDiffPayment / (1000 * 3600 * 24) + 1 );

                    switch( caltype ) {
                        case 'hourly':
                            return ( diffDays >= 1 && diffDays <= 31 ) ? 'pass' : wpErpPayroll.validation_message.date_range.hourly;

                        case 'weekly':
                            return ( diffDays >= 7 && diffDays <= 9 ) ? 'pass' : wpErpPayroll.validation_message.date_range.weekly;

                        case 'biweekly':
                            return ( diffDays >= 14 && diffDays <= 17 ) ? 'pass' : wpErpPayroll.validation_message.date_range.biweekly;

                        case 'monthly':
                            return ( diffDays >= 28 && diffDays <= 31 ) ? 'pass' : wpErpPayroll.validation_message.date_range.monthly;
                    }
                },

                paymendDateValidation: function(fromdate, paymentdate){
                    var from_date = new Date(fromdate);
                    var payment_date = new Date(paymentdate);

                    var timeDiffPayment = payment_date.getTime() - from_date.getTime();
                    var diffDaysPayment = Math.ceil( timeDiffPayment / (1000 * 3600 * 24) + 1 );

                    if ( diffDaysPayment > 0 ) {
                        return "pass";
                    } else {
                        return "fail";
                    }
                },

                getParameterByName: function ( name ) {
                    name = name.replace( /[\[]/, "\\[" ).replace( /[\]]/, "\\]" );
                    var regex = new RegExp( "[\\?&]" + name + "=([^&#]*)" ),
                        results = regex.exec( location.search );
                    return results === null ? "" : decodeURIComponent( results[ 1 ].replace( /\+/g, " " ) );
                }
            }
        } );
    }

})( jQuery );
