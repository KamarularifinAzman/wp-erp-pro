<?php
namespace WeDevs\ERP_PRO\HRM\SmsNotification;

// don't call the file directly
use WeDevs\ERP\SMS\GatewayHandler;
use WeDevs\ERP\SMS\SmsSettings;

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WeDevs ERP SMS Main Class
 */
class Module {

    /**
     * Add-on Version
     *
     * @var  string
     */
    public $version = '1.2.0';


    /**
     * SMS Gateway
     *
     * @since 1.0
     */
    public $gateway;

    /**
     * Initializes the WeDevs_ERP_SMS class
     *
     * Checks for an existing WeDevs_ERP_SMS instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {

            $instance = new self();
        }
        return $instance;
    }

    /**
     * Constructor for the WeDevs_ERP_SMS class
     *
     * Sets up all the appropriate hooks and actions
     *
     * @since 1.0
     *
     * @return void
     */
    private function __construct() {

        // on activate plugin register hook
        add_action( 'erp_pro_activated_module_sms_notification', array( $this, 'activate' ) );

        // on register deactivation hook
        add_action( 'erp_pro_deactivated_module_sms_notification', array( $this, 'deactivate' ) );

        // After loaded erp main files
        add_action( 'erp_loaded', [ $this, 'init_plugin' ] );
    }

    /**
     * Executes while Plugin Activation
     *
     * @since 1.0
     *
     * @return void
     */
    public function activate() {
        // nothing added for now.
    }

    /**
     * Executes while Plugin Activation
     *
     * @since 1.1.1
     *
     * @return void
     */
    public function deactivate() {
        // nothing added for now.
    }

    /**
     * Execute if ERP main is installed
     *
     * @since 1.0
     *
     * @return void
     */
    public function init_plugin() {
        $this->define_constants();
        $this->includes();
        $this->init_classes();
        $this->init_actions();
        $this->init_filters();
    }

    /**
     * Define Add-on constants
     *
     * @since 1.0
     *
     * @return void
     */
    public function define_constants() {
        define( 'WPERP_SMS_VERSION', $this->version );
        define( 'WPERP_SMS_FILE', __FILE__ );
        define( 'WPERP_SMS_PATH', dirname( WPERP_SMS_FILE ) );
        define( 'WPERP_SMS_INCLUDES', WPERP_SMS_PATH . '/includes' );
        define( 'WPERP_SMS_URL', plugins_url( '', WPERP_SMS_FILE ) );
        define( 'WPERP_SMS_ASSETS', WPERP_SMS_URL . '/assets' );
        define( 'WPERP_SMS_VIEWS', WPERP_SMS_PATH . '/views' );
        define( 'WPERP_SMS_LIB', WPERP_SMS_PATH . '/lib' );
    }

    /**
     * Include the required files
     *
     * @since 1.0
     *
     * @return void
     */
    public function includes() {

    }

    /**
     * Instantiate classes
     *
     * @since 1.0
     *
     * @return void
     */
    public function init_classes() {

    }

    /**
     * Initializes action hooks
     *
     * @since 1.0
     *
     * @return  void
     */
    public function init_actions() {
        add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ] );

        // HR Announcement
        add_action( 'hr_announcement_table_last', [ $this, 'announcement_add_option' ] );
        add_action( 'hr_annoucement_save', [ $this, 'announcement_save_option' ], 10, 2 );
        add_action( 'hr_announcement_insert_assignment', [ $this, 'announcement_send_sms' ], 10, 2 );

        // CRM Feed
        add_action( 'erp_crm_load_contact_vue_scripts', [ $this, 'crm_sms_load_scripts' ] );
        add_action( 'erp_crm_load_vue_js_template', [ $this, 'crm_feeds_load_sms_js_template' ] );
        add_action( 'erp_crm_feeds_nav_content', [ $this, 'crm_sms_add_tab_content' ] );
        add_action( 'erp_crm_save_customer_feed_data', [ $this, 'crm_sms_save_sms_feeddata' ] );
        add_action( 'erp_crm_send_schedule_notification', [ $this, 'crm_send_scheduled_sms' ], 10, 2 );

        // Workflow
        add_action( 'erp_wf_send_sms_action', [ $this, 'workflow_send_sms_action' ] );
    }

    /**
     * Initializes action filters
     *
     * @since 1.0
     *
     * @return  void
     */
    public function init_filters() {
        // Settings page filter
        add_filter( 'erp_integration_classes', [ $this, 'add_settings_page' ] );
        // HR Announcement
        add_filter( 'hr_announcement_send_type', [$this, 'assign_announcement_edit_columns'], 10, 2 );

        // Contact Integration
        add_filter( 'erp_crm_customer_feeds_nav', [$this, 'crm_feeds_add_sms_tab'] );
        add_filter( 'erp_crm_activity_schedule_notification_type', [$this, 'crm_activity_notification_type_add'] );
        add_filter( 'erp_crm_format_activity_feed_message', [$this, 'sms_activity_message_format'], 10, 2 );
        add_filter( 'erp_crm_contact_feed_localize_string', [ $this, 'add_localize_string' ] );

        // Workflow
        add_filter( 'erp_workflow_actions_list', [ $this, 'workflow_actions_list' ] );
    }

    /**
     * Register all styles and scripts
     *
     * @since 1.0
     *
     * @return void
     */
    public function register_scripts() {
        wp_enqueue_script( 'erp-sms-script', WPERP_SMS_ASSETS . '/js/erp-sms-script.js' );
        wp_enqueue_style( 'erp-style' );
        wp_enqueue_style( 'erp-fontawesome' );
    }

    /**
     * Register HR settings page
     *
     * @since 1.0
     *
     * @param array
     */
    public function add_settings_page( $settings = [] ) {
        $settings['sms'] = new SmsSettings();
        return $settings;
    }

    /**
     * Add checkbox in hr announcement for confirmation
     *
     * @since 1.0
     *
     * @return void [html]
     */
    public function announcement_add_option( $post ) {
        $sms_enabled = get_post_meta( $post->ID, '_announcement_send_sms', true );
        $sms_content = get_post_meta( $post->ID, '_announcement_sms_content', true );
        $checked     = isset( $sms_enabled ) && 'on' == $sms_enabled ? 'checked' : '';
        $style       = 'checked' == $checked ? '' : 'display:none';
        ?>
            <tr>
                <th><?php _e( 'Send SMS', 'erp-pro' ); ?></th>
                <td>
                    <input id="hr-announcement-sms-check" name="hr_announcement_send_sms" type="checkbox" <?php echo $checked; ?>></input>&nbsp;
                    <span><?php _e( 'Check to send SMS', 'erp-pro' ); ?></span>
                </td>
            </tr>
            <tr id="hr-announcement-sms-body" style="<?php echo $style; ?>">
                <th><?php _e( 'SMS Body', 'erp-pro' ); ?></th>
                <td>
                    <textarea cols="80" rows="10" class="all-options" name="hr_announcement_sms_content"><?php echo isset( $sms_content ) ? $sms_content : '' ?></textarea>
                </td>
            </tr>
        <?php
    }

    /**
     * Announcement Save Option
     *
     * @since 1.0
     *
     * @return void
     */
    public function announcement_save_option( $post_id, $employees ) {
        $sms_enabled = isset( $_REQUEST['hr_announcement_send_sms'] ) ? $_REQUEST['hr_announcement_send_sms'] : '';
        $sms_content = isset( $_REQUEST['hr_announcement_sms_content'] ) ? $_REQUEST['hr_announcement_sms_content'] : '';

        if ( isset( $sms_enabled ) && 'on' != $sms_enabled ) {
            return;
        }

        foreach ( $employees as $employee ) {
            $employee      = new \WeDevs\ERP\HRM\Employee( intval( $employee ) );
            $cell_no_all[] = ltrim( $employee->get_phone( 'mobile' ), '+' );
        }

        $this->send( $cell_no_all, $sms_content );

        update_post_meta( $post_id, '_announcement_send_sms', $sms_enabled );
        update_post_meta( $post_id, '_announcement_sms_content', $sms_content );
    }

    /**
     * Assign announcement for edit column
     *
     * @since 1.0
     *
     * @return void
     */
    public function assign_announcement_edit_columns( $column, $post_id ) {
        if ( 'send_type' == $column ) {
            $sms_enabled = get_post_meta( $post_id, '_announcement_send_sms', true );

            if ( 'on' == $sms_enabled ) {
                echo '&nbsp; <i class="fa fa-comments-o fa-lg"></i>';
            }
        }
    }

    /**
     * Announcement Send SMS
     *
     * @since 1.0
     *
     * @return void
     */
    public function announcement_send_sms( $employees, $post_id ) {
        $sms_enabled = get_post_meta( $post_id, '_announcement_send_sms', true );
        $sms_content = get_post_meta( $post_id, '_announcement_sms_content', true );
        $cell_no_all = [];

        if ( isset( $sms_enabled ) && 'on' != $sms_enabled ) {
            return;
        }

        foreach ( $employees as $employee ) {
            $employee      = new \WeDevs\ERP\HRM\Employee( intval( $employee ) );
            $cell_no_all[] = ltrim( $employee->get_mobile(), '+' );
        }
        $this->send( $cell_no_all, $sms_content );
    }

    /**
     * Function resposible for sending SMS
     *
     * @since 1.0
     *
     * @return void
     */
    public function send( $cell_no_all, $message ) {
        if ( empty( $cell_no_all ) ) {
            return;
        }

        $numbers = array_filter( $cell_no_all );

        $this->gateway = new GatewayHandler();
        $this->gateway->send_sms( $numbers, $message );
    }

    /**
     * Add Nav Item to Customer Feed
     *
     * @since 1.0
     *
     * @return void
     */
    public function crm_sms_load_scripts() {
        wp_enqueue_style( 'crm-feeds-sms-style', WPERP_SMS_ASSETS . '/css/crm-sms.css' );
        wp_enqueue_script( 'crm-feeds-sms-script', WPERP_SMS_ASSETS . '/js/crm-sms.js',[
            'erp-nprogress',
            'erp-script',
            'erp-vuejs',
            'underscore',
            'erp-select2',
            'erp-tiptip'
            ], date( 'Ymd' ), true );
    }

    /**
     * Add nav to CRM feed
     *
     * @since 1.0
     *
     * @return array
     */
    public function crm_feeds_add_sms_tab( $tabs ) {
        $tabs['sms'] = [
            'title' => __( 'SMS', 'erp-pro' ),
            'icon'  => '<i class="fa fa-comments-o"></i>'
        ];

        return $tabs;
    }

    /**
     * Load JS Template to SMS feed
     *
     * @since 1.1
     *
     * @return void
     */
    public function crm_feeds_load_sms_js_template() {
        if ( version_compare( WPERP_VERSION, "1.4.0", '<' )) {
            $this->crm_feeds_load_sms_js_template_old();
            return;
        }

        if ( !empty( $_GET['action'] ) && $_GET['action'] == 'view' ) {
            erp_get_vue_component_template( WPERP_SMS_VIEWS . '/js-templates/new-sms-note.php', 'erp-crm-new-sms-note-template' );
            erp_get_vue_component_template( WPERP_SMS_VIEWS . '/js-templates/timeline-sms-note.php', 'erp-crm-timeline-feed-sms-note' );
        }

        if ( !empty( $_GET['section'] ) && $_GET['section'] == 'activities' ) {
            erp_get_vue_component_template( WPERP_SMS_VIEWS . '/js-templates/timeline-sms-note.php', 'erp-crm-timeline-feed-sms-note' );
        }
    }

    /**
     * Load JS Template to SMS feed
     *
     * @since 1.0
     *
     * @return void
     */
    public function crm_feeds_load_sms_js_template_old(){
        global $current_screen;

        switch ( $current_screen->base ) {
            case 'crm_page_erp-sales-customers':
            case 'crm_page_erp-sales-companies':
                if ( isset( $_GET['action'] ) && $_GET['action'] == 'view' ) {
                    erp_get_vue_component_template( WPERP_SMS_VIEWS . '/js-templates/new-sms-note.php', 'erp-crm-new-sms-note-template' );
                    erp_get_vue_component_template( WPERP_SMS_VIEWS . '/js-templates/timeline-sms-note.php', 'erp-crm-timeline-feed-sms-note' );
                }

                break;

            case 'crm_page_erp-sales-activities':
                erp_get_vue_component_template( WPERP_SMS_VIEWS . '/js-templates/timeline-sms-note.php', 'erp-crm-timeline-feed-sms-note' );
                break;
        }

        $section     = isset( $_GET['section'] )     ? sanitize_text_field( wp_unslash( $_GET['section'] ) )     : '';
        $sub_section = isset( $_GET['sub-section'] ) ? sanitize_text_field( wp_unslash( $_GET['sub-section'] ) ) : '';

        if ( 'contact' === $section && ( 'contacts' === $sub_section || 'companies' === $sub_section ) ) {
            erp_get_vue_component_template( WPERP_SMS_VIEWS . '/js-templates/new-sms-note.php', 'erp-crm-new-sms-note-template' );
            erp_get_vue_component_template( WPERP_SMS_VIEWS . '/js-templates/timeline-sms-note.php', 'erp-crm-timeline-feed-sms-note' );
            erp_get_vue_component_template( WPERP_SMS_VIEWS . '/js-templates/timeline-sms-note.php', 'erp-crm-timeline-feed-sms-note' );
        }
    }
    /**
     * CRM Feed SMS add tab content
     *
     * @since 1.0
     *
     * @return void
     */
    public function crm_sms_add_tab_content() {
        ?>
            <sms-note v-if="tabShow == 'sms'"></sms-note>
        <?php
    }

    /**
     * CRM Feed Save SMS data
     *
     * @since 1.0
     *
     * @return void
     */
    public function crm_sms_save_sms_feeddata( $data = [] ) {
        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( empty( $_POST['type'] ) || 'sms' !== $_POST['type'] ) {
            return;
        }

        if ( empty( $_POST['sms_number'] ) ) {
            return;
        }

        $extra_data  = [
            'sms_number' => array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['sms_number'] ) ),
        ];

        $save_data   = [
            'id'            => ! empty( $_POST['id'] ) ? intval( wp_unslash( $_POST['id'] ) ) : '',
            'user_id'       => ! empty( $_POST['user_id'] ) ? intval( wp_unslash( $_POST['user_id'] ) ) : '',
            'created_by'    => ! empty( $_POST['created_by'] ) ? intval( wp_unslash( $_POST['created_by'] ) ) : '',
            'message'       => ! empty( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '',
            'type'          => sanitize_text_field( wp_unslash( $_POST['type'] ) ),
            'log_type'      => 'sms',
            'start_date'    => erp_current_datetime()->format( 'Y-m-d H:i:s' ),
            'extra'         => base64_encode( json_encode( $extra_data ) )
        ];

        $data = erp_crm_save_customer_feed_data( $save_data );

        if ( ! $data ) {
            wp_send_json_error( __( 'Somthing is wrong, Please try later', 'erp-pro' ) );
        }

        array_walk(
            $extra_data['sms_number'],
            function( &$items ) {
                $items = ltrim( $items, '+' );
            }
        );

        $this->send( $extra_data['sms_number'], $save_data['message'] );

        wp_send_json_success( $data );
    }

    /**
     * Adds CRM activity notification type
     *
     * @since 1.0
     *
     * @return array
     */
    public function crm_activity_notification_type_add( $types ) {
        $types['sms'] = __( 'SMS', 'erp-pro' );
        return $types;
    }

    /**
     * Format sms activities message
     *
     * @since 1.0
     *
     * @param  string $message
     * @param  string $activity
     *
     * @return string
     */
    public function sms_activity_message_format( $message, $activity ) {
        if ( ! isset( $activity['type'] ) ) {
            return $message;
        }

        if ( $activity['type'] != 'sms' ) {
            return $message;
        }

        return nl2br( $message );
    }

    /**
     * Add sms feed localize string
     *
     * @since 1.0
     *
     * @param array $localize
     *
     * @return array
     */
    public function add_localize_string( $localize ) {
        $localize['smsHeaderText'] = sprintf( __( '<strong>%1s</strong> send a sms to <strong>%2s</strong> in <strong>%3s</strong> number', 'erp' ), '{{createdUserName}}', '{{createdForUser}}', '{{smsNumber}}' );
        return $localize;
    }

    /**
     * CRM Feed Send Scheduled SMS
     *
     * @since 1.0
     *
     * @return void
     */
    public function crm_send_scheduled_sms( $activity, $extra ) {
        if ( 'sms' != $extra['notification_via'] ) {
            return;
        }

        $cell_no_all = [];
        $sms_content = sprintf( __( 'You have a schedule after %s %s at %s', 'erp-pro' ), $extra['notification_time_interval'], $extra['notification_time'], date( 'F j, Y, g:i a', strtotime( $activity['start_date'] ) ) );

        foreach( $extra['invite_contact'] as $contact ) {
            $contact = new \WeDevs\ERP\CRM\Contact( intval( $contact ) );
            $cell_no_all[] = $contact->mobile;
        }

        $this->send( $cell_no_all, $sms_content );
    }

    /**
     * Workflow actions list filter
     *
     * @param  array $actions
     *
     * @return array
     */
    public function workflow_actions_list( $actions ) {
        $send_sms = __( 'Send SMS', 'erp-pro' );

        $actions['auto']['created_contact']['send_sms'] = $send_sms;
        $actions['auto']['deleted_contact']['send_sms'] = $send_sms;
        $actions['auto']['subscribed_contact']['send_sms'] = $send_sms;
        $actions['auto']['unsubscribed_contact']['send_sms'] = $send_sms;
        $actions['auto']['created_note']['send_sms'] = $send_sms;
        $actions['auto']['created_task']['send_sms'] = $send_sms;
        $actions['auto']['created_employee']['send_sms'] = $send_sms;
        $actions['auto']['confirmed_leave_request']['send_sms'] = $send_sms;
        $actions['auto']['happened_birthday_today']['send_sms'] = $send_sms;
        $actions['auto']['added_sale']['send_sms'] = $send_sms;
        $actions['auto']['added_expense']['send_sms'] = $send_sms;

        return $actions;
    }

    /**
     * Run workflow's send sms action.
     *
     * @param  array $data
     *
     * @return void
     */
    public function workflow_send_sms_action( $data ) {
        $params  = unserialize( $data['action']->params );
        $message = erp_wf_replace_text_vars( $data['event_data'], $params['message'] );

        $cell_no_all = [];

        if ( ! empty( $params['user'] ) && ! json_decode( $params['send_itself'] ) ) {

            foreach ( $params['user'] as $user ) {
                $employee      = new \WeDevs\ERP\HRM\Employee( intval( $user['id'] ) );
                $cell_no_all[] = ltrim( $employee->get_phone( 'mobile' ), '+' );
            }

        } else {
            if ( $data['event_data']['object'] == 'employee' ) {
                $employee   = new \WeDevs\ERP\HRM\Employee( intval( $data['event_data']['id'] ) );
                $mobile     = ltrim( $employee->get_phone( 'mobile' ), '+' );
            } elseif ( $data['event_data']['object'] == 'contact' ) {
                $contact_id = intval( $data['event_data']['id'] );
                $contact    = erp_get_people( $contact_id );
                $mobile     = isset( $contact->mobile ) ? $contact->mobile : null;
            } else {
                $contact_id = intval( $data['event_data']['user_id'] );
                $contact    = erp_get_people( $contact_id );
                $mobile     = isset( $contact->mobile ) ? $contact->mobile : null;
            }

            if ( ! empty( $mobile ) ) {
                $cell_no_all[] = $mobile;
            }
        }

        $this->send( $cell_no_all, $message );

        return;
    }
}
