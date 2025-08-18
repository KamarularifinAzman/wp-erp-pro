;
(function ($) {
    $.fn.openingFormToWizard = function (options) {
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
        $(element).before("<ul id='steps'></ul>");

        if (steps.length > 1) {
            steps.each(function (i) {
                $(this).wrap("<div class='step-list' id='step" + i + "'></div>");
                $(this).append("<p class='step-command-list' id='step" + i + "commands'></p>");

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
            $("#" + stepName + "commands").append("<a href='#' id='" + stepName + "Prev' class='prev'>< Back</a>");

            $("#" + stepName + "Prev").on("click", function (e) {
                e.preventDefault();

                $("#" + stepName).hide();
                $("#step" + (i - 1)).show();
                $(submmitButtonName).hide();
                /**/
                if ( $('#jobseeker_form').length == 1 ) {
                    $('html, body').animate({
                        scrollTop: $("#step" + (i - 1)).offset().top
                    }, 2000);
                }
                /**/
                selectStep(i - 1);
            });
        }

        function createNextButton(i) {
            var stepName = "step" + i;
            $("#" + stepName + "commands").append("<a href='#' id='" + stepName + "Next' class='next'>Next ></a>");

            $("#" + stepName + "Next").on("click", function (e) {
                e.preventDefault();

                $("#" + stepName).hide();
                $("#step" + (i + 1)).show();
                /**/
                if ( $('#jobseeker_form').length == 1 ) {
                    $('html, body').animate({
                        scrollTop: $("#step" + (i + 1)).offset().top
                    }, 2000);
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
            });
        }

        function selectStep(i) {
            $("#steps li").removeClass("current");
            $("#stepDesc" + i).addClass("current");

            $("#step-list-np li").removeClass("current-active");
            $("#step-list-np li:eq(" + i +")").addClass("current-active");

            $("#step-hndle").text( $("#step-list-np li:eq(" + i +") span.ltext").text() );
        }

    }
})(jQuery);