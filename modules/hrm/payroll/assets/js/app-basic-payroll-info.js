(function ( $ ) {
    var payitem_settings_wrapper = $( '#payitem-settings-wrapper' );
    var spinner = $( '.spinner' );
    if ( payitem_settings_wrapper.length > 0 ) {
        var payitem = new Vue({
            el: '#payitem-settings-wrapper',

            data: {
                payType: '',
                payItem: '',
                amountType: 0,
                payItemList: []
            },

            ready: function() {
                var self = this;
                self.payItem = '';
                self.getPayItem();
            },

            methods: {
                getPayItem: function(){
                    var self = this;
                    var ajaxurl = wpErpPayroll.ajaxurl;
                    spinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, { action: 'erp_payroll_get_payitem', _wpnonce: wpErpPayroll.nonce },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'payItemList', response.data );
                            }
                            spinner.css( { 'visibility': 'hidden' } );
                        }
                    );
                },

                addPayItem: function(){
                    var self = this;
                    spinner.css( { 'visibility': 'visible' } );
                    wp.ajax.send( 'erp_payroll_add_payitem', {
                        data: {
                            paytype: this.payType,
                            payitem: this.payItem,
                            amounttype: this.amountType,
                            _wpnonce: wpErpPayroll.nonce
                        },
                        success: function ( response ) {
                            spinner.css( { 'visibility': 'hidden' } );
                            swal( {
                                title: 'Success',
                                text: response,
                                type: 'success',
                                timer: 3000
                            } );
                            self.payItem = '';
                            self.getPayItem();
                        },
                        error: function ( error ) {
                            spinner.css( { 'visibility': 'hidden' } );
                            swal( {
                                title: 'Oops',
                                text: error,
                                type: 'error',
                                timer: 3000
                            } );
                        }
                    } );
                },

                deletePayItem: function(rowid){
                    var self = this;
                    if ( confirm( wpErpPayroll.remove_confirmation ) ) {
                        wp.ajax.send( 'erp_payroll_remove_payitem', {
                            data: {
                                id: rowid,
                                _wpnonce: wpErpPayroll.nonce
                            },
                            success: function ( response ) {
                                spinner.css( { 'visibility': 'hidden' } );
                                swal( {
                                    title: 'Success',
                                    text: response,
                                    type: 'success',
                                    timer: 3000
                                } );
                                self.getPayItem();
                            },
                            error: function ( error ) {
                                spinner.css( { 'visibility': 'hidden' } );
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

                popupEdit: function(payitemObj){
                    var self = this;
                    $.erpPopup( {
                        title: 'Edit Pay Item',
                        button: 'Update',
                        id: 'edit-payitem-top',
                        //content: wp.template( 'erp-rec-interview-template' )().trim(),
                        content: "<div><label>Pay Item</label>&nbsp;&nbsp;<input type='text' id='pitem' value='"+payitemObj.payitem+"'></div>",
                        extraClass: 'smaller',
                        onReady: function ( modal ) {
                            modal.enableButton();
                        },
                        onSubmit: function ( modal ) {
                            modal.disableButton();
                            wp.ajax.send( 'erp_payroll_edit_payitem', {
                                data: {
                                    id: payitemObj.id,
                                    payitem: $( '#pitem' ).val(),
                                    _wpnonce: wpErpPayroll.nonce
                                },
                                success: function ( response ) {
                                    spinner.css( { 'visibility': 'hidden' } );
                                    swal( {
                                        title: 'Success',
                                        text: response,
                                        type: 'success',
                                        timer: 3000
                                    } );
                                    modal.closeModal();
                                    self.getPayItem();
                                },
                                error: function ( error ) {
                                    spinner.css( { 'visibility': 'hidden' } );
                                    swal( {
                                        title: 'Oops',
                                        text: error,
                                        type: 'error',
                                        timer: 3000
                                    } );
                                    modal.closeModal();
                                }
                            } );
                        }
                    } );
                }
            }
        });
    }

    var basic_payroll_info_wrapper = $( '#basic-payroll-info-wrapper' );

    if ( basic_payroll_info_wrapper.length > 0 ) {

        var payItemSpinner = basic_payroll_info_wrapper.find( '.spinner' );
        var basicInfo_obj = new Vue( {
            el: '#basic-payroll-info-wrapper',

            data: {
                employee_tax_number: '',
                ordinary_rate: 0,
                bank_acc_number: '',
                bank_acc_name: '',
                bank_name: '',
                payment_method: '',
                pay_allowance_title: '',
                pay_allowance_amount: 0,
                paylist: [],
                pay_deduction_title: '',
                pay_deduction_amount: 0,
                deductionlist: [],
                pay_tax_title: '',
                pay_tax_amount: 0,
                taxlist: [],
                payItemList: [],
                payAllowanceItemList: [],
                payDeductionItemList: [],
                payTaxItemList: []
            },

            ready: function () {
                var self = this;
                self.getBasicInfo();
                self.getPaymentMethodInfo();

                self.getAllowances();
                self.getDeductions();
                self.getTaxInfo();

                self.getAllowancePayItem();
                self.getDeductionPayItem();
                self.getTaxPayItem();
            },

            methods: {
                getBasicInfo: function () {
                    var eid = this.getParameterByName( 'id' );
                    var self = this;
                    var ajaxurl = wpErpPayroll.ajaxurl;
                    payItemSpinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, { action: 'erp_payroll_get_basic_info', id: eid, _wpnonce: wpErpPayroll.nonce },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.employee_tax_number = response.data.employee_tax_number;
                                self.ordinary_rate = response.data.ordinary_rate;
                                self.bank_acc_number = response.data.bank_acc_number;
                                self.bank_acc_name = response.data.bank_acc_name;
                                self.bank_name = response.data.bank_name;
                                self.payment_method = response.data.payment_method;
                            }
                            payItemSpinner.css( { 'visibility': 'hidden' } );
                        }
                    );
                },

                getPaymentMethodInfo: function () {
                    var eid = this.getParameterByName( 'id' );
                    var self = this;
                    var ajaxurl = wpErpPayroll.ajaxurl;
                    payItemSpinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, { action: 'erp_payroll_get_payment_method', id: eid, _wpnonce: wpErpPayroll.nonce },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.payment_method = response.data.employee_payment_method;
                            }
                            payItemSpinner.css( { 'visibility': 'hidden' } );
                        }
                    );
                },

                getAllowancePayItem: function(){
                    var self = this;
                    var ajaxurl = wpErpPayroll.ajaxurl;
                    spinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, {
                            action: 'erp_payroll_get_payitem_by_type',
                            type: 'Allowance',
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'payAllowanceItemList', response.data );
                            }
                            spinner.css( { 'visibility': 'hidden' } );
                        }
                    );
                },

                getDeductionPayItem: function(){
                    var self = this;
                    var ajaxurl = wpErpPayroll.ajaxurl;
                    spinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, {
                            action: 'erp_payroll_get_payitem_by_type',
                            type: 'Deduction',
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'payDeductionItemList', response.data );
                            }
                            spinner.css( { 'visibility': 'hidden' } );
                        }
                    );
                },

                getTaxPayItem: function(){
                    var self = this;
                    var ajaxurl = wpErpPayroll.ajaxurl;
                    spinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, {
                            action: 'erp_payroll_get_payitem_by_type',
                            type: 'Tax',
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'payTaxItemList', response.data );
                            }
                            spinner.css( { 'visibility': 'hidden' } );
                        }
                    );
                },

                addPaymentMethodInfo: function () {
                    var eid = this.getParameterByName( 'id' );
                    if ( this.payment_method == '' ) {
                        swal( {
                            title: 'Oops',
                            text: wpErpPayroll.validation_message.empty_payment_method,
                            type: 'error',
                            timer: 3000
                        } );
                    } else {
                        payItemSpinner.css( { 'visibility': 'visible' } );
                        wp.ajax.send( 'erp_payroll_add_payment_method', {
                            data: {
                                id: eid,
                                payment_method: this.payment_method,
                                _wpnonce: wpErpPayroll.nonce
                            },
                            success: function ( response ) {
                                payItemSpinner.css( { 'visibility': 'hidden' } );
                                swal( {
                                    title: 'Success',
                                    text: response,
                                    type: 'success',
                                    timer: 3000
                                } );
                            },
                            error: function ( error ) {
                                payItemSpinner.css( { 'visibility': 'hidden' } );
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

                addBasicInfo: function () {
                    var eid = this.getParameterByName( 'id' );
                    var self = this;

                    payItemSpinner.css( { 'visibility': 'visible' } );
                    wp.ajax.send( 'erp_payroll_add_basic_info', {
                        data: {
                            id                 : eid,
                            employee_tax_number: self.employee_tax_number,
                            ordinary_rate      : self.ordinary_rate,
                            bank_acc_number    : self.bank_acc_number,
                            bank_acc_name      : self.bank_acc_name,
                            bank_name          : self.bank_name,
                            _wpnonce           : wpErpPayroll.nonce
                        },
                        success: function ( response ) {
                            payItemSpinner.css( { 'visibility': 'hidden' } );
                            swal( {
                                title: wpErpPayroll.success,
                                text: response,
                                type: 'success',
                                timer: 3000
                            } );
                            self.viewBasicInfo()
                        },
                        error: function ( error ) {
                            payItemSpinner.css( { 'visibility': 'hidden' } );
                            swal( {
                                title:  wpErpPayroll.oops,
                                text: error,
                                type: 'error',
                                timer: 3000
                            } );
                        }
                    } );
                },

                editBasicInfo: function () {
                    $('.edit_payroll').css( 'display', 'block' )
                    $('.view_payroll').css( 'display', 'none' )
                },

                viewBasicInfo: function () {
                    $('.edit_payroll').css( 'display', 'none' )
                    $('.view_payroll').css( 'display', 'block' )
                },

                savePayitem: function () {
                    var self = this;
                    var eid = this.getParameterByName( 'id' );
                    payItemSpinner.css( { 'visibility': 'visible' } );
                    wp.ajax.send( 'erp_payroll_add_payitem_info', {
                        data: {
                            eid: eid,
                            pay_allowance_title: self.pay_allowance_title,
                            pay_allowance_amount: self.pay_allowance_amount,
                            pay_item_all_or_ded: 1,
                            _wpnonce: wpErpPayroll.nonce
                        },
                        success: function ( response ) {
                            payItemSpinner.css( { 'visibility': 'hidden' } );
                            self.pay_allowance_amount = '';
                            self.getAllowances();
                        },
                        error: function ( error ) {
                            payItemSpinner.css( { 'visibility': 'hidden' } );
                            swal( {
                                title: 'Oops',
                                text: error,
                                type: 'error',
                                timer: 3000
                            } );
                        }
                    } );
                },

                VanishZero: function () {
                    if ( this.pay_allowance_amount == 0 ) {
                        this.pay_allowance_amount = '';
                    }
                },

                ReturnZero: function () {
                    if ( this.pay_allowance_amount == '' ) {
                        this.pay_allowance_amount = 0;
                    }
                },

                VanishZeroDeduct: function () {
                    if ( this.pay_deduction_amount == 0 ) {
                        this.pay_deduction_amount = '';
                    }
                },

                ReturnZeroDeduct: function () {
                    if ( this.pay_deduction_amount == '' ) {
                        this.pay_deduction_amount = 0;
                    }
                },

                VanishZeroTax: function () {
                    if ( this.pay_tax_amount == 0 ) {
                        this.pay_tax_amount = '';
                    }
                },

                ReturnZeroTax: function () {
                    if ( this.pay_tax_amount == '' ) {
                        this.pay_tax_amount = 0;
                    }
                },

                removePayitem: function ( rid ) {
                    if ( confirm( wpErpPayroll.remove_confirmation ) ) {
                        var self = this;
                        payItemSpinner.css( { 'visibility': 'visible' } );
                        wp.ajax.send( 'erp_payroll_remove_add_or_deduct_info', {
                            data: {
                                rowid: rid,
                                _wpnonce: wpErpPayroll.nonce
                            },
                            success: function ( response ) {
                                payItemSpinner.css( { 'visibility': 'hidden' } );
                                self.getAllowances();
                                self.getDeductions();
                                self.getTaxInfo();
                            },
                            error: function ( error ) {
                                payItemSpinner.css( { 'visibility': 'hidden' } );
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

                saveDeductItem: function () {
                    var self = this;
                    var eid = self.getParameterByName( 'id' );
                    payItemSpinner.css( { 'visibility': 'visible' } );
                    wp.ajax.send( 'erp_payroll_add_payitem_info', {
                        data: {
                            eid: eid,
                            pay_allowance_title: self.pay_deduction_title,
                            pay_allowance_amount: self.pay_deduction_amount,
                            pay_item_all_or_ded: 0,
                            _wpnonce: wpErpPayroll.nonce
                        },
                        success: function ( response ) {
                            payItemSpinner.css( { 'visibility': 'hidden' } );
                            self.pay_deduction_amount = '';
                            self.getDeductions();
                            window.scrollTo(0,0);
                        },
                        error: function ( error ) {
                            payItemSpinner.css( { 'visibility': 'hidden' } );
                            swal( {
                                title: 'Oops',
                                text: error,
                                type: 'error',
                                timer: 3000
                            } );
                        }
                    } );
                },

                saveTaxItem: function () {
                    var self = this;
                    var eid = self.getParameterByName( 'id' );
                    payItemSpinner.css( { 'visibility': 'visible' } );
                    wp.ajax.send( 'erp_payroll_add_payitem_info', {
                        data: {
                            eid: eid,
                            pay_allowance_title: self.pay_tax_title,
                            pay_allowance_amount: self.pay_tax_amount,
                            pay_item_all_or_ded: 2,
                            _wpnonce: wpErpPayroll.nonce
                        },
                        success: function ( response ) {
                            payItemSpinner.css( { 'visibility': 'hidden' } );
                            self.pay_tax_amount = '';
                            self.getTaxInfo();
                            window.scrollTo(0,0);
                        },
                        error: function ( error ) {
                            payItemSpinner.css( { 'visibility': 'hidden' } );
                            swal( {
                                title: 'Oops',
                                text: error,
                                type: 'error',
                                timer: 3000
                            } );
                        }
                    } );
                },

                getAllowances: function () {
                    var self = this;
                    var eid = self.getParameterByName( 'id' );
                    var ajaxurl = wpErpPayroll.ajaxurl;
                    payItemSpinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, {
                            action: 'erp_payroll_get_allowance_info',
                            eid: eid,
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.paylist = response.data;
                            }
                            payItemSpinner.css( { 'visibility': 'hidden' } );
                        }
                    );
                },

                getDeductions: function () {
                    var self = this;
                    var eid = self.getParameterByName( 'id' );
                    var ajaxurl = wpErpPayroll.ajaxurl;
                    payItemSpinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, {
                            action: 'erp_payroll_get_deduction_info',
                            eid: eid,
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.deductionlist = response.data;
                            }
                            payItemSpinner.css( { 'visibility': 'hidden' } );
                        }
                    );
                },

                getTaxInfo: function () {
                    var eid = this.getParameterByName( 'id' );
                    var self = this;
                    var ajaxurl = wpErpPayroll.ajaxurl;
                    payItemSpinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, {
                            action: 'erp_payroll_get_tax_info',
                            eid: eid,
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.taxlist = response.data;
                            }
                            payItemSpinner.css( { 'visibility': 'hidden' } );
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
