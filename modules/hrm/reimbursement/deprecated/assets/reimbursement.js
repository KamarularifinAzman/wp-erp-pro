;(function($) {
	var reimbursement = {
		init : function() {
			//Transaction table row action
            $( '.erp-accounting' ).on( 'click', '.erp-ac-reimbur-trns-row-status', this.rowDelete );
            $( '.erp-accounting' ).on( 'click', 'input[name="submit_action_delete"]', this.confirmation );
		},
        confirmation: function(e) {
            e.preventDefault();
            var self = $(this),
                status   = $('#action').val();

            switch( status ) {
                case 'void':
                    text = ERP_AC.message.void;
                    break;
                case 'delete':
                    text = ERP_AC.message.delete;
                    break;
                case '-1':
                    return false;
                    break;
                default:
                    text = ERP_AC.message.confirm;
            }
            swal({
                title: '',
                text: text,
                type: "warning",
                cancelButtonText: ERP_AC.message.cancel,
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: ERP_AC.message.yes,
                closeOnConfirm: false,
                showCancelButton: true,   closeOnConfirm: false,   showLoaderOnConfirm: true,
            },
            function(){
                self.closest('form').submit();
            });
        },
		rowDelete: function(e) {
            e.preventDefault();
            var self = $(this),
                status = self.data('status'),
                text = '',
                id   = self.data('id');

            switch( status ) {
                case 'void':
                    text = ERP_AC.message.void;
                    break;
                case 'delete':
                    text = ERP_AC.message.delete;
                    break;
                default:
                    text = ERP_AC.message.confirm;
            }

            swal({
                title: '',
                text: text,
                type: "warning",
                cancelButtonText: ERP_AC.message.cancel,
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: ERP_AC.message.yes,
                closeOnConfirm: false,
                showCancelButton: true,   closeOnConfirm: false,   showLoaderOnConfirm: true,
            },
            function(){

                wp.ajax.send('erp-ac-reimbur-trns-row-status', {
                    data: {
                        'id': id,
                        'status' : self.data('status'),
                        '_wpnonce': ERP_AC.nonce
                    },
                    success: function(res) {
                        swal("", res.success, "success");
                        location.reload();
                    },
                    error: function(error) {
                        swal({
                            title: error.error,
                            text: error,
                            type: "error",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#DD6B55"
                        });
                    }
                });

            });
        },
	}

	reimbursement.init();
})(jQuery)