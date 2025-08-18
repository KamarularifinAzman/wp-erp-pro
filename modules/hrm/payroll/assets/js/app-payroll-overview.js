(function ( $ ) {
    Vue.filter( 'custom_currency', function(value){
        currency = wpErpPayroll.currency_symbol;
        return currency + "" + parseFloat(value).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,");
    });

    if ( $( '#payrun-overview-wrapper' ).length > 0 ) {
        var payItemSpinner = $( '#payrun-overview-wrapper' ).find( '.spinner' );
        var payrunlist_obj = new Vue( {
            el: '#payrun-overview-wrapper',

            data: {
                payrunlist: []
            },

            ready: function () {
                this.getPayrunList();
            },

            computed: {
                listcounter: function(){
                    var lc = this.payrunlist;
                    if ( lc.length > 0 ) {
                        return true;
                    } else {
                        return false;
                    }
                }
            },

            methods: {
                getPayrunList: function (){
                    payItemSpinner.css( { 'visibility': 'visible' } );
                    $.get( ajaxurl, { action: 'erp_payroll_get_payrun_list', _wpnonce: wpErpPayroll.nonce },
                        function ( response ) {
                            //payrunlist_obj.$set( 'payrunlist', response.data );
                            var temp_list_data = [];
                            for ( var key in response.data ) {
                                temp_list_data.push({
                                    'id': response.data[ key ].id,
                                    'from_date': response.data[ key ].from_date,
                                    'to_date': response.data[ key ].to_date,
                                    'payment_date': response.data[ key ].payment_date,
                                    'pay_cal_id': response.data[ key ].pay_cal_id,
                                    'Pay_Run': response.data[ key ].Pay_Run,
                                    'effected_employees': response.data[ key ].effected_employees,
                                    'employees_payment': response.data[ key ].employees_payment,
                                    'status': response.data[ key ].status,
                                    'payrun_link': wpErpPayroll.payrun_employees_page_url + '&prid=' + response.data[ key ].id
                                });
                            }
                            payrunlist_obj.$set( 'payrunlist', temp_list_data );
                            payItemSpinner.css( { 'visibility': 'hidden' } );
                        }
                    );
                }
            }
        } );
    }

})( jQuery );