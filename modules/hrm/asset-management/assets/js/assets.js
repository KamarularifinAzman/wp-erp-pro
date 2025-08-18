;
(function($) {
    $(document).ready(function() {

        var WeDevs_ERP_Assets = {

            initialize: function() {
                // Asset
                $( '.erp-hr-assets' ).on( 'click', '.asset-new', this.assets.addNew );
                $( '.erp-hr-assets' ).on( 'click', '.asset-edit', this.assets.edit );
                $( '.asset-single-page' ).on( 'click', '.asset-edit', this.assets.edit );
                $( '.erp-hr-assets' ).on( 'click', '.asset-delete', this.assets.remove );
                // Allottment
                $( '.erp-hr-allottment' ).on( 'click', '.allot-new', this.allottment.addNew );
                $( '.erp-hr-allottment' ).on( 'click', '.allottment-edit', this.allottment.edit );
                $( '.erp-hr-allottment' ).on( 'click', '.asset-return', this.allottment.return );
                $( '.erp-hr-allottment' ).on( 'click', '.allott-remove', this.allottment.remove );
                $( '.erp-hr-employee-assets-add' ).on( 'click', '.allott-request-return', this.allottment.requestReturn );
                $( '.erp-hr-allottment' ).on( 'click', '.accept-return-request', this.allottment.acceptRequestReturn );
                $( '.erp-hr-allottment' ).on( 'click', '.reject-return-request', this.allottment.rejectRequestReturn );
                // Single Employee
                $( '.erp-hr-employee-assets-add' ).on( 'click', '#erp-hr-emp-add-asset', this.assets.general.create );
                $( '.erp-hr-employee-assets' ).on( 'click', '.emp-asset-edit', this.assets.general.edit );
                $( '.erp-hr-employee-assets' ).on( 'click', '.emp-asset-delete', this.assets.general.remove );
                //Asset Request
                $( '.erp-hr-employee-assets-request' ).on( 'click', '#erp-hr-emp-request-asset', this.request.create );
                $( '.erp-hr-employee-assets-request' ).on( 'click', '.emp-asset-request-edit', this.request.edit );
                $( '.erp-hr-employee-assets-request' ).on( 'click', '.emp-asset-request-delete', this.request.delete );
                $( '.erp-asset-requests' ).on( 'click', '.request-approve', this.request.approve );
                $( '.erp-asset-requests' ).on( 'click', '.request-reject', this.request.reject );
                $( '.erp-asset-requests' ).on( 'click', '.request-undo', this.request.undo );
                $( '.erp-asset-requests' ).on( 'click', '.request-disapprove', this.request.disapprove );
                // Asset Category
                $( 'body' ).on( 'click', '.asset-add-category', this.assets.category.create );
                $( 'body' ).on( 'click', '.asset-edit-category', this.assets.category.edit );
                $('body').on('click', '.asset-category-delete', this.assets.category.delete);
                // Single Item
                $( '.asset-single-page').on( 'click', '.single-item-delete', this.assets.single.delete );
                $( '.asset-single-page').on( 'click', '.single-item-dissmiss', this.assets.single.dissmiss );
            },

            initDatePicker: function() {
                $( '.assets-date-field' ).datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    yearRange: '1900:+100'
                });
            },

            reload: function() {
                // $('.erp-hr-assets').load(window.location.href + ' .erp-hr-assets');
                location.reload();
            },

            customReload: function( parent, child ) {
                $(parent).load(window.location.href + ' ' + child );
            },

            assets: {

                addNew: function( e ) {
                    e.preventDefault();

                    $.erpPopup({
                        title: wpErpAsset.popup.titleNew,
                        button: wpErpAsset.popup.submitNew,
                        id: 'erp-hr-new-asset',
                        extraClass: 'large',
                        content: wp.template('erp-asset-new')(wpErpAsset.empty_asset).trim(),
                        onReady: function() {
                            $('#erp-hr-new-asset').on('change', '.asset-type', function() {
                                var value = $(this).val();

                                if ('variable' == value) {
                                    $('.item-no').text(1);
                                    $('.add-new-item').append('<button id="add-item" class="button-primary"></button>');
                                    $('#add-item').text(wpErpAsset.popup.addAnoterItem);
                                } else {
                                    $('.item-no').text('');
                                    $('.add-new-item').html('');
                                    $('#extra-item-area').html('');
                                }
                            });

                            $('.add-new-item').on('click', '#add-item', function(e) {
                                e.preventDefault();
                                var clonedItem = $('.single-item-area');
                                var lastClone = clonedItem.eq(clonedItem.length - 1).clone();
                                // Item No
                                var itemNo = lastClone.find('.item-no');
                                var nextNumber = parseInt(itemNo.text()) + 1;
                                itemNo.text(nextNumber);
                                // Item Code
                                var nextItemCode = 'items[' + nextNumber + '][item_code]';
                                lastClone.find('.item-code').val('');
                                lastClone.find('.item-code').attr('name', nextItemCode);
                                lastClone.find('.item-code').attr('id', nextItemCode);
                                lastClone.find('.item-code').prev().attr('for', nextItemCode);
                                // Model NO
                                var nextModelNo = 'items[' + nextNumber + '][model_no]';
                                lastClone.find('.model-no').attr('name', nextModelNo);
                                lastClone.find('.model-no').attr('id', nextModelNo);
                                lastClone.find('.model-no').prev().attr('for', nextModelNo);
                                // Manufacturer
                                var nextManufacturer = 'items[' + nextNumber + '][manufacturer]';
                                lastClone.find('.manufacturer').attr('name', nextManufacturer);
                                lastClone.find('.manufacturer').attr('id', nextManufacturer);
                                lastClone.find('.manufacturer').prev().attr('for', nextManufacturer);
                                // Manufacturer
                                var nextPrice = 'items[' + nextNumber + '][price]';
                                lastClone.find('.price').attr('name', nextPrice);
                                lastClone.find('.price').attr('id', nextPrice);
                                lastClone.find('.price').prev().attr('for', nextPrice);
                                // Expiry Date
                                var nextExpiry = 'items[' + nextNumber + '][date_exp]';
                                lastClone.find('.expiry-date').removeClass('hasDatepicker');
                                lastClone.find('.expiry-date').attr('name', nextExpiry);
                                lastClone.find('.expiry-date').attr('id', nextExpiry);
                                lastClone.find('.expiry-date').prev().attr('for', nextExpiry);
                                // Warranty Date
                                var nextWarranty = 'items[' + nextNumber + '][date_warr]';
                                lastClone.find('.warranty-date').removeClass('hasDatepicker');
                                lastClone.find('.warranty-date').attr('name', nextWarranty);
                                lastClone.find('.warranty-date').attr('id', nextWarranty);
                                lastClone.find('.warranty-date').prev().attr('for', nextWarranty);
                                // Allotable
                                var nextAllottable = 'items[' + nextNumber + '][allottable]';
                                lastClone.find('.allottable').attr('name', nextAllottable);
                                lastClone.find('.allottable').attr('id', nextAllottable);
                                lastClone.find('.allottable').parent('label').attr('for', nextAllottable);
                                lastClone.find('.allottable').parent().parent().prev('label').attr('for', nextAllottable);
                                // Serial
                                var nextItemSerial = 'items[' + nextNumber + '][item_serial]';
                                lastClone.find('.serial-info').val('');
                                lastClone.find('.serial-info').attr('name', nextItemSerial);
                                lastClone.find('.serial-info').attr('id', nextItemSerial);
                                lastClone.find('.serial-info').prev().attr('for', nextItemSerial);
                                // Description
                                var nextItemDesc = 'items[' + nextNumber + '][item_desc]';
                                lastClone.find('.item-desc').attr('name', nextItemDesc);
                                lastClone.find('.item-desc').attr('id', nextItemDesc);
                                lastClone.find('.item-desc').prev().attr('for', nextItemDesc);

                                var deleteButton = lastClone.find('.delete-item');
                                if (0 == deleteButton.length) {
                                    lastClone.append('<button class="delete-item button-secondary">'+wpErpAsset.delete+'</button>');
                                }

                                $('#extra-item-area').append(lastClone);
                                WeDevs_ERP_Assets.initDatePicker();
                            });

                            $('#erp-hr-new-asset').on('click', '.delete-item', function(e) {
                                e.preventDefault();

                                var item = $(this);

                                swal({
                                        title: wpErpAsset.confirm,
                                        type: "warning",
                                        showCancelButton: true,
                                        confirmButtonColor: "#DD6B55",
                                        confirmButtonText: wpErpAsset.deleteConfirmBtn,
                                        cancelButtonText: wpErpAsset.dismissCancelBtn,
                                        closeOnConfirm: true,
                                        closeOnCancel: true,
                                    },
                                    function(isConfirm){
                                        if (isConfirm) {
                                            item.closest('.single-item-area').fadeOut().remove();
                                            var items = $('.single-item-area');
                                            var itemLength = items.length;

                                            $.each(items, function( index, value ){
                                                $(value).find('.item-no').text(index + 1);
                                            });
                                            WeDevs_ERP_Assets.initDatePicker();
                                        }
                                    }
                                );
                            });

                            $('.allottable').prop('checked', true);

                            WeDevs_ERP_Assets.initDatePicker();
                        },
                        onSubmit: function(modal) {
                            modal.disableButton();
                            wp.ajax.send({
                                data: this.serialize(),
                                success: function (res) {
                                    swal({
                                        title: wpErpAsset.popup.asset.added,
                                        timer: 2000,
                                        text: wpErpAsset.popup.asset.addedMsg,
                                        type: "success"
                                    });

                                    modal.enableButton();
                                    WeDevs_ERP_Assets.customReload('.erp-hr-assets', '.erp-hr-assets');
                                    modal.closeModal();
                                },
                                error: function (error) {
                                    modal.enableButton();
                                    sweetAlert(wpErpAsset.error, error, "error");
                                }
                            });
                        }
                    }); //popup
                },

                edit: function( e ) {
                    e.preventDefault();

                    var self = $( this );

                    $.erpPopup( {
                        title: wpErpAsset.popup.titleEdit,
                        button: wpErpAsset.popup.submitEdit,
                        id: 'erp-hr-edit-asset',
                        onReady: function() {
                            var modal = this;

                            $('header', modal).after($('<div class="loader"></div>').show());
                            wp.ajax.send( 'erp-hr-asset-get', {
                                data: {
                                    id: self.data( 'id' ),
                                    _wpnonce: wpErpAsset.nonce
                                },

                                success: function( response ) {

                                    var html = wp.template( 'erp-asset-edit' )( response );

                                    $( '.content', modal ).html( html );
                                    $( '.loader', modal ).remove();

                                    $('div[data-selected]', modal).each(function() {
                                        var self = $(this),
                                            selected = self.data('selected');

                                        if(selected !== '') {
                                            self.find('select').val(selected);
                                        }
                                    });

                                    $('li[data-selected]', modal).each(function() {
                                        var self = $(this),
                                            selected = self.data('selected');

                                        if(selected == 'on') {
                                            self.find('.allottable').prop('checked', true);
                                        }
                                    });

                                    if ('variable' == response[0].asset_type) {
                                        $('.add-new-item').append('<button id="add-item" class="button-primary"></button>');
                                        $('#add-item').text(wpErpAsset.popup.addAnoterItem);
                                    }

                                    $('.add-new-item').on('click', '#add-item', function(e) {
                                        e.preventDefault();
                                        var clonedItem = $('.single-item-area fieldset');
                                        var lastClone = clonedItem.eq(clonedItem.length - 1).clone();
                                        lastClone.find('.row-id').remove();
                                        // Item No
                                        var itemNo = lastClone.find('.item-no');
                                        var nextNumber = parseInt(itemNo.text()) + 1;
                                        itemNo.text(nextNumber);
                                        // Item Code
                                        var nextItemCode = 'items[' + nextNumber + '][item_code]';
                                        lastClone.find('.item-code').val('');
                                        lastClone.find('.item-code').attr('name', nextItemCode);
                                        lastClone.find('.item-code').attr('id', nextItemCode);
                                        lastClone.find('.item-code').prev().attr('for', nextItemCode);
                                        // Model NO
                                        var nextModelNo = 'items[' + nextNumber + '][model_no]';
                                        lastClone.find('.model-no').attr('name', nextModelNo);
                                        lastClone.find('.model-no').attr('id', nextModelNo);
                                        lastClone.find('.model-no').prev().attr('for', nextModelNo);
                                        // Manufacturer
                                        var nextManufacturer = 'items[' + nextNumber + '][manufacturer]';
                                        lastClone.find('.manufacturer').attr('name', nextManufacturer);
                                        lastClone.find('.manufacturer').attr('id', nextManufacturer);
                                        lastClone.find('.manufacturer').prev().attr('for', nextManufacturer);
                                        // Manufacturer
                                        var nextPrice = 'items[' + nextNumber + '][price]';
                                        lastClone.find('.price').attr('name', nextPrice);
                                        lastClone.find('.price').attr('id', nextPrice);
                                        lastClone.find('.price').prev().attr('for', nextPrice);
                                        // Expiry Date
                                        var nextExpiry = 'items[' + nextNumber + '][date_exp]';
                                        lastClone.find('.expiry-date').removeClass('hasDatepicker');
                                        lastClone.find('.expiry-date').attr('name', nextExpiry);
                                        lastClone.find('.expiry-date').attr('id', nextExpiry);
                                        lastClone.find('.expiry-date').prev().attr('for', nextExpiry);
                                        // Warranty Date
                                        var nextWarranty = 'items[' + nextNumber + '][date_warr]';
                                        lastClone.find('.warranty-date').removeClass('hasDatepicker');
                                        lastClone.find('.warranty-date').attr('name', nextWarranty);
                                        lastClone.find('.warranty-date').attr('id', nextWarranty);
                                        lastClone.find('.warranty-date').prev().attr('for', nextWarranty);
                                        // Allotable
                                        var nextAllottable = 'items[' + nextNumber + '][allottable]';
                                        lastClone.find('.allottable').attr('name', nextAllottable);
                                        lastClone.find('.allottable').attr('id', nextAllottable);
                                        lastClone.find('.allottable').parent('label').attr('for', nextAllottable);
                                        lastClone.find('.allottable').parent().parent().prev('label').attr('for', nextAllottable);
                                        // Serial
                                        var nextItemSerial = 'items[' + nextNumber + '][item_serial]';
                                        lastClone.find('.serial-info').val('');
                                        lastClone.find('.serial-info').attr('name', nextItemSerial);
                                        lastClone.find('.serial-info').attr('id', nextItemSerial);
                                        lastClone.find('.serial-info').prev().attr('for', nextItemSerial);
                                        // Description
                                        var nextItemDesc = 'items[' + nextNumber + '][item_desc]';
                                        lastClone.find('.item-desc').attr('name', nextItemDesc);
                                        lastClone.find('.item-desc').attr('id', nextItemDesc);
                                        lastClone.find('.item-desc').prev().attr('for', nextItemDesc);

                                        var deleteButton = lastClone.find('.delete-item');
                                        if (0 == deleteButton.length) {
                                            lastClone.append('<button class="delete-item button-secondary">'+wpErpAsset.delete+'</button>');
                                        }

                                        $('.single-item-area').append(lastClone);

                                        WeDevs_ERP_Assets.initDatePicker();
                                    });

                                    $('#erp-hr-edit-asset').on('click', '.delete-item', function(e) {
                                        e.preventDefault();

                                        var item  = $(this);
                                        var rowID = $(this).prev().children('.row-id').val();

                                        if( !rowID ) {
                                            swal({
                                                    title: wpErpAsset.confirm,
                                                    type: "warning",
                                                    showCancelButton: true,
                                                    confirmButtonColor: "#DD6B55",
                                                    confirmButtonText: wpErpAsset.deleteConfirmBtn,
                                                    cancelButtonText: wpErpAsset.dismissCancelBtn,
                                                    closeOnConfirm: true,
                                                    closeOnCancel: true,
                                                },
                                                function(isConfirm){
                                                    if (isConfirm) {
                                                        item.closest('fieldset').fadeOut().remove();
                                                        var items = $('.single-item-area fieldset');
                                                        var itemLength = items.length;

                                                        $.each(items, function( index, value ){
                                                            $(value).find('.item-no').text(index + 1);
                                                        });
                                                        WeDevs_ERP_Assets.initDatePicker();
                                                    }
                                                }
                                            );
                                        } else {
                                            swal({
                                                    title: wpErpAsset.confirm,
                                                    text: wpErpAsset.singleDeleteConfirm,
                                                    type: "warning",
                                                    showCancelButton: true,
                                                    confirmButtonColor: "#DD6B55",
                                                    confirmButtonText: wpErpAsset.deleteConfirmBtn,
                                                    cancelButtonText: wpErpAsset.dismissCancelBtn,
                                                    closeOnConfirm: true,
                                                    closeOnCancel: true,
                                                },
                                                function(isConfirm){
                                                    if (isConfirm) {
                                                        wp.ajax.send( 'erp-assets-single-item-delete', {
                                                            data: {
                                                                id: rowID,
                                                                _wpnonce: wpErpAsset.nonce
                                                            },
                                                            success: function(res) {
                                                                item.closest('fieldset').fadeOut().remove();
                                                                var items = $('.single-item-area fieldset');
                                                                var itemLength = items.length;

                                                                $.each(items, function( index, value ){
                                                                    $(value).find('.item-no').text(index + 1);
                                                                });
                                                                WeDevs_ERP_Assets.initDatePicker();
                                                            },
                                                            error: function(error) {
                                                                sweetAlert(wpErpAsset.error, error, "error");
                                                            }
                                                        });
                                                    } else {
                                                    }
                                                });
                                        }

                                    });

                                    WeDevs_ERP_Assets.initDatePicker();
                                },

                                error: function ( error ) {
                                    sweetAlert(wpErpAsset.error, error, "error");
                                }
                            });

                        },
                        onSubmit: function( modal ) {
                            modal.disableButton();
                            wp.ajax.send( {
                                data: this.serialize(),
                                success: function( response ) {
                                    if ( $('.asset-single-page').length ) {
                                        WeDevs_ERP_Assets.customReload('.asset-single-page', '.asset-single-page');
                                    } else {
                                        WeDevs_ERP_Assets.customReload('.erp-hr-assets', '.erp-hr-assets');
                                    }

                                    modal.enableButton();
                                    modal.closeModal();
                                },
                                error: function( error ) {
                                    modal.enableButton();
                                    sweetAlert(wpErpAsset.error, error, "error");
                                }
                            });
                        }
                    });
                },

                remove: function( e ) {
                    e.preventDefault();

                    var self = $( this );

                    swal({
                            title: wpErpAsset.confirm,
                            text: wpErpAsset.assetDeleteConfirm,
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: wpErpAsset.deleteConfirmBtn,
                            cancelButtonText: wpErpAsset.dismissCancelBtn,
                            closeOnConfirm: false,
                            closeOnCancel: true,
                        },
                        function(isConfirm){
                            if (isConfirm) {
                                wp.ajax.send( 'erp-hr-asset-delete', {
                                    data: {
                                        id: self.data('id'),
                                        _wpnonce: wpErpAsset.nonce
                                    },
                                    success: function(res) {
                                        swal({
                                            title: wpErpAsset.deleted,
                                            timer: 2000,
                                            text: wpErpAsset.deleteConfirmMsg,
                                            type: "success"
                                            });
                                        WeDevs_ERP_Assets.customReload('.erp-hr-assets', '.erp-hr-assets');
                                    },
                                    error: function(error) {
                                        sweetAlert(wpErpAsset.error, error, "error");
                                    }
                                });
                            } else {
                            }
                        }
                    );
                },
                general: {

                    create: function(e) {
                        e.preventDefault();

                        var self = $(this);

                        $.erpPopup({
                            title: self.data('title'),
                            content: wp.template(self.data('template'))(self.data('data')),
                            extraClass: 'smaller',
                            id: 'erp-hr-new-asset',
                            button: self.data('button'),
                            onReady: function(modal) {
                                WeDevs_ERP_Assets.cascadeAssetList();
                                WeDevs_ERP_Assets.initDatePicker();

                                var selected = $('.row[data-selected]').data('selected');

                                if( selected != '' ) {
                                    $('.asset-category').val(selected);
                                }
                                $('input[name="emp_id"]').val(self.data('data').employee_id);
                            },
                            onSubmit: function(modal) {
                                modal.disableButton();
                                wp.ajax.send( {
                                data: this.serialize(),
                                success: function(res) {
                                    WeDevs_ERP_Assets.customReload( '.erp-hr-employee-assets-add', '.erp-hr-employee-assets-add');
                                    modal.enableButton();
                                    modal.closeModal();
                                },
                                error: function( error ) {
                                    modal.enableButton();
                                    sweetAlert(wpErpAsset.error, error, "error");
                                }
                            });
                            }
                        });
                    },

                    edit: function(e) {
                        e.preventDefault();

                        var self = $(this);

                        $.erpPopup({
                            title: self.data('title'),
                            content: wp.template(self.data('template'))(self.data('data')),
                            extraClass: 'smaller',
                            id: 'erp-hr-new-asset',
                            button: self.data('button'),
                            onReady: function(modal) {
                                WeDevs_ERP_Assets.cascadeAssetList();
                                WeDevs_ERP_Assets.initDatePicker();

                                var selected = $('.row[data-selected]').data('selected');

                                if( selected != '' ) {
                                    $('.asset-category').val(selected);
                                }
                            },
                            onSubmit: function(modal) {
                                modal.disableButton();
                                wp.ajax.send( {
                                    data: this.serialize(),
                                    success: function(res) {
                                        modal.enableButton();
                                        WeDevs_ERP_Assets.customReload( '.erp-hr-employee-assets-add', '.erp-hr-employee-assets-add');
                                        modal.closeModal();
                                    },
                                    error: function( error ) {
                                        modal.enableButton();
                                        sweetAlert(wpErpAsset.error, error, "error");
                                    }
                                });
                            }
                        });
                    },

                    remove: function(e) {
                        e.preventDefault();

                        var self = $(this);

                        if( confirm( wpErpAsset.confirm ) ) {
                            wp.ajax.send( self.data('action'), {
                                data: {
                                    id: self.data('id'),
                                    _wpnonce: wpErpAsset.nonce
                                },
                                success: function(res) {
                                    WeDevs_ERP_Assets.empTabReload();
                                },
                                error: function(error) {
                                    sweetAlert(wpErpAsset.error, error, "error");
                                }
                            });
                        }
                    },
                },

                category: {

                    create: function ( e ) {
                        e.preventDefault();

                        $.erpPopup({
                            title: wpErpAsset.popup.titleCategory,
                            button: wpErpAsset.popup.submitCategory,
                            id: 'asset-category-new',
                            extraClass: 'smaller',
                            content: wp.template('asset-category-new')().trim(),
                            onReady: function() {
                            },
                            onSubmit: function(modal) {
                                modal.disableButton();
                                wp.ajax.send( {
                                    data: this.serialize(),
                                    success: function(res) {
                                        modal.enableButton();
                                        modal.closeModal();
                                        $('.asset-category').append($("<option></option>").attr("value",res.value).text(res.text)).val(res.value);
                                        wperp.scriptReload( 'erp_asset_edit_category_reload', 'tmpl-asset-category-edit' );
                                    },
                                    error: function( error ) {
                                        modal.enableButton();
                                        sweetAlert(wpErpAsset.error, error, "error");
                                    }
                                });
                            }
                        }); //popup
                    },

                    edit: function( e ) {
                        e.preventDefault();

                        $.erpPopup({
                            title: wpErpAsset.popup.editCategory,
                            button: wpErpAsset.popup.submitEdit,
                            id: 'asset-category-edit',
                            extraClass: 'medium',
                            content: wp.template('asset-category-edit')().trim(),
                            onReady: function() {
                                $('.asset-category-edit').on('click', function() {
                                    var self = $(this);
                                    var id = self.data('id');
                                    var value = self.data('value');
                                    var html = '<input name="cat_name" type="text" value="' + value + '">';
                                    html += '<input name="row_id" type="hidden" value="' + id + '">';
                                    $('.category-edit').html(html);
                                    $('.category-edit input[name="cat_name"]').focus();
                                });
                            },
                            onSubmit: function(modal) {
                                modal.disableButton();
                                wp.ajax.send( {
                                    data: this.serialize(),
                                    success: function(res) {
                                        modal.enableButton();
                                        modal.closeModal();
                                        $('.asset-category option[value="'+res.value+'"]').text(res.text);
                                        $('.asset-category').val(res.value);
                                    },
                                    error: function( error ) {
                                        modal.enableButton();
                                        sweetAlert(wpErpAsset.error, error, "error");
                                    }
                                });
                            }
                        }); //popup
                    },

                    delete: function (e) {
                        e.preventDefault();
                        var self = $(this);
                        var id = self.data('id');

                        wp.ajax.send({
                            data: {
                                id: id,
                                action: 'erp-hr-assets-is-category-used',
                                _wpnonce: wpErpAsset.nonce
                            },
                            success: function (res) {

                                if (res.exist) {
                                    sweetAlert(wpErpAsset.error, wpErpAsset.catCantDelete, "error");
                                } else {

                                    swal({
                                            title: wpErpAsset.confirm,
                                            text: wpErpAsset.confirm,
                                            type: "warning",
                                            showCancelButton: true,
                                            confirmButtonColor: "#DD6B55",
                                            confirmButtonText: wpErpAsset.deleteConfirmBtn,
                                            cancelButtonText: wpErpAsset.dismissCancelBtn,
                                            closeOnConfirm: true,
                                            closeOnCancel: true,
                                        },
                                        function(isConfirm){
                                            if (isConfirm) {
                                                wp.ajax.send({
                                                    data: {
                                                        id: id,
                                                        action: 'erp-hr-assets-category-delete',
                                                        _wpnonce: wpErpAsset.nonce
                                                    },
                                                    success: function (res) {
                                                        if (res.deleted) {
                                                            self.closest('tr').fadeOut().delete();
                                                        }
                                                    },
                                                    error: function (error) {
                                                        sweetAlert(wpErpAsset.error, error, "error");
                                                    }
                                                });
                                            }
                                        });

                                }
                            },
                            error: function (error) {
                                sweetAlert(wpErpAsset.error, error, "error");
                            }
                        });
                    }
                },

                single: {
                    delete: function(e) {
                        e.preventDefault();

                        var self = $(this);

                        swal({
                                title: wpErpAsset.confirm,
                                text: wpErpAsset.singleDeleteConfirm,
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#DD6B55",
                                confirmButtonText: wpErpAsset.deleteConfirmBtn,
                                cancelButtonText: wpErpAsset.dismissCancelBtn,
                                closeOnConfirm: false,
                                closeOnCancel: true,
                            },
                            function(isConfirm){
                                if (isConfirm) {
                                    wp.ajax.send( 'erp-assets-single-item-delete', {
                                        data: {
                                            id: self.data('id'),
                                            _wpnonce: wpErpAsset.nonce
                                        },
                                        success: function(res) {
                                            swal({
                                                title: wpErpAsset.deleted,
                                                timer: 2000,
                                                text: wpErpAsset.deleteConfirmMsg,
                                                type: "success"
                                            });
                                            WeDevs_ERP_Assets.reload();
                                        },
                                        error: function(error) {
                                            sweetAlert(wpErpAsset.error, error, "error");
                                        }
                                    });
                                } else {
                                }
                        });
                    },

                    dissmiss: function(e) {
                        e.preventDefault();

                        var self = $(this);

                        swal({
                                title: wpErpAsset.confirm,
                                text: wpErpAsset.singleDismissConfirm,
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#DD6B55",
                                confirmButtonText: wpErpAsset.dismissConfirmBtn,
                                cancelButtonText: wpErpAsset.dismissCancelBtn,
                                closeOnConfirm: false,
                                closeOnCancel: true,
                            },
                            function(isConfirm){
                                if (isConfirm) {
                                    wp.ajax.send( 'erp-assets-single-item-dissmiss', {
                                        data: {
                                            id: self.data('id'),
                                            _wpnonce: wpErpAsset.nonce
                                        },
                                        success: function(res) {
                                            swal({
                                                title: wpErpAsset.dismissed,
                                                timer: 2000,
                                                text: wpErpAsset.dismissConfirmMsg,
                                                type: "success"
                                            });
                                            WeDevs_ERP_Assets.reload();
                                        },
                                        error: function(error) {
                                            sweetAlert(wpErpAsset.error, error, "error");
                                        }
                                    });
                                }
                            }
                        );
                    }
                }
            },

            allottment: {
                addNew: function( e ) {
                    e.preventDefault();

                    $.erpPopup({
                        title: wpErpAsset.popup.allot.titleNew,
                        button: wpErpAsset.popup.allot.submitNew,
                        id: 'erp-hr-new-allotment',
                        extraClass: 'large',
                        content: wp.template('erp-allotment-new')(wpErpAsset.emptyAllot).trim(),
                        onReady: function() {
                            WeDevs_ERP_Assets.cascadeAssetList();
                            $('#is_returnable').attr('checked', true).trigger('change');
                        },
                        onSubmit: function(modal) {
                            modal.disableButton();
                            wp.ajax.send({
                                data: this.serialize(),
                                success: function (res) {
                                    modal.enableButton();
                                    modal.closeModal();
                                    WeDevs_ERP_Assets.customReload('.erp-hr-allottment', '.erp-hr-allottment');
                                },
                                error: function (error) {
                                    modal.enableButton();
                                    sweetAlert(wpErpAsset.error, error, "error");
                                }
                            });
                        }
                    }); //popup
                },

                edit: function(e) {
                    e.preventDefault();

                    var self = $(this);

                    $.erpPopup({
                        title: wpErpAsset.popup.allot.titleEdit,
                        button: wpErpAsset.popup.allot.submitEdit,
                        id: 'erp-assets-edit-allotment',
                        extraClass: 'large',
                        onReady: function() {

                            var modal = this;

                            $('header', modal).after($('<div class="loader"></div>').show());

                            wp.ajax.send( 'erp-assets-allottment-get', {
                                data: {
                                    id: self.data( 'id' ),
                                    _wpnonce: wpErpAsset.nonce
                                },
                                success: function( response ) {
                                    var html = wp.template( 'erp-allotment-new' )( response );
                                    $( '.content', modal ).html( html );

                                    $('.asset-category').on('change', function() {
                                        var cat_id = $(this).val();

                                        $('.item-name option').not(':first').remove();
                                        $('.item option').not(':first').remove();

                                        if ( '-1' == cat_id ) {
                                            return;
                                        }

                                        wp.ajax.send({
                                            data: {
                                                cat_id: cat_id,
                                                action: 'erp-hr-get-item-by-category',
                                                _wpnonce: wpErpAsset.nonce
                                            },
                                            success: function(res) {

                                                var itemSelect = $('.item-name');
                                                var optionValues = [];

                                                $.each(res, function(index, item) {
                                                    itemSelect.append($("<option />").val(item.id).text(item.item_group));
                                                });

                                                itemSelect.find('option').each(function() {
                                                    optionValues.push($(this).val());
                                                });

                                                if ( response.item_group ) {
                                                    if ( '-1' != $.inArray( response.item_group, optionValues ) ) {
                                                        itemSelect.val(response.item_group).trigger('change');
                                                    } else {
                                                        itemSelect.val('-1').trigger('change');
                                                    }
                                                }
                                            },
                                            error: function(error) {
                                                sweetAlert(wpErpAsset.error, error, "error");
                                            }
                                        });
                                    });

                                    $('.item-name').on('change', function() {
                                        var item_id = $(this).val();

                                        $('.item option').not(':first').remove();

                                        if ( '-1' == item_id ) {
                                            return;
                                        }

                                        wp.ajax.send({
                                            data: {
                                                item_group_id: item_id,
                                                action: 'erp-hr-allottment-get-item-by-group',
                                                _wpnonce: wpErpAsset.nonce
                                            },
                                            success: function(res) {

                                                var itemSelect = $('.item');
                                                var optionValues = [];

                                                $.each(res, function(index, item) {
                                                    itemSelect.append($("<option />").val(item.id).text('[' + item.item_code + '] ' + item.model_no));
                                                });

                                                itemSelect.find('option').each(function() {
                                                    optionValues.push($(this).val());
                                                });

                                                if ( response.item_id ) {
                                                    if ( '-1' != $.inArray( response.item_id, optionValues ) ) {
                                                        itemSelect.val(response.item_id).trigger('change');
                                                    } else {
                                                        itemSelect.val('-1').trigger('change');
                                                    }
                                                }

                                                //if ( response.item_id ) {
                                                //    itemSelect.val(response.item_id);
                                                //}
                                                $('.loader', modal).remove();
                                            },
                                            errror: function(error) {
                                                alert(error);
                                            }
                                        });
                                    });

                                    $('.is_returnable').on('change', function() {
                                        if(this.checked) {
                                            $('#return-date').show();
                                            $('#return_date').prop('required', true);
                                        }else{
                                            $('#return-date').hide();
                                            $('#return_date').prop('required', false);
                                        }
                                    });

                                    WeDevs_ERP_Assets.initDatePicker();

                                    $('.asset-category').val(response.category_id).trigger('change');
                                    $('.allotted_to').val(response.allotted_to);

                                    if( 'yes' == response.is_returnable ) {
                                        $('.is_returnable').prop('checked', true).trigger('change');
                                    }
                                },
                                error: function( error ) {
                                    sweetAlert(wpErpAsset.error, error, "error");
                                }
                            });

                            $('.asset-category').change(function() {
                                var cat_id = $(this).val();

                                $('.item-name option').not(':first').remove();
                                $('.item option').not(':first').remove();

                                if ( '-1' == cat_id ) {
                                    return;
                                }

                                wp.ajax.send({
                                    data: {
                                        cat_id: cat_id,
                                        action: 'erp-hr-get-item-by-category',
                                        _wpnonce: wpErpAsset.nonce
                                    },
                                    success: function(res) {

                                        var itemSelect = $('.item-name');

                                        $.each(res, function(index, item) {
                                            itemSelect.append($("<option />").val(item.id).text(item.item_group));
                                        });
                                    },
                                    error: function(error) {
                                        sweetAlert(wpErpAsset.error, error, "error");
                                    }
                                });
                            });

                            $('.item-name').change(function() {
                                var item_id = $(this).val();

                                $('.item option').not(':first').remove();

                                if ( '-1' == item_id ) {
                                    return;
                                }

                                wp.ajax.send({
                                    data: {
                                        item_group_id: item_id,
                                        action: 'erp-hr-get-item-by-group',
                                        _wpnonce: wpErpAsset.nonce
                                    },
                                    success: function(res) {

                                        var itemSelect = $('.item');

                                        $.each(res, function(index, item) {
                                            itemSelect.append($("<option />").val(item.id).text('[' + item.item_code + '] ' + item.model_no));
                                        });
                                    },
                                    errror: function(error) {
                                        alert(error);
                                    }
                                });
                            });

                            $('.is_returnable').change(function() {
                                if(this.checked) {
                                    $('#return-date').show();
                                }else{
                                    $('#return-date').hide();
                                }
                            });
                        },
                        onSubmit: function(modal) {
                            modal.disableButton();
                            wp.ajax.send({
                                data: this.serialize(),
                                success: function (res) {
                                    modal.enableButton();
                                    WeDevs_ERP_Assets.customReload('.erp-hr-allottment', '.erp-hr-allottment');
                                    modal.closeModal();

                                },
                                error: function (error) {
                                    modal.enableButton();
                                    sweetAlert(wpErpAsset.error, error, "error");
                                }
                            });
                        }
                    }); //popup
                },

                return: function(e) {
                    e.preventDefault();

                    var self = $(this);

                    $.erpPopup( {
                        title: wpErpAsset.popup.return.title,
                        button: wpErpAsset.popup.return.button,
                        id: 'erp-asset-return',
                        extraClass: 'smaller',
                        content: wp.template('erp-asset-return')().trim(),
                        onReady: function() {
                            var modal = this;
                            WeDevs_ERP_Assets.initDatePicker();

                            $('input[name="allott_id"]').val(self.data('id'));
                            $('input[name="item_id"]').val(self.data('item-id'));
                        },
                        onSubmit: function( modal ) {
                            modal.disableButton();

                            wp.ajax.send({
                                data: this.serialize(),
                                success: function( res ) {
                                    WeDevs_ERP_Assets.customReload('.erp-hr-allottment', '.erp-hr-allottment');
                                    modal.enableButton();
                                    modal.closeModal();
                                },
                                error: function( error ) {
                                    sweetAlert(wpErpAsset.error, error, "error");
                                    modal.enableButton();
                                }

                            });

                        }
                    });
                },

                remove: function(e) {
                    e.preventDefault();

                    var self = $(this);

                    swal({
                            title: wpErpAsset.confirm,
                            //text: wpErpAsset.assetDeleteConfirm,
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: wpErpAsset.deleteConfirmBtn,
                            cancelButtonText: wpErpAsset.dismissCancelBtn,
                            closeOnConfirm: false,
                            closeOnCancel: true,
                        },
                        function(isConfirm){
                            if (isConfirm) {
                                wp.ajax.send( 'erp-hr-allott-remove', {
                                    data: {
                                        id: self.data('id'),
                                        item_id: self.data('item-id'),
                                        _wpnonce: wpErpAsset.nonce
                                    },
                                    success: function(res) {
                                        swal({
                                            title: wpErpAsset.deleted,
                                            timer: 2000,
                                            text: wpErpAsset.deleteConfirmMsg,
                                            type: "success"
                                        });
                                        WeDevs_ERP_Assets.customReload('.erp-hr-allottment', '.erp-hr-allottment');
                                    },
                                    error: function(error) {
                                        sweetAlert(wpErpAsset.error, error, "error");
                                    }
                                });
                            } else {
                            }
                        }
                    );
                },

                requestReturn: function(e) {
                    e.preventDefault();

                    var self = $(this);

                    swal({
                            title: wpErpAsset.returnReqTitle,
                            text: wpErpAsset.returnReqMsg,
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: wpErpAsset.returnReqOkBtn,
                            cancelButtonText: wpErpAsset.returnReqCancelBtn,
                            closeOnConfirm: false,
                            closeOnCancel: true,
                        },
                        function(isConfirm){
                            if (isConfirm) {
                                wp.ajax.send( 'erp-assets-emp-request-return', {
                                    data: {
                                        id: self.data('id'),
                                        _wpnonce: wpErpAsset.nonce
                                    },
                                    success: function(res) {
                                        swal({
                                            title: wpErpAsset.returnReqConf,
                                            timer: 2000,
                                            text: wpErpAsset.returnReqConfMsg,
                                            type: "success"
                                        });
                                        WeDevs_ERP_Assets.reload();
                                    },
                                    error: function(error) {
                                        sweetAlert(wpErpAsset.error, error, "error");
                                    }
                                });
                            }
                        }
                    );
                },

                acceptRequestReturn: function(e) {
                    e.preventDefault();

                    var self = $(this);

                    $.erpPopup( {
                        title: wpErpAsset.popup.return.title,
                        button: wpErpAsset.popup.return.button,
                        id: 'erp-asset-return',
                        extraClass: 'smaller',
                        content: wp.template('erp-asset-return')().trim(),
                        onReady: function() {
                            var modal = this;
                            WeDevs_ERP_Assets.initDatePicker();

                            $('input[name="allott_id"]').val(self.data('id'));
                            $('input[name="item_id"]').val(self.data('item-id'));
                            $('input[name="date_return"]').val(self.data('date'));
                        },
                        onSubmit: function( modal ) {
                            modal.disableButton();

                            wp.ajax.send({
                                data: this.serialize(),
                                success: function( res ) {
                                    WeDevs_ERP_Assets.customReload('.erp-hr-allottment', '.erp-hr-allottment');
                                    modal.enableButton();
                                    modal.closeModal();
                                },
                                error: function( error ) {
                                    sweetAlert(wpErpAsset.error, error, "error");
                                    modal.enableButton();
                                }

                            });

                        }
                    });
                },

                rejectRequestReturn: function(e) {
                    e.preventDefault();

                    var self = $(this);

                    swal({
                            title: wpErpAsset.popup.reject.submit,
                            text: wpErpAsset.popup.reject.title,
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            //confirmButtonText: wpErpAsset.returnReqOkBtn,
                            //cancelButtonText: wpErpAsset.returnReqCancelBtn,
                            closeOnConfirm: false,
                            closeOnCancel: true,
                        },
                        function(isConfirm){
                            if (isConfirm) {
                                wp.ajax.send( 'erp-assets-emp-reject-return-request', {
                                    data: {
                                        id: self.data('id'),
                                        _wpnonce: wpErpAsset.nonce
                                    },
                                    success: function(res) {
                                        swal({
                                            title: wpErpAsset.popup.rejected.rejected,
                                            timer: 2000,
                                            text: wpErpAsset.popup.rejected.rejectedMsg,
                                            type: "success"
                                        });
                                        WeDevs_ERP_Assets.reload();
                                    },
                                    error: function(error) {
                                        sweetAlert(wpErpAsset.error, error, "error");
                                    }
                                });
                            }
                        }
                    );
                }
            },

            request: {
                create: function(e) {
                    e.preventDefault();

                    var self = $(this);

                    $.erpPopup({
                        title: self.data('title'),
                        content: wp.template(self.data('template'))(self.data('data')),
                        extraClass: 'smaller',
                        id: 'erp-hr-request-asset',
                        button: self.data('button'),
                        onReady: function(modal) {
                            $('.asset-category').change(function() {
                                var cat_id = $(this).val();

                                $('.item-name option').not(':first').remove();
                                $('.item option').not(':first').remove();

                                if ( '-1' == cat_id ) {
                                    return;
                                }

                                wp.ajax.send({
                                    data: {
                                        cat_id: cat_id,
                                        action: 'erp-hr-get-item-by-category',
                                        _wpnonce: wpErpAsset.nonce
                                    },
                                    success: function(res) {

                                        var itemSelect = $('.item-name');

                                        $.each(res, function(index, item) {
                                            itemSelect.append($("<option />").val(item.id).text(item.item_group));
                                        });
                                    },
                                    error: function(error) {
                                        sweetAlert(wpErpAsset.error, error, "error");
                                    }
                                });
                            });

                            //$('.item-name').change(function() {
                            //    var item_id = $(this).val();
                            //
                            //    $('.item option').not(':first').remove();
                            //
                            //    if ( '-1' == item_id ) {
                            //        return;
                            //    }
                            //
                            //    wp.ajax.send({
                            //        data: {
                            //            item_group_id: item_id,
                            //            action: 'erp-hr-get-item-by-group',
                            //            _wpnonce: wpErpAsset.nonce
                            //        },
                            //        success: function(res) {
                            //
                            //            var itemSelect = $('.item');
                            //
                            //            $.each(res, function(index, item) {
                            //                itemSelect.append($("<option />").val(item.id).text('[' + item.item_code + '] ' + item.model_no));
                            //            });
                            //        },
                            //        errror: function(error) {
                            //            alert(error);
                            //        }
                            //    });
                            //});

                            $('.not_in_list').on('change', function() {
                                if(this.checked) {
                                    $('#request-description').show();
                                    $('#category_id').attr('disabled', true);
                                    $('.item-name').attr('disabled', true);
                                    $('#request_desc').attr('required', true);
                                }else{
                                    $('#request-description').hide();
                                    $('#category_id').attr('disabled', false);
                                    $('.item-name').attr('disabled', false);
                                    $('#request_desc').attr('required', false);
                                }
                            });
                        },
                        onSubmit: function(modal) {
                            modal.disableButton();
                            wp.ajax.send( {
                                data: this.serialize(),
                                success: function(res) {
                                    swal({
                                        title: wpErpAsset.popup.request.request,
                                        timer: 2000,
                                        text: wpErpAsset.popup.request.requestConfirmMsg,
                                        type: "success"
                                    });
                                    modal.enableButton();
                                    modal.closeModal();
                                    WeDevs_ERP_Assets.customReload('.erp-hr-employee-assets-request', '.erp-hr-employee-assets-request');
                                },
                                error: function( error ) {
                                    sweetAlert(wpErpAsset.error, error, "error");
                                }
                            });
                        }
                    });
                },

                edit: function(e) {
                    e.preventDefault();

                    var self = $(this);

                    $.erpPopup({
                        title: self.data('title'),
                        button: self.data('button'),
                        id: 'erp-assets-edit-request',
                        extraClass: 'smaller',
                        onReady: function() {

                            var modal = this;

                            $('header', modal).after($('<div class="loader"></div>').show());

                            wp.ajax.send( 'erp-assets-request-get', {
                                data: {
                                    row_id: self.data( 'row_id' ),
                                    _wpnonce: wpErpAsset.nonce
                                },
                                success: function( response ) {

                                    var html = wp.template( 'erp-hr-emp-request-asset' )( response );
                                    $( '.content', modal ).html( html );

                                    $('.not_in_list').on('change', function() {
                                        if(this.checked) {
                                            $('#request-description').show();
                                            $('#category_id').attr('disabled', true);
                                            $('.item-name').attr('disabled', true);
                                            $('#request_desc').attr('required', true);
                                        }else{
                                            $('#request-description').hide();
                                            $('#category_id').attr('disabled', false);
                                            $('.item-name').attr('disabled', false);
                                            $('#request_desc').attr('required', false);
                                        }
                                    });

                                    if ( 'on' == response.not_in_list ) {
                                        $('.not_in_list').prop('checked', true).trigger('change');
                                        $('.request-desc').val(response.request_desc);
                                    }

                                    $('.asset-category').on('change', function() {
                                        var cat_id = $(this).val();

                                        $('.item-name option').not(':first').remove();

                                        if ( '-1' == cat_id ) {
                                            $('.loader', modal).remove();
                                            return;
                                        }

                                        wp.ajax.send({
                                            data: {
                                                cat_id: cat_id,
                                                action: 'erp-hr-get-item-by-category',
                                                _wpnonce: wpErpAsset.nonce
                                            },
                                            success: function(res) {

                                                var itemSelect = $('.item-name');
                                                var optionValues = [];

                                                $.each(res, function(index, item) {
                                                    itemSelect.append($("<option />").val(item.id).text(item.item_group));
                                                });

                                                itemSelect.find('option').each(function() {
                                                    optionValues.push($(this).val());
                                                });
                                            },
                                            error: function(error) {
                                                sweetAlert(wpErpAsset.error, error, "error");
                                            }
                                        });
                                    });

                                    WeDevs_ERP_Assets.initDatePicker();

                                    if ( !response.category_id ) {
                                        response.category_id = '-1';
                                    }

                                    $('.asset-category').val(response.category_id).trigger('change');
                                    $('.allotted_to').val(response.allotted_to);
                                },
                                error: function( error ) {
                                    sweetAlert(wpErpAsset.error, error, "error");
                                }
                            });

                            $('.asset-category').change(function() {
                                var cat_id = $(this).val() || '-1';

                                $('.item-name option').not(':first').remove();
                                $('.item option').not(':first').remove();

                                if ( '-1' == cat_id ) {
                                    return;
                                }

                                wp.ajax.send({
                                    data: {
                                        cat_id: cat_id,
                                        action: 'erp-hr-get-item-by-category',
                                        _wpnonce: wpErpAsset.nonce
                                    },
                                    success: function(res) {

                                        var itemSelect = $('.item-name');

                                        $.each(res, function(index, item) {
                                            itemSelect.append($("<option />").val(item.id).text(item.item_group));
                                        });

                                        itemSelect.find('option').each(function() {
                                            optionValues.push($(this).val());
                                        });

                                        if ( response.item_group ) {
                                            if ( '-1' != $.inArray( response.item_group, optionValues ) ) {
                                                itemSelect.val(response.item_group).trigger('change');
                                            } else {
                                                itemSelect.val('-1').trigger('change');
                                            }
                                        }
                                    },
                                    error: function(error) {
                                        sweetAlert(wpErpAsset.error, error, "error");
                                    }
                                });
                            });

                            $('.item-name').change(function() {
                                var item_id = $(this).val();

                                $('.item option').not(':first').remove();

                                if ( '-1' == item_id ) {
                                    return;
                                }

                                wp.ajax.send({
                                    data: {
                                        item_group_id: item_id,
                                        action: 'erp-hr-get-item-by-group',
                                        _wpnonce: wpErpAsset.nonce
                                    },
                                    success: function(res) {

                                        var itemSelect = $('.item');

                                        $.each(res, function(index, item) {
                                            itemSelect.append($("<option />").val(item.id).text('[' + item.item_code + '] ' + item.model_no));
                                        });

                                        itemSelect.find('option').each(function() {
                                            optionValues.push($(this).val());
                                        });

                                        if ( response.item_id ) {
                                            if ( '-1' != $.inArray( response.item_id, optionValues ) ) {
                                                itemSelect.val(response.item_id).trigger('change');
                                            } else {
                                                itemSelect.val('-1').trigger('change');
                                            }
                                        }
                                    },
                                    errror: function(error) {
                                        alert(error);
                                    }
                                });
                            });
                        },
                        onSubmit: function(modal) {
                            modal.disableButton();
                            wp.ajax.send({
                                data: this.serialize(),
                                success: function (res) {
                                    WeDevs_ERP_Assets.customReload('.erp-hr-employee-assets-request', '.erp-hr-employee-assets-request');
                                    modal.enableButton();
                                    modal.closeModal();

                                },
                                error: function (error) {
                                    modal.enableButton();
                                    sweetAlert(wpErpAsset.error, error, "error");
                                }
                            });
                        }
                    }); //popup
                },

                delete: function(e) {
                    e.preventDefault();

                    var self = $(this);

                    swal({
                            title: wpErpAsset.confirm,
                            text: wpErpAsset.requestDeleteConfirm,
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: wpErpAsset.deleteConfirmBtn,
                            cancelButtonText: wpErpAsset.dismissCancelBtn,
                            closeOnConfirm: false,
                            closeOnCancel: true,
                        },
                        function(isConfirm){
                            if (isConfirm) {
                                wp.ajax.send( 'erp-assets-request-delete', {
                                    data: {
                                        id: self.data('id'),
                                        _wpnonce: wpErpAsset.nonce
                                    },
                                    success: function(res) {
                                        swal({
                                            title: wpErpAsset.deleted,
                                            timer: 2000,
                                            text: wpErpAsset.deleteConfirmMsg,
                                            type: "success"
                                        });
                                        WeDevs_ERP_Assets.customReload('.erp-hr-employee-assets-request', '.erp-hr-employee-assets-request');
                                    },
                                    error: function(error) {
                                        sweetAlert(wpErpAsset.error, error, "error");
                                    }
                                });
                            }
                        }
                    );
                },

                approve: function(e) {
                    e.preventDefault();

                    var self = $(this);

                    $.erpPopup( {
                        title: wpErpAsset.popup.approve.title,
                        button: wpErpAsset.popup.approve.submit,
                        id: 'erp-asset-request-reply',
                        extraClass: 'large',
                        content: wp.template('erp-asset-request-reply')().trim(),
                        onReady: function() {
                            var modal = this;
                            var item_id = self.data('item-id');

                            $('.row-id').val(self.data('id'));
                            $('.item-id').val(self.data('item-id'));

                            $('header', modal).after($('<div class="loader"></div>').show());

                            $('.asset-category').on('change', function() {
                                var cat_id = $(this).val();

                                $('.item-name option').not(':first').remove();
                                $('.item option').not(':first').remove();

                                if ( '-1' == cat_id ) {
                                    return;
                                }

                                wp.ajax.send({
                                    data: {
                                        cat_id: cat_id,
                                        action: 'erp-hr-get-item-by-category',
                                        _wpnonce: wpErpAsset.nonce
                                    },
                                    success: function(res) {

                                        var itemSelect = $('.item-name');
                                        var optionValues = [];

                                        $.each(res, function(index, item) {
                                            itemSelect.append($("<option />").val(item.id).text(item.item_group));
                                        });

                                        itemSelect.find('option').each(function() {
                                            optionValues.push($(this).val());
                                        });

                                        if ( self.data('item-name') ) {
                                            if ( '-1' != self.data('item-name') && $.inArray( self.data('item-name'), optionValues ) ) {
                                                itemSelect.val(self.data('item-name')).trigger('change');
                                            } else {
                                                itemSelect.val('-1').trigger('change');
                                            }
                                        }
                                    },
                                    error: function(error) {
                                        sweetAlert(wpErpAsset.error, error, "error");
                                    }
                                });
                            });

                            $('.item-name').on('change', function() {
                                var item_id = $(this).val();

                                $('.item option').not(':first').remove();

                                if ( '-1' == item_id ) {
                                    return;
                                }

                                wp.ajax.send({
                                    data: {
                                        item_group_id: item_id,
                                        action: 'erp-hr-allottment-get-item-by-group',
                                        _wpnonce: wpErpAsset.nonce
                                    },
                                    success: function(res) {

                                        var itemSelect = $('.item');
                                        var optionValues = [];

                                        $.each(res, function(index, item) {
                                            itemSelect.append($("<option />").val(item.id).text('[' + item.item_code + '] ' + item.model_no));
                                        });

                                        itemSelect.find('option').each(function() {
                                            optionValues.push($(this).val());
                                        });

                                        $('.loader', modal).remove();
                                    },
                                    errror: function(error) {
                                        alert(error);
                                    }
                                });
                            });

                            $('.is_returnable').change(function() {
                                if(this.checked) {
                                    $('#return-date').show();
                                    $('#return_date').prop('required', true);
                                }else{
                                    $('#return-date').hide();
                                    $('#return_date').prop('required', false);
                                }
                            });

                            if (self.data('category')) {
                                $('.asset-category').val(self.data('category')).trigger('change');
                            } else {
                                $('.loader', modal).remove();
                            }

                            $('.is_returnable').attr('checked', true).trigger('change');
                            WeDevs_ERP_Assets.initDatePicker();
                        },
                        onSubmit: function( modal ) {
                            modal.disableButton();
                            wp.ajax.send( {
                                data: this.serialize(),
                                success: function( response ) {
                                    swal({
                                        title: wpErpAsset.popup.approved.approved,
                                        timer: 2000,
                                        text: wpErpAsset.popup.approved.approvedMsg,
                                        type: "success"
                                    });
                                    WeDevs_ERP_Assets.customReload('.erp-asset-requests', '.erp-asset-requests');
                                    modal.enableButton();
                                    modal.closeModal();
                                },
                                error: function( error ) {
                                    modal.enableButton();
                                    sweetAlert(wpErpAsset.error, error, "error");
                                }
                            });
                        }
                    });
                },

                reject: function(e) {
                    e.preventDefault();

                    var self = $(this);

                    $.erpPopup( {
                        title: wpErpAsset.popup.reject.title,
                        button: wpErpAsset.popup.reject.submit,
                        id: 'erp-asset-request-reject',
                        extraClass: 'smaller',
                        content: wp.template('erp-asset-request-reject')( { 'row_id' : self.data('id') } ).trim(),
                        onReady: function() {
                            var modal = this;

                        },
                        onSubmit: function(modal) {
                            modal.disableButton();
                            wp.ajax.send( {
                                data: this.serialize(),
                                success: function( response ) {
                                    swal({
                                        title: wpErpAsset.popup.rejected.rejected,
                                        timer: 2000,
                                        text: wpErpAsset.popup.rejected.rejectedMsg,
                                        type: "success"
                                    });
                                    modal.enableButton();
                                    modal.closeModal();
                                    WeDevs_ERP_Assets.customReload('.erp-asset-requests', '.erp-asset-requests');
                                },
                                error: function( error ) {
                                    modal.enableButton();
                                    sweetAlert(wpErpAsset.error, error, "error");
                                }
                            });
                        }
                    });
                },

                undo: function(e) {
                    e.preventDefault();

                    var self = $(this);

                    swal({
                            title: wpErpAsset.confirm,
                            text: wpErpAsset.popup.undo.message,
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: wpErpAsset.popup.undo.confirmBtn,
                            cancelButtonText: wpErpAsset.dismissCancelBtn,
                            closeOnConfirm: false,
                            closeOnCancel: true,
                        },
                        function(isConfirm){
                            if (isConfirm) {
                                wp.ajax.send( 'erp-assets-request-undo', {
                                    data: {
                                        id: self.data('id'),
                                        _wpnonce: wpErpAsset.nonce
                                    },
                                    success: function(res) {
                                        swal({
                                            title: wpErpAsset.restored,
                                            timer: 2000,
                                            text: wpErpAsset.restoreConfirmMsg,
                                            type: "success"
                                        });
                                        WeDevs_ERP_Assets.customReload('.erp-asset-requests', '.erp-asset-requests');
                                    },
                                    error: function(error) {
                                        sweetAlert(wpErpAsset.error, error, "error");
                                    }
                                });
                            }
                        }
                    );
                },

                disapprove: function(e) {
                    e.preventDefault();

                    var self = $(this);

                    swal({
                            title: wpErpAsset.confirm,
                            text: wpErpAsset.popup.undo.message,
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: wpErpAsset.popup.disapprove.confirmBtn,
                            cancelButtonText: wpErpAsset.dismissCancelBtn,
                            closeOnConfirm: false,
                            closeOnCancel: true,
                        },
                        function(isConfirm){
                            if (isConfirm) {
                                wp.ajax.send( 'erp-assets-request-disapprove', {
                                    data: {
                                        id: self.data('id'),
                                        _wpnonce: wpErpAsset.nonce
                                    },
                                    success: function(res) {
                                        swal(wpErpAsset.restored, wpErpAsset.restoreConfirmMsg, "success");
                                        WeDevs_ERP_Assets.customReload('.erp-asset-requests', '.erp-asset-requests');
                                    },
                                    error: function(error) {
                                        sweetAlert(wpErpAsset.error, error, "error");
                                    }
                                });
                            }
                        }
                    );
                }
            },

            cascadeAssetList: function() {
                $('.asset-category').change(function() {
                    var cat_id = $(this).val();

                    $('.item-name option').not(':first').remove();
                    $('.item option').not(':first').remove();

                    if ( '-1' == cat_id ) {
                        return;
                    }

                    wp.ajax.send({
                        data: {
                            cat_id: cat_id,
                            action: 'erp-hr-get-item-by-category',
                            _wpnonce: wpErpAsset.nonce
                        },
                        success: function(res) {

                            var itemSelect = $('.item-name');

                            $.each(res, function(index, item) {
                                itemSelect.append($("<option />").val(item.id).text(item.item_group));
                            });
                        },
                        error: function(error) {
                            sweetAlert(wpErpAsset.error, error, "error");
                        }
                    });
                });

                $('.item-name').change(function() {
                    var item_id = $(this).val();

                    $('.item option').not(':first').remove();

                    if ( '-1' == item_id ) {
                        return;
                    }

                    wp.ajax.send({
                        data: {
                            item_group_id: item_id,
                            action: 'erp-hr-get-item-by-group',
                            _wpnonce: wpErpAsset.nonce
                        },
                        success: function(res) {

                            var itemSelect = $('.item');

                            $.each(res, function(index, item) {
                                itemSelect.append($("<option />").val(item.id).text('[' + item.item_code + '] ' + item.model_no));
                            });
                        },
                        errror: function(error) {
                            alert(error);
                        }
                    });
                });

                $('.is_returnable').change(function() {
                    if(this.checked) {
                        $('#return-date').show();
                        $('#return_date').prop('required', true);
                    }else{
                        $('#return-date').hide();
                        $('#return_date').prop('required', false);
                    }
                });
                WeDevs_ERP_Assets.initDatePicker();
            }
        }

        $(function() {
            WeDevs_ERP_Assets.initialize();
        });
    });
})(jQuery);