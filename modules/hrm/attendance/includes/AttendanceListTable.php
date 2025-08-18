<?php
namespace WeDevs\Attendance;
/**
 * List table class
 */
if ( ! class_exists ( 'WP_List_Table' ) ) {

    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class AttendanceListTable extends \WP_List_Table {

    function __construct() {

        parent::__construct( array(
            'singular' => 'attendance',
            'plural'   => 'attendances',
            'ajax'     => false
        ) );

        $this->table_css();
    }

    /**
     * Render extra filtering option in
     * top of the table
     *
     * @since 0.1
     *
     * @param  string $which
     *
     * @return void
     */
    function extra_tablenav( $which ) {

        if ( $which != 'top' ) {

            return;
        }

        $selected_duration = ( isset( $_GET['filter_duration'] ) ) ? $_GET['filter_duration'] : '';

        ?>

        <div class="alignleft actions">

            <label class="screen-reader-text" for="new_role"><?php _e( 'Filter by Duration', 'erp-pro' ) ?></label>
            <select name="filter_duration" id="att-filter-duration">
                <option value="-1"><?php _e( '- Select Duration -', 'erp-pro' ) ?></option>
                <?php
                    $types = erp_att_get_filters();

                    foreach ( $types as $key => $title ) {
                        echo sprintf( "<option value='%s'%s>%s</option>\n", $key, selected( $selected_duration, $key, false ), $title );
                    }
                ?>
            </select>

            <?php
            submit_button( __( 'Filter' ), 'button', 'filter_attendance', false );
        echo '</div>';
    }

    function get_table_classes() {

        return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
    }

    /**
     * Message to show if no designation found
     *
     * @return void
     */
    function no_items() {

        _e( 'No Record Found', 'erp-pro' );
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $item, $column_name ) {

        switch ( $column_name ) {
            case 'dates':
                return $item->date;

            case 'attended':
                return $item->present;

            case 'absent':
                return $item->absent;

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    public function current_action() {

        if ( isset( $_REQUEST['filter_attendance'] ) ) {

            return 'filter_attendance';
        }

        return parent::current_action();
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {

        $columns = array(
            'cb'       => '<input type="checkbox" />',
            'dates'    => __( 'Date', 'erp-pro' ),
            'attended' => __( 'Attended', 'erp-pro' ),
            'absent'   => __( 'Absent', 'erp-pro' ),
            'presence' => __( 'Presence', 'erp-pro')
        );

        return $columns;
    }

    /**
     * Render the designation name column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_dates( $item ) {

        $actions           = array();
        $edit_url          = admin_url( 'admin.php?page=erp-edit-attendance' );
        $edit_url            = add_query_arg( 'edit_date', $item->date, $edit_url );
        $actions['edit']   = sprintf( '<a href="%s" title="%s">%s</a>', $edit_url, __( 'Edit this item', 'erp-pro' ), __( 'Edit', 'erp-pro' ) );
        //$actions['delete'] = sprintf( '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=erp-hr-attendance&action=delete&id=' . $item->date ), $item->date, __( 'Delete this item', 'erp-pro' ), __( 'Delete', 'erp-pro' ) );

        return sprintf( '<a href="%1$s"><strong>%2$s</strong></a> %3$s', admin_url( 'admin.php?page=erp-hr-attendance&q=view&id=' . $item->date ), erp_format_date( $item->date ), $this->row_actions( $actions ) );
    }

    /**
     * Calculates the presense column
     *
     * @return string
     */
    function column_presence( $item ) {

        $employee = new \WeDevs\ERP\HRM\Models\Employee();
        $total = $item->present + $item->absent;
	    $percentage = 0;

        if ( $total > 0 ) {

            $percentage = intval( ( $item->present * 100 ) / $total, 0 );
        }

        return $percentage . ' %';
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {

        $sortable_columns = array(
            'date' => array( 'date', true ),
        );

        return $sortable_columns;
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    function get_bulk_actions() {

        $actions = array(
             'delete'  => __( 'Delete Permanently', 'erp-pro' ),
        );

        return $actions;
    }

    /**
     * Render the checkbox column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_cb( $item ) {

        return sprintf( '<input type="checkbox" name="attendance_date[]" value="%s" />', $item->date );
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views_() {

        $status_links   = array();
        $base_link      = admin_url( 'admin.php?page=sample-page' );

        foreach ($this->counts as $key => $value) {

            $class                = ( $key == $this->page_status ) ? 'current' : 'status-' . $key;
            $status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => $key ), $base_link ), $class, $value['label'], $value['count'] );
        }

        return $status_links;
    }

    /**
     * Prepare the class items
     *
     * @return void
     */
    function prepare_items() {

        $columns               = $this->get_columns();
        $hidden                = array( );
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';

        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
        );

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {

            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = $_REQUEST['order'] ;
        }

        $start = isset( $_REQUEST['start'] ) ? $_REQUEST['start'] : '';
        $end   = isset( $_REQUEST['end'] ) ? $_REQUEST['end'] : '';

        if ( isset( $_REQUEST['filter_duration'] ) && $_REQUEST['filter_duration'] ) {

            $time = $_REQUEST['filter_duration'];

            if ( '-1' != $time ) {

                if ( 'custom' == $time ) {

                    $args['start'] = $start;
                    $args['end']   = $end;

                } else {

                    $duration      = erp_att_get_start_end_date( $time );
                    $args['start'] = $duration['start'];
                    $args['end']   = $duration['end'];
                }
            }
        }

        $this->items  = erp_att_get_all_attendance( $args );

        $this->set_pagination_args( array(
            'total_items' => erp_att_get_attendance_count( $args ),
            'per_page'    => $per_page
        ) );
    }
}

