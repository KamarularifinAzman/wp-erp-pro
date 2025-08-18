;( function( $ ) {

    'use strict';

    var listParams = {
        tab       : 'own',
        status    : '',
        contact   : 0,
        user      : 0,
        date      : [],
        page      : 1,
        number    : 10,
        q         : '',
        _wpnonce  : erpTasks.nonce,
        action    : 'erp_crm_get_tasks'
    };

    var currentTask;
    var i18n = erpTasks.i18n;

    listTasks( listParams );

    $( '#own-tab' ).click( function() {

        $( this ).addClass( 'nav-tab-active' );
        $( '#all-tab' ).removeClass( 'nav-tab-active' );

        $( '#filter-user' ).removeAttr( 'class' );
        $( '#filter-user' ).next( 'span' ).hide();
        $( '#filter-user' ).hide();

        listParams.tab = 'own';

        resetFilters();
        listTasks( listParams );
    });

    $( '#all-tab' ).click( function() {

        $( this ).addClass( 'nav-tab-active' );
        $( '#own-tab' ).removeClass( 'nav-tab-active' );

        listParams.tab = 'all';

        renderUserFilter();
        resetFilters();
        listTasks( listParams );
    });

    $( '#filter-status' ).select2({

        allowClear: true,
        placeholder: i18n.filterStatus,
        data: [
            {
                id: '',
                text: '',
            },
            {
                id: 'done',
                text: i18n.done,
            },
            {
                id: 'pending',
                text: i18n.pending,
            },
            {
                id: 'due',
                text: i18n.due,
            },
        ]
    });

    $( '#filter-contact' ).select2({

        allowClear        : true,
        placeholder       : i18n.filterContact,
        minimumInputLength: 1,
        width             : '190px',
        ajax              : {
            url     : wpErpCrm.ajaxurl,
            dataType: 'json',
            delay   : 250,

            escapeMarkup: function( m ) {
                return m;
            },

            data: function ( params ) {
                return {
                    q       : params.term,
                    types   : $( this ).data( 'types' ).split(','),
                    _wpnonce: erpTasks.nonce,
                    action  : 'erp_crm_search_contact'
                };
            },

            processResults: function ( data, params ) {
                var terms = [];

                if ( data) {
                    $.each( data.data, function( id, text ) {
                        terms.push({
                            id  : id,
                            text: text
                        });
                    });
                }

                if ( terms.length ) {
                    return { results: terms };
                } else {
                    return { results: '' };
                }
            },

            cache: true
        }
    });

    $( 'input[name="filter_date"]' ).daterangepicker({

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

    $( 'input[name="filter_date"]' ).on( 'apply.daterangepicker', function( event, picker ) {

        $( this ).val( picker.startDate.format( 'MMM DD, YYYY' ) + ' - ' + picker.endDate.format( 'MMM DD, YYYY' ) );

        listParams.date = {
            start: picker.startDate.format( 'DD.MM.YYYY' ),
            end  : picker.endDate.format( 'DD.MM.YYYY' )
        }

        listParams.page = 1;
        listTasks( listParams );
    });

    $( 'input[name="filter_date"]' ).on( 'cancel.daterangepicker', function( event, picker ) {

        $( this ).val('');
        listParams.date = [];
        listTasks( listParams );
    });

    $( '#filter-status, #filter-contact, #filter-user' ).change( function() {

        listParams.q          = '';
        listParams.page       = 1;
        listParams.date       = [];
        listParams.user       = $( '#filter-user option:selected' ).val();
        listParams.status     = $( '#filter-status option:selected' ).val();
        listParams.contact    = $( '#filter-contact option:selected' ).val();

        $( '#search-task' ).val('');
        $( 'input[name="filter_date"]' ).val('');

        listTasks( listParams );
    });

    $( '#search-task' ).on( 'search', function() {

        listParams.q      = $( this ).val();
        listParams.page   = 1;
        listTasks( listParams );
    });

    $( '#crm-task-list' ).on( 'change', '.check-column', toggleActionBtn );

    $( '#erp-task-table-wrap' ).on( 'change', '.cb-all', toggleActionBtn );

    $( '#erp-task-table-wrap' ).on( 'click', '#action-btn', function( e ) {

        e.preventDefault();
        var taskId = [];

        $.each( $( "input[name='task_id[]']:checked" ), function() {
            taskId.push( $( this ).val() );
        });

        updateStatus( taskId, listParams.status );
    });

    $( '#status-btn' ).click( function() {

        var taskId     = [currentTask];
        var taskStatus = $( '#status-btn i' ).data( 'status' );
        updateStatus( taskId, taskStatus );
    });

    $( '#crm-task-prev' ).click( function() {

        if ( listParams.page > 1 ) {
            listParams.page -= 1;
            listTasks( listParams );
        }
    });

    $( '#crm-task-next' ).click( function() {

        if ( listParams.page < parseInt( $( '.erp-crm-task .total-pages' ).html() ) ) {
            listParams.page += 1;
            listTasks( listParams );
        }
    });

    $( '.erp-crm-task #current-page-selector' ).keypress( function( e ) {

        var keycode = e.keyCode ? e.keyCode : e.which;

        if ( keycode == '13' ) {
            var page = parseInt( $( this ).val() );

            if ( page >= 1 && page <= parseInt( $( '.erp-crm-task .total-pages' ).html() ) ) {
                listParams.page = page;
                listTasks( listParams );
            }
        }
    });

    $( '#crm-task-list' ).on( 'click', '.task', function( e ) {

        e.preventDefault();

        currentTask = $( this ).data( 'id' );
        var done    = $( '#done-at-' + currentTask ).val();

        $( '.erp-crm-task-single' ).show();
        $( '.erp-modal-backdrop' ).show();

        listParams.tab === 'all' ? $( '#status-btn' ).hide() : $( '#status-btn' ).show();
        done === 'null' ? toggleStatusBtn( false ) : toggleStatusBtn( true );

        $( '#status-btn' ).attr( 'data-id', currentTask );
        $( '#task-des' ).html( $( '#task-' + currentTask ).val() );
        $( '#task-title' ).html( $( '#title-' + currentTask ).html() );
        $( '#task-due-at' ).html( $( '#due-at-' + currentTask ).val() );
        $( '#task-created-at' ).html( $( '#created-at-' + currentTask ).val() );
        $( '#task-created-by' ).html( $( '#assigned-by-' + currentTask ).html() );
    });

    $( '#close-task-modal' ).click( function(e) {

        e.preventDefault();

        $( '.erp-crm-task-single' ).hide();
        $( '.erp-modal-backdrop' ).hide();
    });

    function updateStatus( taskIds, status ) {

        $.ajax({
            type    : "POST",
            url     : erpTasks.ajaxurl,
            dataType: 'json',
            data    : {
                task_ids: taskIds,
                status  : status,
                _wpnonce: erpTasks.nonce,
                action  : 'erp_crm_update_task_status'
            },
        })
        .fail( function( xhr ) {

            listLoading( false );
            showFailMsg( xhr );
        })
        .done( function( response ) {

            if ( response.success ) {
                showAlertMsg( 'success', response.data.message );

                toggleStatusBtn( response.data.done );

                listTasks( listParams );

            } else {
                showAlertMsg( 'error', response.data );
            }
        });
    }

    function renderUserFilter() {

        $( '#filter-user' ).select2({
            allowClear        : true,
            placeholder       : i18n.filterUser,
            width             : '190px',
            minimumInputLength: 1,
            ajax: {
                url     : wpErpCrm.ajaxurl,
                dataType: 'json',
                delay   : 250,

                escapeMarkup: function( m ) {
                    return m;
                },

                data: function ( params ) {
                    return {
                        q       : params.term,
                        _wpnonce: erpTasks.nonce,
                        action  : 'erp_crm_search_user'
                    };
                },

                processResults: function ( data, params ) {
                    var terms = [];

                    if ( data) {
                        $.each( data.data, function( id, text ) {
                            terms.push({
                                id  : id,
                                text: text
                            });
                        });
                    }

                    if ( terms.length ) {
                        return { results: terms };
                    } else {
                        return { results: '' };
                    }
                },

                cache: true
            }
        });
    }

    function listLoading( loading ) {

        $( '#erp-task-table-wrap tbody' ).append( '<span class="erp-loader"></span>' );

        loading ? $( '#erp-task-table-wrap' ).css( 'opacity', '0.3' ) : $( '#erp-task-table-wrap' ).css( 'opacity', '1' );
    }

    function resetFilters() {

        listParams.page       = 1;
        listParams.q          = '';
        listParams.date       = [];
        listParams.status     = '';
        listParams.user       = 0;
        listParams.contact    = 0;

        $( '#search-task' ).val('');
        $( '#filter-date' ).val('');

        $( '#select2-filter-user-container' ).html(
            '<span class="select2-selection__placeholder">'
            + i18n.filterUser
            + '</span>'
        );

        $( '#select2-filter-status-container' ).html(
            '<span class="select2-selection__placeholder">'
            + i18n.filterStatus
            + '</span>'
        );

        $( '#select2-filter-contact-container' ).html(
            '<span class="select2-selection__placeholder">'
            + i18n.filterContact
            + '</span>'
        );
    }

    function toggleActionBtn() {

        if ( listParams.status !== 'done' && $( 'input[name="task_id[]"]:checked' ).length ) {
            $( '#action-btn' ).show();
            $( '#action-btn' ).html( i18n.markComplete );

        } else if ( listParams.status === 'done' && $( 'input[name="task_id[]"]:checked' ).length ) {
            $( '#action-btn' ).show();
            $( '#action-btn' ).html( i18n.markIncomplete );

        } else {
            $( '#action-btn' ).hide();
        }
    }

    function toggleStatusBtn( complete ) {

        if ( complete ) {
            $( '#status-btn' ).html(
                '<i class="dashicons dashicons-yes" data-status="done"></i> '
                + i18n.markIncomplete
            );

            $( '#status-btn' ).attr( 'data-status', 'done' );

        } else {
            $( '#status-btn' ).html(
                '<i class="dashicons dashicons-yes-alt" data-status="undone"></i> '
                + i18n.markComplete
            );

            $( '#status-btn' ).attr( 'data-status', 'undone' );
        }
    }

    function listTasks( data ) {

        $.ajax({
            type    : "GET",
            url     : erpTasks.ajaxurl,
            dataType: 'json',
            data    : data,
            beforeSend: function() {
                listLoading( true );
            }
        })
        .fail( function( xhr ) {

            listLoading( false );
            showFailMsg( xhr );
        })
        .done( function( response ) {

            listLoading( false );

            if ( response.success ) {
                renderTasks( response.data );
            } else {
                showAlertMsg( 'error', response.data );
            }
        });
    }

    function renderTasks( data ) {

        var list      = '';
        var tasks     = data.activity;
        var totalPage = data.total_page;
        var item      = tasks ? tasks.length : 0;
        var page      = tasks ? listParams.page : 0;
        var numHeader = ( listParams.tab === 'own' ) ? 4 : 5;

        item += ( item > 1 ) ? ' tasks' : ' task';

        $( '#erp-task-table-wrap thead' ).html( '<tr>' + getTableHeaders( 'head' ) + '</tr>' );
        $( '#erp-task-table-wrap tfoot' ).html( '<tr>' + getTableHeaders( 'foot' ) + '</tr>' );

        $( '.erp-crm-task .current-page' ).val( page );
        $( '.erp-crm-task .displaying-num' ).html( item );
        $( '.erp-crm-task .total-pages' ).html( totalPage );
        $( '.erp-crm-task .current-page' ).attr( 'disabled', false );

        $( '#crm-task-prev' ).attr( 'disabled', false );
        $( '#crm-task-next' ).attr( 'disabled', false );

        if ( page <= 1 ) {
            $( '#crm-task-prev' ).attr( 'disabled', true );
        } else if ( page === totalPage ) {
            $( '#crm-task-next' ).attr( 'disabled', true );
        }

        if ( totalPage <= 1 ) {
            $( '#crm-task-prev' ).attr( 'disabled', true );
            $( '#crm-task-next' ).attr( 'disabled', true );
            $( '.erp-crm-task .current-page' ).attr( 'disabled', true );
        }

        if ( ! tasks ) {

            $( '#crm-task-list' ).html(
                '<tr class="no-items"><td class="colspanchange" colspan="' + numHeader + '">'
                + i18n.noTask
                + '</td></tr>'
            );

            return;
        }

        tasks.forEach( function( task ) {

            var checked  = '';
            var disabled = '';

            if ( listParams.tab === 'own' && task.done_at && listParams.status !== 'done' ){
                checked  = 'checked';
                disabled = 'disabled';
            }

            var rowData = [
                '<th scope="row" class="check-column"><input class="task-done-cb" type="checkbox" name="task_id[]" value="' + task.task.id + '" ' + checked + ' ' + disabled + '/></th>',
                '<td class="task" data-id="' + task.task.id + '"><a href="" id="title-' + task.task.id + '">' + task.task.title + '</a></td>',
                '<td id="contact-' + task.task.id + '">' + task.contact + '</td>',
                '<td id="assigned-to-' + task.task.id + '">' + task.assigned_to.name + '</td>',
                '<td id="assigned-by-' + task.task.id + '">' + task.assigned_by.name + '</td>',
                '<td class="cen-align" id="status-' + task.task.id + '">' + task.status + '</td>',
                '<input type="hidden" id="created-at-' + task.task.id + '" value="' + task.created_at + '"/>' +
                '<input type="hidden" id="due-at-' + task.task.id + '" value="' + task.due_at + '"/>' +
                '<input type="hidden" id="done-at-' + task.task.id + '" value="' + task.done_at + '"/>' +
                '<input type="hidden" id="task-' + task.task.id + '" value="' + task.task.desc + '"/>'
            ];

            if ( listParams.tab === 'own' ) {
                list += '<tr>' + rowData[0] + rowData[1] + rowData[2] + rowData[4] + rowData[6] + '</tr>';
            } else {
                list += '<tr>' + rowData[1] + rowData[2] + rowData[3] + rowData[4] + rowData[5] + rowData[6] + '</tr>';
            }
        });

        $( '#crm-task-list' ).html( list );
    }

    function getTableHeaders( position ) {

        var cols = '';
        var cb   = 1;
        var btn  = '<button type="submit" id="action-btn" class="add-new-h2"></button>';

        if ( position === 'foot' ) {
            cb  = 2;
            btn = '';
        }

        var headers = [
            '<td id="cb" class="manage-column column-cb check-column"><input type="checkbox" class="cb-all" id="cb-select-all-' + cb + '"></td>',
            '<th>' + i18n.task + btn + '</th>',
            '<th id="th-contact">' + i18n.contact + '</th>',
            '<th id="th-asgnto">' + i18n.assignedTo + '</th>',
            '<th id="th-asgnby">' + i18n.assignedBy + '</th>',
            '<th id="th-status">' + i18n.status + '</th>'
        ];

        if ( listParams.tab === 'own' ) {
            cols += headers[0] + headers[1] + headers[2] + headers[4];
        } else {
            cols += headers[1] + headers[2] + headers[3] + headers[4] + headers[5];
        }

        return cols;
    }

    function showAlertMsg( type, message, title ) {

        if ( ! title ) {
            title = type[0].toUpperCase() + type.slice(1) + '!';
        }

        swal({
            title             : __( title, 'erp-pro' ),
            text              : message,
            type              : type,
            timer             : 2200,
            showConfirmButton : false,
        });
    }

    function showFailMsg( xhr ) {
        swal(
            'Failed!',
            'Request Status: '
            + xhr.status
            + ' Status Text: '
            + xhr.statusText
            + ' '
            + xhr.responseText,
            'error'
        );
    }

})( jQuery );
