(function ( $ ) {
    var payCalWrapper = $('#payrun-wrapper');
    if ( $( payCalWrapper ).length > 0 ) {
        var payrun_spinner = $( '.spinner' );
        var payItemCategory_obj = new Vue( {
            el: '#payrun-wrapper',

            data: {
            },

            methods: {
                removePayrun: function( payrun_id ){
                    if ( confirm( wpErpPayroll.remove_confirmation ) ) {
                        payrun_spinner.css( { 'visibility': 'visible' } );
                        //remove a row from payrun table by payrun id and payment date
                        wp.ajax.send( 'erp_payroll_remove_payrun', {
                            data: {
                                payrunid: payrun_id,
                                _wpnonce: wpErpPayroll.nonce
                            },
                            success: function ( response ) {
                                payrun_spinner.css( { 'visibility': 'hidden' } );
                                swal( {
                                    title: 'Success',
                                    text: response,
                                    type: 'success',
                                    timer: 3000
                                } );
                                location.reload();
                            },
                            error: function ( error ) {
                                payrun_spinner.css( { 'visibility': 'hidden' } );
                                swal( {
                                    title: 'Oops',
                                    text: error,
                                    type: 'error',
                                    timer: 5000
                                } );
                            }
                        } );
                    }
                },

                undoPayrun: function(payrunid){
                    if ( confirm( 'Are you sure you want undo this payrun?' ) ) {
                        payrun_spinner.css( { 'visibility': 'visible' } );
                        //remove a row from payrun table by payrun id and payment date
                        wp.ajax.send( 'erp_payroll_undo_approve_payment', {
                            data: {
                                payrunid: payrunid,
                                _wpnonce: wpErpPayroll.nonce
                            },
                            success: function ( response ) {
                                payrun_spinner.css( { 'visibility': 'hidden' } );
                                swal( {
                                    title: 'Success',
                                    text: response,
                                    type: 'success',
                                    timer: 3000
                                } );
                                location.reload();
                            },
                            error: function ( error ) {
                                payrun_spinner.css( { 'visibility': 'hidden' } );
                                swal( {
                                    title: 'Oops',
                                    text: error,
                                    type: 'error',
                                    timer: 5000
                                } );
                            }
                        } );
                    }
                },

                copyPayrun: function(payrunid){
                    if ( confirm( 'Are you sure you want copy this payrun?' ) ) {
                        payrun_spinner.css( { 'visibility': 'visible' } );
                        //remove a row from payrun table by payrun id and payment date
                        wp.ajax.send( 'erp_payroll_copy_payment', {
                            data: {
                                payrunid: payrunid,
                                _wpnonce: wpErpPayroll.nonce
                            },
                            success: function ( response ) {
                                payrun_spinner.css( { 'visibility': 'hidden' } );
                                swal( {
                                    title: 'Success',
                                    text: response,
                                    type: 'success',
                                    timer: 3000
                                } );
                                location.reload();
                            },
                            error: function ( error ) {
                                payrun_spinner.css( { 'visibility': 'hidden' } );
                                swal( {
                                    title: 'Oops',
                                    text: error,
                                    type: 'error',
                                    timer: 5000
                                } );
                            }
                        } );
                    }
                }
            }
        } );
    }

})( jQuery );
