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

    Vue.component( 'multiselect', VueMultiselect.default );

    if ( $( '#pay-run-wrapper-variable-input-tab' ).length > 0 ) {
        var payrun_wrapper_spinner = $( '.spinner' );
        Vue.config.debug = wpErpPayroll.scriptDebug;
        var payRun_obj = new Vue( {
            el: '#pay-run-wrapper-variable-input-tab',

            data: {
                payrunid: '',
                payment_date: '',
                selectedemp: 0,
                pay_basic: 0,
                employeelist: [],
                additional_info: [],
                deduct_info: [],
                cal_info: [],
                additional_basic_pay_title: '',
                additional_basic_pay_amount: '',
                additional_payments_title: '',
                additional_payments_amount: '',
                additional_payment_non_taxable_title: '',
                additional_payment_non_taxable_amount: '',
                additional_deduction_title: '',
                additional_deduction_amount: '',
                additional_basic_pay_amount_note: '',
                additional_payments_amount_note: '',
                additional_payment_non_taxable_amount_note: '',
                additional_deduction_amount_note: '',
                emloyee: {},
                payAllowanceItemList: [],
                payNonTaxbleItemList: [],
                payDeductionItemList: []
            },

            ready: function () {
                var self = this;
                self.payrunid = this.getParameterByName('prid');
                this.getEmployeeList();
                this.getCalendarInfo();

                self.getAllowancePayItem();
                self.getNonTaxablePayItem();
                self.getDeductionPayItem();
                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
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
                    return   this.total_payment  - this.total_deduction;
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

                removeBtn_status: function(){
                    if ( this.cal_info[ 0 ] ) {
                        if ( this.cal_info[ 0 ].approve_status == '0' ) {
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        return true;
                    }
                }

            },

            methods: {
                getEmployeeList: function () {
                    var self = this;
                    $.get( ajaxurl, {
                            action: 'erp_payroll_get_employee_list_by_calid',
                            calid: self.calid,
                            prid: self.payrunid,
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'selectedemp', response.data[ 0 ].empid );
                                self.$set( 'employeelist', response.data );
                                self.get_pay_basic(response.data[ 0 ].empid);
                                self.getFirstEmpAdditionInfoByCalId(response.data[ 0 ].empid);
                                self.getFirstEmpSubtractInfoByCalId(response.data[ 0 ].empid);
                                self.employee = {
                                    'empid': response.data[ 0 ].empid,
                                    'display_name': response.data[ 0 ].display_name,
                                    'dept': response.data[ 0 ].dept,
                                    'desig': response.data[ 0 ].desig,
                                    'pay_basic': response.data[ 0 ].pay_basic,
                                    'payment': response.data[ 0 ].payment,
                                    'deduction': response.data[ 0 ].deduction,
                                    'tax': response.data[ 0 ].tax
                                };
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
                            calid: self.paycalid,
                            prid: self.payrunid,
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'cal_info', response.data );
                                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                            }
                        }
                    );
                },

                getFirstEmpAdditionInfoByCalId: function(empid){
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
                                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                            }
                        }
                    );
                },

                getFirstEmpSubtractInfoByCalId: function(empid){
                    var self = this;
                    payrun_wrapper_spinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, {
                            action: 'erp_payroll_get_first_employee_info_by_calid',
                            eid: empid,
                            prid: self.payrunid,
                            add_or_deduct: 0,
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'deduct_info', response.data );
                                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                            }
                        }
                    );
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

                getAllowancePayItem: function(){
                    var self = this;
                    payrun_wrapper_spinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, {
                            action: 'erp_payroll_get_payitem_by_type',
                            type: 'Allowance',
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'payAllowanceItemList', response.data );
                            }
                            payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                        }
                    );
                },

                getNonTaxablePayItem: function(){
                    var self = this;
                    payrun_wrapper_spinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, {
                            action: 'erp_payroll_get_payitem_by_type',
                            type: 'Non-Taxable Payments',
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'payNonTaxbleItemList', response.data );
                            }
                            payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                        }
                    );
                },

                getDeductionPayItem: function(){
                    var self = this;
                    payrun_wrapper_spinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, {
                            action: 'erp_payroll_get_payitem_by_type',
                            type: 'Deduction',
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'payDeductionItemList', response.data );
                            }
                            payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                        }
                    );
                },

                changeSelectedEmp: function(selectedEmpp){
                    var self = this;
                    if ( selectedEmpp == null ) {
                        self.getEmployeeList();
                    } else {
                        self.getFirstEmpAdditionInfoByCalId(selectedEmpp.empid);
                        self.getFirstEmpSubtractInfoByCalId(selectedEmpp.empid);
                        self.get_pay_basic(selectedEmpp.empid);
                        self.selectedemp = selectedEmpp.empid;
                    }
                },

                addAdditionBasicPay: function(){
                    var self = this;
                    if ( self.additional_basic_pay_amount == '' || isNaN(self.additional_basic_pay_amount) ) {
                        swal( {
                            title: 'Oops',
                            text: 'Please input number only in amount field!',
                            type: 'error',
                            timer: 3000
                        } );
                    } else {
                        //insert this all addiotional allowance and deduction to DB
                        wp.ajax.send( 'erp_payroll_add_additional_allowance_deduction', {
                            data: {
                                eid: self.selectedemp,
                                calid: self.cal_info[ 0 ].id,
                                payment_date: self.cal_info[ 0 ].payment_date,
                                additional_info: 1,
                                deduct_info: 0,
                                payrunid: self.payrunid,
                                pay_item: self.additional_basic_pay_title,
                                pay_item_amount: self.additional_basic_pay_amount,
                                note: self.additional_basic_pay_amount_note,
                                _wpnonce: wpErpPayroll.nonce
                            },
                            success: function ( response ) {
                                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                                self.additional_basic_pay_amount = '';
                                self.additional_basic_pay_amount_note = '';
                                self.getFirstEmpAdditionInfoByCalId(self.selectedemp);
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

                addAdditionalPayment: function(){
                    var self = this;
                    if ( this.additional_payments_amount == '' || isNaN(this.additional_payments_amount) ) {
                        swal( {
                            title: 'Oops',
                            text: 'Please input number only in amount field!',
                            type: 'error',
                            timer: 3000
                        } );
                    } else {
                        //insert this all addiotional allowance and deduction to DB
                        wp.ajax.send( 'erp_payroll_add_additional_allowance_deduction', {
                            data: {
                                eid: self.selectedemp,
                                calid: self.cal_info[ 0 ].id,
                                payment_date: self.cal_info[ 0 ].payment_date,
                                additional_info: 1,
                                deduct_info: 0,
                                payrunid: self.payrunid,
                                pay_item: self.additional_payments_title,
                                pay_item_amount: self.additional_payments_amount,
                                note: self.additional_payments_amount_note,
                                _wpnonce: wpErpPayroll.nonce
                            },
                            success: function ( response ) {
                                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                                self.additional_payments_amount = '';
                                self.additional_payments_amount_note = '';
                                self.getFirstEmpAdditionInfoByCalId(self.selectedemp);
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

                addAdditionalPaymentNonTaxable: function(){
                    var self = this;
                    if ( this.additional_payment_non_taxable_amount == '' || isNaN(this.additional_payment_non_taxable_amount) ) {
                        swal( {
                            title: 'Oops',
                            text: 'Please input number only in amount field!',
                            type: 'error',
                            timer: 3000
                        } );
                    } else {
                        //insert this all addiotional allowance and deduction to DB
                        wp.ajax.send( 'erp_payroll_add_additional_allowance_deduction', {
                            data: {
                                eid: self.selectedemp,
                                calid: self.cal_info[ 0 ].id,
                                payment_date: self.cal_info[ 0 ].payment_date,
                                additional_info: 1,
                                deduct_info: 0,
                                payrunid: self.payrunid,
                                pay_item: self.additional_payment_non_taxable_title,
                                pay_item_amount: self.additional_payment_non_taxable_amount,
                                note: self.additional_payment_non_taxable_amount_note,
                                _wpnonce: wpErpPayroll.nonce
                            },
                            success: function ( response ) {
                                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                                self.additional_payment_non_taxable_amount = '';
                                self.additional_payment_non_taxable_amount_note = '';
                                self.getFirstEmpAdditionInfoByCalId(self.selectedemp);
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

                addAdditionalDeduction: function(){
                    var self =this;
                    if ( this.additional_deduction_amount == '' || isNaN(this.additional_deduction_amount) ) {
                        swal( {
                            title: 'Oops',
                            text: 'Please input number only in amount field!',
                            type: 'error',
                            timer: 3000
                        } );
                    } else {
                        //insert this all addiotional allowance and deduction to DB
                        wp.ajax.send( 'erp_payroll_add_additional_allowance_deduction', {
                            data: {
                                eid: self.selectedemp,
                                calid: self.cal_info[ 0 ].id,
                                payment_date: self.cal_info[ 0 ].payment_date,
                                additional_info: 0,
                                deduct_info: 1,
                                payrunid: self.payrunid,
                                pay_item: self.additional_deduction_title,
                                pay_item_amount: self.additional_deduction_amount,
                                note: self.additional_deduction_amount_note,
                                _wpnonce: wpErpPayroll.nonce
                            },
                            success: function ( response ) {
                                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                                self.additional_deduction_amount = '';
                                self.additional_deduction_amount_note = '';
                                self.getFirstEmpSubtractInfoByCalId(self.selectedemp);
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

                deleteAddItem: function(payitemObj){
                    payrun_wrapper_spinner.css( { 'visibility': 'visible' } );
                    var self = this;
                    $.post( ajaxurl, {
                            action: 'erp_payroll_delete_extra_payment_info',
                            payrunid: self.payrunid,
                            eid: payitemObj.empid,
                            payitem: payitemObj.payitem,
                            add_or_deduct: 1,
                            _wpnonce: wpErpPayroll.nonce },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.additional_info.$remove(payitemObj);
                                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                            }
                        }
                    );
                },

                deleteDeductItem: function(payitemObj){
                    payrun_wrapper_spinner.css( { 'visibility': 'visible' } );
                    var self = this;
                    $.post( ajaxurl, {
                            action: 'erp_payroll_delete_extra_payment_info',
                            eid: payitemObj.empid,
                            payitem: payitemObj.payitem,
                            add_or_deduct: 0,
                            payrunid: self.payrunid,
                            _wpnonce: wpErpPayroll.nonce },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.deduct_info.$remove(payitemObj);
                                payrun_wrapper_spinner.css( { 'visibility': 'hidden' } );
                            }
                        }
                    );
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
