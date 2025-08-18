(function ( $ ) {
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

    // Select2 Direcetive
    Vue.directive( 'selecttwoo', {
        bind: function () {
            var self = this;
            var vm = this.vm;
            var key = this.expression;
            var select = $( this.el );

            select.on( 'change', function () {
                var search_key = $( this ).attr( 'data-searchkey' );
                var search_key_index = $( this ).attr( 'data-searchkeyindex' );
                if ( search_key && search_key_index ) {
                    key = key.replace( 'search_key', search_key );
                    key = key.replace( 'search_field_key', search_key_index );
                }
                vm.$set( key, select.val() );
            } );

            select.select2( {
                width: 'resolve',
                placeholder: $( this.el ).attr( 'data-placeholder' ),
                allowClear: true
            } );
        },

        update: function ( newValue, oldValue ) {
            var self = this;
            var select = $( self.el );

            if ( newValue && !oldValue ) {
                select.val( newValue );
                select.trigger( 'change' );
            }
        }
    } );

    var payCalAddEdit = $('#pay-calendar-add-edit-wrapper');

    if ( payCalAddEdit.length > 0 ) {
        var payItemSpinner = $( '.spinner' );
        var payItemCategory_obj = new Vue( {
            el: '#pay-calendar-add-edit-wrapper',

            data: {
                cal_name: '',
                cal_type: '',
                cal_pay_day: 1,
                showForMonthly: false,
                weekday: '6',
                paydaymode: '0',
                showForMonthlySpecificday: false,
                specific_monthly_pay_date: 0,
                dept: [],
                desig: [],
                selectAll: false,
                selected: [],
                bringEmpData: [],
                bringEmpDataRoot: [],
                deptNewEmpData: [],
                desigNewEmpData: [],
                cal_list: [],
                createAndUpdateButtonController: true,
                calIdforUpdate: 0,
                oldEmpData: [],
                allowedEmps: [],
            },

            ready: function () {
                var self = this;
                var calid = self.getParameterByName('cal_id');
                self.getCalList();
                if ( calid != '' ) {
                    self.createAndUpdateButtonController = false;
                    self.getSelectedCalInfo(calid);
                    self.arrangeDataforupdate(calid);
                    self.calIdforUpdate = calid;
                }

                this.showForWeekly = true;
                jQuery('.emp-select-dropdown').select2();
            },

            computed: {
                totalEmp: function(){
                    return this.selected.length;
                },

                selectAll: {
                    get: function () {
                        return this.bringEmpData ? this.selected.length == this.bringEmpData.length : false;
                    },
                    set: function ( value ) {
                        var selected = [];

                        if ( value ) {
                            this.bringEmpData.forEach( function ( user ) {
                                selected.push( user.id );
                            } );
                        }

                        this.selected = selected;
                    }
                }
            },

            methods: {
                getCalList: function () {
                    var self = this;
                    payItemSpinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl,
                        {
                            action: 'erp_payroll_get_cal_list',
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'cal_list', response.data );
                                payItemSpinner.css( { 'visibility': 'hidden' } );
                            }
                        }
                    );
                },

                recalculatetable: function () {
                    var self    = this;
                    var dept    = self.dept;
                    var desig   = self.desig;
                    var payType = self.cal_type;

                    payItemSpinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl,
                        {
                            action: 'erp_payroll_get_emp',
                            dept: dept,
                            desig: desig,
                            pay_type: payType,
                            selectedEmp : JSON.stringify( $('.emp-select-dropdown').val() ),
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'bringEmpData', response.data );
                                self.$set( 'bringEmpDataRoot', response.data );
                                self.selectAll = true;
                                payItemSpinner.css( { 'visibility': 'hidden' } );
                            }
                        }
                    );
                },

                bringEmpToList: function(){
                    var self = this;
                    self.recalculatetable();
                },

                bringEmpToListEditMode: function(){
                    var self = this;
                    var dept = self.dept;
                    var desig = self.desig;
                    var payType = self.cal_type;

                    payItemSpinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl,
                        {
                            action: 'erp_payroll_get_emp',
                            dept: dept,
                            desig: desig,
                            pay_type: payType,
                            selectedEmp : JSON.stringify( $('.emp-select-dropdown').val() ),
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                var newEmpList = [];
                                for ( var key in response.data ) {
                                    newEmpList.push({
                                        'dept_name': response.data[ key ].dept_name,
                                        'desig_name': response.data[ key ].desig_name,
                                        'display_name': response.data[ key ].display_name,
                                        'id': response.data[ key ].id,
                                        'pay_basic': response.data[ key ].pay_basic,
                                        'user_email': response.data[ key ].user_email
                                    });
                                }
                                newEmpList = newEmpList.concat(self.oldEmpData);
                                self.$set( 'bringEmpData', newEmpList );
                                self.$set( 'bringEmpDataRoot', newEmpList );
                                self.selectAll = true;
                                payItemSpinner.css( { 'visibility': 'hidden' } );
                            }
                        }
                    );
                },

                changeCalType: function () {
                    if ( this.cal_type != 'monthly' ) {
                        this.showForMonthly = false;
                        this.showForMonthlySpecificday = false;

                        if ( this.cal_type == 'hourly' && ! wpErpPayroll.attendace_active ) {
                            swal( {
                                title: 'Caution!',
                                text: wpErpPayroll.validation_message.attendance_required,
                                type: 'error',
                            } );

                            this.cal_type = 'monthly';
                        }
                    } else {
                        this.showForMonthly = true;
                    }
                },

                paymodeChanger: function () {
                    if ( this.paydaymode == 2 ) {
                        this.showForMonthlySpecificday = true;
                    } else {
                        this.showForMonthlySpecificday = false;
                    }
                },

                createPayCal: function () {
                    var self = this;
                    var departmentId = this.dept;
                    var designationId = this.desig;

                    if ( self.cal_type == 'hourly' && ! wpErpPayroll.attendace_active ) {
                        swal( {
                            title: 'Caution!',
                            text: wpErpPayroll.validation_message.attendance_required,
                            type: 'error',
                        } );

                        self.cal_type = '';
                    }

                    if ( self.checkCalendarExistByType( self.cal_type ) == 'fail' ) {
                        swal( {
                            title: 'Oops',
                            text: wpErpPayroll.validation_message.cal_type_exist,
                            type: 'error',
                            timer: 9000
                        } );
                    }

                    if ( this.cal_name == '' ) {
                        swal( {
                            title: 'Oops',
                            text: wpErpPayroll.validation_message.empty_calendar_name,
                            type: 'error',
                            timer: 3000
                        } );
                    } else if ( departmentId.length == 0 && designationId.length == 0 && 1 != 1) {
                        swal( {
                            title: 'Oops',
                            text: wpErpPayroll.validation_message.empty_departmen_n_desig,
                            type: 'error',
                            timer: 3000
                        } );
                    } else if ( self.selected.length == 0 ) {
                        swal( {
                            title: 'Oops',
                            text: wpErpPayroll.validation_message.empty_emp,
                            type: 'error',
                            timer: 3000
                        } );
                    } else if ( self.cal_type == '' ) {
                        swal( {
                            title: 'Oops',
                            text: wpErpPayroll.validation_message.empty_cal_type,
                            type: 'error',
                            timer: 3000
                        } );
                    } else {

                        swal({
                            title: wpErpPayroll.create_confirm_pay_cal,
                            text: wpErpPayroll.unable_to_revert,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Confirm'
                         }, () => {
                            wp.ajax.send( 'erp_payroll_create_pay_calendar', {
                                data: {
                                    cal_name: self.cal_name,
                                    cal_type: self.cal_type,
                                    weekday: self.weekday,
                                    empids: self.selected,
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
                                    location.href = wpErpPayroll.pay_calendar_url
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
                        });
                    }
                },

                checkCalendarExistByType: function(caltype){
                    var self = this;
                    for ( var key in self.cal_list ) {
                        if ( self.cal_list[ key ].pay_calendar_type == caltype ) {
                            return 'fail';
                        }
                    }
                },

                getSelectedCalInfo: function( calid ) {
                    var self = this;
                    $.get( ajaxurl,
                        {
                            action: 'erp_payroll_get_selected_calendar_info',
                            calendarid: calid,
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                payItemSpinner.css( { 'visibility': 'hidden' } );
                                self.$set( 'cal_name', response.data[ 0 ].pay_calendar_name );
                                self.$set( 'cal_type', response.data[ 0 ].pay_calendar_type );
                                self.$set( 'weekday', response.data[ 0 ].pay_day );
                                self.$set( 'paydaymode', response.data[ 0 ].pay_day_mode );
                                // show calendar additional settings
                                if ( self.cal_type == 'monthly' ) {
                                    self.showForMonthly = true;
                                    
                                    if ( response.data[ 0 ].pay_day_mode  == 2 ) {
                                        self.$set( 'showForMonthlySpecificday', true );
                                        self.$set( 'specific_monthly_pay_date', response.data[ 0 ].custom_month_day );
                                    }
                                }
                            }
                        }
                    );
                },

                arrangeDataforupdate: function ( calid ) {
                    var self = this;
                    $.get( ajaxurl,
                        {
                            action: 'erp_payroll_get_calendar_emp',
                            calendarid: calid,
                            _wpnonce: wpErpPayroll.nonce
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                payItemSpinner.css( { 'visibility': 'hidden' } );
                                self.$set( 'bringEmpData', response.data );
                                self.$set( 'oldEmpData', response.data );
                                self.$set( 'bringEmpDataRoot', response.data );
                                self.selectAll = true;
                            }
                        }
                    );
                },

                updateActionPayCal: function () {
                    var self = this;
                    if ( confirm( wpErpPayroll.update_confirm_pay_cal ) ) {
                        if ( self.cal_name == '' ) {
                            swal( {
                                title: 'Oops',
                                text: wpErpPayroll.validation_message.empty_calendar_name,
                                type: 'error',
                                timer: 3000
                            } );
                        } else if ( self.cal_type == '' ) {
                            swal( {
                                title: 'Oops',
                                text: wpErpPayroll.validation_message.empty_cal_type,
                                type: 'error',
                                timer: 3000
                            } );
                        } else if ( self.selected.length == 0 ) {
                            swal( {
                                title: 'Oops',
                                text: wpErpPayroll.validation_message.empty_emp,
                                type: 'error',
                                timer: 3000
                            } );
                        } else {
                            payItemSpinner.css( { 'visibility': 'visible' } );
                            wp.ajax.send( 'erp_payroll_update_pay_calendar', {
                                data: {
                                    cal_name: self.cal_name,
                                    cal_type: self.cal_type,
                                    cal_pay_day: self.cal_pay_day,
                                    empids: self.selected,
                                    calid: self.calIdforUpdate,
                                    weekday: self.weekday,
                                    paydaymode: self.paydaymode,
                                    specific_monthly_pay_date: self.specific_monthly_pay_date,
                                    _wpnonce: wpErpPayroll.nonce
                                },
                                success: function ( response ) {
                                    payItemSpinner.css( { 'visibility': 'hidden' } );
                                    //init model value
                                    self.cal_name = '';
                                    self.cal_type = '';
                                    self.cal_pay_day = 1;
                                    self.dept = [];
                                    self.desig = [];
                                    self.$set( 'bringEmpData', [] );
                                    self.createAndUpdateButtonController = false;
                                    swal( {
                                        title: 'Success',
                                        text: response,
                                        type: 'success',
                                        timer: 3000
                                    } );
                                    location.href = wpErpPayroll.pay_calendar_url
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
                    }
                },

                openModal: function(){
                    var self = this;
                    $('#myModal').modal('show');

                    wp.ajax.send( {
                        data: {
                            action   : 'erp_payroll_get_available_employees',
                            pay_type : self.cal_type,
                            _wpnonce : wpErpPayroll.nonce
                        },
                        success: function ( response ) {
                            self.allowedEmps = response;
                        },
                        error: function ( error ) {
                            alert( error );
                        }
                    } );
                },

                tmplReload: function () {
                    this.jsscriptReload( 'erp_payroll_get_payitem_category', 'erp-payroll-item-new-template' );
                },

                jsscriptReload: function ( action, id ) {
                    wp.ajax.send( {
                        data: {
                            action: action
                        },
                        success: function ( response ) {
                            var selectdroppicat = $( '#pi_category' );
                            selectdroppicat.children( 'option' ).remove();
                            $.each( response, function ( key, value ) {
                                selectdroppicat.append( "<option value=" + value.id + ">" + value.payitem_category + "</option>" );
                            } );
                        },
                        error: function ( error ) {
                            alert( error );
                        }
                    } );
                },

                getParameterByName: function ( name ) {
                    name = name.replace( /[\[]/, "\\[" ).replace( /[\]]/, "\\]" );
                    var regex = new RegExp( "[\\?&]" + name + "=([^&#]*)" ),
                        results = regex.exec( location.search );
                    return results === null ? "" : decodeURIComponent( results[ 1 ].replace( /\+/g, " " ) );
                },

                searchEmpByParam : function() {
                    var self = this;
                    var dept_name = $('#emp_dept').val();
                    var desg_name = $('#emp_desig').val();
                    var emp_name  = $('#emp_name').val().toLowerCase();

                    var result = self.bringEmpDataRoot.filter(function(element){

                        if( dept_name == "All Department" && desg_name == "All Designations"  ) {
                            if ( emp_name.length == 0 ) {
                                return true;
                            } else {
                                return element.display_name.toLowerCase().indexOf( emp_name ) != -1 ;
                            }
                        }

                        if( dept_name != "All Department" && desg_name == "All Designations"  ) {
                            if ( emp_name.length == 0 ) {
                                return element.dept_name == dept_name;
                            } else {
                                return element.dept_name == dept_name && element.display_name.toLowerCase().indexOf( emp_name ) != -1 ;
                            }
                        }

                        if( dept_name == "All Department" && desg_name != "All Designations" ) {
                            if ( emp_name.length == 0 ) {
                                return element.desig_name == desg_name;
                            } else {
                                return element.desig_name == desg_name && element.display_name.toLowerCase().indexOf( emp_name ) != -1 ;
                            }
                        }

                        if( dept_name != "All Department" && desg_name != "All Designations" ) {
                            if ( emp_name.length == 0 ) {
                                return element.dept_name == dept_name && element.desig_name == desg_name;
                            } else {
                                return element.dept_name == dept_name && element.desig_name == desg_name && element.display_name.toLowerCase().indexOf( emp_name ) != -1 ;
                            }
                        }
                    });
                    
                    self.$set( 'bringEmpData', result );
                }
            }
        } );
    }

})( jQuery );
