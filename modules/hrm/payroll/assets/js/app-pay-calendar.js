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

    if ( $( '#pay-calendar-wrapper' ).length > 0 ) {
        var payItemSpinner = $( '.spinner' );
        var payItemCategory_obj = new Vue( {
            el: '#pay-calendar-wrapper',

            data: {
                calendarData: [],
                loading_calendar: true
            },

            ready: function () {
                this.getCalendar();
            },

            filters: {
                ucFirst: function(str) {
                    return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
                }
            },

            methods: {
                getCalendar: function () {
                    payItemSpinner.css( { 'visibility': 'visible' } );
                    var self = this;
                    $.get( ajaxurl, { action: 'erp_payroll_get_pay_calendar', _wpnonce: wpErpPayroll.nonce },
                        function ( response ) {
                            if ( response.success === true ) {
                                self.$set( 'calendarData', response.data );
                                payItemSpinner.css( { 'visibility': 'hidden' } );
                                self.loading_calendar = false ;
                            }
                        }
                    );
                },

                removeCal: function ( caldata ) {
                    if ( confirm( wpErpPayroll.remove_confirm_pay_cal ) ) {
                        var self = this;
                        payItemSpinner.css( { 'visibility': 'visible' } );
                        $.get( ajaxurl,
                            {
                                action: 'erp_payroll_remove_calendar',
                                calendarid: caldata.id,
                                _wpnonce: wpErpPayroll.nonce
                            },
                            function ( response ) {
                                if ( response.success === true ) {
                                    self.getCalendar();
                                    payItemSpinner.css( { 'visibility': 'hidden' } );
                                    swal( {
                                        title: 'Success',
                                        text: response.data,
                                        type: 'success',
                                        timer: 3000
                                    } );
                                } else {
                                    swal( {
                                        title: 'Oops',
                                        text: response.data,
                                        type: 'error',
                                        timer: 3000
                                    } );
                                }
                            }
                        );
                    }
                },

                runPayrun: function (calObj) {
                    tab = 'employees';
                    location.href = wpErpPayroll.payrun_url + '&tab=' + tab + '&calid=' + calObj.id;
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
