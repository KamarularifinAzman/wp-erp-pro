;(function ($) {
    'use strict';

    var WeDevs_ERP_PRO_LEAVE = {

        initialize: function() {
            $('#calculate-unpaid-leave').on('click', this.openUnpaidCalculateModal);
            $('.unpaid-amount').on('click', this.singleRowCalculate);
            $('.leave_unpaids').find('.column-amount input').on('change', this.showUnpaidRelatedTickMark);

            $('#forward_f_year').on('change', this.hideGenerateApplyBtn);
            $('.erp-hr-pro-leave-forward-btn').on('click', this.leaveRequestForwardModal);
            $('.advanced-leave-req-expand').on('click', this.expandRequestListRow);

            $( 'body' ).on( 'change', '.new-leave-request-form #f_year', this.checkHalfdayAvailablity );
            $( 'body' ).on( 'change', '#erp-hr-leave-req-employee-id', this.checkHalfdayAvailablity );
            $( 'body' ).on( 'change', '#erp-hr-leave-req-leave-policy', this.checkHalfdayAvailablity );

            $( 'body' ).on( 'change', '.request-halfday-form #halfday', this.enableHalfdayRequest );
            $( 'body' ).on( 'change', '#erp-hr-leave-req-from-date', this.fillUpRequestToDate );

            $( '.erp-hr-leave-forward-inner form').on( 'submit', this.showForwardLeaveConfirmation );
            $( '.leave-policy-form' ).on( 'submit', this.validateSegregationDays );
        },

        /**
         *
         * Unpaid leave calculation modal
         *
         * @param {event} e
         */
        openUnpaidCalculateModal: function(e) {
            e.preventDefault();

            $.erpPopup({
                title: wpErpPro.calculate_title,
                button: wpErpPro.calculate,
                id: 'erp-pro-unpaid-leave',
                content: wp.template( 'unpaid-leave-modal' ),
                extraClass: 'medium',
                onReady: function() {},
                onSubmit: function(modal) {
                    wp.ajax.send( {
                        type: 'post',
                        data: $('.erp-modal-form').serialize(),
                        success: function(res) {
                            modal.closeModal();
                            location.reload(true);
                        },
                        error: function(error) {
                            modal.showError( error );
                        }
                    });
                }
            });
        },

        /**
         * Unpaid leave single row calculation
         *
         * @param {event} e
         */
        singleRowCalculate: function(e) {
            e.preventDefault();

            var self = $(this);

            var id = self.siblings('input').attr('id').split('-')[1]; // `unpaid_id-1` => `1`

            wp.ajax.send( {
                type: 'post',
                data: {
                    '_wpnonce': wpErpPro.nonce,
                    'action'  : 'erp_pro_hr_unpaid_leave_calc_single',
                    'id'      : id,
                    'amount'  : self.siblings('input').val()
                },
                success: function(res) {
                    self.hide();
                    $('#total-' + id).text(res);
                }
            });

        },

        /**
         * Show unpaid leave amount related tick mark
         *
         * @param {event} e
         */
        showUnpaidRelatedTickMark: function(e) {
            $(this).siblings('a').show();
        },

        /**
         *
         * Hide encashment / forward leave apply button
         *
         * @param {event} e
         */
        hideGenerateApplyBtn: function(e) {
            $('#generate_forward_leaves').hide();
            $('#apply_forward_leaves').hide();
        },

        /**
         *
         * Expand request list row
         *
         * @param {event} e
         */
        expandRequestListRow: function(e) {
            var self  = $(this),
                reqId = self.data('req-id'),
                tr    = self.parent().parent();

            // rotate expand icon and show/hide newly added row
            self.toggleClass('rotate');
            tr.toggleClass('erp-expanded-req');
            self.removeClass( 'dashicons-plus' ).addClass( 'dashicons-minus' );

            // previously expanded, now close
            if ( ! tr.hasClass('erp-expanded-req') ) {
                self.removeClass( 'dashicons-minus' ).addClass( 'dashicons-plus' );
                tr.next().fadeTo('fast', 0.3, function() {
                    $(this).remove();
                });

                return;
            }

            // approvals table base
            var html = [
                '<tr>',
                    '<td colspan="7">',
                        '<table class="erp-table advanced-leave-req-expand-table">',
                            '<thead>',
                                '<tr>',
                                    '<th>' + wpErpPro.req_forward_table.approved_by + '</th>',
                                    '<th>' + wpErpPro.req_forward_table.date + '</th>',
                                    '<th>' + wpErpPro.req_forward_table.forward_status + '</th>',
                                    //'<th>' + wpErpPro.req_forward_table.forward_to + '</th>',
                                    '<th>' + wpErpPro.req_forward_table.reason + '</th>',
                                '</tr>',
                            '</thead>',

                            '<tbody>',
                                '<tr>',
                                    '<td colspan="4">',
                                        '<div class="erp-pro-spinner-center">',
                                            '<span class="spinner is-active"></span>',
                                        '</div>',
                                    '</td>',
                                '</tr>',
                            '</tbody>',
                        '</table>',
                    '</td>',
                '</tr>'
            ];

            // new row html content
            var approvalTable = $( html.join('') ).insertAfter( tr );

            // Get approvals data by ajax
            wp.ajax.send( {
                type: 'get',
                data: {
                    '_wpnonce': wpErpPro.nonce,
                    'action'  : 'erp_pro_hr_leave_multilevel_approval',
                    'req_id'  : reqId
                },
                success: function(res) {
                    if ( res.length ) {
                        var content = '';

                        for ( var i = 0; i < res.length; i++ ) {
                            content += '<tr>';
                            content += '<td>' + res[i].approved_by_name + '</td>';
                            content += '<td>' + res[i].created_at + '</td>';
                            content += '<td class="status">' + res[i].approval_status + '</td>';
                            //content += '<td>' + res[i].forward_to_name + '</td>';
                            content += '<td>' + res[i].message + '</td>';
                            content += '</tr>';
                        }

                        approvalTable.find('tbody').html(content);
                    }

                    approvalTable.find('.spinner').removeClass('is-active');
                },
                error: function(e) {
                    approvalTable.find('.spinner').removeClass('is-active');
                }
            });

            e.preventDefault();
        },

        /**
         * Show leave forward modal
         *
         * @param {event} e
         */
        leaveRequestForwardModal: function(e) {
            var self = $(this);

            $.erpPopup({
                title: wpErpPro.approval_modal_title,
                button: wpErpPro.process,
                id: 'advanced-leave-approval-modal',
                content: wp.template( 'pro-leave-approval-modal' ),
                extraClass: 'medium',
                onReady: function() {},
                onSubmit: function(modal) {
                    var request_id = parseInt( self.data('id') );
                    var forward_to = parseInt( $('.forward-form').find('#forward_to').val() );

                    if ( ! forward_to ) {
                        alert( wpErpPro.select_employee );

                        return;
                    }

                    var data = $('.erp-modal-form').serialize();

                    data += '&request_id=' + request_id;

                    wp.ajax.send( {
                        type: 'post',
                        data: data,
                        success: function(res) {
                            modal.closeModal();
                            location.reload(true);
                        },
                        error: function(error) {
                            modal.showError( error );
                        }
                    } );
                }
            });

            e.preventDefault();
        },

        /**
         * Check if halfday option available
         *
         * @param {Event} e
         */
        checkHalfdayAvailablity: function(e) {
            var entitle_id = $('#erp-hr-leave-req-leave-policy').val();

            wp.ajax.send( {
                type: 'get',
                data: {
                    '_wpnonce'  : wpErpPro.nonce,
                    'action'    : 'erp_pro_hr_check_halfday_availability',
                    'entitle_id': entitle_id
                },
                success: function(res) {
                    if ( res ) {
                        $('.request-halfday-form').show();
                    } else {
                        $('.request-halfday-form').hide();

                        // show/hide request to-date
                        $('.erp-leave-to-date').show();
                        // uncheck haklfday checkbox
                        $( "#halfday" ).prop( 'checked', false );
                    }
                }
            });
        },

        /**
         * Enable half day request
         *
         * @param {Event} e
         */
        enableHalfdayRequest: function(e) {
            // show/hide halfday period
            $('.halfday-leave-period').toggle();

            // show/hide request to-date
            $('.erp-leave-to-date').toggle();

            // hide request days count
            if ( $('#halfday').is(':checked') ) {
                $('#erp-hr-leave-req-to-date').val(
                    $('#erp-hr-leave-req-from-date').val()
                ).trigger('change');
            } else {
                $('#erp-hr-leave-req-to-date').val('');
            }
        },

        /**
         * Hide days count
         *
         * @param {Event} e
         */
        fillUpRequestToDate: function(e) {
            if ( $('#halfday').is(':checked') ) {
                $('#erp-hr-leave-req-to-date').val( $(this).val() ).trigger('change');
            }
        },

        /**
         * Show confirmation when click on apply button
         *
         * @param {Event} e
         */
        showForwardLeaveConfirmation: function(e) {
            if ( ! confirm( wpErpPro.forward_confirmation ) ) {
                return false;
            }
        },

        /**
         * This method will check if segregation days is <= to policy days.
         *
         * @param {Event} e
         */
        validateSegregationDays: function ( e ) {
            //e.preventDefault();

            var policy_days = $( '.leave-policy-form #days' ).val();
            if ( policy_days === '' || typeof policy_days === 'undefined') {
                return false;
            }

            // get all segregation input
            var seg = $('.leave-policy-form').find( 'input.segre' );

            if ( ! seg.length ) {
                return true;
            }

            var ret = true;
            $.each( seg, function() {
                if ( parseInt( $(this).val() ) < 0 ) {
                    alert( wpErpPro.segregation_negative_error );
                    return ret = false;
                }
                else if ( $(this).val() > 0 &&  parseInt( policy_days ) < parseInt( $(this).val() ) ) {
                    $(this).focus();
                    alert( wpErpPro.segregation_policy_error );
                    return ret = false;
                }
            });

            return ret;
        },
    };

    WeDevs_ERP_PRO_LEAVE.initialize();

})(jQuery, this);

