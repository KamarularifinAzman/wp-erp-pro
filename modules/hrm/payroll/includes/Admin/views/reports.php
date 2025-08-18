<div class="wrap">
    <h2><?php _e( 'Reports', 'erp-pro' );?></h2>

    <div id="dashboard-widgets-wrap">

        <div id="dashboard-widgets" class="metabox-holder">

            <div class="postbox-container">
                <div class="meta-box-sortables">

                    <div class="postbox">
                        <h2 class="hndle"><span><?php _e( 'Pay Run by Employee', 'erp-pro' ); ?></span></h2>
                        <div class="inside">
                            <p><?php _e( 'Pay Run report detail by employee', 'erp-pro' ); ?></p>
                            <p><a class="button button-primary" href="<?php echo erp_payroll_get_admin_link( 'reports', [ 'type' => 'payrun-employee' ] ) ?>"><?php _e( 'View Report', 'erp-pro' ); ?></a></p>
                        </div>
                    </div><!-- .postbox -->

                </div><!-- .meta-box-sortables -->
            </div><!-- .postbox-container -->

            <div class="postbox-container">
                <div class="meta-box-sortables">

                    <div class="postbox">
                        <h2 class="hndle"><span><?php _e( 'Pay Run Summary', 'erp-pro' ); ?></span></h2>
                        <div class="inside">
                            <p><?php _e( 'Pay Run Summary reports', 'erp-pro' ); ?></p>
                            <p><a class="button button-primary" href="<?php echo erp_payroll_get_admin_link( 'reports', [ 'type' => 'payrun-summary' ] ) ?>"><?php _e( 'View Report', 'erp-pro' ); ?></a></p>
                        </div>
                    </div><!-- .postbox -->

                </div><!-- .meta-box-sortables -->
            </div><!-- .postbox-container -->

        </div><!-- .metabox-holder -->
    </div><!-- .dashboar-widget-wrap -->

</div>
