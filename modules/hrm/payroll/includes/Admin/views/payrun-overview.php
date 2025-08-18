<div id="payrun-overview-wrapper" class="wrap erp hrm-dashboard hrm-payroll-dashboard erp-payroll-payitem-detail">
    <h2><?php _e('Payroll Overview','erp-pro');?></h2>
    <div class="erp-grid-container payrun-overview-container">
        <div class="erp-single-container">
            <div class="badge-container">
                <?php if ( is_setup_wizard_done() == false || is_pay_calendar_created() == false || is_payrun_executed_ever() == false ) : ?>
                    <div class="postbox badge-wrap">
                        <h3 class="hndle"><span><?php _e( 'Checklist', 'erp-payrun');?></span></h3>
                        <div class="inside">
                            <ul class="checklist">
                                <li>
                                    <?php if ( is_setup_wizard_done() ) : ?>
                                        <span><i class="fa fa-lg fa-check-square-o"></i></span>
                                    <?php else : ?>
                                        <span><i class="fa fa-lg fa-square-o"></i></span>
                                    <?php endif; ?>
                                    <?php
                                        $redirect_url = add_query_arg( ['page' => 'erp-payroll-setup'], admin_url( 'index.php' ) );
                                    ?>
                                    <span>
                                        <?php _e( 'Setup Wizard', 'erp-pro' );?>
                                    </span>
                                </li>
                                <li>
                                    <?php if ( is_pay_calendar_created() ) : ?>
                                        <span><i class="fa fa-lg fa-check-square-o"></i></span>
                                    <?php else : ?>
                                        <span><i class="fa fa-lg fa-square-o"></i></span>
                                    <?php endif; ?>
                                    <a href="<?php echo erp_payroll_get_admin_link('calendar') ?>">
                                        <?php _e( 'Pay Calendar', 'erp-pro' );?>
                                    </a>
                                </li>
                                <li>
                                    <?php if ( is_payrun_executed_ever() ) : ?>
                                        <span><i class="fa fa-lg fa-check-square-o"></i></span>
                                    <?php else : ?>
                                        <span><i class="fa fa-lg fa-square-o"></i></span>
                                    <?php endif; ?>
                                    <a href="<?php echo erp_payroll_get_admin_link('payrun') ?>">
                                        <?php _e( 'Pay Run', 'erp-pro' );?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                <?php endif;?>

                <?php if ( is_payrun_executed_ever() ) : ?>
                    <div class="badge-wrap badge-green">
                        <?php $total_expense = erp_payroll_get_total_expense();?>
                        <div class="badge-inner">
                            <h3><?php
                                if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
                                    echo erp_acct_get_price( $total_expense );
                                } else {
                                    echo erp_ac_get_price( $total_expense );
                                }
                            ?></h3>
                            <p><?php _e( 'Total Expenses', 'erp-pro' );?></p>
                        </div>
                        <div class="badge-footer wp-ui-highlight">
                            <a href="<?php echo erp_payroll_get_admin_link('reports'); ?>">
                                <?php _e( 'View Detail', 'erp-pro' );?>
                            </a>
                        </div>
                    </div>
                <?php endif;?>

                <div class="badge-wrap badge-green">
                    <?php $total_pay_calendar = erp_payroll_get_total_calendar_created();?>
                    <div class="badge-inner">
                        <h3><?php echo $total_pay_calendar;?></h3>
                        <p><?php _e( 'Total Pay Calendar Created', 'erp-pro' );?></p>
                    </div>
                    <div class="badge-footer wp-ui-highlight">
                        <a href="<?php echo erp_payroll_get_admin_link('calendar'); ?>">
                            <?php _e( 'View Pay Calendar', 'erp-pro' );?>
                        </a>
                    </div>
                </div>

                <div class="badge-wrap badge-green">
                    <?php $total_exection = erp_payroll_get_total_execution_pay_calendar();?>
                    <div class="badge-inner">
                        <h3><?php echo $total_exection;?></h3>
                        <p><?php _e( 'Pay Calendar Approved', 'erp-pro' );?></p>
                    </div>
                    <div class="badge-footer wp-ui-highlight">
                        <a href="<?php echo erp_payroll_get_admin_link('payrun') ?>">
                            <?php _e( 'View Pay Run List', 'erp-pro' );?>
                        </a>
                    </div>
                </div>

                <div class="badge-wrap badge-green">
                    <?php $amount = erp_payroll_get_spent_of_previous_month(); ?>
                    <div class="badge-inner">
                        <h3><?php
                            if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
                                echo erp_acct_get_price( $amount );
                            } else {
                                echo erp_ac_get_price( $amount );
                            }
                        ?></h3>
                        <p><?php _e( 'Spent on Previous Month', 'erp-pro' );?></p>
                    </div>
                    <div class="badge-footer wp-ui-highlight">
                        <a href="<?php echo erp_payroll_get_admin_link('reports') ?>">
                            <?php _e( 'View Detail', 'erp-pro' );?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="erp-single-container payrun-records not-loaded">
            <div class="postbox">
                <h3 class="hndle"><span><i class="fa fa-file-text-o"></i>&nbsp<?php _e( 'Latest 5 Pay Run Records', 'erp-pro' );?></span></h3>
                <div class="inside">
                    <h4 v-if="!listcounter"><?php _e( 'No record found.', 'erp-pro' );?></h4>
                    <ul v-if="listcounter" class="erp-list list-table-like">
                        <li>
                            <label><?php _e( 'Pay Period', 'erp-pro');?></label>
                            <label><?php _e( 'Pay Run', 'erp-pro');?></label>
                            <label><?php _e( 'Payment Date', 'erp-pro');?></label>
                            <label><?php _e( 'Employees', 'erp-pro');?></label>
                            <label><?php _e( 'Net Pay + Tax', 'erp-pro');?></label>
                            <label><?php _e( 'Status', 'erp-pro');?></label>
                            <label class="action-tiny-cell"></label>
                        </li>
                        <li v-for="prdata in payrunlist">
                            <label>{{ prdata.from_date }} - {{ prdata.to_date }}</label>
                            <label>{{ prdata.Pay_Run }}</label>
                            <label>{{ prdata.payment_date }}</label>
                            <label>{{ prdata.effected_employees }}</label>
                            <label>{{ prdata.employees_payment | custom_currency }}</label>
                            <label>{{ prdata.status }}</label>
                            <label class="action-tiny-cell">
                                <a :href="prdata.payrun_link"><span class="dashicons dashicons-visibility"></span></a>
                            </label>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="erp-single-container">
            <?php do_action( 'erp_payroll_overview_widget_left' );?>
        </div>

    </div><!-- erp-grid-container -->
</div>
