<style>
    .erp_pro-extension_section{
        padding-top: 40px;

    }

    .erp_pro-extension_section h3{
        font-family: Helvetica;
        font-size: 20px;
        color: #000000;
        letter-spacing: 0.18px;
        line-height: 30px;
        font-weight: 700;

    }

    .erp_pro-extension_section .erp_pro_extension_wrap{
        background: #FFFFFF;
        border: 1px solid #E5E4E4;
        border-radius: 0 3px 3px 0;
        display: flex;
        flex-flow: row wrap;
        width: 908px;
        margin: 0 auto;

    }

    .erp_pro-extension_section .license_details{
        flex-basis: 213px;
        max-width: 213px;
        border-right: 1px solid #E5E4E4;
        padding: 4px 39px;
        position: relative;

    }

    .license_details .deactive-license button {
        font-family: Helvetica;
        font-size: 14px;
        color: #787C83;
        letter-spacing: 0.13px;
        text-align: center;
        border: 0;
        background: none;
        text-decoration: underline;
        margin-top: 85px;
    }

    .erp_pro-extension_section .license_details h3{
        margin-bottom: 35px;

    }

    .erp_pro-extension_section .extension-wrap{
        flex-basis: 535px;
        max-width: 535px;
        padding: 4px 39px 36px;

    }

    .erp_pro-extension_section .license_details_item_wrap .single_item {
        display: flex;
        align-items: center;
        margin-bottom: 24px;

    }

    .erp_pro-extension_section .license_details_item_wrap .single_item .icon {
        margin-right: 16px;

    }

    .erp_pro-extension_section .license_details_item_wrap .single_item .desc p {
        font-family: Helvetica;
        font-size: 13.97px;
        color: #787C83;
        letter-spacing: 0.13px;
        line-height: 27px;
        padding: 0;
        margin: 0;

    }

    .erp_pro-extension_section .license_details_item_wrap .single_item .desc p span {
        font-family: Helvetica;
        font-size: 15.13px;
        color: #000000;
        letter-spacing: 0.14px;
        line-height: 20.95px;
        font-weight: 700;
        padding: 0 3px 0 0;

    }

    .erp_pro-extension_section .license_details_item_wrap .single_item .desc p span.status {
        background: #55BA45;
        border-radius: 14.5px;
        font-size: 14px;
        color: #FFFFFF;
        letter-spacing: 0.13px;
        text-align: center;
        line-height: 21px;
        padding: 4px 16px;

    }

    .erp_pro-extension_section .btn-wrap .btn {
        background: #1A9ED4;
        border-radius: 3px;
        font-family: Helvetica;
        font-size: 14px;
        color: #FFFFFF;
        letter-spacing: 0.13px;
        text-align: center;
        border: 0;
        padding: 6px 27px;
        margin-right: 15px;
        cursor: pointer;
        margin-bottom: 10px;
    }

    .erp_pro-extension_section .btn-wrap .upgrade-btn {
        background: none;
        font-family: Helvetica;
        font-size: 14px;
        color: #787C83;
        letter-spacing: 0.13px;
        text-align: center;
        padding: 0;
        text-decoration: none;

    }

    .upgrade-btn img {
        position: relative;
        top: 1px;
    }

    .erp_pro-extension_section .btn-wrap .btn img {
        margin-right: 7px;
        position: relative;
        top: 2px;

    }

    .erp_pro-extension_section .extension_item_wrap {
        display: flex;
        flex-flow: row wrap;
        justify-content: space-between;

    }

    .erp_pro-extension_section .extension_item_wrap .extension_item {
        display: flex;
        align-items: center;
        flex-basis: 48%;
        margin-bottom: 2px;

    }

    .erp_pro-extension_section .extension_item_wrap .extension_item .extension_image{
        margin-right: 15px;

    }

    .erp_pro-extension_section .extension_item_wrap .extension_item .extension_name h4 a{
        font-family: Helvetica;
        font-size: 14px;
        color: #000000;
        letter-spacing: 0;
        line-height: 21px;
        margin: 0;
        font-weight: 700;
        text-decoration: none;

    }

    .erp_pro-extension_section .purchases-extension{
        margin-top: 40px;
    }

    /* Mobile Responsive */
    @media(max-width: 767px) {
        .erp_pro-extension_section .erp_pro_extension_wrap {
            display: block;
            width: 100%;
            flex-flow: inherit;

        }

        .erp_pro-extension_section .license_details {
            flex-basis: 100%;
            max-width: 100%;
            border-right: 0px solid #E5E4E4;
            padding: 20px 20px 80px;
            border-bottom: 1px solid #E5E4E4;
        }

        .license_details .deactive-license {
            left: 14px;
        }

        .erp_pro-extension_section .extension-wrap {
            flex-basis: 100%;
            max-width: 100%;
            padding: 20px;

        }

        .erp_pro-extension_section .extension_item_wrap .extension_item {
            flex-basis: 100%;
        }
    }




</style>

<div class="erp_pro-extension_section">
    <div class="erp_pro_extension_wrap">
        <div class="license_details">
            <h3><?php _e('WP ERP Pro', 'erp-pro'); ?> <br> License Details</h3>
            <div class="license_details_item_wrap">
                <div class="single_item">
                    <div class="icon">
                        <img src="<?php echo ERP_PRO_PLUGIN_ASSEST . '/images/license/subscription-type-icon.svg'?>" alt="Subscript Type">
                    </div>
                    <div class="desc">
                        <p>Subscription Status <br><span class="date"><?php echo ucfirst( $subscription_type ); ?></span> <span class="status"><?php echo ucfirst( $this->get_subscription_status() ); ?></span></p>
                    </div>
                </div>
                <div class="single_item">
                    <div class="icon">
                        <img src="<?php echo ERP_PRO_PLUGIN_ASSEST . '/images/license/license-date-icon.svg'?>" alt="Date Icon">
                    </div>
                    <div class="desc">
                        <p>Renewal Date<br> <span class="date"><?php echo $this->get_subscription_expire_date(); ?></span></p>
                    </div>
                </div>
                <div class="single_item">
                    <div class="icon">
                        <img src="<?php echo ERP_PRO_PLUGIN_ASSEST . '/images/license/license-user-icon.svg'?>" alt="User Icon">
                    </div>
                    <div class="desc">
                        <p>Number of user<br> <span class="date"><?php echo $this->get_licensed_user(); ?></span></p>
                    </div>
                </div>
            </div>
            <div class="btn-wrap">
                <form method="post">
                    <button class="btn sync-btn" type="submit" name="submit">Sync</button>
                    <a target="_blank" class="upgrade-btn" href="<?php echo $upgrade_link ;?>"> <img src="<?php echo ERP_PRO_PLUGIN_ASSEST . '/images/license/license-upgrade-icon.svg'?>" alt="Upgrade Icon"> Upgrade</a>
                </form>
            </div>


            <div class="btn-wrap deactive-license">
                <form method="post">
                    <button type="submit" name="deactivate_license">Deactivate License</button>
                </form>
            </div>
        </div>

        <div class="extension-wrap">
            <div class="included_extension">
                <h3>Included Extensions</h3>
                <div class="extension_item_wrap">
                    <?php  foreach ( $pro_modules as $single_module ): ?>
                    <!-- single extension -->
                    <div class="extension_item">
                        <div class="extension_image">
                            <img src="<?php echo esc_url_raw( $single_module['icon'] ); ?>" alt="<?php echo esc_attr( $single_module['name'] ); ?>">
                        </div>
                        <div class="extension_name">
                            <h4><a href="<?php echo esc_url_raw( $single_module['url'] ); ?>"><?php echo esc_attr( $single_module['name'] ); ?></a></h4>
                        </div>
                    </div>
                    <!-- end single extension -->
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ( ! empty( $extension_purchased ) ): ?>
            <div class="purchases-extension">
                <h3>Extra Extension Purchased</h3>

                <div class="extension_item_wrap">
                    <?php  foreach ( $extension_purchased as $single_module ): ?>
                        <!-- single extension -->
                        <div class="extension_item">
                            <div class="extension_image">
                                <img src="<?php echo esc_url_raw( $single_module['icon'] ); ?>" alt="<?php echo esc_attr( $single_module['name'] ); ?>">
                            </div>
                            <div class="extension_name">
                                <h4><a href="<?php echo esc_url_raw( $single_module['url'] ); ?>"><?php echo esc_attr( $single_module['name'] ); ?></a></h4>
                            </div>
                        </div>
                        <!-- end single extension -->
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
