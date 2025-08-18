<?php
namespace WeDevs\Recruitment;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 *  Recruitment class HR
 *
 *  Recruitment for employees
 *
 * @since 0.1
 *
 * @author weDevs <info@wedevs.com>
 */
class Recruitment extends \WP_List_Table {

    use Hooker;

    private $post_type = 'erp_hr_recruitment';
    private $post_type_plural = 'erp_hr_recruitments';
    private $assign_type = [];

    public function __construct() {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'recruitment',
            'plural'   => 'recruitments',
            'ajax'     => false,
        ));
    }

    /**
     * Render extra filtering option in
     * top of the table
     *
     * @since 1.0.0
     *
     * @param  string $which
     *
     * @return void
     */
    function extra_tablenav( $which ) {

        //return;

        if ( $which != 'top' ) {
            return;
        }

        $selected_status = ( isset( $_REQUEST['filter_status'] ) ) ? $_REQUEST['filter_status'] : -1;
        $jobid = ( isset( $_REQUEST['jobid'] ) ) ? $_REQUEST['jobid'] : 0;

        ?>
        <div class="alignleft actions">
            <input type="hidden" name="jobid" value="<?php echo $jobid; ?>">
        <?php

        echo '</div>';
    }

    /**
     * Message to show if no department found
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function no_items() {
        _e( 'No opening found.', 'erp-pro' );
    }

    /**
     * Get the column names
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_columns() {
        $columns = array(
            'cb'            => '<input type="checkbox" />',
            'job_title'     => __( 'Job Title', 'erp-pro' ),
            'applicants'    => __( 'Applicants', 'erp-pro' ),
            'status'        => __( 'Status', 'erp-pro' ),
            'created_on'    => __( 'Created On', 'erp-pro' ),
            'expire_date'   => __( 'Expire Date', 'erp-pro' ),
            'publish_date'  => __( 'Publish Date', 'erp-pro' ),
            'raction'       => __( 'Action', 'erp-pro' ),
        );

        return apply_filters( 'erp_hr_jobseeker_table_cols', $columns );
    }

    /**
     * Show default column
     *
     * @since  1.0.0
     *
     * @param array $item
     * @param string $column_name
     *
     * @return string
     */
    public function column_default( $item, $column_name ) {
        $jobseeker_preview_url = erp_rec_url( 'jobseeker_list&jobid=' . $item['id'] );

        switch ( $column_name ) {
            case 'job_title':
                return sprintf( __( '<a href="%s">' . $item['job_title'] . '</a>', 'erp-pro' ), $jobseeker_preview_url );

            case 'applicants':
                $jobid = $item['id'];
                $applicants_number = get_applicants_counter( $jobid );
                return sprintf( __( '<a href="%s">' . $applicants_number . '</a>', 'erp-pro' ), $jobseeker_preview_url );

            // case 'hiring_lead':
            //     $post_id = $item['id'];
            //     $hiring_lead_ids = get_post_meta( $post_id, '_hiring_lead', true ) ? get_post_meta( $post_id, '_hiring_lead', true ) : [];
            //     $hiring_lead_name = [];
            //     if ( is_array( $hiring_lead_ids ) && count( $hiring_lead_ids ) > 0 ) {
            //         foreach ( $hiring_lead_ids as $hiring_lead_id ) {
            //             $employees = new \WeDevs\ERP\HRM\Employee( intval( $hiring_lead_id ) );
            //             array_push( $hiring_lead_name, $employees->get_full_name() );
            //         }
            //         $hiring_list = implode( ', ', $hiring_lead_name );
            //         $hiring_leads = $hiring_list;
            //     } else {
            //         $hiring_leads = __( 'No Lead found', 'erp-pro' );
            //     }
            //     return $hiring_leads;

            case 'status':
                $post_id = $item['id'];
                $e_date = get_post_meta( $post_id, '_expire_date', true ) ? get_post_meta( $post_id, '_expire_date', true ) : '';

                if ( 'publish' == get_post_status( $post_id ) && $e_date ) {
                    if ( strtotime( date( 'Y-m-d' ) ) > strtotime( $e_date ) ) {
                            $status = sprintf( '<span class="status-red">%s</span>', __( 'Expired', 'erp-pro' ) );
					} else {
						$status = sprintf( '<span class="status-green">%s</span>', __( 'Open', 'erp-pro' ) );
					}
                } elseif ( 'pending' == get_post_status( $post_id ) ) {
                    $status = sprintf( '<span class="status-yellow">%s</span>', __( 'Pending', 'erp-pro' ) );
                } elseif ( 'draft' == get_post_status( $post_id ) ) {
                    $status = sprintf( '<span class="status-ash">%s</span>', __( 'Draft', 'erp-pro' ) );
                } else {
                    $status = sprintf( '<span class="status-green">%s</span>', __( 'Open', 'erp-pro' ) );
                }
                return $status;

            case 'created_on':
                return erp_format_date( $item['created_on'] );

            case 'expire_date':
                $expire_date_output = '';
                $post_id = $item['id'];
                $e_date = get_post_meta( $post_id, '_expire_date', true ) ? get_post_meta( $post_id, '_expire_date', true ) : '';
                if ( $e_date != '' ) {
                    $edata = erp_format_date( $e_date );
                    $future_date = date_create( $e_date );
                    $current_date = date_create( date( 'Y-m-d' ) );
                    if ( 'publish' == get_post_status( $post_id ) ) {
                        if ( strtotime( date( 'Y-m-d' ) ) < strtotime( $e_date ) ) {
                            $rdays = date_diff( $current_date, $future_date );
                            $remaining_days = $rdays->format( '%a days' );
                            $expire_date_output = sprintf( '%s<div class="row-actions-days"><span>(in %s)</span></div>', $edata, $remaining_days );
                        } else {
                            $expire_date_output = sprintf( '%s', $edata );
                        }
                    } else {
                        $expire_date_output = sprintf( '%s', $edata );
                    }
                } else {
					$expire_date_output = sprintf( '<div class="row-actions-days"><span>%s</span></div>', __( 'No expire date', 'erp-pro' ) );
                }
                return $expire_date_output;

            case 'publish_date':
                return date( erp_get_date_format() . ', h:i a', strtotime( $item['created_on'] ) );

            case 'raction':
                $url                    = version_compare( WPERP_VERSION, '1.4.0', '<' ) ? 'admin.php?page=erp-hr-recruitment' : 'admin.php?page=erp-hr&section=recruitment';
                $post_id                = $item['id'];
                $four_links             = '';
                $edit_template_link     = admin_url( $url . '&view=detail_template&jobid=' . $post_id );
                $edit_link              = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
                $applicants_list_list   = erp_rec_url( 'jobseeker_list&jobid=' . $post_id );
                $preview_url            = get_post_permalink( $post_id );
                $copy_url               = admin_url( $url . '&action=copy&jobid=' . $post_id );

                $four_links .= sprintf( '<a class="button list-tbl-action-btn" href="%s" title="%s"><i class="fa fa-eye"></i></a>', $edit_template_link, __( 'View Job', 'erp-pro' ) );
                $four_links .= sprintf( '<a class="button list-tbl-action-btn" href="%s" title="%s"><i class="fa fa-pencil"></i></a>', $edit_link, __( 'Edit Job Opening', 'erp-pro' ) );
                $four_links .= sprintf( '<a class="button list-tbl-action-btn" href="%s" title="%s"><i class="fa fa-group"></i></a>', $applicants_list_list, __( 'View Applicants', 'erp-pro' ) );
                $four_links .= sprintf( '<a class="button list-tbl-action-btn" href="%s" title="%s" target="_blank"><i class="fa fa-external-link"></i></a>', $preview_url, __( 'Preview Job', 'erp-pro' ) );

                /*** Copy a job link start ***/
                $four_links .= sprintf( '<a class="button list-tbl-action-btn" href="%s" title="%s"><i class="fa fa-copy"></i></a>', $copy_url, __( 'Copy Job', 'erp-pro' ) );
                /*** Copy a job link end ***/

                return $four_links;

            default:
        }
        return $item[ $column_name ];
    }

    /**
     * Get sortable columns
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'job_title'     => array( 'job_title', true ),
            'applicants'    => array( 'applicants', true ),
        );

        return $sortable_columns;
    }

    /**
     * Render the bulk edit checkbox
     *
     * @since 1.0.0
     *
     * @param array $item
     *
     * @return string
     */
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="bulk-id[]" value="%s" />', $item['id']
        );
    }

    /**
     * Returns an associative array containing the bulk action.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = [
            'bulk-id' => __( 'Delete', 'erp-pro' ),
        ];

        return $actions;
    }

    /**
     * Render current trigger bulk action
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function current_action() {
        if ( isset( $_REQUEST['filter_status_button'] ) ) {
            return 'filter_status';
        }

        if ( isset( $_REQUEST['recruitment_search'] ) ) {
            return 'recruitment_search';
        }

        return parent::current_action();
    }

    /**
     * Get views
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_views() {
        $base_link          = admin_url( 'admin.php?page=erp-hr&section=recruitment' );
        $all_counter        = erp_rec_get_all_count_number();
        $open_counter       = erp_rec_get_open_count_number();
        $draft_counter      = erp_rec_get_draft_count_number();
        $pending_counter    = erp_rec_get_pending_count_number();
        $expired_counter    = erp_rec_get_expire_count_number();
        $status_links = [
            'all'       => sprintf( '<a href="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'all' ), $base_link ), __( 'All', 'erp-pro' ), $all_counter ),
            'publish'   => sprintf( '<a href="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'publish' ), $base_link ), __( 'Open', 'erp-pro' ), $open_counter ),
            'draft'     => sprintf( '<a href="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'draft' ), $base_link ), __( 'Draft', 'erp-pro' ), $draft_counter ),
            'pending'   => sprintf( '<a href="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'pending' ), $base_link ), __( 'Pending', 'erp-pro' ), $pending_counter ),
            'expired'   => sprintf( '<a href="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'expired' ), $base_link ), __( 'Expired', 'erp-pro' ), $expired_counter ),
        ];

        return $status_links;
    }

    /**
     * Search form for list table
     *
     * @since 1.0.0
     *
     * @param  string $text
     * @param  string $input_id
     *
     * @return void
     */
    public function search_box( $text, $input_id ) {
        if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
            return;
        }

        $input_id = $input_id . '-search-input';

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
        }

        if ( ! empty( $_REQUEST['order'] ) ) {
            echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
        }

        if ( ! empty( $_REQUEST['status'] ) ) {
            echo '<input type="hidden" name="status" value="' . esc_attr( $_REQUEST['status'] ) . '" />';
        }

        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo $input_id; ?>"><?php echo $text; ?>:</label>
            <input type="search" id="<?php echo $input_id; ?>" name="s" value="<?php _admin_search_query(); ?>"/>
            <?php submit_button( $text, 'button', 'recruitment_search', false, array( 'id' => 'search-submit' ) ); ?>
        </p>
		<?php
    }

    /**
     * Prepare the class items
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function prepare_items() {
        global $per_page;

        $columns               = $this->get_columns();
        $hidden                = [];
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page - 1 ) * $per_page;
        //$this->page_status     = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : '2';
        $post_status           = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'all';
        $jobid                 = isset( $_REQUEST['jobid'] ) ? $_REQUEST['jobid'] : 0;

        // only necessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
            'jobid'  => $jobid,
            'status' => $post_status,
        );

        if ( isset( $_REQUEST['filter_status'] ) && $_REQUEST['filter_status'] ) {
            $args['status'] = $_REQUEST['filter_status'];
        }

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order'] = $_REQUEST['order'];
        }

        if ( isset( $_REQUEST['s'] ) ) {
            $args['search_key'] = $_REQUEST['s'];
        }

        $this->items = erp_rec_get_opening_information( $args );
        $total_rows  = erp_rec_total_opening_counter( $args );

        $this->set_pagination_args(array(
            'total_items' => $total_rows,
            'per_page'    => $per_page,
        ));
    }

}
