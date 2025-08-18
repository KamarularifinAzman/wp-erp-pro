<?php
namespace WeDevs\AdvancedLeave\Halfday;

// don't call the file directly
if ( ! defined('ABSPATH') ) {
    exit;
}

class Halfday {

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        $this->include_files();

        add_filter( 'erp_settings_hr_leave_section_fields', array( $this, 'leave_settings_fields' ) );

        if ( get_option('erp_pro_half_leave') !== 'yes' ) {
            return;
        }

        add_action( 'erp-hr-leave-policy-form-bottom', array( $this, 'policy_halfday_field' ) );
        add_filter( 'erp_hr_leave_insert_policy_extra', array( $this, 'prepare_insert_data' ) );

        add_action( 'erp_hr_leave_request_form_middle', array( $this, 'request_halfday_field' ) );
        add_action( 'wp_ajax_erp_pro_hr_check_halfday_availability', array( $this, 'check_halfday_availability' ) );

        add_filter( 'erp_hr_leave_new_args', array( $this, 'add_halfday_args_in_request' ) );
    }

    /**
     * Include files
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function include_files() {
        require_once ERP_PRO_MODULE_DIR . '/pro/advanced-leave/includes/Halfday/functions.php';
    }

    /**
     * Halfday policy field view
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function policy_halfday_field( $leave_policy ) { ?>
        <div class="form-group">
            <div class="row">
                <?php
                    erp_html_form_input(array(
                        'label'       => esc_html__('Enable Halfday Leave', 'erp'),
                        'name'        => 'enable-halfday',
                        'type'        => 'checkbox',
                        'value'       => ! empty( $leave_policy ) ? $leave_policy->halfday_enable : '',
                        'custom_attr' => [
                            'checked' => ! empty( $leave_policy ) && $leave_policy->halfday_enable ? true : false
                        ]
                    ));
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Halfday request field view
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function request_halfday_field() {
        include_once ERP_PRO_MODULE_DIR . '/pro/advanced-leave/includes/Halfday/form.php';
    }

    /**
     * Prepare data before insert
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function prepare_insert_data( $data ) {
        $halfday_enable = ! empty( $_POST['enable-halfday'] ) ? sanitize_text_field( wp_unslash( $_POST['enable-halfday'] ) ) : '';

        if ( $halfday_enable === 'on' ) {
            $data['halfday_enable'] = true;
        }

        return $data;
    }

    /**
     * Check policy halfday availability
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function check_halfday_availability() {
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'erp-pro-nonce' ) ) {
            wp_send_json_error( __( 'Nonce verification failed!', 'erp-pro' ) );
        }

        $entitle_id = ! empty( $_GET['entitle_id'] ) ? absint( wp_unslash( $_GET['entitle_id'] ) ) : 0;

        $available = erp_pro_hr_leave_check_halfday_availability( $entitle_id );

        if ( $available ) {
            wp_send_json_success( true );
        }

        wp_send_json_success( false ); // not an error
    }

    /**
     * Add halfday args in leave request
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function add_halfday_args_in_request( $data ) {
        $halfday = isset( $_POST['halfday'] ) ? sanitize_text_field( wp_unslash( $_POST['halfday'] ) ) : '';
        $leave_period = isset( $_POST['leave-period'] ) ? sanitize_text_field( wp_unslash( $_POST['leave-period'] ) ) : '';

        if ( $halfday === 'on' ) {
            $data['days'] = .5;
            $data['day_status_id'] = $leave_period;
        }

        return $data;
    }

    /**
     * leave settings fields
     *
     * @since 1.0.0
     *
     * @param $fields array
     *
     * @return array
     */
    public function leave_settings_fields($fields) {
        $fields['leave'][] = [
            'title' => esc_html__( 'Enable Half-Day Request', 'erp-pro' ),
            'type'  => 'checkbox',
            'id'    => 'erp_pro_half_leave',
            'desc'  => esc_html__( 'Request leave in the morning or evening time of the day.', 'erp-pro' )
        ];

        return $fields;
    }
}
