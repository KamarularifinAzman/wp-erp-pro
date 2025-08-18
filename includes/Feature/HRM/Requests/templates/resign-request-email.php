<style>
    @import url('https://fonts.googleapis.com/css2?family=Lato:wght@300&display=swap');

    .resign-email-wrap {
        background-color: #EDF7FF;
        font-family: Lato !important;
    }

    .resign-email-body {
        margin: 0 auto;
        font-family: Lato !important;
    }

    .resign-email-title {
        color: #0091FF;
        font-size: 28.2px;
        font-weight: 900;
        letter-spacing: 0;
        line-height: 34px;
        font-family: Lato !important;
    }

    .description {
        color: #515282;
        font-size: 18px;
        letter-spacing: 0;
        line-height: 30px;
        font-family: Lato !important;
    }

    .logo img {
        width: 100px;
        margin-bottom: 30px;
    }

    .login-container {
        text-align: center;
        margin-top: 60px;
    }

    .site_login {
        text-decoration: none;
        color: #FFFFFF;
        background-color: #0091FF;
        font-size: 16px;
        letter-spacing: 0;
        line-height: 19px;
        text-align: right;
        border-radius: 35px;
        padding: 12px 29px;
    }

    .company-info {
        display: flex;
    }

    .company-info div {
        padding-left: 10px;
    }

    .company-info .title {
        color: #0091FF;
        font-size: 20px;
        font-weight: 900;
        letter-spacing: 0;
        line-height: 34px;
        font-family: Lato !important;
    }

    .footer-divider {
        text-align: center;
        margin-top: 45px;
    }
</style>

<?php $company = new \WeDevs\ERP\Company(); ?>

<div class="resign-email-wrap">
    <div class="resign-email-body">
        <div class="line">
            <img src="<?php echo ERP_PRO_PLUGIN_ASSEST  ;?>//images/email-icons/line.svg" alt="">
        </div>

        <div class="header">
            <?php _e( 'Dear Sir,', 'erp-pro' ); ?>
        </div>

        <div class="description">
            <?php echo wp_kses_post( $description ); ?>
        </div>

        <div class="footer">
            <?php _e( 'Best regards,', 'erp-pro' ); ?></br>
            <?php echo $employee->get_full_name(); ?></br>
            <?php echo ! empty( $employee->get_job_id() ) ? __( 'ID: ', 'erp-pro' ) . $employee->get_job_id() : ''; ?></br>
        </div>

        <div class="login-container">
            <a href="<?php echo wp_login_url() ?>" class="site_login"><?php esc_html_e( 'Login Now', 'erp-pro' ) ?></a>
        </div>

        <div class="footer-divider">
            <img src="<?php echo ERP_PRO_PLUGIN_ASSEST  ;?>//images/email-icons/footer_divider.svg" alt="">
        </div>
    </div>
</div>
