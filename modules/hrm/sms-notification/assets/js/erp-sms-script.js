(function($){
    $(document).ready(function(){
        $('#hr-announcement-sms-check').on('click', function(){
            $('#hr-announcement-sms-body').toggle();
            $('#hr-announcement-sms-body textarea').val($('input[name=post_title]').val());
        });
    });
})(jQuery);