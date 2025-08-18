/* jshint devel:true */
/* global wpErpHr */
/* global wp */

;
(function ($) {
    'use strict';

    var files;

    var WeDevs_ERP_Recruitment_frontend = {

        /**
         * Initialize the events
         *
         * @return {void}
         */
        initialize: function () {

            $('body').on('click', '#btn_apply_job', this.newJobSeekerFormShow);
            // $('#job_seeker_table_wrapper').hide();
            $("#jobseeker_insertion_message").animate({ opacity: 'hide' }, "slow");
            $('#loader_wrapper').hide();
            $('body').on('submit', '#jobseeker_form', this.jobSeekerFormSubmit);
            $('body').on('change', 'select.erp-country-select', this.populateState);
            $('body').on('change', '#erp_rec_file', this.prepareUpload);

            $('.erp-rec-date-field').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: "-100:+100",
            });

            $("#jobseeker_form").formToWizard({ submitButton: 'submit_app' });
            /* job list drop down filter*/
            $('#key-department').change(function(){
                var jobKey = $(this).val();
                if ( jobKey === '' ) {
                    $('.erp-rec-job-list').show();
                } else {
                    $('.erp-rec-job-list').hide();
                    $('.'+jobKey).show();
                }
            });

        },

        prepareUpload: function (e) {
            //files = e.target.files;
            files = $('#erp_rec_file').prop('files')[0];
        },

        newJobSeekerFormShow: function (e) {
            $('#job_seeker_table_wrapper').slideToggle('fast');
        },

        jobSeekerFormSubmit: function (e) {
            e.stopPropagation();
            e.preventDefault();

            var messageBox = $("#jobseeker_insertion_message");

            messageBox.removeClass('success error');

            // if any radio not selected, then append a hidden field
            $('.new_answer_not_selected').remove(); // first remove all hidden field if created,
            $("#jobseeker_insertion_message" ).animate({ opacity: 'hide' }, "slow"); // first hide message

            $('.question_answer_fieldset').each(function () {
                $(this).find('.radio_zone').each(function () {
                    if (typeof ( $(this).find(".answer_radio:checked").val() ) == "undefined") {
                        $(this).append("<input type='hidden' class='new_answer_not_selected' name='answer[]' value='not selected'>");
                    }
                });
            });

            // if any radio not selected, then append a hidden field
            $('.question_answer_fieldset').each(function () {
                $(this).find('.checkbox_zone').each(function () {
                    if (typeof ( $(this).find(".answer_checkbox:checked").val() ) == "undefined") {
                        $(this).append("<input type='hidden' class='new_answer_not_selected' name='answer[]' value='not selected'>");
                    }
                });
            });

            var flag_for_submit = true;
            // start a loading spinner here
            $('#loader_wrapper').show();

            // Create a formdata object and add the files
            var fdata = new FormData(this);

            var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;

            $(".reqc").each(function () {
                var reqclass = $(this);

                if (reqclass.attr('type') == 'text' || reqclass.attr('type') == 'textarea') {

                    if (reqclass.val() == '') {
                        messageBox.animate({ opacity: 'show' }, "slow")
                            .text('please enter ' + reqclass.attr('name').split("_").join(" "))
                            .addClass('error');

                        $('#loader_wrapper').hide();
                        flag_for_submit = false;
                    }
                }
                else if (reqclass.attr('type') == 'email') {

                    if (reqclass.val() == '' || re.test(reqclass.val()) == false) {
                        messageBox.animate({ opacity: 'show' }, "slow")
                            .text('invalid ' + reqclass.attr('name').split("_").join(" "))
                            .addClass('error');

                        $('#loader_wrapper').hide();
                        flag_for_submit = false;
                    }
                }

            });

            if ($('#captcha_correct_result').val() != $('#captcha_result').val()) {
                messageBox.animate({ opacity: 'show' }, "slow")
                        .text('please enter correct captcha')
                        .addClass('error');
                $('#loader_wrapper').hide();
                flag_for_submit = false;
            }

            $.each($('input[name="erp_rec_file[]"]')[0].files, function(index, file) {
                var cvFileSize = file.size / 1024;

                if ( Math.ceil(cvFileSize) > 2048 ) {
                    messageBox.animate({ opacity: 'show' }, "slow")
                            .text(wpErpHr.fileSize)
                            .addClass('error');
    
                    $('#loader_wrapper').hide();
                    flag_for_submit = false;
                }
            });

            if (flag_for_submit == true) {
                $.ajax({
                    url: wpErpHr.ajax_url,
                    type: 'POST',
                    data: fdata,
                    processData: false, // Don't process the files
                    contentType: false, // Set content type to false as jQuery will tell the server its a query string request
                    success: function (response) {
                        if (response.success) {
                            // Success so call function to process the form
                            messageBox.animate({ opacity: 'show' }, "slow")
                                    .text(response.data.message)
                                    .addClass('success');

                            $("#jobseeker_form_table input[type='text']").val('');
                            $("#job_seeker_table_wrapper").hide('slow', function() {
                                this.remove();
                            });

                            $('#loader_wrapper').hide();
                        } else {
                            // Handle errors here
                            messageBox.animate({ opacity: 'show' }, "slow")
                                    .text(response.data.message)
                                    .addClass('error');
                            $('#loader_wrapper').hide();
                        }
                    }
                });
            }
        },

        /**
         * Populate the state dropdown based on selected country
         *
         * @return {void}
         */
        populateState: function () {
            if (typeof wpErpCountries === 'undefined') {
                return false;
            }

            var self = $(this),
                country = self.val(),
                parent = self.closest(self.data('parent')),
                empty = '<option val="-1">-------------</option>';

            if (wpErpCountries[ country ]) {
                var options = '',
                    state = wpErpCountries[ country ];

                for (var index in state) {
                    options = options + '<option value="' + index + '">' + state[ index ] + '</option>';
                }

                if ($.isArray(wpErpCountries[ country ])) {
                    parent.find('select.erp-state-select').html(empty);
                } else {
                    parent.find('select.erp-state-select').html(options);
                }

            } else {
                parent.find('select.erp-state-select').html(empty);
            }
        }

    };

    $(function () {
        WeDevs_ERP_Recruitment_frontend.initialize();
    });
})(jQuery);
