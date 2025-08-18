(function ( $ ) {
    Vue.filter( 'custom_currency', function ( value ) {
        currency = wpErpPayroll.currency_symbol;
        return currency + "" + parseFloat( value ).toFixed( 2 ).replace( /(\d)(?=(\d{3})+\.)/g, "$1," );
    } );

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

    if ( $( '#pay-run-wrapper-approve-tab' ).length > 0 ) {
        var payrun_wrapper_spinner = $( '.spinner' );
        Vue.config.debug = wpErpPayroll.scriptDebug;
        var payRun_obj = new Vue( {
            el: '#pay-run-wrapper-approve-tab',

            data: {
                payrunid: '',
                cal_info: [],
                employeelist: [],
                payment_date: ''
            },

            ready: function () {
                var self = this;
                self.payrunid = this.getParameterByName('prid');
                this.getCalendarInfo();
                //this.getPaymentDate();
                this.getEmployeeList();
                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
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
                    return (this.totalBasic +  this.totalPayment) - (this.totalDeduction + this.totalTax);
                },

                approve_status: function(){
                    if ( this.cal_info[ 0 ] ) {
                        if ( this.cal_info[ 0 ].approve_status == '0' ) {
                            return 'Not Approved';
                        } else {
                            return 'Approved';
                        }
                    } else {
                        return 'Not Approved';
                    }
                }
            },

            methods: {
                getCalendarInfo: function () {
                    var self = this;
                    payrun_wrapper_spinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, {
                            action: 'erp_payroll_get_calendar_info',
                            prid: self.payrunid,
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'cal_info', response.data );
                                self.payment_date = ( self.cal_info[ 0 ] ? self.cal_info[ 0 ].payment_date : self.payment_date );
                            }
                        }
                    );
                },

                getEmployeeList: function (calid) {
                    var self = this;
                    payrun_wrapper_spinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, {
                            action: 'erp_payroll_get_employee_list_by_calid',
                            prid: self.payrunid,
                            _wpnonce: wpErpPayroll.nonce },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'employeelist', response.data );
                            } else {
                                swal({
                                    title: 'Caution!',
                                    text: response.data,
                                    type: 'error'
                                });
                            }
                        }
                    );
                },

                getPaymentDate: function() {
                    var self = this;
                    $.get( ajaxurl, { action: 'erp_payroll_get_payment_date', _wpnonce: wpErpPayroll.nonce },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'payment_date', response.data );
                            }
                        }
                    );
                },

                totalRowNetPay: function(basic, payment, deduction, tax){
                    return  ( parseFloat( basic ) +  parseFloat( payment ) ) - parseFloat( deduction ) - parseFloat( tax );
                },

                approve: function(){
                    var self = this;
                    swal({
                        title: wpErpPayroll.confirm_payrun_approve_msg,
                        text: wpErpPayroll.unable_to_revert,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Confirm'
                        }, () => {
                        wp.ajax.send( 'erp_payroll_approve_payment', {
                            data: {
                                payrunid: self.payrunid,
                                payment_date: self.payment_date,
                                employeedata: self.employeelist,
                                _wpnonce: wpErpPayroll.nonce
                            },
                            success: function ( response ) {
                                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                                swal( {
                                    title: 'Success',
                                    text: response,
                                    type: 'success',
                                    timer: 3000
                                } );
                                location.href = wpErpPayroll.payrun_url;
                            },
                            error: function ( error ) {
                                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                                swal( {
                                    title: 'Oops',
                                    text: error,
                                    type: 'error',
                                    timer: 3000
                                } );
                            }
                        } );
                    });
                },

                approveUndo: function(){
                    var self = this;
                    if ( confirm( wpErpPayroll.confirm_payrun_undoapprove_msg ) ) {
                        wp.ajax.send( 'erp_payroll_undo_approve_payment', {
                            data: {
                                payrunid: self.payrunid,
                                _wpnonce: wpErpPayroll.nonce
                            },
                            success: function ( response ) {
                                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                                swal( {
                                    title: 'Success',
                                    text: response,
                                    type: 'success',
                                    timer: 3000
                                } );
                                location.href = wpErpPayroll.payrun_url;
                            },
                            error: function ( error ) {
                                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                                swal( {
                                    title: 'Oops',
                                    text: error,
                                    type: 'error',
                                    timer: 3000
                                } );
                            }
                        } );
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
