<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,600,700" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo ERP_DASHBOARD_ASSETS; ?>/css/bulma.css">
    <link rel="stylesheet" href="<?php echo ERP_DASHBOARD_ASSETS; ?>/css/checkradio.css">
    <link rel="stylesheet" href="<?php echo ERP_DASHBOARD_ASSETS; ?>/css/lib/fullcalendar.min.css">
    <link rel="stylesheet" href="<?php echo ERP_DASHBOARD_ASSETS; ?>/css/lib/jquery-ui-1.9.2.custom.min.css">
    <link rel="stylesheet" href="<?php echo ERP_DASHBOARD_ASSETS; ?>/css/lib/noty.css">
    <link rel="stylesheet" href="<?php echo ERP_DASHBOARD_ASSETS; ?>/css/style.css">

    <?php
    if (defined('WPERP_DOC_URL')) {
        ?>
        <link rel="stylesheet" href="<?php echo WPERP_DOC_URL; ?>/assets/css/frontend.css">
        <?php
    }


    ?>

    <title><?php echo get_erp_dashboard_title() ?></title>
</head>
<body>

<div id="erp-app"></div>

<script>
    <?php global $current_user; ?>
    window.user_id = <?php echo $current_user->ID;?> ;
    window.site_url = '<?php echo site_url();?>';
    window.rest_nonce = '<?php echo wp_create_nonce( "wp_rest" ); ?>';
    window.erp_dashboard_url = '<?php echo get_erp_dashboard_url(); ?>';
    window.erp_dashboard_asset_url = '<?php echo ERP_DASHBOARD_ASSETS; ?>';
    window.erp_dashboard_logo = '<?php echo get_erp_dashboard_logo(); ?>';
    window.logout_url = '<?php echo esc_url_raw(wp_logout_url()); ?>';
    window.erpHr = JSON.parse('<?php echo addslashes(
        json_encode( apply_filters( 'erp_hr_frontend_localized_data', [] ) )
    ); ?>');
    window.erpHrAtt = <?php echo json_encode( erp_hr_get_attendance_info() ); ?>
</script>

<script src="<?php echo ERP_DASHBOARD_ASSETS; ?>/js/lib/jquery.min.js"></script>
<script src="<?php echo ERP_DASHBOARD_ASSETS; ?>/js/lib/moment.min.js"></script>
<script src="<?php echo ERP_DASHBOARD_ASSETS; ?>/js/lib/jquery-ui.min.js"></script>
<script src="<?php echo ERP_DASHBOARD_ASSETS; ?>/js/lib/noty.min.js"></script>
<script src="<?php echo ERP_DASHBOARD_ASSETS; ?>/js/lib/ckeditor.min.js"></script>
<script src="<?php echo ERP_DASHBOARD_ASSETS; ?>/js/app.js"></script>
<script src="<?php echo ERP_DASHBOARD_ASSETS; ?>/js/script.js"></script>

<?php
if (defined('WPERP_DOC_URL')) {
    $file_upload_nonce = wp_create_nonce('file_upload_nonce');
    ?>
    <script src="<?php echo WPERP_DOC_URL; ?>/assets/js/vendor.js"></script>
    <script src="<?php echo WPERP_DOC_URL; ?>/assets/js/frontend.js"></script>
    <script>
        window.file_upload_nonce = '<?php echo $file_upload_nonce ;?>';
    </script>
    <?php
}
?>
</body>
</html>
