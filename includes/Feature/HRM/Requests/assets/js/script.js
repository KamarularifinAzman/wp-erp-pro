;(function($) {
    'use strict';

    var erpProHr = {

        init: function() {
            
            self = this;

            // Resign request events
            $( '.erp-hr-employees' ).on( 'click', 'a#erp-employee-resign', self.employee.requestResign );
            $( '.erp-hr-employees' ).on( 'click', 'a#erp-employee-cancel-resign', self.employee.cancelResignReq );

            // Remote work requests events
            $( 'body' ).on( 'click', 'a#erp-hr-remote-work-req', self.employee.requestRemoteWork );
            $( 'body' ).on( 'click', 'a#erp-hr-del-remote-work-history', self.employee.removeRemoteWork );
            $( 'body' ).on( 'click', 'a#erp-hr-edit-remote-work-history', self.employee.updateRemoteWork );
            $( 'form#erp-hr-empl-remote-work-filter' ).on( 'submit', self.employee.filterRemoteWork );
            
            $( 'body' ).on( 'change', '#rw_reason', function() {
                if ( $(this).val() == 'other' ) {
                    $( 'div#erp-rw-other-reason' ).show();
                } else {
                    $( 'div#erp-rw-other-reason' ).hide();
                }
            });

            $( 'body' ).on( 'click', '.erp-row-actions-btn', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                $(`#request-row-actions-${id}`).toggleClass( 'show' );
            });

            $(document).click( function(e) {
                Array.prototype.forEach.call( $( '.erp-row-actions-btn' ), function(row, index) {
                    if ( typeof row !== 'undefined' && ! row.contains( e.target ) ) {
                        $( '.dropdown-content' )[index].classList.remove( 'show' );
                    }
                });
            });

            self.select2Action('erp-hrm-select2');
        },

        initDateField: function() {
            $( '.erp-date-field').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+100',
            });
        },

        reloadPage: function() {
            $( '.erp-area-left' ).load( window.location.href + ' #erp-area-left-inner', function() {
                $('.select2').select2();
            } );
        },

        select2Action: function(element) {
            $('.'+element).select2({
                width: 'element',
            });
        },
        
        showAlert: function(type, message, title = '') {            
            swal({
                title : title,
                text  : message,
                type  : type,
                timer : 2200,
                showConfirmButton : false,
            });
        },

        employee:  {
            requestResign: function(e) {
                if ( typeof e !== 'undefined' ) {
                    e.preventDefault();
                }

                var self = $(this);

                if ( self.data('data') ) {
                    var resignData = self.data('data');
                } else {
                    var resignData = window.wpErpCurrentEmployee;
                }

                $.erpPopup({
                    title: self.data('title'),
                    button: wpErpHr.resign,
                    id: 'erp-hr-employee-resign',
                    content: '',
                    extraClass: 'smaller',

                    onReady: function() {
                        var html = wp.template( self.data('template') )(resignData);
                        $( '.content', this ).html( html );
                        erpProHr.initDateField();

                        $( '.row[data-selected]', this ).each(function() {
                            var self = $(this),
                                selected = self.data('selected');

                            if ( selected !== '' ) {
                                self.find( 'select' ).val( selected );
                            }
                        });

                        erpProHr.select2Action('erp-hrm-select2');
                    },

                    onSubmit: function(modal) {
                        wp.ajax.send( {
                            data: this.serializeObject(),
                            success: function(res) {                                
                                wp.ajax.send({
                                    data: res.data,
                                    success: function(res) {
                                        console.log(res);
                                    },
                                    error: function(err) {
                                        console.log(err);
                                    }
                                });
                                erpProHr.reloadPage();
                                erpProHr.showAlert('success', res.msg);
                                modal.closeModal();
                            },
                            error: function(error) {
                                modal.enableButton();
                                swal('', error, 'error');
                            }
                        });
                    }
                });
            },

            cancelResignReq: function(e) {
                if ( typeof e !== 'undefined' ) {
                    e.preventDefault();
                }

                var self = $(this);

                swal({
                    title              : self.data('title'),
                    type               : 'warning',
                    showCancelButton   : true,
                    cancelButtonText   : erpProReq.cancel,
                    confirmButtonColor : '#22b527',
                    confirmButtonText  : erpProReq.withdraw,
                    closeOnConfirm     : false
                },
                function() {
                    wp.ajax.send({
                        data : {
                            user_id  : self.data('id'),
                            action   : self.data('action'),
                            _wpnonce : self.data('nonce')
                        },
                        success: function(res) {
                            erpProHr.reloadPage();
                            erpProHr.showAlert('success', res);
                        },
                        error: function(error) {
                            erpProHr.reloadPage();
                            swal('', error, 'error');
                        }
                    });
                });
            },

            requestRemoteWork: function(e) {
                if ( typeof e !== 'undefined' ) {
                    e.preventDefault();
                }

                var self = $(this),
                    data = {
                        id         : '',
                        start_date : '',
                        end_date   : '',
                        reason     : {
                            id     : '',
                            others : '',
                        },
                    };

                $.erpPopup({
                    title: self.data('title'),
                    button: erpProReq.submit,
                    id: 'erp-hr-employee-remote-work',
                    content: '',
                    extraClass: 'smaller',

                    onReady: function() {
                        var html = wp.template( self.data('template') )(data);
                        $( '.content', this ).html( html );
                        erpProHr.initDateField();

                        $( '.row[data-selected]', this ).each(function() {
                            var self = $(this),
                                selected = self.data('selected');

                            if ( selected !== '' ) {
                                self.find( 'select' ).val( selected );
                            }
                        });

                        erpProHr.select2Action('erp-hrm-select2');
                    },

                    onSubmit: function(modal) {
                        wp.ajax.send( {
                            data: this.serializeObject(),
                            success: function(res) {
                                erpProHr.showAlert('success', res);
                                modal.closeModal();
                            },
                            error: function(error) {
                                modal.enableButton();
                                swal('', error, 'error');
                            }
                        });
                    }
                });
            },

            removeRemoteWork: function(e) {
                if ( typeof e !== 'undefined' ) {
                    e.preventDefault();
                }

                var self = $(this);

                swal({
                    title              : self.data('title'),
                    type               : "warning",
                    showCancelButton   : true,
                    cancelButtonText   : erpProReq.cancel,
                    confirmButtonColor : "#22b527",
                    confirmButtonText  : erpProReq.delete,
                    closeOnConfirm     : false
                },
                function() {
                    wp.ajax.send({
                        data : {
                            req_id   : self.data('id'),
                            action   : self.data('action'),
                            _wpnonce : self.data('nonce')
                        },
                        success: function(res) {
                            erpProHr.reloadPage();
                            erpProHr.showAlert('success', res);
                        },
                        error: function(error) {
                            erpProHr.reloadPage();
                            swal('', error, 'error');
                        }
                    });
                });
            },

            updateRemoteWork: function(e) {
                if ( typeof e !== 'undefined' ) {
                    e.preventDefault();
                }

                var self     = $(this),
                    reqId    = self.data('id'),
                    title    = self.data('title'),
                    nonce    = self.data('nonce'),
                    template = self.data('template');

                wp.ajax.send({
                    data : {
                        req_id   : reqId,
                        action   : 'erp_hr_employee_get_single_remote_work_history',
                        _wpnonce : nonce
                    },
                    success: function(res) {
                        $.erpPopup({
                            title: title,
                            button: erpProReq.update,
                            id: 'erp-hr-employee-remote-work-update',
                            content: '',
                            extraClass: 'smaller',
        
                            onReady: function() {
                                var html = wp.template( template )(res);
                                $( '.content', this ).html( html );
                                erpProHr.initDateField();
        
                                $( '.row[data-selected]', this ).each(function() {
                                    var self = $(this),
                                        selected = self.data('selected');
        
                                    if ( selected !== '' ) {
                                        self.find( 'select' ).val( selected );
                                    }
                                });
        
                                erpProHr.select2Action('erp-hrm-select2');
                            },
                            onSubmit: function(modal) {
                                wp.ajax.send( {
                                    data: this.serializeObject(),
                                    success: function(res) {
                                        erpProHr.reloadPage();
                                        erpProHr.showAlert('success', res);
                                        modal.closeModal();
                                    },
                                    error: function(error) {
                                        erpProHr.reloadPage();
                                        modal.enableButton();
                                        swal('', error, 'error');
                                    }
                                });
                            }
                        });
                    },
                    error: function(error) {
                        erpProHr.reloadPage();
                        swal('', error, 'error');
                    }
                });
            },

            filterRemoteWork: function(e) {
                e.preventDefault();

                var data   = {},
                    values = $(this).serializeArray();

                jQuery.each(values, function() {
                    if ( data[this.name] !== undefined  ) {
                        if ( !data[this.name].push ) {
                            data[this.name] = [ data[this.name] ];
                        }
                        data[this.name].push(this.value || '');
                    } else {
                        data[this.name] = this.value || '';
                    }
                });

                wp.ajax.send( {
                    data: data,
                    success: function(res) {
                        $('tbody#erp-hr-remote-work-history').html(res);
                    },
                    error: function(error) {
                        swal('', error, 'error');
                    }
                });   
            }
        }
    }

    $(function() {
        erpProHr.init();
    });

})(jQuery);