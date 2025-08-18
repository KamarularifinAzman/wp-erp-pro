<?php
namespace WeDevs\HrTraining;

use WP_Query;
/**
 * ERP Trainig Employee class
 */

class TrainingEmployee {
    /**
     * Construct function
     */
    function __construct() {
        add_action( 'erp_hr_employee_single_tabs', array( $this, 'employee_training_tab' ) );

        add_action( 'admin_footer', array( $this, 'employee_template' ) );

        add_action( 'admin_footer', array( $this, 'assign_new_training_template' ) );

        add_action( 'erp_hr_employee_new', array( $this, 'assign_new_employee' ), 10, 2 );
    }

    /**
     * Employee template
     *
     * @return void
     */
    public function employee_template() {
        erp_get_js_template( WPERP_TRAINING_INCLUDES . '/views/employee-training.php', 'employee-training' );
    }

    /**
     * Employee Training Tab
     *
     * @param  array $tabs
     * @return array
     */
    public function employee_training_tab( $tabs ) {
        $profile_id = !empty( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ): get_current_user_id();

        if ( $profile_id !== get_current_user_id() && !current_user_can( 'erp_hr_manager' ) ) {
            return $tabs;
        }

        $args = array(
            'post_type'       => 'erp_hr_training',
            'posts_per_page'  => -1,
            'meta_query' => array(
                array(
                    'key'       => 'erp_training_incompleted_employee',
                    'value'     => $profile_id,
                    'compare'   => 'LIKE',
                )
            ),
        );

        $last_changed = erp_cache_get_last_changed( 'hrm', 'training', 'erp-training' );
        $cache_key    = 'erp-all-training-' . md5( serialize( $args ) ).": $last_changed";
        $incompleted  = wp_cache_get( $cache_key, 'erp-training' );

        if ( false === $incompleted ) {
            $incompleted = new WP_Query( $args );
            wp_cache_set( $cache_key, $incompleted, 'erp-training' );
        }

        $total_training = ( $incompleted->found_posts > 0 ) ? '( ' . $incompleted->found_posts . ' ) ' : '';

        $tabs['training'] = array(
            'title'    => __( 'Training' . $total_training, 'erp-pro' ),
            'callback' => array( $this, 'employees_training_tab_content' )
        );

        return $tabs;
    }

    /**
     * Employee training content
     *
     * @return void
     */
    public function employees_training_tab_content() {
        require_once WPERP_TRAINING_INCLUDES . '/views/employee-training-list.php';
    }

    public function assign_new_training_template() {
        erp_get_js_template( WPERP_TRAINING_INCLUDES . '/views/assign-new-traing.php', 'employee-assign-new-training' );
    }

    /**
     * Auto assign the training
     *
     * @param  integer $user_id
     *
     * @param  array $data
     * @return void
     */
    public function assign_new_employee( $user_id, $data ) {
        $args = array(
            'post_type'       => 'erp_hr_training',
            'posts_per_page'  => -1,
            'meta_query' => array(
                array(
                    'key'       => 'auto_assigned',
                    'value'     => 'yes',
                )
            ),
        );

        $last_changed = erp_cache_get_last_changed( 'hrm', 'training', 'erp-training' );
        $cache_key    = 'erp-all-training-' . md5( serialize( $args ) ).": $last_changed";
        $posts  = wp_cache_get( $cache_key, 'erp-training' );

        if ( false === $posts ) {
            $posts = new WP_Query( $args );
            wp_cache_set( $cache_key, $posts, 'erp-training' );
        }

        while ( $posts->have_posts() ) {
            $posts->the_post();

            $training_users     = get_post_meta( get_the_ID(), 'erp_training_incompleted_employee', true );
            $training_users[]   = $user_id;

            update_post_meta( get_the_ID(), 'erp_training_incompleted_employee', $training_users );
        }

        erp_training_purge_cache( ['list' => 'training'] );
    }
}

/**
 * Purge cache data for training addon
 *
 * Remove all cache for training addon
 *
 * @since 1.1.4
 *
 * @param array $args
 *
 * @return void
 */
function erp_training_purge_cache( $args = [] ) {

    $group = 'erp-training';

    if ( isset( $args['list'] ) ) {
        erp_purge_cache( [ 'group' => $group, 'module' => 'hrm', 'list' => $args['list'] ] );
    }

}
