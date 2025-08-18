Vue.directive( 'barrating', function ( value ) {
        var select = jQuery( this.el );
        select.barrating( {
            theme: 'fontawesome-stars',
            readonly: true,
            initialRating: value
        } );
    }
);

if ( jQuery('#rating_status_form_wrapper' ).length > 0 ) {

    var ratingviewmodel = new Vue( {
        el: '#rating_status_form_wrapper',

        data: {
            ratingData: [],
            avgRating: 0,
            success_notice_class: 'success_notice',
            error_notice_class: 'error_notice',
            isError: false,
            isVisible: false,
            response_message: '',
            showSwitch: true
        },

        ready: function () {
            this.getApplicationRating();
            this.getApplicationAvgRating();
            jQuery( '#message' ).hide();
        },

        methods: {
            getApplicationRating: function () {
                jQuery( '#section-rating .spinner' ).show();
                jQuery( '#section-rating .spinner' ).css( {'visibility': 'visible'} );
                jQuery.get( ajaxurl,
                    {action: 'wp-erp-rec-get-applicationRating', application_id: jQuery( '#application_id' ).val()},
                    function ( response ) {
                        if ( response.success === true ) {
                            ratingviewmodel.$set( 'ratingData', response.data );
                            ratingviewmodel.$set( 'showSwitch', response.data.length === 0 ? false : true );
                            jQuery( '#section-rating .spinner' ).hide();
                        }
                    }
                );
            },

            getApplicationAvgRating: function () {
                jQuery.get( ajaxurl,
                    {action: 'wp-erp-rec-get-applicationAvgRating', application_id: jQuery( '#application_id' ).val()},
                    function ( response ) {
                        if ( response.success === true ) {
                            ratingviewmodel.$set( 'avgRating', response.data );
                        }
                    }
                );
            },

            ratingSubmit: function () {
                jQuery( '#section-rating .spinner' ).css( {'visibility': 'visible'} );
                var managerRatingFormData = jQuery( '#rating_status_form' ).serialize();
                jQuery.ajax( {
                    url: ajaxurl,
                    type: 'POST',
                    data: managerRatingFormData,
                    dataType: 'json',
                    success: function ( response ) {
                        if ( response.success === true ) {
                            ratingviewmodel.getApplicationRating();
                            ratingviewmodel.getApplicationAvgRating();
                            alertify.success( response.data );
                        }
                        else {
                            alertify.error( response.data );
                            jQuery( '#section-rating .spinner' ).css( {'visibility': 'hidden'} );
                        }
                    },
                    error: function ( response ) {
                        alertify.error( response.data );
                        jQuery( '#section-rating .spinner' ).css( {'visibility': 'hidden'} );
                    }
                } );
            }
        }
    } );
}

if ( jQuery('#status_form_wrapper' ).length > 0 ) {

    var statusViewModel = new Vue( {
        el: "#status_form_wrapper",

        data: {
            success_notice_class: 'success_notice',
            error_notice_class: 'error_notice',
            isError: false,
            isVisible: false,
            response_message: ''
        },

        ready: function () {
            jQuery( '#message' ).hide();
        },

        methods: {
            statusSubmit: function () {
                var managerStatusFormData = jQuery( '#status_form' ).serialize();

                jQuery.ajax( {
                    url: ajaxurl,
                    type: 'POST',
                    data: managerStatusFormData,
                    dataType: 'json',
                    success: function ( response ) {
                        if ( response.success === true ) {
                            jQuery( '#message' ).show();
                            jQuery( '#message' ).addClass( 'notice-success' );
                            jQuery( '#message' ).removeClass( 'notice-error' );
                            jQuery( '#message #message_text' ).text( response.data );
                        }
                        else {
                            jQuery( '#message' ).show();
                            jQuery( '#message' ).addClass( 'notice-error' );
                            jQuery( '#message' ).removeClass( 'notice-success' );
                            jQuery( '#message #message_text' ).text( response.data );
                        }
                    },
                    error: function ( response ) {
                        jQuery( '#message' ).show();
                        jQuery( '#message' ).addClass( 'notice-error' );
                        jQuery( '#message' ).removeClass( 'notice-success' );
                        jQuery( '#message #message_text' ).text( 'Request error' );
                    }
                } );
            }
        }
    } );
}

if ( jQuery('#comment_form_wrapper' ).length > 0 ) {

    var commentviewmodel = new Vue( {
        el: "#comment_form_wrapper",

        data: {
            comments: [],
            manager_comment: '',
            success_notice_class: 'success_notice',
            error_notice_class: 'error_notice',
            isError: false,
            isVisible: false,
            response_message: ''
        },

        ready: function () {
            this.getAllComments();
        },

        methods: {
            postManagerComment: function () {
                var managerCommentFormData = jQuery( '#applicant-comment-form' ).serialize();
                if ( commentviewmodel.manager_comment == '' ) {
                    commentviewmodel.$set( 'isVisible', true );
                    commentviewmodel.$set( 'isError', true );
                    commentviewmodel.$set( 'response_message', 'Comment is empty!' );
                } else {
                    jQuery.post( ajaxurl, managerCommentFormData, function ( response ) {
                            commentviewmodel.comments.push( {
                                display_name: response.data.display_name,
                                comment_date: response.data.comment_date,
                                comment: response.data.comment,
                                user_pic: response.data.user_pic
                            } );

                            commentviewmodel.manager_comment = '';
                            commentviewmodel.$set( 'isVisible', false );
                        }
                    );
                }
            },
            getAllComments: function () {
                jQuery( '#section-comment .spinner' ).show();
                jQuery( '#section-comment .spinner' ).css( {'visibility': 'visible'} );
                jQuery.get( ajaxurl,
                    {action: 'wp-erp-rec-get-comments', application_id: jQuery( '#application_id' ).val()},
                    function ( response ) {
                        if ( response.success === true ) {
                            jQuery.each( response.data, function ( k, v ) {
                                commentviewmodel.comments.push( {
                                    display_name: v.display_name,
                                    comment_date: v.comment_date,
                                    comment: v.comment,
                                    user_pic: v.user_pic
                                } );
                            } );
                            jQuery( '#section-comment .spinner' ).hide();
                        }
                    }
                );
            }
        }
    } );
}

if ( jQuery('#exam_detail' ).length > 0 ) {

    var exam_detail = new Vue( {
        el: '#exam_detail',

        data: {
            exam_data: []
        },

        ready: function () {
            this.getExamDetail();
        },

        methods: {
            getExamDetail: function () {
                jQuery( '#section-exam-detail .spinner' ).show();
                jQuery( '#section-exam-detail .spinner' ).css( {'visibility': 'visible'} );
                jQuery.get( ajaxurl,
                    {action: 'wp-erp-rec-get-examDetail', application_id: jQuery( '#application_id' ).val()},
                    function ( response ) {
                        if ( response.success === true ) {
                            jQuery.each( response.data, function ( k, v ) {
                                exam_detail.exam_data.push( {
                                    question: k,
                                    answer: v
                                } );
                            } );
                        }
                        jQuery( '#section-exam-detail .spinner' ).hide();
                    }
                );
            }
        }
    } );
}

if ( jQuery('#send_email_wrapper' ).length > 0 ) {

    var send_email = new Vue( {
        el: '#send_email_wrapper',

        data: {
            success_notice_class: 'success_notice',
            error_notice_class  : 'error_notice',
            isError             : false,
            isVisible           : false,
            response_message    : '',
            template_id         : '',
            subject             : ''
        },

        ready: function () {
            jQuery( '#message' ).hide();
        },

        methods: {
            sendEmail: function () {
                tinyMCE.triggerSave();
                if ( send_email.subject == '' ) {

                    jQuery( '#message' ).show();
                    jQuery( '#message' ).addClass( 'notice-error' );
                    jQuery( '#message' ).removeClass( 'notice-success' );
                    jQuery( '#message #message_text' ).text( 'Subject is empty!' );
                }
                else {
                    var emailFormData = jQuery( '#send_email_to_jobseeker' ).serialize();

                    jQuery.ajax( {
                        url     : ajaxurl,
                        type    : 'POST',
                        data    : emailFormData,
                        dataType: 'json',
                        success : function ( response ) {
                            if ( response.success === true ) {
                                jQuery( '#message' ).show();
                                jQuery( '#message' ).addClass( 'notice-success' );
                                jQuery( '#message' ).removeClass( 'notice-error' );
                                jQuery( '#message #message_text' ).text( response.data );
                            }
                            else {
                                jQuery( '#message' ).show();
                                jQuery( '#message' ).addClass( 'notice-error' );
                                jQuery( '#message' ).removeClass( 'notice-success' );
                                jQuery( '#message #message_text' ).text( response.data );
                            }
                        },
                        error: function ( response ) {
                            jQuery( '#message' ).show();
                            jQuery( '#message' ).addClass( 'notice-error' );
                            jQuery( '#message' ).removeClass( 'notice-success' );
                            jQuery( '#message #message_text' ).text( 'Request error' );
                        }
                    } );
                }
            },

            /**
             * After select email templates, set data to fields
             */
            insertSaveRepliesRecruitment: function() {
                var self = this;

                if (! self.template_id ) {
                    return;
                }

                var data = {
                    action      : 'erp-crm-load-save-replies-data',
                    template_id : this.template_id,
                    contact_id  : 0,
                    _wpnonce    : wpErp.nonce
                };

                jQuery.post( wpErp.ajaxurl, data, function( resp ) {
                    if ( resp.success ) {
                        self.subject = resp.data.subject;
                        tinyMCE.activeEditor.setContent(' ' + resp.data.template + ' ');
                    }
                });
            }
        }
    } );
}

if ( jQuery('#section-interview' ).length > 0 ) {

    var interviewModel = new Vue( {
        el: '#section-interview',

        data: {
            interviewData: [],
            success_notice_class: 'success_notice',
            error_notice_class: 'error_notice',
            isError: false,
            isVisible: false,
            response_message: '',
            hasInterview: true
        },

        ready: function () {
            this.getInterview();
        },

        methods: {
            getInterview: function () {
                jQuery( '#section-interview .spinner' ).css( {'visibility': 'visible'} );
                jQuery.get( ajaxurl,
                    {action: 'erp-rec-get-interview', application_id: jQuery( '#application_id' ).val()},
                    function ( response ) {
                        if ( response.success === true ) {
                            var serverInterviewData = response.data;
                            interviewModel.$set( 'interviewData', response.data );
                            if ( serverInterviewData.length > 0 ) {
                                interviewModel.$set( 'hasInterview', false);
                            }
                            jQuery( '#section-interview .spinner' ).hide();
                        }
                    }
                );
            },

            deleteInterview: function ( invID ) {
                if ( confirm( wpErpRec.interview_popup.del_confirm ) ) {
                    jQuery( '#section-interview .spinner' ).css( {'visibility': 'visible'} );
                    jQuery.post( ajaxurl,
                        {action: 'erp-rec-del-interview', interview_id: invID, _wpnonce: wpErpRec.nonce},
                        function ( response ) {
                            if ( response.success === true ) {
                                interviewModel.getInterview();
                                jQuery( '#section-interview .spinner' ).hide();
                                alertify.success( response.data );
                            } else {
                                alertify.error( response.data );
                            }
                        }
                    );
                }
            },

            editInterview: function ( invID ) {
                jQuery.erpPopup( {
                    title: wpErpRec.interview_popup.update_title,
                    button: wpErpRec.interview_popup.update,
                    id: 'new-interview-top',
                    content: wp.template( 'erp-rec-interview-template' )().trim(),
                    extraClass: 'medium',
                    onReady: function ( modal ) {
                        modal.enableButton();

                        var selected_interviewers_id = jQuery( '#interviewers-id-' + invID ).val();
                    
                        if (selected_interviewers_id) {
                            jQuery( '#interviewers' ).val(JSON.parse(selected_interviewers_id)).trigger('change');
                        }

                        jQuery( '#interviewers' ).select2();
                        // select interview type
                        var interviewTypeTitle = jQuery( '#interview-type-title-' + invID ).text();
                        jQuery( '#type_of_interview option' ).each( function () {
                            if ( jQuery( this ).text() == interviewTypeTitle ) {
                                this.selected = (this.text == interviewTypeTitle);
                            }
                        } );
                        jQuery( '#type_of_interview' ).change( function () {
                            var selected_interview_name = jQuery( this ).find( "option:selected" ).text();
                            jQuery( '#type_of_interview_text' ).val( selected_interview_name );
                        } );
                        // end of interview type selection
                        // set interview detail text
                        var interviewDetailText = jQuery( '#interview-detail-text-' + invID ).text();
                        jQuery( '#interview_detail' ).val( interviewDetailText );
                        // end of interview detail text set
                        // select interview type
                        var interviewDuration = jQuery( '#interview-duration-min-' + invID ).val();
                        jQuery( "#duration" ).val( interviewDuration );
                        // end of set duration
                        // set interview date
                        var interviewDate = jQuery( '#interview-date-' + invID ).val();
                        jQuery( "#interview_date" ).val( interviewDate );
                        // end of set interview date
                        // set interview date
                        var interviewTime = jQuery( '#interview-time-' + invID ).val();
                        jQuery( "#interview_time" ).val( interviewTime );
                        // end of set interview date
                        // set interview time
                        var application_id = interviewModel.getApplicationId();
                        jQuery( '#interview_application_id' ).val( application_id );
                        // end of set interview time
                        jQuery( '.erp-time-field' ).timepicker();
                        jQuery( '.erp-date-field' ).datepicker( {
                            dateFormat: 'yy-mm-dd',
                            changeMonth: true,
                            changeYear: true,
                            yearRange: '-100:+0'
                        } );
                    },
                    onSubmit: function ( modal ) {
                        modal.disableButton();
                        wp.ajax.send( 'erp-rec-update-interview', {
                            data: {
                                interview_id: invID,
                                fdata: this.serialize(),
                                _wpnonce: wpErpRec.nonce
                            },
                            success: function ( response ) {
                                alertify.success( response );
                                modal.closeModal();
                                interviewModel.getInterview();
                            },
                            error: function ( error ) {
                                modal.enableButton();
                                alert( error );
                            }
                        } );
                    }
                } );
            },

            getApplicationId: function () {
                var name = 'application_id';
                name = name.replace( /[\[]/, "\\[" ).replace( /[\]]/, "\\]" );
                var regex = new RegExp( "[\\?&]" + name + "=([^&#]*)" ),
                    results = regex.exec( location.search );
                return results === null ? "" : decodeURIComponent( results[ 1 ].replace( /\+/g, " " ) );
            }
        }
    } );
}

if ( jQuery('#section-todo').length > 0 ) {

    var todoModel = new Vue( {
        el: '#section-todo',

        data: {
            todoData: [],
            success_notice_class: 'success_notice',
            error_notice_class: 'error_notice',
            isError: false,
            isVisible: false,
            response_message: '',
            hasTodo: true
        },

        ready: function () {
            this.getTodo();
        },

        methods: {
            getTodo: function () {
                jQuery( '#section-todo .spinner' ).css( {'visibility': 'visible'} );
                jQuery.get( ajaxurl,
                    {action: 'erp-rec-get-todo', application_id: jQuery( '#application_id' ).val()},
                    function ( response ) {
                        if ( response.success === true ) {
                            var serverTodoData = response.data;
                            todoModel.$set( 'todoData', response.data );
                            if ( serverTodoData.length > 0 ) {
                                todoModel.$set( 'hasTodo', false);
                            }
                            jQuery( '#section-todo .spinner' ).css( {'visibility': 'hidden'} );
                        }
                    }
                );
            },

            handleTodo: function ( todo_id, checkCondition ) {
                jQuery( '#section-todo .spinner' ).css( {'visibility': 'visible'} );
                jQuery.post( ajaxurl, { action: 'erp-rec-update-todo', todo_id: todo_id, todo_status: checkCondition, _wpnonce: wpErpRec.nonce }, function(response) {
                    if ( response.success == true ) {
                        todoModel.getTodo();
                        jQuery( '#section-todo .spinner' ).css( {'visibility': 'hidden'} );
                        alertify.success( response.data );
                    } else {
                        todoModel.getTodo();
                        jQuery( '#section-todo .spinner' ).css( {'visibility': 'hidden'} );
                        alertify.error( response.data );
                    }
                } );
            },

            deleteTodo: function ( todo_id ) {
                if ( confirm( wpErpRec.todo_popup.del_confirm ) ) {
                    jQuery( '#section-todo .spinner' ).css( {'visibility': 'visible'} );
                    jQuery.post( ajaxurl, {action: 'erp-rec-delete-todo', todo_id: todo_id, _wpnonce: wpErpRec.nonce}, function ( response ) {
                        if ( response.success == true ) {
                            todoModel.getTodo();
                            jQuery( '#section-todo .spinner' ).css( {'visibility': 'hidden'} );
                            alertify.success( response.data );
                        } else {
                            todoModel.getTodo();
                            jQuery( '#section-todo .spinner' ).css( {'visibility': 'hidden'} );
                            alertify.error( response.data );
                        }
                    } );
                }
            }
        }
    } );
}

if ( jQuery('#openingform_stage_handler').length > 0 ) {

    var openingform_stage_handler = new Vue( {
        el: '#openingform_stage_handler',

        data: {
            stageData: [],
            checkedStage: [],
            jobid: 0
        },

        ready: function(){
            //this.getStage();
        },

        methods: {
            getStage: function () {
                jQuery( '#openingform_stage_handler .spinner' ).css( {'visibility': 'visible'} );
                this.jobid = jQuery('#postid').val();
                jQuery.get( ajaxurl, {action: 'erp-rec-get-stage', jobid: this.jobid },
                    function ( response ) {
                        if ( response.success === true ) {
                            openingform_stage_handler.$set( 'stageData', response.data );
                            jQuery( '#openingform_stage_handler .spinner' ).css( {'visibility': 'hidden'} );
                        }
                    }
                );
            },

            createStage: function () {
                var stageTitle = prompt( 'Please enter stage title' );
                if ( stageTitle != null && stageTitle != '' ) {
                    jQuery( '#openingform_stage_handler .spinner' ).css( {'visibility': 'visible'} );
                    var jobid = this.getParameterByName('postid');
                    jQuery.post( ajaxurl, { action: 'erp-rec-add-application-stage', _wpnonce: wpErpRec.nonce, job_id: jobid, stage_title: stageTitle }, function(response){
                        if ( response.success === true ) {
                            location.reload();
                            jQuery( '#openingform_stage_handler .spinner' ).css( {'visibility': 'hidden'} );
                        } else {
                            // Given stage name already exist!
                            alertify.error(response.data);
                        }
                    } );
                    jQuery( '#openingform_stage_handler .spinner' ).css( {'visibility': 'hidden'} );
                }
            },

            deleteStage: function ( index ) {
                if ( confirm( wpErpRec.stage_del_confirm ) ) {
                    if ( this.stageData.length < 2 ) {
                        alertify.error('You have to keep one stage atleast');
                    } else {
                        jQuery( '#openingform_stage_handler .spinner' ).show();
                        jQuery( '#openingform_stage_handler .spinner' ).css( {'visibility': 'visible'} );
                        this.stageData.splice( index, 1 );
                        jQuery( '#openingform_stage_handler .spinner' ).hide();
                    }
                }
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

if ( jQuery('#candidate-detail').length > 0 ) {

    var candidateDetail = new Vue( {
        el: '#candidate-detail',

        data: {
            stage_id: 0,
            status_name: '',
            avgRating: 0,
            success_notice_class: 'success_notice',
            error_notice_class: 'error_notice',
            isError: false,
            isVisible: false,
            response_message: ''
        },

        ready: function () {
            this.getApplicationAvgRating();
        },

        methods: {
            changeStage: function () {
                jQuery( '.erp-applicant-detail #dropdown-actions #stage_action .spinner' ).show();
                jQuery( '.erp-applicant-detail #dropdown-actions #stage_action .spinner' ).css( {'visibility': 'visible'} );
                var application_id = this.getParameterByName( 'application_id' );
                jQuery.post( ajaxurl,
                    {
                        action: 'erp-rec-change_stage',
                        application_id: application_id,
                        stage_id: this.stage_id,
                        _wpnonce: wpErpRec.nonce
                    }, function ( response ) {
                        if ( response.success == true ) {
                            var stage_name = jQuery('#change_stage option:selected').text();
                            jQuery( '#stage_name #change_stage_name' ).text( stage_name );
                            jQuery( '.erp-applicant-detail #dropdown-actions #stage_action .spinner' ).css( {'visibility': 'hidden'} );
                            alertify.success( response.data );
                        } else {
                            jQuery( '.erp-applicant-detail #dropdown-actions #stage_action .spinner' ).css( {'visibility': 'hidden'} );
                            alertify.error( response.data );
                        }
                    } );
            },

            getParameterByName: function ( name ) {
                name = name.replace( /[\[]/, "\\[" ).replace( /[\]]/, "\\]" );
                var regex = new RegExp( "[\\?&]" + name + "=([^&#]*)" ),
                    results = regex.exec( location.search );
                return results === null ? "" : decodeURIComponent( results[ 1 ].replace( /\+/g, " " ) );
            },

            getApplicationAvgRating: function () {
                var application_id = this.getParameterByName( 'application_id' );
                jQuery.get( ajaxurl,
                    {action: 'wp-erp-rec-get-applicationAvgRating', application_id: application_id},
                    function ( response ) {
                        if ( response.success === true ) {
                            candidateDetail.$set( 'avgRating', response.data );
                        }
                    }
                );
            },

            changeStaus: function () {
                jQuery( '.erp-applicant-detail #dropdown-actions #stage_action .spinner' ).css( {'visibility': 'visible'} );
                var application_id = this.getParameterByName( 'application_id' );
                jQuery.post( ajaxurl,
                    {
                        action: 'erp-rec-change_status',
                        application_id: application_id,
                        status_name: this.status_name,
                        _wpnonce: wpErpRec.nonce
                    }, function ( response ) {
                        if ( response.success == true ) {
                            var status = jQuery('#change_status option:selected').text();
                            jQuery( '#status #change_status_name' ).text( status );
                            jQuery( '.erp-applicant-detail #dropdown-actions #stage_action .spinner' ).css( {'visibility': 'hidden'} );
                            alertify.success( response.data );
                        } else {
                            jQuery( '.erp-applicant-detail #dropdown-actions #stage_action .spinner' ).css( {'visibility': 'hidden'} );
                            alertify.error( response.data );
                        }
                    } );
            },

            editInterview: function () {

            }
        }
    } );
}

if ( jQuery('#update_openingform_stage_handler').length > 0 ) {

    var update_openingform_stage_handler = new Vue( {
        el: '#update_openingform_stage_handler',

        data: {
            updateStageData: [],
            jobid: 0
        },

        ready: function () {
            this.jobid = this.getParameterByName( 'post' );
            //this.getSpecificStages( this.jobid );
        },

        methods: {
            getSpecificStages: function ( jobid ) {
                jQuery( '#update_openingform_stage_handler .spinner' ).show();
                jQuery( '#update_openingform_stage_handler .spinner' ).css( {'visibility': 'visible'} );
                jQuery( '#update_openingform_stage_handler .spinner' ).hide();
                jQuery.get( ajaxurl,
                    {action: 'erp-rec-get-application-stage', job_id: jobid},
                    function ( response ) {
                        if ( response.success === true ) {
                            update_openingform_stage_handler.$set( 'updateStageData', response.data );
                            jQuery( '#update_openingform_stage_handler .spinner' ).hide();
                        }
                    }
                );
            },

            createStage: function () {
                var stageTitle = prompt( wpErpRec.stage_message.prompt_message );
                if ( stageTitle != null && stageTitle != '' ) {
                    jQuery( '#update_openingform_stage_handler .spinner' ).show();
                    jQuery( '#update_openingform_stage_handler .spinner' ).css( {'visibility': 'visible'} );
                    // insert new stage
                    // first check given stage title is exist or not
                    var matchFlag = false;
                    for ( var stges in this.updateStageData ) {
                        if ( this.updateStageData[ stges ].title == stageTitle ) {
                            matchFlag = true;
                        }
                    }
                    if ( matchFlag == false ) {
                        jQuery.post( ajaxurl, { action: 'erp-rec-add-application-stage', _wpnonce: wpErpRec.nonce, job_id: this.jobid, stage_title: stageTitle }, function ( response ) {
                            if ( response.success === true ) {
                                update_openingform_stage_handler.getSpecificStages( update_openingform_stage_handler.jobid );
                                location.reload();
                                jQuery( '#update_openingform_stage_handler .spinner' ).hide();
                            } else {
                                // Given stage name already exist!
                                alert(response.data);
                            }
                        } );
                    } else {
                        alert(wpErpRec.stage_message.duplicate_error_message);
                    }
                    jQuery( '#update_openingform_stage_handler .spinner' ).hide();
                }
            },

            deleteStage: function ( stage_title ) {
                if ( confirm( wpErpRec.stage_del_confirm ) ) {
                    jQuery( '#update_openingform_stage_handler .spinner' ).show();
                    jQuery( '#update_openingform_stage_handler .spinner' ).css( {'visibility': 'visible'} );
                    jQuery.post( ajaxurl,
                        {
                            action: 'erp-rec-delete-application-stage',
                            _wpnonce: wpErpRec.nonce,
                            job_id: this.jobid,
                            stage_title: stage_title
                        },
                        function ( response ) {
                            if ( response.success === true ) {
                                update_openingform_stage_handler.getSpecificStages( update_openingform_stage_handler.jobid );
                                jQuery( '#update_openingform_stage_handler .spinner' ).hide();
                            } else {
                                // applicant found in this stage so you cant delete this stage
                                alertify.alert(response.data);
                            }
                        }
                    );
                    jQuery( '#update_openingform_stage_handler .spinner' ).hide();
                }
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

if ( jQuery('#reports-wrapper').length > 0 ) {
    var reportZone = new Vue( {
        el: '#reports-wrapper',

        data: {
            openingReportData: [],
            candidateReportData: [],
            jobidSelection: 0
        },

        ready: function () {
            this.getOpeningReportData();
        },

        computed: {
            totalCandidate: function () {
                var ttcandidate = 0;
                var item;
                for ( item in this.openingReportData ) {
                    ttcandidate = ttcandidate + parseInt( this.openingReportData[ item ].total_candidate );
                }
                return ttcandidate;
            },
            totalInProcess: function () {
                var ttcandidate = 0;
                var item;
                for ( item in this.openingReportData ) {
                    ttcandidate = ttcandidate + parseInt( this.openingReportData[ item ].in_process );
                }
                return ttcandidate;
            },
            totalArchive: function () {
                var ttcandidate = 0;
                var item;
                for ( item in this.openingReportData ) {
                    ttcandidate = ttcandidate + parseInt( this.openingReportData[ item ].archive );
                }
                return ttcandidate;
            },
            totalUnscreen: function () {
                var ttcandidate = 0;
                var item;
                for ( item in this.openingReportData ) {
                    ttcandidate = ttcandidate + parseInt( this.openingReportData[ item ].unscreen );
                }
                return ttcandidate;
            },
            totalOther: function () {
                var ttcandidate = 0;
                var item;
                for ( item in this.openingReportData ) {
                    ttcandidate = ttcandidate + parseInt( this.openingReportData[ item ].other );
                }
                return ttcandidate;
            }
        },

        methods: {
            getOpeningReportData: function () {
                jQuery( '#reports-wrapper .spinner' ).css( { 'visibility': 'visible' } );
                jQuery.get( ajaxurl,
                    { action: 'erp-rec-get-opening-report', _wpnonce: wpErpRec.nonce, jobid: 0 },
                    function ( response ) {
                        if ( response.success === true ) {
                            reportZone.$set( 'openingReportData', response.data );
                            jQuery( '#reports-wrapper .spinner' ).css( { 'visibility': 'hidden' } );
                        }
                    }
                );
            },

            generateReport: function () {
                jQuery( '#reports-wrapper .spinner' ).css( { 'visibility': 'visible' } );
                // set new csv url
                var get_base_url = jQuery( '#hidden-base-url' ).val();
                var jobid = jQuery( '#job-title' ).val();
                var current_url = get_base_url + '&jobid=' + jobid;
                jQuery( '#csv-dl-link' ).attr( 'href', current_url );
                jQuery.get( ajaxurl,
                    { action: 'erp-rec-get-opening-report', _wpnonce: wpErpRec.nonce, jobid: this.jobidSelection },
                    function ( response ) {
                        if ( response.success === true ) {
                            reportZone.$set( 'openingReportData', response.data );
                            jQuery( '#reports-wrapper .spinner' ).css( { 'visibility': 'hidden' } );
                        }
                    }
                );
            },

            generateCandidateReport: function () {
                jQuery( '#reports-wrapper .spinner' ).css( { 'visibility': 'visible' } );
                // set new csv url
                var jobid = jQuery( '#job-title' ).val();
                var current_url = jQuery( '#csv-dl-link' ).attr( 'href' );
                current_url = current_url + '&jobid=' + jobid;
                jQuery( '#csv-dl-link' ).attr( 'href', current_url );
                jQuery.get( ajaxurl,
                    {action: 'erp-rec-get-candidate-report', _wpnonce: wpErpRec.nonce, jobid: this.jobidSelection},
                    function ( response ) {
                        if ( response.success === true ) {
                            reportZone.$set( 'candidateReportData', response.data );
                            jQuery( '#reports-wrapper .spinner' ).css( {'visibility': 'hidden'} );
                        }
                    }
                );
            }
        }

    } );
}

if ( jQuery('#candidate-reports-wrapper').length > 0 ) {
    var candidateReportZone = new Vue( {
        el: '#candidate-reports-wrapper',

        data: {
            candidateReportData: [],
            jobidSelection: 0
        },

        methods: {
            generateCandidateReport: function () {
                jQuery( '#candidate-reports-wrapper .spinner' ).css( { 'visibility': 'visible' } );
                var get_base_url = jQuery( '#hidden-base-url' ).val();
                var jobid = jQuery( '#job-title' ).val();
                var current_url = get_base_url + '&jobid=' + jobid;
                jQuery( '#csv-dl-link' ).attr( 'href', current_url );
                jQuery.get( ajaxurl,
                    {action: 'erp-rec-get-candidate-report', _wpnonce: wpErpRec.nonce, jobid: this.jobidSelection},
                    function ( response ) {
                        if ( response.success === true ) {
                            candidateReportZone.$set( 'candidateReportData', response.data );
                            jQuery( '#candidate-reports-wrapper .spinner' ).css( {'visibility': 'hidden'} );
                        }
                    }
                );
            }
        }

    } );
}
