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

    if ( $( '#pay-run-wrapper-payslips-tab' ).length > 0 ) {
        var payrun_wrapper_spinner = $( '.spinner' );
        Vue.config.debug = wpErpPayroll.scriptDebug;
        var payRun_payslip_obj = new Vue( {
            el: '#pay-run-wrapper-payslips-tab',

            data: {
                payrunid : '',
                payment_date: '',
                selectedemp: 0,
                selectedemp_name: '',
                pay_basic: 0,
                employeelist: [],
                cal_info: {},
                additional_info: [],
                deduct_info: [],
                emp_info: [],
                extra_info: {}
            },

            ready: function () {
                var self = this;
                self.payrunid = this.getParameterByName('prid');
                self.getEmployeeList();
                self.getCalendarInfo();
            },

            computed: {
                total_payment: function(){
                    var adata = this.additional_info;
                    var total = 0;
                    for ( var key in adata ) {
                        total = total + parseFloat( adata[ key ].pay_item_amount );
                    }
                    return total  + parseFloat( this.pay_basic ) ;
                },

                total_deduction: function(){
                    var adata = this.deduct_info;
                    var total = 0;
                    for ( var key in adata ) {
                        total = total + parseFloat( adata[ key ].pay_item_amount );
                    }
                    return total;
                },

                net_pay: function(){
                    return this.total_payment - this.total_deduction;
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
                },

                draft_text: function(){
                    if ( this.cal_info[ 0 ] ) {
                        if ( this.cal_info[ 0 ].approve_status == '0' ) {
                            return 'Draft';
                        } else {
                            return '';
                        }
                    } else {
                        return '';
                    }
                }
            },

            methods: {
                getEmployeeList: function () {
                    var self = this;
                    payrun_wrapper_spinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, {
                            action: 'erp_payroll_get_employee_list_by_calid',
                            prid: self.payrunid,
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'selectedemp', response.data[ 0 ].empid);
                                self.$set( 'employeelist', response.data );

                                self.get_pay_basic(response.data[ 0 ].empid);
                                self.getFirstEmpAdditionInfoCalId(response.data[ 0 ].empid);
                                self.getFirstEmpSubtractInfoByCalId(response.data[ 0 ].empid);
                                self.getPaySlipEmpInfo(response.data[ 0 ].empid);
                                self.getPaySlipExtraInfo(response.data[ 0 ].empid);
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
                            }
                        }
                    );
                },

                getPaySlipEmpInfo: function (eid) {
                    var self = this;
                    payrun_wrapper_spinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, { action: 'erp_payroll_get_employee_info', eid: eid, _wpnonce: wpErpPayroll.nonce },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'emp_info', response.data );
                            }
                        }
                    );
                },

                getPaySlipExtraInfo: function (eid) {
                    var self = this;
                    payrun_wrapper_spinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, { action: 'erp_payroll_get_extra_info', eid: eid, _wpnonce: wpErpPayroll.nonce },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'extra_info', response.data );
                            }
                        }
                    );
                },

                totalRowNetPay: function(basic, payment, deduction){
                    return parseFloat( basic ) + parseFloat( payment ) - parseFloat( deduction );
                },

                changeSelectedEmp: function(selectedEmpp){
                    var self = this;
                    if ( selectedEmpp == null ) {
                        self.getEmployeeList();
                    } else {
                        self.getFirstEmpAdditionInfoCalId(selectedEmpp.empid);
                        self.getFirstEmpSubtractInfoByCalId(selectedEmpp.empid);
                        self.get_pay_basic(selectedEmpp.empid);
                        self.getPaySlipEmpInfo(selectedEmpp.empid);
                        self.selectedemp = selectedEmpp.empid;
                        self.selectedemp_name = selectedEmpp.display_name;
                    }
                },

                get_pay_basic: function(selected_emp){
                    var self = this;
                    var emplist = self.employeelist;
                    for ( var key in emplist ) {
                        if ( selected_emp === emplist[ key ].empid ) {
                            self.pay_basic = parseFloat( emplist[ key ].pay_basic );
                        }
                    }
                },

                getFirstEmpAdditionInfoCalId: function(empid){
                    var self = this;
                    payrun_wrapper_spinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, {
                            action: 'erp_payroll_get_first_employee_info_by_calid',
                            prid: self.payrunid,
                            eid: empid,
                            add_or_deduct: 1,
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'additional_info', response.data );
                            }
                        }
                    );
                },

                getFirstEmpSubtractInfoByCalId: function(empid){
                    var self = this;
                    payrun_wrapper_spinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, {
                            action: 'erp_payroll_get_first_employee_info_by_calid',
                            prid: self.payrunid,
                            eid: empid,
                            add_or_deduct: 0,
                            _wpnonce: wpErpPayroll.nonce },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'deduct_info', response.data );
                            }
                        }
                    );
                },

                printPayslip: function(){
                    // var divName = "printPayslipArea";
                    // var printContents = document.getElementById(divName).innerHTML;
                    // var originalContents = document.body.innerHTML;
                    //
                    // document.body.innerHTML = printContents;
                    //
                    // window.print();
                    //
                    // document.body.innerHTML = originalContents;
                    window.print();
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

function get_preview( prid, eid, callback = null ) {
    jQuery.erpPopup({
        title: "Employee Payslip",
        id: "view_employee_payslip_modal",
        extraClass : 'erp_employee_payslip_popup',
        button: 'Print',
        content: jQuery( '#payslip_preview' ).html(),
        onReady: function() {
            var modal = this;
            jQuery('header', modal).after(jQuery('<div class="loader"></div>').show());
            var data = {
                _wpnonce  : wpErpPayroll.nonce,
                action    : 'erp_payroll_get_payslip_by_payrun',
                prid: prid,
                eid: eid,
            };

            jQuery.post(wpErp.ajaxurl, data).done(function(response) {
                if ( response.success === true ) {
                    var payslip_data = response.data;

                    var total_payment = 0;
                    var total_deduction = 0;

                    jQuery( '#company_name' ).html( payslip_data.company_name );
                    jQuery( '#company_address' ).html( payslip_data.company_address );

                    jQuery( '#emp_name' ).html( payslip_data.emp_name );
                    jQuery( '#emp_address' ).html( payslip_data.emp_address );

                    jQuery( '#emp_dept' ).html( payslip_data.emp_details.dept );
                    jQuery( '#emp_desig' ).html( payslip_data.emp_details.desig );
                    jQuery( '#emp_period' ).html( payslip_data.emp_calendar_info.from_date + ' to ' + payslip_data.emp_calendar_info.to_date );

                    jQuery( '#emp_payment_date' ).html( payslip_data.emp_calendar_info.payment_date );
                    jQuery( '#emp_tax_number' ).html( payslip_data.emp_details.tax_number );
                    jQuery( '#emp_bank_acc_number' ).html( payslip_data.emp_details.bank_acc_number );
                    jQuery( '#emp_payment_method' ).html( payslip_data.emp_details.payment_method );

                    if ( payslip_data.emp_details.basic_pay == '-' ) {
                        payslip_data.emp_details.basic_pay = 0;
                    }

                    jQuery( '#emp_basic_pay' ).html( wpErpPayroll.currency_symbol + parseFloat( payslip_data.emp_details.basic_pay ).toFixed(2) );

                    var emp_added_payrun = "";

                    payslip_data.emp_added_payrun.map( function ( item, index ) {
                        emp_added_payrun += "<li>";
                        emp_added_payrun += "<label class='text-alignleft'>" + item.payitem + "</label>";
                        emp_added_payrun += "<label class='text-alignright'>" + wpErpPayroll.currency_symbol + item.allowance + "</label>";
                        emp_added_payrun += "</li>";
                        total_payment += parseFloat( item.allowance );
                    } );

                    jQuery( '#emp_added_payrun' ).replaceWith( emp_added_payrun );

                    var emp_deducted_payrun = "";

                    payslip_data.emp_deducted_payrun.map( function ( item, index ) {
                        emp_deducted_payrun += "<li>";
                        emp_deducted_payrun += "<label class='text-alignleft'>" + item.payitem + "</label>";
                        emp_deducted_payrun += "<label class='text-alignright'>" + wpErpPayroll.currency_symbol + item.deduction + "</label>";
                        emp_deducted_payrun += "</li>";
                        total_deduction += parseFloat( item.deduction );
                    } );

                    jQuery( '#emp_deducted_payrun' ).replaceWith( emp_deducted_payrun );

                    var base_payment = parseFloat(total_payment ) + parseFloat( payslip_data.emp_details.basic_pay );
                    var net_payment  = parseFloat(base_payment ) - parseFloat( total_deduction );

                    jQuery( '#total_payment' ).html( wpErpPayroll.currency_symbol + base_payment.toFixed(2) );
                    jQuery( '#total_deduction' ).html( wpErpPayroll.currency_symbol + total_deduction.toFixed(2) );
                    jQuery( '#total_net_payment' ).html( wpErpPayroll.currency_symbol + net_payment.toFixed(2) );

                    jQuery( '.loader', modal).remove();

                    if ( callback ) {
                        callback();
                    }
                }
                //jQuery( '#view_date_log_tbody' ).html( str );
                jQuery( '.loader', modal).remove();
            }).always(function() {

            });
        },
        onSubmit: function (modal) {
            window.print();
            jQuery( '.erp-loader' ).addClass('erp-hide');
        }
    });
}
