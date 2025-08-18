<?php 
namespace WeDevs\ERP_PRO\Feature\HRM\Requests\Resignation;

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
        $this->action( 'erp_daily_scheduled_events', 'exec_approved_resigned_requests' );
        $this->action( 'erp_hr_employee_extra_actions', 'add_employee_actions' );
        $this->filter( 'erp_hr_employee_request_types', 'add_request_type' );
        $this->filter( 'erp_hr_employee_pending_request_count', 'get_pending_request_count' );
        $this->filter( 'erp_hr_get_employee_resigned_requests', 'get_resign_requests' );
        $this->filter( 'erp_hr_employee_resigned_request_bulk_action', 'resignation_bulk_action', 10, 2 );
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
        $requests = erp_hr_employee_get_resign_requests();

        $type     = [
            'resigned' => [
                'count'   => $requests['total_items'],
                'label'   => __( 'Resignation', 'erp-pro' ),
            ]
        ];

        return $type + $types;
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
        $pending = erp_hr_employee_get_resign_requests( [ 'status' => 'pending', 'number' => null ] );

        $requests['resigned'] = $pending['total_items'];

        return $requests;
    }

    /**
     * Adds extra actions for employee
     * 
     * @since 1.2.0
     *
     * @param int|string $emp_id
     * 
     * @return void
     */
    public function add_employee_actions( $emp_id ) {
        if ( (int) $emp_id === get_current_user_id() ) {
            ob_start();

            if ( erp_hr_employee_exists_resign_request( $emp_id, 'approved' ) ) : ?>

                <a href="#"
                    class="button"
                    disabled="disabled"
                    id="erp-employee-approved-resign">
                    <?php esc_html_e( 'Resignation Approved', 'erp-pro' ); ?></a>

            <?php elseif ( erp_hr_employee_exists_resign_request( $emp_id, 'pending' ) ) : ?>

                <a href="#"
                    class="button"
                    id="erp-employee-cancel-resign"
                    data-id="<?php echo esc_attr( $emp_id ); ?>"
                    data-action="erp_hr_employee_cancel_resign"
                    data-title="<?php esc_attr_e( 'Withdraw Resign Request', 'erp-pro' ); ?>"
                    data-nonce="<?php echo wp_create_nonce( 'employee_resign_request' ); ?>">
                    <?php esc_html_e( 'Withdraw Resignation', 'erp-pro' ); ?></a>

            <?php else : ?>

                <a href="#"
                    class="button"
                    id="erp-employee-resign"
                    data-id="<?php echo esc_attr( $emp_id ); ?>"
                    data-template="erp-employee-resign"
                    data-title="<?php esc_attr_e( 'Resign Request', 'erp-pro' ); ?>">
                    <?php esc_html_e( 'Resign', 'erp-pro' ); ?></a>

            <?php endif;

            echo ob_get_clean();
        }
    }

    /**
     * Executes approved resignation requests
     * to change the employee status to resigned
     * 
     * @since 1.2.0
     *
     * @return void
     */
    public function exec_approved_resigned_requests() {
        $requests  = erp_hr_employee_get_resign_requests( [
            'status'   => 'approved',
            'number'   => null,
            'order_by' => 'date',
            'order'    => 'ASC'
        ] );

        if ( is_wp_error( $requests ) ) {
            return;
        }

        $curr_date = erp_current_datetime()->format( 'Y-m-d' );

        foreach ( $requests['data'] as $request ) {
            $resign_date = erp_current_datetime()->modify( $request['date'] )->format( 'Y-m-d' );
            
            if ( $resign_date <= $curr_date  ) {
                $employee = new \WeDevs\ERP\HRM\Employee( $request['employee']['id'] );
                
                if ( empty( $employee->erp_user->user_id ) ) {
                    continue;
                }
                
                $employee->update_employment_status( [
                    'module'   => 'employee',
                    'category' => 'resigned',
                    'comments' => "Self requested resignation due to {$request['reason']['title']}",
                    'date'     => $resign_date
                ] );
            }
        }
    }

    /**
     * Retrieves resign requests
     * 
     * @since 1.2.0
     *
     * @param array $args
     * 
     * @return array
     */
    public function get_resign_requests( $args ) {
        $requests = erp_hr_employee_get_resign_requests( $args );

        foreach ( $requests['data'] as &$request ) {
            $request['date']    = ! empty( $request['date'] )    ? erp_format_date( $request['date'] )    : '-';
            $request['created'] = ! empty( $request['created'] ) ? erp_format_date( $request['created'] ) : '-';
        }

        return $requests;
    }

    /**
     * Performs bulk action on resignation request
     * 
     * @since 1.2.0
     *
     * @param array $req_ids
     * @param string $action
     * 
     * @return mixed
     */
    public function resignation_bulk_action( $req_ids, $action ) {
        
        if ( 'deleted' === $action ) {
            $result = erp_hr_delete_resign_request_by( $req_ids );
        } else {
            $result = erp_hr_employee_update_resign_request( $req_ids, [ 'status' => $action ] );
        }

        return $result;
    }
}