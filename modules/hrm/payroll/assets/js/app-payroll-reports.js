(function ( $ ) {
    Vue.filter( 'custom_currency', function(value){
        currency = wpErpPayroll.currency_symbol;
        return currency + "" + parseFloat(value).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
    });

    if ( $( '#summary-reports-wrapper' ).length > 0 ) {
        var summarySpinner = $( '.spinner' );
        var reportsummary_obj = new Vue( {
            el: '#summary-reports-wrapper',

            data: {
                from_date: '',
                to_date: '',
                paysumdata: [],
                grossTotal: 0
            },

            ready: function () {
                //this.getSumReportData();
            },

            computed: {
                paymentData(){
                    let total = 0;
                    let data = []
                    this.paysumdata.forEach(item=>{
                        item.net_pay = (parseFloat(item.gross_wages) + parseFloat(item.allowance_amount)) - (parseFloat(item.deduction_amount) + parseFloat(item.tax_amount))
                        total +=  item.net_pay
                        data.push(item)
                    })
                    this.grossTotal = total;
                    return  this.paysumdata;
                }/*,
                grossTotal: function(){
                    var rowsNetPay = this.paysumdata;
                    var gt = 0;
                    for ( var key in rowsNetPay ) {
                        gt = gt + parseFloat( rowsNetPay[ key ].net_pay );
                    }
                    return gt;
                }*/
            },

            methods: {
                getSumReportData: function(){
                    if(!this.from_date  || !this.to_date ) {
                        swal( {
                            title: wpErpPayroll.oops,
                            text: wpErpPayroll.select_date_range,
                            type: 'error',
                            timer: 2000
                        } );
                        return false;
                    }

                    summarySpinner.css( { 'visibility': 'visible' } );
                    // set new csv url
                    var get_base_url = $( '#hidden-base-url' ).val();
                    var current_url = get_base_url + '&from_date=' + this.from_date + '&to_date=' + this.to_date;
                    $( '#csv-dl-link' ).attr( 'href', current_url + '&func=payroll-summary-report-csv' );
                    $( '#csv-dr-link' ).attr( 'href', current_url + '&func=payroll-details-report-csv' );
                    $( '#csv-br-link' ).attr( 'href', current_url + '&func=payroll-bank-report-csv' );
                    //
                    $.get( ajaxurl, { action: 'erp_payroll_get_paysum_info', from_date: this.from_date, to_date: this.to_date, _wpnonce: wpErpPayroll.nonce },
                         ( response )=> {
                            if ( response.success === true ) {
                                reportsummary_obj.$set( 'paysumdata', response.data );
                            }
                            summarySpinner.css( { 'visibility': 'hidden' } );
                        }
                    );
                },

                netpaycalculation: function(gross_wages, allowances, deductions, taxes){
                    return parseFloat(gross_wages) + parseFloat(allowances) - parseFloat(deductions) - parseFloat(taxes);
                },

                clearData: function(){
                    this.from_date = '';
                    this.to_date = '';
                }
            }
        } );
    }

    if ( $( '#employee-report-wrapper' ).length > 0 ) {
        var empSpinner = $( '.spinner' );
        var reportemp_obj = new Vue( {
            el: '#employee-report-wrapper',

            data: {
                from_date: '',
                to_date: '',
                payempdata: []
            },

            computed: {
                egrossTotal: function(){
                    var rowsNetPay = this.payempdata;
                    var gt = 0;
                    for ( var key in rowsNetPay ) {
                        gt = gt + parseFloat( rowsNetPay[ key ].net_pay );
                    }
                    return gt;
                }
            },

            methods: {
                getEmpReportData: function(){
                    if(!this.from_date  || !this.to_date ) {
                        swal( {
                            title: wpErpPayroll.oops,
                            text: wpErpPayroll.select_date_range,
                            type: 'error',
                            timer: 2000
                        } );
                        return false;
                    }
                    empSpinner.css( { 'visibility': 'visible' } );
                    // set new csv url
                    var get_base_url = $( '#hidden-base-url' ).val();
                    var current_url = get_base_url + '&from_date=' + this.from_date + '&to_date=' + this.to_date;
                    $( '#csv-dl-link' ).attr( 'href', current_url );
                    //
                    $.get( ajaxurl, { action: 'erp_payroll_get_emp_pay_info', from_date: this.from_date, to_date: this.to_date, _wpnonce: wpErpPayroll.nonce },
                         ( response ) => {
                            if ( response.success === true ) {
                                reportemp_obj.$set( 'payempdata', response.data );
                            }
                            empSpinner.css( { 'visibility': 'hidden' } );
                        }
                    );
                },

                clearData: function(){
                    this.from_date = '';
                    this.to_date = '';
                }
            }
        } );
    }

    if ( $( '#bulk_edit_items' ).length > 0 ) {
        var bulk_items_edit_obj = new Vue( {
            el: '#bulk_edit_items_wrapper',

            data: {
                items: [],
                pay_item_id: -1,
                setPayment: 'fixed',
                showFixedInput: true,
                showAttsInput: false,
                employeeLoaded: false,
                attsLoaded: false,
                attsDate: {},
                paymentRate: '',
            },

            ready: function () {
                var self = this;

                $('#pay_items').change(function(){
                    self.items = [];
                    self.employeeLoaded = false;
                    $('#update_button').css('display', 'none');
                });
            },

            computed: {
                grossTotal: function(){
                    var itemVal = this.items;
                    var gt = 0;
                    for ( var key in itemVal ) {
                        if ( itemVal[ key ].pay_item_value !== "" ) {
                            gt = gt + parseFloat( itemVal[ key ].pay_item_value );
                        }
                    }
                    return gt;
                },
            },

            methods: {
                searchByItem : function() {
                    var self = this;
                    var pay_item = $( '#pay_items' ).val();

                    var emp_dept  = $( '#emp_dept' ).val();
                    var emp_desig = $( '#emp_desig' ).val();
                    var emp_name  = $( '#emp_name' ).val();

                    self.pay_item_id = pay_item;

                    if ( self.paymentRate && self.attsDate.start && self.attsDate.end ) {
                        $( '#spinner-2' ).css( { 'visibility': 'visible' } );
                        self.attsLoaded = true;
                    } else {
                        self.attsLoaded = false;

                        if ( self.setPayment == 'atts' ) {
                            return swal({
                                        title: 'Warning',
                                        text: wpErpPayroll.validation_message.atts_input_required,
                                        type: 'warning',
                                        timer: 3000
                                    });
                        }

                        $( '#spinner-1' ).css( { 'visibility': 'visible' } );
                    }

                    $.post( ajaxurl, {
                            action      : 'erp_payroll_get_fixed_payitems',
                            pay_item_id : pay_item,
                            emp_dept    : emp_dept,
                            emp_desig   : emp_desig,
                            emp_name    : emp_name,
                            pay_rate    : self.paymentRate,
                            date        : self.attsDate,
                            _wpnonce: wpErpPayroll.nonce },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.items = [];

                                response.data.forEach( function( item ) {
                                    self.items.push( {
                                        first_name : item.first_name,
                                        last_name : item.last_name,
                                        pay_item_value : ( item.pay_item_value != null ) ? item.pay_item_value : 0,
                                        user_id : item.user_id,
                                        designation : item.designation,
                                        department : item.department,
                                        days_worked : item.days_worked != 'undefined' ? item.days_worked : 0,
                                    } );
                                } );

                                self.employeeLoaded = true;
                                $('#update_button').css('display', 'block');
                            } else {
                                self.items = [];
                                self.employeeLoaded = false;

                                swal( {
                                    title: 'Warning',
                                    text: response.data,
                                    type: 'warning',
                                    timer: 3000
                                } );
                            }
                            $( '.render_spinner' ).css( { 'visibility': 'hidden' } );
                        }
                    );
                },

                preventToTypeNull : function() {
                    var min = 0;
                    if ( parseInt( event.target.value ) < min || event.target.value === "" ) {
                        event.target.value = min;
                    }
                },

                updateBulkItem : function() {
                    var self = this;
                    $( '.update_spinner' ).css( { 'visibility': 'visible' } );

                    $.post( ajaxurl, {
                            action: 'erp_payroll_update_fixed_payitems',
                            items : self.items,
                            pay_item_id : self.pay_item_id,
                            _wpnonce: wpErpPayroll.nonce },
                        function ( response ) {
                            if ( response.success === true ) {
                                swal( {
                                    title: 'Success',
                                    text: "Data updated successfully",
                                    type: 'success',
                                    timer: 3000
                                } );
                            }
                            $( '.update_spinner' ).css( { 'visibility': 'hidden' } );
                        }
                    );
                },

                setFixedValToAll : function() {
                    var self = this;
                    var value = $( '#set_fixed_value_to_all_field' ).val();
                    self.items.forEach( function( item, index ) {
                        self.items[index].pay_item_value = value;
                    } );
                },

                togglePaymentInput: function() {
                    if ( this.setPayment == 'fixed' ) {
                        this.showFixedInput = true;
                        this.showAttsInput  = false;
                    } else if ( this.setPayment == 'atts' ) {
                        if ( ! wpErpPayroll.attendace_active ) {
                            swal({
                                title: 'Caution!',
                                text: wpErpPayroll.validation_message.attendance_required,
                                type: 'error',
                            });

                            this.setPayment == 'fixed';
                        } else {
                            this.showFixedInput = false;
                            this.showAttsInput  = true;
                            setTimeout( this.initAttsDateRange, 500 );
                        }
                    }
                },

                initAttsDateRange: function(e) {
                    var elem = $( "input[name='atts_date']" );
                    var self = this;

                    elem.daterangepicker({
                        autoUpdateInput: false,
                        locale         : {
                            cancelLabel: 'Clear'
                        },
                        ranges: {
                            'Today'       : [ moment(), moment() ],
                            'This Week'   : [ moment().startOf( 'isoWeek' ), moment().endOf( 'isoWeek' ) ],
                            'This Month'  : [ moment().startOf( 'month' ), moment().endOf( 'month' ) ],
                            'Last Month'  : [ moment().subtract( 1, 'month' ).startOf( 'month' ), moment().subtract( 1, 'month' ).endOf( 'month' ) ],
                            'This Year'   : [ moment().startOf( 'year' ), moment().endOf( 'year' ) ],
                            'Last Year'   : [ moment().subtract( 1, 'year' ).startOf( 'year' ), moment().subtract( 1, 'year' ).endOf( 'year' ) ],
                        }
                    });
                
                    elem.on( 'apply.daterangepicker', function( event, picker ) {
                        $( this ).val( picker.startDate.format( 'MMM DD, YYYY' ) + ' - ' + picker.endDate.format( 'MMM DD, YYYY' ) );
                
                        self.attsDate = {
                            start: picker.startDate.format( 'DD.MM.YYYY' ),
                            end  : picker.endDate.format( 'DD.MM.YYYY' )
                        }
                    });
                
                    elem.on( 'cancel.daterangepicker', function( event, picker ) {
                        $( this ).val('');
                        self.attsDate = {};
                    });
                },
            }
        } );
    }

})( jQuery );
