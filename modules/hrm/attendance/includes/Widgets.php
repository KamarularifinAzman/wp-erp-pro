<?php
namespace WeDevs\Attendance;

/**
 * Widgets provided by this plugin
 *
 * @since 1.1.0
 */
class Widgets {
    private $is_scripts_enqueued = false;

    /**
     * Class constructor
     *
     * @since 1.1.0
     *
     * @return void
     */
    function __construct() {
        // Adds an widget in HR dashboard page
        add_action( 'erp_hr_dashboard_widgets_right', [ $this, 'widget_attendance_status' ] );

        // Add self attendance option to employee dashboard
        if ( 'yes' === erp_get_option( 'enable_self_att' ) ) {
            add_action( 'erp_hr_dashboard_widgets_right', [ $this, 'widget_self_checking_checkout' ] );
        }
    }

    /**
     * Register attendance status widget
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function widget_attendance_status() {
        erp_admin_dash_metabox( __( '<i class="fa fa-pie-chart"></i> Attendance Status', 'erp-pro' ), [ $this, 'widget_attendance_status_view' ] );
    }

    /**
     * Register self checking/checkout widget
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function widget_self_checking_checkout() {
        erp_admin_dash_metabox( __( '<i class="fa fa-clock-o"></i> Attendance Self Service', 'erp-pro' ), [ $this, 'widget_self_checking_checkout_view' ] );
    }

    /**
     * Include attendance status view
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function widget_attendance_status_view() {
        $this->enqueue_scripts();
        include WPERP_ATTEND_VIEWS . '/widget-attendance-status.php';
    }

    /**
     * Include self checking/checkout view
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function widget_self_checking_checkout_view() {
        $this->enqueue_scripts();
        include WPERP_ATTEND_VIEWS . '/widget-employee-self-service.php';
    }

    /**
     * Enqueue required scripts for widgets
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function enqueue_scripts() {
        if ( $this->is_scripts_enqueued ) {
            return;
        }

        wp_enqueue_style(
            'erp-attendance-widgets',
            WPERP_ATTEND_ASSETS . '/css/erp-attendance-widgets.css',
            [],
            WPERP_ATTEND_VERSION
        );

        wp_enqueue_script(
            'erp-attendance-widgets',
            WPERP_ATTEND_ASSETS . '/js/erp-attendance-widgets.js',
            [ 'jquery', 'erp-vuejs', 'erp-flotchart', 'erp-flotchart-pie' ],
            WPERP_ATTEND_VERSION,
            true
        );

        // localized data
        $erp_attendance_widgets = [
            'ajaxurl'       => admin_url( 'admin-ajax.php' ),
            'nonce'         => wp_create_nonce( 'wp-erp-attendance' ),
            'scriptDebug'   => defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG : false,
            'i18n'          => [
                'filterBy'          => __( 'Filter By', 'erp-pro' ),
                'today'             => __( 'Today', 'erp-pro' ),
                'yesterday'         => __( 'Yesterday', 'erp-pro' ),
                'thisMonth'         => __( 'This Month', 'erp-pro' ),
                'lastMonth'         => __( 'Last Month', 'erp-pro' ),
                'thisQuarter'       => __( 'This Quarter', 'erp-pro' ),
                'thisYear'          => __( 'This Year', 'erp-pro' ),
                'notEnoughData'     => __( 'Not enough data', 'erp-pro' ),
                'loadingData'       => __( 'Loading data', 'erp-pro' ),
                'noShiftAssigned'   => __( 'No shift is assigned for current time', 'erp-pro' ),
                'checkin'           => __( 'Checkin', 'erp-pro' ),
                'checkout'          => __( 'Checkout', 'erp-pro' ),
                'thanksForCheckin'  => __( 'Your checkin entry is saved. Thank you for checkin.', 'erp-pro' ),
                'thanksForCheckout' => __( 'Your checkout entry is saved. Thank you.', 'erp-pro' ),
                'selectShiftFirst'  => __( 'You must select a shift first!', 'erp-pro' ),
            ]
        ];

        wp_localize_script( 'erp-attendance-widgets', 'erpAttendanceWidgets', $erp_attendance_widgets );

        $this->is_scripts_enqueued = true;
    }

}

