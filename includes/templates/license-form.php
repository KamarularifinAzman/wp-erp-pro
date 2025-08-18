<style>
    .license_page-wrap {
        padding: 20px;

    }

    .license_page-wrap h3, h4, h5 {
        font-family: Helvetica;
        color: #000000;

    }

    .license_page-wrap p{
        font-family: Helvetica;
        color: #000000;
        text-align: center;

    }

    .license_page-wrap .head-section h4 {
        font-size: 18px;
        letter-spacing: 0.16px;
        font-weight: 700;
        margin-bottom: 0px;
        text-align: center;

    }

    .license_page-wrap .head-section p {
        font-size: 13px;
        letter-spacing: 0.12px;
        line-height: 21px;
        font-weight: 400;
        margin-bottom: 37px;

    }

    .license_page-wrap .form_wrap {
        background: #FFFFFF;
        border: 1px solid #E5E4E4;
        border-radius: 3px;
        width: 635px;
        height: auto;
        margin: 0 auto;
        padding: 40px 50px;

    }

    .license_page-wrap .form_wrap h3 {
        font-size: 24px;
        letter-spacing: 0.22px;
        font-weight: 700;
        margin: 0 0 43px;

    }

    .license_page-wrap .form_group {
        margin-bottom: 30px;

    }

    .license_page-wrap .form_group label {
        font-family: Helvetica;
        font-size: 13px;
        color: #000000;
        letter-spacing: 0.12px;
        line-height: 21px;
        display: block;
        margin-bottom: 11px;

    }

    .license_page-wrap .form_group input[type="text"], input[type="email"], select {
        background: #FBFBFB;
        border: 1px solid #E5E4E4;
        border-radius: 3px;
        width: 100%;
        height: 37px;
        font-family: Helvetica;
        font-size: 12px;
        color: #B5C0C3;
        letter-spacing: 0.11px;
        outline: none

    }

    .license_page-wrap .form_group select {
        max-width: 100%;
        background: #FBFBFB;
        border: 1px solid #E5E4E4;
        font-family: Helvetica;
        font-size: 12px;
        color: #B5C0C3;
        letter-spacing: 0.11px;

    }

    .license_page-wrap .form_group input[type="submit"] {
        background: #1A9ED4;
        border-radius: 3px;
        font-family: Helvetica;
        font-size: 12px;
        color: #FFFFFF;
        letter-spacing: 0.11px;
        text-align: center;
        border: 0;
        width: 105px;
        height: 29px;
        margin-top: 15px;
        cursor: pointer;
        outline: none
    }

    .license_page-wrap .save_btn {
        margin-bottom: 8px;
        text-align: right;
    }

    /* Mobile Responsive */
    @media(max-width: 767px) {
        .license_page-wrap {
            padding: 0 16px 0 0;
            margin: 0;
        }

        .license_page-wrap .form_wrap {
            width: 100%;
            padding: 0px;

        }

        .license_page-wrap .form_wrap form {
            padding: 20px;

        }

        .error p {
            word-break: break-all;
        }

    }

    @media(min-width: 768px) and (max-width: 1024px) {

        .license_page-wrap .form_wrap {
            padding: 30px;

        }
    }
</style>

<div class="license_page-wrap">
    <div class="license__page-section">
        <div class="head-section">
            <h4><?php _e( 'Activate License', 'erp-pro' ); ?></h4>
            <p><?php _e( 'Activate your license to enable the Pro features & extensions you have purchased. It is also <br>required to get regular plugin updates and premium support.', 'erp-pro' ); ?></p>
        </div>
        <div class="form_wrap">
            <form method="post">
                <h3><?php _e('WP ERP Pro', 'erp-pro'); ?></h3>
                <?php
                if ( $errors ) {
                    foreach ( $errors as $error ) {
                ?>
                    <div class="error"><p><?php echo $error; ?></p></div>
                <?php
                    }
                }
                ?>
                <div class="form_group">
                    <label for="email"><?php _e( 'E-mail Address', 'erp-pro' ); ?></label>
                    <input type="email" name="email" id="email" value="<?php echo esc_attr( $email ); ?>" class="form_field" placeholder="<?php _e( 'Enter your purchase Email address', 'erp-pro' ); ?>">
                </div>
                <div class="form_group">
                    <label for="license-key"><?php _e( 'License Key', 'erp-pro' ); ?></label>
                    <input type="text" name="license_key" value="<?php echo esc_attr( $key ); ?>" id="license_key" class="form_field" placeholder="<?php _e( 'Enter your license key', 'erp-pro' ); ?>">
                </div>
                <div class="form_group">
                    <label for="subscription-type"><?php _e( 'Subscription Type', 'erp-pro' ); ?></label>
                    <select name="subscription_type" id="subscription-type">
                        <option value="monthly" <?php selected( $subscription_type, 'monthly') ?>><?php _e( 'Monthly', 'erp-pro' ); ?></option>
                        <option value="yearly" <?php selected( $subscription_type, 'yearly') ?>><?php _e( 'Yearly', 'erp-pro'); ?></option>
                    </select>
                </div>
                <div class="form_group save_btn">
                    <?php submit_button( __( 'Save & Activate', 'erp-pro' ), 'primary', 'submit', false ); ?>
                </div>
            </form>
        </div>
    </div>
</div>
