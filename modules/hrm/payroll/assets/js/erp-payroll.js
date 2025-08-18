/* jshint devel:true */
/* global wpErpPayroll */
/* global wp */

;
(function ( $ ) {
    'use strict';

    var WeDevs_ERP_Payroll = {

        /**
         * Initialize the events
         *
         * @return {void}
         */
        initialize: function () {
            $(document).ready(function () {
                $('.not-loaded').removeClass('not-loaded');
                // Pay roll Overview dashboard chart js
                var chartScript = {
                    /**
                     * Initialize the events
                     *
                     * @return {void}
                     */
                    initialize: function () {

                        if ( $( '#salary-chart' ).length > 0 ) {
                            jQuery( function ( $ ) {
                                // For Line chart
                                //$( '.spinner' ).show();
                                $.get( ajaxurl, { action: 'erp_payroll_get_payrun', _wpnonce: wpErpPayroll.nonce },
                                    function ( response ) {
                                        if ( response.success === true ) {
                                            var payment_dates = [];
                                            var payment_amounts = [];
                                            for ( var key in response.data ) {
                                                payment_dates.push( response.data[ key ].payment_date );
                                                payment_amounts.push( response.data[ key ].paid_amount );
                                            }

                                            var lineData = {
                                                labels: payment_dates,
                                                datasets: [
                                                    {
                                                        label: "Payment",
                                                        fillColor: "rgba(120,200, 223, 0.4)",
                                                        strokeColor: "#79C7DF",
                                                        pointColor: "#79C7DF",
                                                        pointStrokeColor: "#79C7DF",
                                                        pointHighlightFill: "#79C7DF",
                                                        pointHighlightStroke: "#79C7DF",
                                                        data: payment_amounts
                                                    }
                                                ]
                                            };

                                            Chart.defaults.global.responsive = true;
                                            var ctxl = $( "#salary-chart" ).get( 0 ).getContext( "2d" );

                                            // This will get the first returned node in the jQuery collection.
                                            var cpmChart = new Chart( ctxl ).Bar( lineData, {
                                                pointDotRadius: 8,
                                                animationSteps: 60,
                                                tooltipTemplate: "<%=label%>:<%= value %>",
                                                multiTooltipTemplate: "<%= datasetLabel %> - <%= value %>",
                                                animationEasing: "easeOutQuart",
                                                showDatasetLabels: true,
                                                responsive: false,
                                                maintainAspectRatio: false
                                            } );
                                        }
                                    }
                                );
                            } );
                        }
                    }
                };
                chartScript.initialize();
            });
        },

        bankShowAndHide: function() {
            $(document).ready(function () {
                if ( $('#erp_payroll_payment_method_settings').val() === 'cash' ) {
                    $('.form-table').find('tr').eq(1).hide();
                }

                $('#erp_payroll_payment_method_settings').change(function() {
                    // not a quality solution
                    if ( 'cash' !== $(this).val() ) {
                        $('.form-table').find('tr').eq(1).show();
                    } else {
                        $('.form-table').find('tr').eq(1).hide();
                    }
                });
            });
        }
    };

    $( function () {
        WeDevs_ERP_Payroll.initialize();

        if ( '?page=erp-settings&tab=payroll' || '?page=erp-settings&tab=payroll&section=payment'
                === window.location.search ) {
            WeDevs_ERP_Payroll.bankShowAndHide();
        }
    } );
})( jQuery );
