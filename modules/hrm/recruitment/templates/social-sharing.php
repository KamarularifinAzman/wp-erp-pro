<div id="social_thing">
    <h6><?php _e('Share this job', 'erp-pro'); ?></h6>

    <div id="fb-root"></div>
    <script>(function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s);
        js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
    <?php $current_job_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";; ?>
    <div class="fb-share-button" data-href="<?php echo $current_job_link; ?>" data-layout="button"></div>

    <a href="<?php echo $current_job_link; ?>" class="twitter-share-button" data-via="-">Tweet</a>
    <script>
        !function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
            if (!d.getElementById(id)) {
                js = d.createElement(s);
                js.id = id;
                js.src = p + '://platform.twitter.com/widgets.js';
                fjs.parentNode.insertBefore(js, fjs);
            }
        }(document, 'script', 'twitter-wjs');
    </script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script src="https://apis.google.com/js/platform.js" async defer></script>

    <!-- Place this tag where you want the share button to render. -->
    <div class="g-plus" data-action="share" data-annotation="none" data-height="24" data-href="<?php echo $current_job_link; ?>"></div>

    <script src="//platform.linkedin.com/in.js" type="text/javascript"> lang: en_US</script>
    <script type="IN/Share" data-url="<?php echo $current_job_link; ?>"></script>
</div>
