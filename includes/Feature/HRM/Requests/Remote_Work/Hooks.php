<?php 
namespace WeDevs\ERP_PRO\Feature\HRM\Requests\Remote_Work;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Hooks handler class
 * 
 * @since 1.2.0
 */
class Hooks {

    use Hooker;

    /**
     * Class constructor
     * 
     * @since 1.2.0
     */
    function __construct() {
        $this->action( 'erp_hr_leave_calendar_actions', 'add_remote_work_button' );
        $this->filter( 'erp_hr_employee_request_types', 'add_request_type' );
        $this->filter( 'erp_hr_employee_single_tabs', 'add_remote_work_tab' );
        $this->filter( 'erp_hr_employee_pending_request_count', 'get_pending_request_count' );
        $this->filter( 'erp_hr_get_employee_remote_work_requests', 'get_remote_work_requests' );
        $this->filter( 'erp_hr_employee_remote_work_request_bulk_action', 'remote_work_bulk_action', 10, 2 );
    }

    /**
     * Adds necessary request type
     * 
     * @since 1.2.0
     * 
     * @param array $types
     *
     * @return void
     */
    public function add_request_type( $types ) {
        $requests = erp_hr_employee_get_remote_work_requests();

        $type     = [
            'remote_work' => [
                'count'   => $requests['total_items'],
                'label'   => __( 'Remote Work', 'erp-pro' ),
            ]
        ];

        $index    = array_search( 'leave', array_keys( $types ) );
        $index    = false !== $index ? $index : count( $types );

        return array_slice( $types, 0, $index, true ) + $type + array_slice( $types, $index, NULL, true );
    }

    /**
     * Adds remote work tab in single employee page
     * 
     * @since 1.2.0
     *
     * @param array $tabs
     * 
     * @return array
     */
    public function add_remote_work_tab( $tabs ) {
        $section  = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';
        $emp_id   = isset( $_GET['id'] )      ? intval( wp_unslash( $_GET['id'] ) )                   : get_current_user_id();

        if ( 'yes' !== erp_get_option( 'erp_hr_remote_work_enable' )
            || 'my-profile' !== $section
            || $emp_id !== get_current_user_id()
        ) {
            return $tabs;
        }

        $rw_tab['remote_work'] = [
            'title' => __( 'Remote Work', 'erp-pro' ),
            'callback' => [ $this, 'render_remote_work_tab']
        ];

        $index = array_search( 'leave', array_keys( $tabs ) );
        $index = false !== $index ? $index + 1 : count( $tabs );

        return array_slice( $tabs, 0, $index, true ) + $rw_tab + array_slice( $tabs, $index, NULL, true );
    }

    /**
     * Renders remote work tab of employee
     * 
     * @since 1.2.0
     *
     * @return void
     */
    public function render_remote_work_tab() {
        $args = [
            'user_id' => get_current_user_id(),
            'date'    => [
                'start' => erp_current_datetime()->format( 'Y-01-01 00:00:00' ),
                'end'   => erp_current_datetime()->format( 'Y-12-31 23:59:59' )
            ],
            'number'  => null
        ];

        if ( ! empty( $_REQUEST['year'] ) ) {
            $year = sanitize_text_field( wp_unslash( $_REQUEST['year'] ) );
            $date = $year . '-01-01';
            
            $args['date']['start'] = erp_current_datetime()->modify( $date )->format( 'Y-01-01 00:00:00' );
            $args['date']['end']   = erp_current_datetime()->modify( $date )->format( 'Y-12-31 23:59:59' );
        }

        if ( ! empty( $_REQUEST['status'] ) && '-1' != $_REQUEST['status'] ) {
            $args['status'] = sanitize_text_field( wp_unslash( $_REQUEST['status'] ) );
        }
        
        $requests = erp_hr_employee_get_remote_work_requests( $args );
        $requests = $requests['data'];
        $cur_year = erp_current_datetime()->format( 'Y' );
        $years    = [];

        for ( $year = intval( $cur_year ); $year >= 1970; -- $year ) {
            $years[ $year ] = sprintf( __( '%s', 'erp-pro' ), $year );
        }

        $statuses = [
            'pending'  => __( 'Pending', 'erp-pro' ),
            'approved' => __( 'Approved', 'erp-pro' ),
            'rejected' => __( 'Rejected', 'erp-pro' )
        ];
        
        include_once ERP_PRO_FEATURE_DIR . '/HRM/Requests/templates/remote-work-employee-tab.php';
    }

    /**
     * Adds remote work request button
     * 
     * @since 1.2.0
     *
     * @return string
     */
    public function add_remote_work_button() {
        $rw_enabled = erp_get_option( 'erp_hr_remote_work_enable' );

        ob_start();

        if ( 'yes' === $rw_enabled && current_user_can( 'employee' ) && erp_hr_is_employee_active() ) : ?>
            <div class="erp-hr-remote-work-req-wrap">
                <a href="#"
                    class="button button-primary"
                    id="erp-hr-remote-work-req" 
                    data-id="<?php echo esc_attr( get_current_user_id() ); ?>"
                    data-template="erp-employee-remote-work-request"
                    data-title="<?php esc_attr_e( 'Remote Work Request', 'erp-pro' ); ?>">
                    <?php esc_html_e( 'Request Remote Work', 'erp-pro' ); ?>
                </a>
            </div>
        <?php endif;

        echo ob_get_clean();
    }

    /**
     * Gets pending request count
     * 
     * @since 1.2.0
     *
     * @param array $requests
     * 
     * @return array
     */
    public function get_pending_request_count( $requests ) {
        $pending  = erp_hr_employee_get_remote_work_requests( [ 'status' => 'pending', 'number' => null ] );

        $requests['remote_work'] = $pending['total_items'];

        return $requests;
    }

    /**
     * Retrieves remote work requests
     * 
     * @since 1.2.0
     *
     * @param array $args
     * 
     * @return array
     */
    public function get_remote_work_requests( $args ) {
        return erp_hr_employee_get_remote_work_requests( $args );
    }

    /**
     * Performs bulk action on remote work request
     * 
     * @since 1.2.0
     *
     * @param array $req_ids
     * @param string $action
     * 
     * @return mixed
     */
    public function remote_work_bulk_action( $req_ids, $action ) {
        
        if ( 'deleted' === $action ) {
            $result = erp_hr_delete_remote_work_request_by( $req_ids );
        } else {
            $result = erp_hr_employee_update_remote_work_request( $req_ids, [ 'status' => $action ] );
        }
        
        return $result;
    }
}