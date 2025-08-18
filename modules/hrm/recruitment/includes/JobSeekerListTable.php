<?php
namespace WeDevs\Recruitment;

/**
 * List table class
 */
class JobSeekerListTable extends \WP_List_Table {

    protected $page_status;
    function __construct() {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'jobseeker',
            'plural'   => 'jobseekers',
            'ajax'     => false
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

        $selected_status = (isset($_REQUEST['filter_status'])) ? $_REQUEST['filter_status'] : -1;
        $jobid = (isset($_REQUEST['jobid'])) ? $_REQUEST['jobid'] : 0;

        ?>
        <div class="alignleft actions">
            <label class="screen-reader-text" for="new_role"><?php _e( 'Filter by Status', 'erp-pro' ) ?></label>
            <select name="filter_status" id="filter_status_select">
                <option value="-1"><?php _e('- Select All -', 'erp-pro'); ?></option>
                <?php echo erp_hr_get_status_dropdown($selected_status); ?>
            </select>
            <input type="hidden" name="jobid" value="<?php echo $jobid;?>">
        <?php
        submit_button(__('Filter'), 'button', 'filter_status_button', false);
        submit_button(__('Download All CV'), 'button dacv', 'download_all_cv', false);
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
        _e( 'No jobseeker found.', 'erp-pro' );
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
            'cb'         => '<input type="checkbox" />',
            'full_name'  => __( 'Name', 'erp-pro' ),
            'stage'      => __( 'Stage', 'erp-pro' ),
            'avg_rating' => __( 'Rating', 'erp-pro' ),
            'apply_date' => __( 'Date', 'erp-pro' ),
            'action'     => __( 'Action', 'erp-pro' )
        );

        return apply_filters( 'erp_hr_jobseeker_table_cols', $columns );
    }

    /**
     * Show default column
     *
     * @since  1.0.0
     *
     * @param  array    $item
     * @param  string    $column_name
     *
     * @return string
     */
    public function column_default( $item, $column_name ) {
        $jobseeker_preview_url = erp_rec_url( 'applicant_detail&application_id=' . $item['applicationid'] );
        $send_email_url        = erp_rec_url( 'jobid=' . $item['applicationid'] . '&sub-section=jobseeker_list_email&email_ids[]=' . $item['email'] );

        switch ($column_name) {
            case 'apply_date':
                return erp_format_date( $item['apply_date'] );
            case 'full_name':
                return sprintf(__('<a href="%s">' . $item['first_name'] . ' ' . $item['last_name'] . '</a>', 'erp-pro'), $jobseeker_preview_url);
            case 'avg_rating':
                return number_format($item['avg_rating'], 2, '.', ',');
            case 'job_title':
                return $item['post_title'];
            case 'stage':
                return $item['title'];
            case 'action':
                $actions = [
                    'detail' => sprintf( '<a class="button button-sescondary button-small" href="%s"><span class="dashicons dashicons-visibility"></span></a>', $jobseeker_preview_url ),
                    'email'  => sprintf( '<a class="button button-sescondary button-small" href="%s"><span class="dashicons dashicons-email-alt"></span></a>', $send_email_url )
                ];

                $attachments = erp_people_get_meta( $item['applicant_id'], 'attach_id' );

                foreach ( $attachments as $index => $attach_id ) {
                    $actions["cv_$index"] = sprintf(
                        '<a title="%s" class="button button-sescondary button-small" target="_blank" href="%s">
                            <span class="dashicons dashicons-download"></span>
                        </a>',
                        __( 'Download CV', 'erp-pro' ),
                        wp_get_attachment_url( $attach_id )
                    );
                }

                return implode( ' ', $actions );
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
            'apply_date' => array( 'apply_date', true ),
            'full_name'  => array( 'full_name', true ),
            'avg_rating' => array( 'avg_rating', true ),
            'stage'      => array( 'title', true ),
            'job_title'  => array( 'post_title', true ),
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
            '<input type="checkbox" name="bulk-email[]" value="%s" />', $item['email']
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
            'bulk-email' => __( 'Send Email', 'erp-pro' ),
            'bulk-delete-jobseeker' => __( 'Delete', 'erp-pro' )
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

        if ( isset( $_REQUEST['download_all_cv'] ) ) {
            return 'download_all_cv';
        }

        return parent::current_action();
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

        if ( empty($_REQUEST['s']) && !$this->has_items() ) {
            return;
        }

        $input_id = $input_id . '-search-input';

        if ( !empty($_REQUEST['orderby']) ) {
            echo '<input type="hidden" name="orderby" value="' . esc_attr($_REQUEST['orderby']) . '" />';
        }

        if ( !empty($_REQUEST['order']) ) {
            echo '<input type="hidden" name="order" value="' . esc_attr($_REQUEST['order']) . '" />';
        }

        if ( !empty($_REQUEST['status']) ) {
            echo '<input type="hidden" name="status" value="' . esc_attr($_REQUEST['status']) . '" />';
        }

        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
            <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>"/>
            <?php submit_button($text, 'button', 'recruitment_search', false, array('id' => 'search-submit')); ?>
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
        $this->_column_headers = array($columns, $hidden, $sortable);

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ($current_page - 1) * $per_page;
        $this->page_status     = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : '2';
        $jobid                 = isset($_REQUEST['jobid']) ? $_REQUEST['jobid'] : 0;
        $filter_status         = isset($_REQUEST['filter_status']) ? $_REQUEST['filter_status'] : '-1' ;

        // only necessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
            'jobid'  => $jobid,
            'status' => $filter_status
        );

        if ( isset($_REQUEST['filter_stage']) && $_REQUEST['filter_stage'] ) {
            $args['stage'] = $_REQUEST['filter_stage'];
        }

        if ( isset($_REQUEST['filter_added_by_me']) && $_REQUEST['filter_added_by_me'] ) {
            $args['added_by_me'] = $_REQUEST['filter_added_by_me'];
        }

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order'] = $_REQUEST['order'] ;
        }

        if ( isset($_REQUEST['s']) ) {
            $args['search_key'] = $_REQUEST['s'];
        }

        $this->items = erp_rec_get_applicants_information($args);
        $total_rows  = erp_rec_total_applicant_counter($args);

        $this->set_pagination_args(array(
            'total_items' => $total_rows,
            'per_page'    => $per_page
        ));
    }
}
