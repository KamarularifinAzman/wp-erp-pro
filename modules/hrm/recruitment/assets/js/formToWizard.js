;
(function ($) {
    $.fn.formToWizard = function (options) {
        options = $.extend({
            submitButton: ""
        }, options);

        var element = this;

        var steps = $(element).find("fieldset");
        //var count = steps.size();
        var count = steps.length;
        var submmitButtonName = "#" + options.submitButton;
        $(submmitButtonName).hide();

        // 2
        // $(element).before("<ul id='steps'></ul>");

        if (steps.length > 1) {
            steps.each(function (i) {
                $(this).wrap("<div class='step-list' id='step" + i + "'></div>");
                $(this).append("<p class='step-command-list rec-clearfix' id='step" + i + "commands'></p>");

                // 2
                var name = $(this).find("legend").html();
                $("#steps").append("");

                if (i == 0) {
                    createNextButton(i);
                    selectStep(i);
                }
                else if (i == count - 1) {
                    $("#step" + i).hide();
                    createPrevButton(i);
                }
                else {
                    $("#step" + i).hide();
                    createPrevButton(i);
                    createNextButton(i);
                }
            });
        } else {
            $(submmitButtonName).show();
        }

        function createPrevButton(i) {
            var stepName = "step" + i;
            //$("#" + stepName + "commands").append("<a href='#' id='" + stepName + "Prev' class='prev'>< Back</a>");
            $("#" + stepName + "commands").append("<input type='button' id='" + stepName + "Prev' class='prev' value='Back'>");

            $("#" + stepName + "Prev").on("click", function (e) {
                e.preventDefault();

                $("#" + stepName).hide();
                $("#step" + (i - 1)).show();
                $(submmitButtonName).hide();
                /**/
                if ( $('#jobseeker_form').length == 1 ) {
                    $('html, body').animate({
                        scrollTop: $("#step" + (i - 1)).offset().top
                    }, 300);
                }
                /**/
                selectStep(i - 1);
            });
        }

        function createNextButton(i) {
            var stepName = "step" + i;
            //$("#" + stepName + "commands").append("<a href='#' id='" + stepName + "Next' class='next'>Next ></a>");
            $("#" + stepName + "commands").append("<input type='button' id='" + stepName + "Next' class='next' value='Next'>");

            $("#" + stepName + "Next").on("click", function (e) {
                e.preventDefault();
                //validate question require fields
               var required_field = $( '#jobseeker_form' ).find('.erp-rec-required-field');
                var errors = 0;

                required_field.each( function( index, field ) {
                    if ( $(field).val() === '' ) {
                        $(field).addClass('erp-rec-error');
                        errors++;
                    }
                } );

                if ( errors > 0 ) {
                    return;
                }
                var validation_result = validateAnswer(stepName);

                if ( validation_result ) {
                    $("#" + stepName).hide();
                    $("#step" + (i + 1)).show();
                    /**/
                    if ( $('#jobseeker_form').length == 1 ) {
                        $('html, body').animate({
                            scrollTop: $("#step" + (i + 1)).offset().top
                        }, 300);
                    }
                    /**/
                    if (i + 2 == count)
                        $(submmitButtonName).show();
                    selectStep(i + 1);

                    var q = "step" + (i + 1);
                    if (q == 'step1') {
                    }
                    if (q == 'step2') {
                    }
                    if (q == 'step3') {
                    }
                    if (q == 'step4') {
                    }
                }

            });
        }

        function selectStep(i) {
            $("#steps li").removeClass("current");
            $("#stepDesc" + i).addClass("current");
        }

        function validateAnswer(stepName){
            var validation_flag = true;

            $('#'+stepName +' fieldset.question_answer_fieldset .erp-rec-form-field' ).each(function(){
                if ( $( this ).find( '.required' ).length > 0 ) {
                    var inputType = $( this ).find( 'input' ).attr('type');
                    var selectInput = $( this ).find( 'select' );
                    var textareaInput = $( this ).find( 'textarea' );
                    if ( inputType == "text" ) {
                        txtValue = $( this ).find( 'input' ).val();
                        if ( txtValue == "" ) {
                            alertify.error('Please answer required question properly');
                            validation_flag = false;
                        }
                    } else if ( inputType == "radio" ) {
                        var radios = $( this ).find( 'input[type="radio"]' );
                        var radioValid = true;
                        var i = 0;
                        while ( radioValid && i < radios.length ) {
                            if ( radios[i].checked ) {
                                radioValid = false;
                            }
                            i++;
                        }
                        if ( radioValid ) {
                            alertify.error('Please answer required question properly');
                            validation_flag = false;
                        }
                    } else if ( inputType == "checkbox" ) {
                        var checkboxes = $( this ).find( 'input[type="checkbox"]' );
                        var checkboxesValid = true;
                        var j = 0;
                        while ( checkboxesValid && j < checkboxes.length ) {
                            if ( checkboxes[j].checked ) {
                                checkboxesValid = false;
                            }
                            j++;
                        }
                        if ( checkboxesValid ) {
                            alertify.error('Please answer required question properly');
                            validation_flag = false;
                        }
                    } else if ( textareaInput.length > 0 ) {
                        txtareaValue = $( this ).find( 'textarea' ).val();
                        if ( txtareaValue == "" ) {
                            alertify.error('Please answer required question properly');
                            validation_flag = false;
                        }
                    }
                }
            });

            return validation_flag;

        }

    }
})(jQuery);