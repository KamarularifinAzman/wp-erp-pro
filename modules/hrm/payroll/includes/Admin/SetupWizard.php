<?php
/**
 * Setup wizard class
 *
 * Walkthrough to the basic setup upon installation
 *
 * @package WP-ERP\Admin
 */

namespace WeDevs\Payroll\Admin;

/**
 * The class
 */
class SetupWizard {

    /** @var string Currenct Step */
    private $step   = '';

    /** @var array Steps for the setup wizard */
    private $steps  = array();

    /**
     * Hook in tabs.
     */
    public function __construct() {

        // if we are here, we assume we don't need to run the wizard again
        // and the user doesn't need to be redirected here
        update_option( 'erp_payroll_setup_wizard_ran', '1' );

        //if ( apply_filters( 'erp_enable_setup_wizard', true ) && current_user_can( 'erp_hr_manager' ) ) {
        //if ( current_user_can( 'erp_hr_manager' ) ) {
            add_action( 'admin_menu', array( $this, 'admin_menus' ) );
            add_action( 'admin_init', array( $this, 'setup_wizard' ) );
        //}
    }

    /**
     * Add admin menus/screens.
     */
    public function admin_menus() {
        add_dashboard_page( '', '', 'erp_hr_manager', 'erp-payroll-setup', '' );
    }

    /**
     * Show the setup wizard
     */
    public function setup_wizard() {
        if ( empty( $_GET['page'] ) || 'erp-payroll-setup' !== $_GET['page'] ) {
            return;
        }

        $this->steps = array(
            'account' => array(
                'name'    =>  __( 'Accounts Setup', 'erp-pro' ),
                'view'    => array( $this, 'setup_step_accounts' ),
                'handler' => array( $this, 'setup_step_accounts_save' )
            ),
            'payment' => array(
                'name'    =>  __( 'Payment Method Setup', 'erp-pro' ),
                'view'    => array( $this, 'setup_step_payment_method' ),
                'handler' => array( $this, 'setup_step_payment_method_save' ),
            ),
            'next_steps' => array(
                'name'    =>  __( 'Ready!', 'erp-pro' ),
                'view'    => array( $this, 'setup_step_ready' ),
                'handler' => ''
            )
        );

        if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) {
            array_shift( $this->steps );
        }

        $this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );
        $suffix     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '';

        wp_enqueue_style( 'jquery-ui', WPERP_ASSETS . '/vendor/jquery-ui/jquery-ui-1.9.1.custom.css' );
        wp_enqueue_style( 'erp-setup', WPERP_ASSETS . '/css/setup.css', array( 'dashicons', 'install' ) );
        wp_enqueue_style( 'erp-payroll-style', WPERP_PAYROLL_ASSETS . '/css/stylesheet.css' );

        wp_register_script( 'erp-select2', WPERP_ASSETS . '/vendor/select2/select2.full.min.js', false, false, true );
        wp_register_script( 'erp-setup', WPERP_ASSETS . "/js/erp$suffix.js", array( 'jquery', 'jquery-ui-datepicker', 'erp-select2' ), date( 'Ymd' ), true );

        if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
            call_user_func( $this->steps[ $this->step ]['handler'] );
        }

        ob_start();
        $this->setup_wizard_header();
        $this->setup_wizard_steps();
        $this->setup_wizard_content();
        $this->setup_wizard_footer();
        exit;
    }

    public function get_next_step_link() {
        $keys = array_keys( $this->steps );
        return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ], remove_query_arg( 'translation_updated' ) );
    }

    /**
     * Setup Wizard Header
     */
    public function setup_wizard_header() {
        ?>
        <!DOCTYPE html>
        <html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width" />
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php _e( 'WP ERP Payroll &rsaquo; Setup Wizard', 'erp-pro' ); ?></title>
            <?php wp_print_scripts( 'erp-setup' ); ?>
            <?php do_action( 'admin_print_styles' ); ?>
            <?php //do_action( 'admin_head' ); ?>
        </head>
        <body class="erp-setup wp-core-ui">
            <h1 class="erp-logo"><a href="http://wperp.com/">WP ERP</a></h1>
            <h3 class="erp-logo">ERP Payroll</h3>
        <?php
    }

    /**
     * Setup Wizard Footer
     */
    public function setup_wizard_footer() {
    ?>
        <?php if ( 'next_steps' === $this->step ) : ?>
            <a class="erp-return-to-dashboard" href="<?php echo esc_url( erp_payroll_get_admin_link() ); ?>">
                <?php _e( 'Return to the Payroll Dashboard', 'erp-pro' ); ?>
            </a>
        <?php endif; ?>
        </body>
    </html>
    <?php
        $this->arrange_setup_steps_css_width();
        $this->arrange_setup_payment_method();
    }

    /**
     * Arrage width of setup steps by css and jquery
     */
    public function arrange_setup_steps_css_width() {
        ?>
        <script type="text/javascript">
            jQuery(function($) {
                var list = $('.erp-payroll-setup-steps');
                var listCounter = list.children().length;
                if ( listCounter === 2 ) {
                    list.children().css( { "width" : '50%' } );
                } else if ( listCounter === 3 ) {
                    list.children().css( { "width" : '33%' } );
                } else if ( listCounter === 4 ) {
                    list.children().css( { "width" : '25%' } );
                }
                //reduce radio input field width
                $( '.tiny-radio' ).css( { 'width': '19%' } );
            });
        </script>
        <?php
    }

    /**
     * Arrage setup payment method
     */
    public function arrange_setup_payment_method() {
        ?>
        <script type="text/javascript">
            jQuery(function($) {
                function bankShowAndHide( pay_method ) {
                    if ( $('.bank-selector').length ) {
                        if ( 'cash' !== pay_method ) {
                            $('.bank-selector').removeClass('hide');
                        } else {
                            $('.bank-selector').addClass('hide');
                        }
                    }
                }

                if ( $('.erp-setup-content').length ) {
                    bankShowAndHide( $('input[name=payment_method]:checked').val() );

                    $('input[name=payment_method]').change(function() {
                        bankShowAndHide( $(this).val() );
                    });
                }
            });
        </script>
        <?php
    }

    /**
     * Output the steps
     */
    public function setup_wizard_steps() {
        $ouput_steps = $this->steps;
        //array_shift( $ouput_steps );
        ?>
        <ol class="erp-setup-steps erp-payroll-setup-steps">
            <?php foreach ( $ouput_steps as $step_key => $step ) : ?>
                <li class="<?php
                    if ( $step_key === $this->step ) {
                        echo 'active';
                    } elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
                        echo 'done';
                    }
                ?>"><?php echo esc_html( $step['name'] ); ?></li>
            <?php endforeach; ?>
        </ol>
        <?php
    }

    /**
     * Output the content for the current step
     */
    public function setup_wizard_content() {
        echo '<div class="erp-setup-content">';
        call_user_func( $this->steps[ $this->step ]['view'] );
        echo '</div>';
    }

    public function next_step_buttons() {
    ?>
    <p class="erp-setup-actions step">
        <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'erp-pro' ); ?>" name="save_step" />
        <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php _e( 'Skip this step', 'erp-pro' ); ?></a>
        <?php wp_nonce_field( 'erp-setup' ); ?>
    </p>
    <?php
    }

    /**
     * Introduction step
     */
    public function setup_step_introduction() {
        ?>
        <h1><?php _e( 'Welcome to ERP Payroll!', 'erp-pro' ); ?></h1>
        <p><?php _e( 'Thank you for choosing ERP-Payroll. An easier way to manage your employee salary! This quick setup wizard will help you configure the basic settings. <strong>It’s completely optional and shouldn’t take longer than two minutes.</strong>', 'erp-pro' ); ?></p>
        <p><?php _e( 'No time right now? If you don’t want to go through the wizard, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!', 'erp-pro' ); ?></p>
        <p class="erp-setup-actions step">
            <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button-primary button button-large button-next"><?php _e( 'Let\'s Go!', 'erp-pro' ); ?></a>
            <a href="<?php echo esc_url( wp_get_referer() ? wp_get_referer() : admin_url( 'plugins.php' ) ); ?>" class="button button-large"><?php _e( 'Skip', 'erp-pro' ); ?></a>
        </p>
        <?php
    }

    public function setup_step_accounts() {
        $general    = get_option( 'erp_settings_general', array() );
        $financial_month = isset( $general['gen_financial_month'] ) ? $general['gen_financial_month'] : '1';
        ?>
        <h1><?php _e( 'Accounts Setup', 'erp-pro' ); ?></h1>
        <?php $assets_head = get_option('erp_payroll_account_head_assets');?>
        <?php $salary_head = get_option('erp_payroll_account_head_salary');?>
        <?php $salary_tax_head = get_option('erp_payroll_account_head_salary_tax');?>
        <form method="post">

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="gen_financial_month"><?php _e( 'Account head for Assets', 'erp-pro' ); ?></label>
                    </th>
                    <td>
                        <div class="row">
                            <?php
                            $dropdown      = erp_ac_get_chart_dropdown( [
                                'exclude' => [ 3, 2, 4, 5 ]
                            ] );
                            ?>
                            <select name="account_head_assets">
                                <?php if ( is_array($dropdown) && count($dropdown) > 0 ) : ?>
                                    <?php foreach ( $dropdown as $key => $value ) : ?>
                                        <?php if ( is_array($value) && count($value) > 0 ) : ?>
                                            <?php foreach ( $value as $inner_key => $inner_value ) : ?>
                                                <?php if ( is_array($inner_value) && count($inner_value) > 0 ) : ?>
                                                    <?php foreach ( $inner_value as $ik => $iv ) : ?>
                                                        <?php if ( $assets_head == $iv->id ) : ?>
                                                            <option value="<?php echo $iv->id;?>" selected="selected">
                                                                <?php echo $iv->name;?>
                                                            </option>
                                                        <?php else :?>
                                                            <option value="<?php echo $iv->id;?>">
                                                                <?php echo $iv->name;?>
                                                            </option>
                                                        <?php endif;?>
                                                    <?php endforeach;?>
                                                <?php endif;?>
                                            <?php endforeach;?>
                                        <?php endif;?>
                                    <?php endforeach;?>
                                <?php endif;?>
                            </select>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="gen_financial_month"><?php _e( 'Account head for salary reporting', 'erp-pro' ); ?></label>
                    </th>
                    <td>
                        <div class="row">
                            <?php
                                $dropdown      = erp_ac_get_chart_dropdown( [
                                    'exclude' => [ 1, 2, 4, 5 ]
                                ] );
                            ?>
                            <select name="account_head_salary">
                                <?php if ( is_array($dropdown) && count($dropdown) > 0 ) : ?>
                                    <?php foreach ( $dropdown as $key => $value ) : ?>
                                        <?php if ( is_array($value) && count($value) > 0 ) : ?>
                                            <?php foreach ( $value as $inner_key => $inner_value ) : ?>
                                                <?php if ( is_array($inner_value) && count($inner_value) > 0 ) : ?>
                                                    <?php foreach ( $inner_value as $ik => $iv ) : ?>
                                                        <?php if ( $salary_head == $iv->id ) : ?>
                                                            <option value="<?php echo $iv->id;?>" selected="selected">
                                                                <?php echo $iv->name;?>
                                                            </option>
                                                        <?php else :?>
                                                            <option value="<?php echo $iv->id;?>">
                                                                <?php echo $iv->name;?>
                                                            </option>
                                                        <?php endif;?>
                                                    <?php endforeach;?>
                                                <?php endif;?>
                                            <?php endforeach;?>
                                        <?php endif;?>
                                    <?php endforeach;?>
                                <?php endif;?>
                            </select>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="gen_com_start"><?php _e( 'Account head for tax reporting', 'erp-pro' ); ?></label>
                    </th>
                    <td>
                        <div class="row">
                            <?php
                            $dropdown      = erp_ac_get_chart_dropdown( [
                                'exclude' => [ 1, 2, 4, 5 ]
                            ] );
                            ?>
                            <select name="account_head_salary_tax">
                                <?php if ( is_array($dropdown) && count($dropdown) > 0 ) : ?>
                                    <?php foreach ( $dropdown as $key => $value ) : ?>
                                        <?php if ( is_array($value) && count($value) > 0 ) : ?>
                                            <?php foreach ( $value as $inner_key => $inner_value ) : ?>
                                                <?php if ( is_array($inner_value) && count($inner_value) > 0 ) : ?>
                                                    <?php foreach ( $inner_value as $ik => $iv ) : ?>
                                                        <?php if ( $salary_tax_head == $iv->id ) : ?>
                                                            <option value="<?php echo $iv->id;?>" selected="selected">
                                                                <?php echo $iv->name;?>
                                                            </option>
                                                        <?php else : ?>
                                                            <option value="<?php echo $iv->id;?>">
                                                                <?php echo $iv->name;?>
                                                            </option>
                                                        <?php endif;?>
                                                    <?php endforeach;?>
                                                <?php endif;?>
                                            <?php endforeach;?>
                                        <?php endif;?>
                                    <?php endforeach;?>
                                <?php endif;?>
                            </select>
                        </div>
                    </td>
                </tr>
            </table>

            <?php $this->next_step_buttons(); ?>
        </form>
        <?php
    }

    public function setup_step_accounts_save() {
        check_admin_referer( 'erp-setup' );

        $account_head_assets     = sanitize_text_field( $_POST['account_head_assets'] );
        $account_head_salary     = sanitize_text_field( $_POST['account_head_salary'] );
        $account_head_salary_tax = sanitize_text_field( $_POST['account_head_salary_tax'] );

        update_option( 'erp_payroll_account_head_assets', $account_head_assets );
        update_option( 'erp_payroll_account_head_salary', $account_head_salary );
        update_option( 'erp_payroll_account_head_salary_tax', $account_head_salary_tax );

        update_option( 'erp_payroll_install_status', 'installed' );

        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    public function setup_step_payment_method() {
        ?>
        <h1><?php _e( 'Payment Methods Setup', 'erp-pro' ); ?></h1>
        <?php $payment_method = get_option('erp_payroll_payment_method_settings', 'cash'); ?>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="gen_financial_month"><?php _e( 'Select a method', 'erp-pro' ); ?></label></th>
                    <td>
                        <ul class="list-inline">
                            <?php if ( isset($payment_method) && $payment_method == 'cash' ) : ?>
                                <li>
                                    <label><input type="radio" name="payment_method" group="pm" value="cash" checked="checked"/><?php _e( 'Cash', 'erp-pro');?></label>
                                </li>
                            <?php else : ?>
                                <li>
                                    <label><input type="radio" name="payment_method" group="pm" value="cash"/><?php _e( 'Cash', 'erp-pro');?></label>
                                </li>
                            <?php endif;?>
                            <?php if ( isset($payment_method) && $payment_method == 'cheque' ) : ?>
                                <li>
                                    <label><input type="radio" name="payment_method" group="pm" value="cheque" checked="checked"/><?php _e( 'Cheque', 'erp-pro');?></label>
                                </li>
                            <?php else : ?>
                                <li>
                                    <label><input type="radio" name="payment_method" group="pm" value="cheque"/><?php _e( 'Cheque', 'erp-pro');?></label>
                                </li>
                            <?php endif; ?>
                            <?php if ( isset($payment_method) && $payment_method == 'bank' ) : ?>
                                <li>
                                    <label><input type="radio" name="payment_method" group="pm" value="bank" checked="checked"/><?php _e( 'Bank', 'erp-pro'); ?></label>
                                </li>
                            <?php else : ?>
                                <li>
                                    <label><input type="radio" name="payment_method" group="pm" value="bank"/><?php _e( 'Bank', 'erp-pro');?></label>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </td>
                </tr>
                <?php if ( version_compare( WPERP_VERSION, '1.5.0', '>=' ) ) { ?>

                    <tr class="bank-selector hide">
                        <th scope="row"><label><?php _e( 'Select a bank', 'erp-pro' ); ?></label></th>
                        <td>
                            <?php
                            $banks = array_column(erp_acct_get_banks(), 'name', 'id');
                            if ( ! empty( $banks ) ) {
                                erp_html_form_input([
                                    'name'    => 'bank_setting',
                                    'id'      => 'bank_setting',
                                    'type'    => 'select',
                                    'options' => $banks,
                                    'value'   => get_option( 'erp_payroll_payment_bank_settings' ),
                                    'help'    => __( 'Please select a bank for payment.', 'erp' ),
                                ]);
                            } else {
                                echo '<p>Please create a bank from <strong>Accounting -> chart of accounts</strong>. And you can always change the settings later.</p>';
                            }
                            ?>
                        </td>
                    </tr>

                <?php } ?>
            </table>
            <?php $this->next_step_buttons(); ?>
        </form>
        <?php
    }

    public function setup_step_payment_method_save() {
        check_admin_referer( 'erp-setup' );

        $payment_method = $_POST['payment_method'];
        $bank_setting   = ! empty( $_POST['bank_setting'] ) ?  $_POST['bank_setting'] : null;

        update_option( 'erp_payroll_payment_method_settings', $payment_method );
        update_option( 'erp_payroll_payment_bank_settings', $bank_setting );

        update_option( 'erp_payroll_install_status', 'installed' );

        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    public function setup_step_ready() {
        ?>

        <div class="final-step">
            <h1><?php _e( 'Your payroll system is ready!', 'erp-pro' ); ?></h1>

            <div class="erp-setup-next-steps">
                <div class="erp-setup-next-steps-first">
                    <h2><?php _e( 'Next Steps &rarr;', 'erp-pro' ); ?></h2>

                    <a class="button button-primary button-large" href="<?php echo esc_url( erp_payroll_get_admin_link( 'calendar' ) ); ?>">
                        <?php _e( 'Add Pay Calendar to pay!', 'erp-pro' ); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
}
