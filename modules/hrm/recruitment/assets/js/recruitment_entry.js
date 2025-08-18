/* jshint devel:true */
/* global wpErpHr */
/* global wp */

;
(function ($) {
    'use strict';

    var fattachment;
    var physical_file;

    var WeDevs_ERP_Recruitment = {

        /**
         * Initialize the events
         *
         * @return {void}
         */
        initialize: function () {
            $('body').on('change', '#erp-state', this.erp_state_saver);
            $('body').on('submit', '#jobseeker_form', this.jobSeekerFormSubmit);
            //$( 'body' ).on( 'submit', '#job-information-step-form', this.jobInformationStepFormSubmit );

            $('.erp-date-field-expire-date').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: 'c-0:+10'
            });

            // file upload
            var file_frame;
            $('.file_upload').on('click', function (event) {
                event.preventDefault();

                // If the media frame already exists, reopen it.
                if ( file_frame ) {
                    file_frame.open();
                    return;
                }

                // Create the media frame.
                file_frame = wp.media.frames.file_frame = wp.media({
                    title: $(this).data('uploader_title'),
                    button: {
                        text: $(this).data('uploader_button_text')
                    },
                    multiple: 'add'
                });

                // When an image is selected, run a callback.
                file_frame.on('select', function () {
                    // Reset attach then attach again
                    $('#jobseeker_form .attacher_name').remove();

                    var selection  = file_frame.state().get('selection'),
                        uploadElem = $('#jobseeker_form .file_upload'),
                        serialNo   = selection.length;

                    selection.map( function( attachment, index ) {
                        attachment = attachment.toJSON();
                        serialNo -= index;
                        uploadElem.after("<span class='attacher_name'> [" + serialNo + "] " + attachment.filename + " </span>");
                        uploadElem.after("<input type='hidden' class='attacher_name' name='attach_ids[]' value='" + attachment.id + "'>");
                    });
                });

                // Finally, open the modal
                file_frame.open();
            });

            // file upload for file system
            // end of file upload in file system

            $('#example-fontawesome').barrating({
                theme: 'fontawesome-stars',
                showSelectedRating: true,
                onSelect: function () {
                    ratingviewmodel.ratingSubmit();
                }
            });

            $('.erp-filter-status').on('change', function () {
                var catFilter = $(this).val();
                if ( catFilter != '' ) {
                    document.location.href = catFilter;
                }
            });

            $("#checkAll").change(function () {
                $(".applicant_chkbox:checkbox").prop('checked', $(this).prop("checked"));
            });

            $("#checkAllReq").change(function () {
                $(".applicant_chkbox_req:checkbox").prop('checked', $(this).prop("checked"));
            });

            // if any personal field uncheck then uncheck check all checkbox
            $('.applicant_chkbox').change(function () {
                if ( $(this).prop("checked") == false ) {
                    $('#checkAll').prop('checked', false);
                }
                // if all check by manually then check the check all checkbox
                var flag_for_check_all_check_manually = 0;
                var total_checkbox_checked = 0;
                $('.applicant_chkbox').each(function () {
                    total_checkbox_checked++;
                    if ( $(this).prop("checked") == true ) {
                        flag_for_check_all_check_manually++;
                    }
                });
                if ( total_checkbox_checked == flag_for_check_all_check_manually ) {
                    $('#checkAll').prop('checked', true);
                }
            });
            $('.applicant_chkbox_req').change(function () {
                if ( $(this).prop("checked") == false ) {
                    $('#checkAllReq').prop('checked', false);
                }
                // if all check by manually then check the check all checkbox
                var flag_for_check_all_check_manually_req = 0;
                var total_checkbox_checked_req = 0;
                $('.applicant_chkbox_req').each(function () {
                    total_checkbox_checked_req++;
                    if ( $(this).prop("checked") == true ) {
                        flag_for_check_all_check_manually_req++;
                    }
                });
                if ( total_checkbox_checked_req == flag_for_check_all_check_manually_req ) {
                    $('#checkAllReq').prop('checked', true);
                }
            });


            $('#sortit').sortable({
                update: function (event, ui) {
                    //var postdata = $(this).sortable("serialize");
                    var postdata = $(this).sortable("toArray");
                    var post_id = $('#post_ID').val();
                    $.post(ajaxurl, {
                        post_id: post_id,
                        action: 'wp-erp-rec-serial-personal-fields',
                        list: postdata
                    }, function (response) {
                        //console.log( response );
                    }, 'json');
                }
            });

            $(document).ready(function () {
                $('.not-loaded').removeClass('not-loaded');
            });

            // google place api to location text field
            if ( $('.post-type-erp_hr_recruitment').length > 0 ) {
                //var geocoder;
                //geocoder = new google.maps.Geocoder();
                //$('#glocation').autocomplete({
                //    source: function (request, response) {
                //        geocoder.geocode({ 'address': request.term }, function (results) {
                //            response($.map(results, function (item) {
                //                return {
                //                    label: item.formatted_address,
                //                    value: item.formatted_address,
                //                    latitude: item.geometry.location.lat(),
                //                    longitude: item.geometry.location.lng()
                //                }
                //            }))
                //        });
                //    },
                //    select: function (event, ui) {
                //        var lati = ui.item.latitude;
                //        var longi = ui.item.longitude;
                //        $('#latlocation').val(lati);
                //        $('#lnglocation').val(longi);
                //    }
                //});
            }

            /*recruitment questionnaire insert into input hidden field*/

            $('.input-question-text').blur(function () {
                $('#hr_questions').val(JSON.stringify(wperprec.qcollection));
            });
            $('.post-type-erp_hr_questionnaire input#publish,.post-type-erp_hr_questionnaire #major-publishing-actions').hover(function () {
                $('#hr_questions').val(JSON.stringify(wperprec.qcollection));
            });
            $('.post-type-erp_hr_questionnaire .post-type-erp_hr_questionnaire').keypress(function (e) {
                if ( e.which == 13 ) {
                    $('#hr_questions').val(JSON.stringify(wperprec.qcollection));
                }
            });

            /*recruitment questionnaire adding*/
            var qSetServerData = ( typeof wpErpHrQuestionnaire != "undefined" && wpErpHrQuestionnaire.qset != null ) ? wpErpHrQuestionnaire.qset : [];
            var exam_list = [];
            var duplicate_flag = false;
            var qset_id;
            var qset_text;
            // if server has some data, then populate and make the list
            if ( Object.keys(qSetServerData).length > 0 ) {
                jQuery.each(qSetServerData, function (index, value) {
                    jQuery('#here').append('<p> Selected Question Set: ' +
                        '<input readonly type="text" name="questions[' + index + '][questionset_name]" value="' + value.questionset_name + '" /> ' +
                        '<input type="hidden" name="questions[' + index + '][questionset_id]" value="' + index + '" /> ' +
                        '<span id="' + index + '" class="button remove">Remove</span></p>');
                    exam_list.push(index);
                });
            }

            jQuery(".add").click(function () {
                qset_id = jQuery('#qset').val();
                if ( qset_id != null ) {
                    qset_text = jQuery('#qset option:selected').text();
                    for ( i = 0; i < exam_list.length; i++ ) {
                        if ( exam_list[i] == qset_id ) {
                            duplicate_flag = true;
                        }
                    }

                    if ( duplicate_flag == false ) {
                        jQuery('#here').append('<p class="selected-question-set"> Selected Question Set: ' +
                            '<input readonly type="text" name="questions[' + qset_id + '][questionset_name]" value="' + qset_text + '" /> ' +
                            '<input type="hidden" name="questions[' + qset_id + '][questionset_id]" value="' + qset_id + '" /> ' +
                            '<span id="' + qset_id + '" class="button remove">Remove</span></p>');
                        exam_list.push(qset_id);
                    }
                    duplicate_flag = false;
                }
            });
            jQuery('body').on('click', '.remove', function () {
                var index = exam_list.indexOf(jQuery(this).attr('id'));
                if ( index > -1 ) {
                    exam_list.splice(index, 1);
                }
                jQuery(this).parent().remove();
            });
            /* end of adding questionnaire in recruitment custom post type */

            /*email recipient list and functionality for remove them */
            jQuery('#total_recipient').text('Recipient List (' + jQuery('#email_recipient_list ul li').length + ')');
            jQuery('body').on('click', '.remove_email', function () {
                var new_email_list = [];
                jQuery(this).parent().remove();
                jQuery('#email_recipient_list ul li').each(function () {
                    new_email_list.push(jQuery(this).find('label').text());
                });
                jQuery('#recipient_list').val("" + new_email_list.join());
                jQuery('#total_recipient').text('Recipient List (' + jQuery('#email_recipient_list ul li').length + ')');
            });
            /* end of email list */

            /* opening form */
            $('#openingform').openingFormToWizard({ submitButton: 'submit_opening' });
            $('#openingform_sortit').sortable({
                update: function (event, ui) {
                    var postdata = $(this).sortable("toArray");
                    var post_id = $('#postid').val();
                    $.post(ajaxurl, {
                        post_id: post_id,
                        action: 'wp-erp-rec-serial-stage',
                        list: postdata,
                    }, function( response ) {

                    }, 'json');
                }
            });

            $('#openingform_sortit_edit_mode').sortable({
                update: function (event, ui) {
                    var postdata = $(this).sortable("toArray");
                    var post_id = $('#post_ID').val();
                    $.post(ajaxurl, {
                        post_id: post_id,
                        action: 'wp-erp-rec-serial-stage',
                        list: postdata,
                    }, function( response ) {

                    }, 'json');
                }
            });

            // Candidate single page
            var floatingDiv = $("#left-fixed-menu");
            var perspectiveDiv = $(".cpostbox");
            var perspectiveDivPosition = perspectiveDiv.position();
            var tdWidth = 0;
            $(window).scroll(function () {
                var scrollPosition = $(window).scrollTop();
                if ( perspectiveDivPosition != undefined ) {
                    if ( scrollPosition >= perspectiveDivPosition.top ) {
                        tdWidth = $('#td-lside').width();
                        floatingDiv.css({ 'position': 'fixed', 'top': 35, 'width': tdWidth });
                    } else {
                        floatingDiv.css({ 'position': 'relative', 'top': 0, 'width': 'auto' });
                    }
                }
            });

            // candidate single page admin section
            var jobid = jQuery('#cjob_id').val();
            var get_base_url = jQuery('#hidden-base-url').val();
            jQuery('#job-link').attr('href', get_base_url + '&jobid=' + jobid);
            jQuery('#cjob_id').change(function () {
                var jobid = jQuery('#cjob_id').val();
                var get_base_url = jQuery('#hidden-base-url').val();
                jQuery('#job-link').attr('href', get_base_url + '&jobid=' + jobid);
            });

            // toogle button
            $('.hndle-toogle-button').click(function () {
                $(this).siblings('.section-content').toggleClass('toggle-metabox-hide');
            });

            $('.btn-todo').click(function () {
                $.erpPopup({
                    title: wpErpRec.todo_popup.title,
                    button: wpErpRec.todo_popup.submit,
                    id: 'new-todo-top',
                    content: wp.template('erp-rec-todo-template')().trim(),
                    extraClass: 'smaller',
                    onReady: function (modal) {
                        modal.enableButton();
                        $('#assign_user_id').select2();
                        var application_id = WeDevs_ERP_Recruitment.getApplicationId();
                        $('#todo_application_id').val(application_id);
                        WeDevs_ERP_Recruitment.initTimePicker();
                        WeDevs_ERP_Recruitment.initDateField();
                    },
                    onSubmit: function (modal) {
                        modal.disableButton();
                        wp.ajax.send('erp-rec-create-todo', {
                            data: {
                                fdata: this.serialize(),
                                _wpnonce: wpErpRec.nonce
                            },
                            success: function (res) {
                                alertify.success(res);
                                modal.closeModal();
                                todoModel.getTodo();
                            },
                            error: function (error) {
                                modal.enableButton();
                                alert(error);
                            }
                        });
                    }
                });
            });

            $('#new-todo').click(function () {
                $.erpPopup({
                    title: wpErpRec.todo_popup.title,
                    button: wpErpRec.todo_popup.submit,
                    id: 'new-todo-low',
                    content: wp.template('erp-rec-todo-template')().trim(),
                    extraClass: 'smaller',
                    onReady: function (modal) {
                        modal.enableButton();
                        $('#assign_user_id').select2();
                        var application_id = WeDevs_ERP_Recruitment.getApplicationId();
                        $('#todo_application_id').val(application_id);
                        WeDevs_ERP_Recruitment.initTimePicker();
                        WeDevs_ERP_Recruitment.initDateField();
                    },
                    onSubmit: function (modal) {
                        modal.disableButton();
                        wp.ajax.send('erp-rec-create-todo', {
                            data: {
                                fdata: this.serialize(),
                                _wpnonce: wpErpRec.nonce
                            },
                            success: function (res) {
                                alertify.success(res);
                                modal.closeModal();
                                todoModel.getTodo();
                            },
                            error: function (error) {
                                modal.enableButton();
                                alert(error);
                            }
                        });
                    }
                });
            });

            $('.btn-interview').click(function () {
                $.erpPopup({
                    title: wpErpRec.interview_popup.title,
                    button: wpErpRec.interview_popup.submit,
                    id: 'new-interview-top',
                    content: wp.template('erp-rec-interview-template')().trim(),
                    extraClass: 'medium',
                    onReady: function (modal) {
                        modal.enableButton();
                        $('#interviewers').select2();
                        $('#type_of_interview').change(function () {
                            var selected_interview_name = $(this).find("option:selected").text();
                            $('#type_of_interview_text').val(selected_interview_name);
                        });
                        var application_id = WeDevs_ERP_Recruitment.getApplicationId();
                        $('#interview_application_id').val(application_id);
                        WeDevs_ERP_Recruitment.initTimePicker();
                        WeDevs_ERP_Recruitment.initDateField();
                    },
                    onSubmit: function (modal) {
                        modal.disableButton();
                        wp.ajax.send('erp-rec-create-interview', {
                            data: {
                                fdata: this.serialize(),
                                _wpnonce: wpErpRec.nonce
                            },
                            success: function (res) {
                                alertify.success(res);
                                modal.closeModal();
                                interviewModel.getInterview();
                            },
                            error: function (error) {
                                modal.enableButton();
                                alert(error);
                            }
                        });
                    }
                });
            });

            $('#new-interview').click(function () {
                $.erpPopup({
                    title: wpErpRec.interview_popup.title,
                    button: wpErpRec.interview_popup.submit,
                    id: 'new-interview-low',
                    content: wp.template('erp-rec-interview-template')().trim(),
                    extraClass: 'medium',
                    onReady: function (modal) {
                        modal.enableButton();
                        $('#interviewers').select2();
                        $('#type_of_interview').change(function () {
                            var selected_interview_name = $(this).find("option:selected").text();
                            $('#type_of_interview_text').val(selected_interview_name);
                        });
                        var application_id = WeDevs_ERP_Recruitment.getApplicationId();
                        $('#interview_application_id').val(application_id);
                        WeDevs_ERP_Recruitment.initTimePicker();
                        WeDevs_ERP_Recruitment.initDateField();
                    },
                    onSubmit: function (modal) {
                        modal.disableButton();
                        wp.ajax.send('erp-rec-create-interview', {
                            data: {
                                fdata: this.serialize(),
                                _wpnonce: wpErpRec.nonce
                            },
                            success: function (res) {
                                alertify.success(res);
                                modal.closeModal();
                                interviewModel.getInterview();
                            },
                            error: function (error) {
                                modal.enableButton();
                                alert(error);
                            }
                        });
                    }
                });
            });

            // to-do or calendar page
            $(document).ready(function () {
            });

            $('#add-todo').click(function () {
                $.erpPopup({
                    title: wpErpRec.todo_popup.title,
                    button: wpErpRec.todo_popup.submit,
                    id: 'new-todo-template',
                    content: wp.template('erp-rec-todo-template')().trim(),
                    extraClass: 'smaller',
                    onReady: function (modal) {
                        modal.enableButton();
                        $('#assign_user_id').select2();
                        var application_id = 0;
                        $('#todo_application_id').val(application_id);
                        WeDevs_ERP_Recruitment.initTimePicker();
                        WeDevs_ERP_Recruitment.initDateField();
                    },
                    onSubmit: function (modal) {
                        modal.disableButton();
                        wp.ajax.send('erp-rec-create-todo', {
                            data: {
                                fdata: this.serialize(),
                                _wpnonce: wpErpRec.nonce
                            },
                            success: function (res) {
                                $('#message').show();
                                $('#message #message_text').text(res);
                                modal.closeModal();
                                location.reload();
                            },
                            error: function (error) {
                                alert(error);
                            }
                        });
                    }
                });
            });

            $('#todo-calendar-overview').load(ajaxurl, { action: 'erp-get-calendar-overview' }, function (res) {
                $('.erp-calendar-detail #left-fixed-menu ul li:first-child span').addClass('left-menu-current-item');
                $('.erp-calendar-detail #left-fixed-menu').height($('.erp-calendar-detail .postbox').height() + 'px');
            });

            $('#section-overview').click(function () {
                $('#todo-calendar-overdue').hide();
                $('#todo-calendar-today').hide();
                $('#todo-calendar-later').hide();
                $('#todo-calendar-no-date').hide();
                $('#todo-calendar-this-month').hide();
                $('#todo-calendar-overview').show();
                $('#left-fixed-menu ul li span').removeClass('left-menu-current-item');
                $(this).addClass('left-menu-current-item');
                if ( $('#todo-calendar-overview div').hasClass('fc-toolbar') == false ) {
                    $('.erp-calendar-detail .spinner').css({ 'visibility': 'visible' });
                    $('#todo-calendar-overview').load(ajaxurl, { action: 'erp-get-calendar-overview' }, function (res) {
                        $('.erp-calendar-detail .spinner').css({ 'visibility': 'hidden' });
                        $('.erp-calendar-detail #left-fixed-menu').height($('.erp-calendar-detail .postbox').height() + 'px');
                    });
                } else {
                    $('.erp-calendar-detail .spinner').css({ 'visibility': 'hidden' });
                }
            });

            $('#section-overdue').click(function () {
                $('#todo-calendar-overview').hide();
                $('#todo-calendar-today').hide();
                $('#todo-calendar-later').hide();
                $('#todo-calendar-no-date').hide();
                $('#todo-calendar-this-month').hide();
                $('#todo-calendar-overdue').show();
                $('#left-fixed-menu ul li span').removeClass('left-menu-current-item');
                $(this).addClass('left-menu-current-item');
                if ( $('#todo-calendar-overdue div').hasClass('fc-toolbar') == false ) {
                    $('.erp-calendar-detail .spinner').css({ 'visibility': 'visible' });
                    $('#todo-calendar-overdue').load(ajaxurl, { action: 'erp-get-calendar-overdue' }, function (res) {
                        $('.erp-calendar-detail .spinner').css({ 'visibility': 'hidden' });
                        $('.erp-calendar-detail #left-fixed-menu').height($('.erp-calendar-detail .postbox').height() + 'px');
                    });
                } else {
                    $('.erp-calendar-detail .spinner').css({ 'visibility': 'hidden' });
                }
            });

            $('#section-today').click(function () {
                $('#todo-calendar-overdue').hide();
                $('#todo-calendar-overview').hide();
                $('#todo-calendar-later').hide();
                $('#todo-calendar-no-date').hide();
                $('#todo-calendar-this-month').hide();
                $('#todo-calendar-today').show();
                $('#left-fixed-menu ul li span').removeClass('left-menu-current-item');
                $(this).addClass('left-menu-current-item');
                if ( $('#todo-calendar-today div').hasClass('fc-toolbar') == false ) {
                    $('.erp-calendar-detail .spinner').css({ 'visibility': 'visible' });
                    $('#todo-calendar-today').load(ajaxurl, { action: 'erp-get-calendar-today' }, function (res) {
                        $('.erp-calendar-detail .spinner').css({ 'visibility': 'hidden' });
                        $('.erp-calendar-detail #left-fixed-menu').height($('.erp-calendar-detail .postbox').height() + 'px');
                    });
                } else {
                    $('.erp-calendar-detail .spinner').css({ 'visibility': 'hidden' });
                }
            });

            $('#section-later').click(function () {
                $('#todo-calendar-overdue').hide();
                $('#todo-calendar-today').hide();
                $('#todo-calendar-overview').hide();
                $('#todo-calendar-no-date').hide();
                $('#todo-calendar-this-month').hide();
                $('#todo-calendar-later').show();
                $('#left-fixed-menu ul li span').removeClass('left-menu-current-item');
                $(this).addClass('left-menu-current-item');
                $('.erp-calendar-detail .spinner').css({ 'visibility': 'visible' });
                if ( $('#todo-calendar-later div').hasClass('fc-toolbar') == false ) {
                    $('#todo-calendar-later').load(ajaxurl, { action: 'erp-get-calendar-later' }, function (res) {
                        $('.erp-calendar-detail .spinner').css({ 'visibility': 'hidden' });
                        $('.erp-calendar-detail #left-fixed-menu').height($('.erp-calendar-detail .postbox').height() + 'px');
                    });
                } else {
                    $('.erp-calendar-detail .spinner').css({ 'visibility': 'hidden' });
                }
            });

            $('#section-no-due-date').click(function () {
                $('#todo-calendar-overdue').hide();
                $('#todo-calendar-today').hide();
                $('#todo-calendar-overview').hide();
                $('#todo-calendar-this-month').hide();
                $('#todo-calendar-later').hide();
                $('#todo-calendar-no-date').show();
                $('#left-fixed-menu ul li span').removeClass('left-menu-current-item');
                $(this).addClass('left-menu-current-item');
                $('.erp-calendar-detail .spinner').css({ 'visibility': 'visible' });
                $.getJSON(ajaxurl, { action: 'erp-get-calendar-no-date' }, function (res) {
                    $('.erp-calendar-detail .spinner').css({ 'visibility': 'hidden' });
                    $('#todo-calendar-no-date ul').empty();
                    $.each(res.data, function (i, item) {
                        $('#todo-calendar-no-date ul').append('<li>' + item.title + '</li>');
                    });
                });
                $('.erp-calendar-detail .spinner').css({ 'visibility': 'hidden' });
            });

            $('#section-this-month').click(function () {
                $('#todo-calendar-overdue').hide();
                $('#todo-calendar-today').hide();
                $('#todo-calendar-overview').hide();
                $('#todo-calendar-no-date').hide();
                $('#todo-calendar-later').hide();
                $('#todo-calendar-this-month').show();
                $('#left-fixed-menu ul li span').removeClass('left-menu-current-item');
                $(this).addClass('left-menu-current-item');
                $('.erp-calendar-detail .spinner').css({ 'visibility': 'visible' });
                if ( $('#todo-calendar-this-month div').hasClass('fc-toolbar') == false ) {
                    $('#todo-calendar-this-month').load(ajaxurl, { action: 'erp-get-calendar-this-month' }, function (res) {
                        $('.erp-calendar-detail .spinner').css({ 'visibility': 'hidden' });
                        $('.erp-calendar-detail #left-fixed-menu').height($('.erp-calendar-detail .postbox').height() + 'px');
                    });
                } else {
                    $('.erp-calendar-detail .spinner').css({ 'visibility': 'hidden' });
                }
            });

            /*candidate page*/
            $('.erp-candidate-detail #left-fixed-menu').height($('.erp-candidate-detail .postbox').height() + 'px');
            $('.list-item-scroller').click(function (event) {
                event.preventDefault();
                var current_id = $(this).attr('href');
                $('html,body').animate({ scrollTop: $(current_id).offset().top }, 1000);
            });
            $('#add-candidate').click(function () {
                $.erpPopup({
                    title: wpErpRec.add_candidate_popup.title,
                    button: wpErpRec.add_candidate_popup.submit,
                    id: 'new-candidate-popup-template',
                    content: wp.template('erp-rec-candidate-popup-template')().trim(),
                    extraClass: 'smaller',
                    onReady: function () {
                    },
                    onSubmit: function (modal) {
                        modal.disableButton();
                        wp.ajax.send('erp-rec-create-candidate', {
                            data: {
                                fdata: this.serialize(),
                                _wpnonce: wpErpRec.nonce
                            },
                            success: function (res) {
                                $('#message').show();
                                $('#message #message_text').text(res);
                                modal.closeModal();
                                location.reload();
                            },
                            error: function (error) {
                                alert(error);
                            }
                        });
                    }
                });
            });

            /*questionnaire step*/
            $('#step-questionnaire').hide();
            $('#show-questionnaire').click(function () {
                if ( $(this).is(':checked') ) {
                    $('#step-questionnaire').show();
                } else {
                    $('#step-questionnaire').hide();
                }
            });

            /*reports*/

            /*step job description*/
            $('#create_opening').attr('disabled', true);
            if ( $('#opening_title').val() == "" ) {
                $('#create_opening').attr('disabled', true);
            } else {
                $('#create_opening').attr('disabled', false);
            }
            $('#opening_title').keyup(function () {
                if ( $(this).val().length != 0 )
                    $('#create_opening').attr('disabled', false);
                else
                    $('#create_opening').attr('disabled', true);
            });

            /*hiring workflow stage control*/
            if ( $('#hiring-workflow-form').length > 0 ) {
                var checked_counter = 0;
                $("#openingform_sortit input[type='checkbox']:checked").each(function () {
                    checked_counter++;
                });
                $("#openingform_sortit input[type='checkbox']").click(function () {
                    checked_counter = 0;
                    $("#openingform_sortit input[type='checkbox']:checked").each(function () {
                        checked_counter++;
                    });
                    if ( checked_counter == 0 ) {
                        $('#stage-validation-message').show();
                        $('#stage-validation-message').text(wpErpRec.stage_message.title_message);
                        $(this).prop('checked', true);
                    } else {
                        $('#stage-validation-message').hide();
                        $('#hiring_workflow').prop('disabled', false);
                    }
                });
                if ( checked_counter == 0 ) {
                    $('#stage-validation-message').show();
                    $('#stage-validation-message').text(wpErpRec.stage_message.title_message);
                    $(this).prop('checked', true);
                } else {
                    $('#stage-validation-message').hide();
                    $('#hiring_workflow').prop('disabled', false);
                }
                $('#hiring_workflow').click(function (e) {
                    e.preventDefault();
                    if ( checked_counter > 0 ) {
                        $('#hiring-workflow-form').submit();
                    } else {
                        $('#stage-validation-message').show();
                        $('#stage-validation-message').text('Please select at least one stage');
                    }
                });
            }

            //job information
            if ( $('#job-information-step').length > 0 ) {
                //var hiring_lead_value = $( "select[name='hiring_lead']" ).val();
                // var hiring_lead_value = $("#hiring_lead").val();
                var department_value = $("select[name='department']").val();
                var employment_type_value = $("select[name='employment_type']").val();
                var minimum_experience_value = $("select[name='minimum_experience']").val();
                var expire_date_value = $("#expire_date").val();
                var glocation = $("#glocation").val();
                var vacation_value = $("#vacancy").val();

                // $("#hiring_lead").change(function () {
                //     hiring_lead_value = $(this).val();
                // });

                $("#expire_date").datepicker({
                    dateFormat: 'yy-mm-dd',
                    onSelect: function () {
                        //$( this ).focus();
                        //$( "#job_information" ).prop( 'disabled', false );
                    }
                });

                $('#job_information').click(function (e) {
                    e.preventDefault();

                    // hiring_lead_value = $("#hiring_lead").val();
                    department_value = $("select[name='department']").val();
                    employment_type_value = $("select[name='employment_type']").val();
                    minimum_experience_value = $("select[name='minimum_experience']").val();
                    expire_date_value = $("#expire_date").val();
                    glocation = $("#glocation").val();
                    vacation_value = $("#vacancy").val();
                    var flag_for_success_validation = true;
                    var error_text_messaage = '';

                    // if ( hiring_lead_value == '' ) {
                    //     flag_for_success_validation = false;
                    //     error_text_messaage = wpErpRec.information_validation_message.hiring_validation_message;
                    // }
                    if ( department_value == '' ) {
                        flag_for_success_validation = false;
                        error_text_messaage = wpErpRec.information_validation_message.department_validation_message;
                    }
                    else if ( employment_type_value == '' ) {
                        flag_for_success_validation = false;
                        error_text_messaage = wpErpRec.information_validation_message.employment_validation_message;
                    }
                    else if ( minimum_experience_value == '' ) {
                        flag_for_success_validation = false;
                        error_text_messaage = wpErpRec.information_validation_message.minimum_exp_validation_message;
                    }
                    else if ( glocation == '' ) {
                        flag_for_success_validation = false;
                        error_text_messaage = wpErpRec.information_validation_message.location_validation_message;
                    }
                    else if ( vacation_value == '' ) {
                        flag_for_success_validation = false;
                        error_text_messaage = wpErpRec.information_validation_message.vacancy_validation_message;
                    }

                    if ( flag_for_success_validation == true ) {
                        $('#job-information-step-form').submit();
                    } else {
                        swal({
                            title: 'Oops',
                            text: error_text_messaage,
                            type: 'error',
                            timer: 2000
                        });
                    }
                });
            }

            /*hiring workflow stage control in opening edit mode*/
            $('#stage-validation-message').hide();
            if ( $('.post-type-erp_hr_recruitment #openingform_sortit_edit_mode').length > 0 ) {
                checked_counter = 0;
                $("#openingform_sortit_edit_mode input[type='checkbox']:checked").each(function () {
                    checked_counter++;
                });
                $("#openingform_sortit_edit_mode input[type='checkbox']").click(function () {
                    checked_counter = 0;
                    if ( !$(this).is(':checked') ) { // in uncheck we have to check this stage has candidate or not, if has candidate then user cannot uncheck it
                        if ( $(this).next('.candidate_number').val() > 0 ) {
                            $('#stage-validation-message').show();
                            $('#stage-validation-message').text(wpErpRec.stage_message.candidate_number_error_message);
                            $('#stage-validation-message').delay(5000).hide();
                            $(this).prop('checked', true);
                        }
                    }
                    $("#openingform_sortit_edit_mode input[type='checkbox']:checked").each(function () {
                        checked_counter++;
                    });
                    if ( checked_counter == 0 ) {
                        $('#stage-validation-message').show();
                        $('#stage-validation-message').text(wpErpRec.stage_message.title_message);
                        $('#stage-validation-message').delay(5000).hide();
                        $(this).prop('checked', true);
                    } else {
                        $('input#publish').prop('disabled', false);
                    }
                });
            }
        },

        //jobInformationStepFormSubmit: function(e){
        //    e.preventDefault();
        //    /*job information validation*/
        //
        //},

        initTimePicker: function () { // init timepicker
            $('.erp-time-field').timepicker();
        },

        initDateField: function () { // date picker
            $('.erp-date-field').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+0'
            });
            var dateToday = new Date();
            $('.erp-date-field-todo-deadline').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                minDate: dateToday
            });
        },

        getApplicationId: function () {
            var name = 'application_id';
            name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
            var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                results = regex.exec(location.search);
            return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
        },

        Uploader: {
            deleteFile: function (e) {
                e.preventDefault();
                if ( confirm('This file will be deleted permanently') ) {
                    var that = $(this),
                        data = {
                            file_id: that.data('id'),
                            action: 'erp_hr_attachment_delete_file',
                            _wpnonce: wpErpRec.nonce
                        };
                    $.post(ajaxurl, data, function () {
                    });
                    that.closest('.cpm-uploaded-item').fadeOut(function () {
                        $(this).remove();
                    });
                }
            }
        },

        erp_state_saver: function () {
            $("#erp_state_text").val($("#erp-state :selected").text());
        },

        jobSeekerFormSubmit: function (e) {
            e.stopPropagation();
            e.preventDefault();

            var flag_for_submit = true;
            // start a loading spinner here
            $('#job_seeker_table_wrapper .spinner').css({ 'visibility': 'visible' });
            var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
            $(".validation-error-notification").remove();
            $(".reqc").each(function () {
                var reqclass = $(this);
                if ( reqclass.attr('type') == 'text' || reqclass.attr('type') == 'textarea' ) {

                    if ( reqclass.val() == '' ) {
                        reqclass.after('<span class="validation-error-notification">' + 'Please enter ' + reqclass.attr('name').split('_').join(' ') + '</span>');
                        $('#job_seeker_table_wrapper .spinner').css({ 'visibility': 'hidden' });
                        flag_for_submit = false;
                    }
                }
                else if ( reqclass.attr('type') == 'email' ) {

                    if ( reqclass.val() == '' || re.test(reqclass.val()) == false ) {
                        reqclass.after('<span class="validation-error-notification">' + 'Invalid ' + reqclass.attr('name').split('_').join(' ') + '</span>');
                        $('#job_seeker_table_wrapper .spinner').css({ 'visibility': 'hidden' });
                        flag_for_submit = false;
                    }
                }

            });

            if ( flag_for_submit == true ) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: $('#jobseeker_form').serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if ( response.success === true ) {
                            alertify.success(wpErpRec.candidate_submission.success_message);
                            $("#jobseeker_form_table input[type='text']").val('');
                            $("#job_seeker_table_wrapper").hide('slow');
                            $('#job_seeker_table_wrapper .spinner').css({ 'visibility': 'hidden' });
                            setTimeout(function () {
                                location.href = wpErpRec.candidate_submission.candidate_list_url;
                            }, 1500);
                        }
                        else {
                            // Handle errors here
                            var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
                            $("#job_seeker_table_wrapper .reqc").each(function () {
                                var reqclass = $(this);
                                if ( reqclass.attr('type') == 'text' || reqclass.attr('type') == 'textarea' ) {
                                    if ( reqclass.val() == '' ) {
                                        reqclass.after('<span class="validation-error-notification">' + 'Please enter ' + reqclass.attr('name').split('_').join(' ') + '</span>');
                                        $('#job_seeker_table_wrapper .spinner').css({ 'visibility': 'hidden' });
                                    } else if ( reqclass.hasClass('first_name') && response.data.type == 'first-name-error' ) {
                                        reqclass.after('<span class="validation-error-notification">' + response.data.message + '</span>');
                                        $('#job_seeker_table_wrapper .spinner').css({ 'visibility': 'hidden' });
                                    } else if ( reqclass.hasClass('last_name') && response.data.type == 'last-name-error' ) {
                                        reqclass.after('<span class="validation-error-notification">' + response.data.message + '</span>');
                                        $('#job_seeker_table_wrapper .spinner').css({ 'visibility': 'hidden' });
                                    }
                                }
                                else if ( reqclass.attr('type') == 'email' ) {
                                    if ( reqclass.val() == '' || re.test(reqclass.val()) == false ) {
                                        reqclass.after('<span class="validation-error-notification">' + 'Invalid ' + reqclass.attr('name').split('_').join(' ') + '</span>');
                                        $('#job_seeker_table_wrapper .spinner').css({ 'visibility': 'hidden' });
                                    } else if ( response.data.type == 'duplicate-email' ) {
                                        reqclass.after('<span class="validation-error-notification">' + response.data.message + '</span>');
                                        $('#job_seeker_table_wrapper .spinner').css({ 'visibility': 'hidden' });
                                    }
                                }
                                else if ( reqclass.attr('type') == 'file' ) {
                                    if ( response.data.type == 'file-error' ) {
                                        reqclass.after('<span class="validation-error-notification">' + response.data.message + '</span>');
                                        $('#job_seeker_table_wrapper .spinner').css({ 'visibility': 'hidden' });
                                    }
                                }
                                else if ( reqclass.attr('type') == 'button' ) {
                                    if ( response.data.type == 'file-error' ) {
                                        reqclass.after('<span class="validation-error-notification">' + response.data.message + '</span>');
                                        $('#job_seeker_table_wrapper .spinner').css({ 'visibility': 'hidden' });
                                    }
                                }
                            });
                            $('#job_seeker_table_wrapper .spinner').css({ 'visibility': 'hidden' });
                        }
                    },
                    error: function (response) {
                        alertify.error(response.data);
                        // Handle errors here
                        // STOP LOADING SPINNER
                        $('#job_seeker_table_wrapper .spinner').css({ 'visibility': 'hidden' });
                    }
                });
            }
        }

    };

    $(function () {
        WeDevs_ERP_Recruitment.initialize();
    });
})(jQuery);
