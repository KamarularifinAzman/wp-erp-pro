<?php
$profile_id = !empty( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ): get_current_user_id();
$args = array(
    'post_type'       => 'erp_hr_training',
    'posts_per_page'  => -1,
    'meta_query' => array(
        array(
            'key'       => 'erp_training_completed_employee',
            'value'     => $profile_id,
            'compare'   => 'LIKE',
        )
    ),
);

$last_changed = erp_cache_get_last_changed( 'hrm', 'training', 'erp-training' );
$cache_key    = 'erp-all-training-' . md5( serialize( $args ) ).": $last_changed";
$posts        = wp_cache_get( $cache_key, 'erp-training' );

if ( false === $posts ) {
    $posts = new WP_Query( $args );
    wp_cache_set( $cache_key, $posts, 'erp-training' );
}

$user_meta = get_user_meta( $profile_id, 'erp_employee_training', true );
?>
<h3><?php _e( 'Completed Training', 'erp-pro' ); ?></h3>
<?php
    if ( current_user_can( 'erp_hr_manager' ) ) {
        ?>
        <a href="#" id="erp-add-new-employee-training" class="action button"><span class="dashicons dashicons-plus erp-add-new-employee-training-icon"></span> <?php _e( 'Add New Training', 'erp-pro' ); ?>
        </a>
        <?php
    }
?>
<table class="widefat">
    <thead>
        <tr>
            <th><?php _e( 'Title', 'erp-pro' ) ?></th>
            <th><?php _e( 'Training Subject', 'erp-pro' ) ?></th>
            <th><?php _e( 'Completed Date', 'erp-pro' ) ?></th>
            <th><?php _e( 'Trainer', 'erp-pro' ) ?></th>
            <th><?php _e( 'Trainer Phone', 'erp-pro' ) ?></th>
            <th><?php _e( 'Cost', 'erp-pro' ) ?></th>
            <th><?php _e( 'Credit', 'erp-pro' ) ?></th>
            <th><?php _e( 'Hours', 'erp-pro' ) ?></th>
            <th><?php _e( 'Notes', 'erp-pro' ) ?></th>
            <?php
                if ( current_user_can( 'erp_hr_manager' ) ) {
                    ?>
                        <th>Completed</th>
                        <th><?php _e( 'Action', 'erp-pro' ); ?></th>
                    <?php
                }
            ?>
        </tr>
    </thead>

    <tbody>
        <?php
            if ( $posts->have_posts() ) {
                $i = 0 ;
                while ( $posts->have_posts() ) {
                    $posts->the_post();
                    $post_meta        = get_post_meta( get_the_ID(), 'erp_training_employee', true );
                    $meta_key         = ( isset( $post_meta[$profile_id] ) ) ? $post_meta[$profile_id] : '';

                    $data             = ( isset( $user_meta[ get_the_ID() ] ) ) ? $user_meta[ get_the_ID() ] : array();
                    $completed_date   = ( isset( $data['erp_training_completed_date'] ) ) ? $data['erp_training_completed_date'] : '';
                    $trainer          = ( isset( $data['erp_training_trainer'] ) ) ? $data['erp_training_trainer'] : '';
                    $trainer_phone    = ( isset( $data['erp_trainer_phone'] ) ) ? $data['erp_trainer_phone'] : '';
                    $training_cost    = ( isset( $data['erp_training_cost'] ) ) ? $data['erp_training_cost'] : '';
                    $training_credit  = ( isset( $data['erp_training_credit'] ) ) ? $data['erp_training_credit'] : '';
                    $training_hour    = ( isset( $data['erp_training_hours'] ) ) ? $data['erp_training_hours'] : '';
                    $training_note    = ( isset( $data['erp_training_note'] ) ) ? $data['erp_training_note'] : '';
                    $subject          = get_post_meta( get_the_ID(), 'training_subject', true );
                    $i++;
                    ?>
                    <tr class="<?php echo ( $i % 2 == 1 ) ? 'alternate' : '' ?>">
                        <td><?php the_title(); ?></td>
                        <td><?php echo $subject; ?></td>
                        <td><?php echo $completed_date ?></td>
                        <td><?php echo $trainer ?></td>
                        <td><?php echo $trainer_phone ?></td>
                        <td><?php echo $training_cost ?></td>
                        <td><?php echo $training_credit ?></td>
                        <td><?php echo $training_hour ?></td>
                        <td><?php echo $training_note ?></td>
                        <?php if( current_user_can( 'erp_hr_manager' ) ):?>
                            <td><input type="checkbox" class="check-incompleted-training" value="incompleted" checked user_id="<?php echo $profile_id ?>" post_id="<?php echo get_the_ID() ?>"></td>
                        <td><a href="" id="<?php echo $profile_id ?>" class="erp-edit-employee-training" data-id="<?php echo get_the_ID() ?>"><span class="dashicons dashicons-edit"></span></a>  <a href="" class="erp-delete-employee-training" data-id="<?php echo get_the_ID() ?>" id="<?php echo $profile_id ?>"><span class="dashicons dashicons-trash"></span></a></td>
                        <?php endif?>
                    </tr>
                    <?php
                }
            } else{
                ?>
                <tr>
                    <td colspan="7"><?php _e( 'No Training Found !', 'erp-pro' ) ?></td>
                </tr>
                <?php
            }

        ?>
    </tbody>
</table>
<h2><?php _e( 'Assigned Training', 'erp-pro' )?></h2>
<table class="widefat">
    <thead>
        <tr>
            <th><?php _e( 'Title', 'erp-pro' ) ?></th>
            <th><?php _e( 'Training Subject', 'erp-pro' ) ?></th>
            <?php
                if ( current_user_can( 'erp_list_employee' ) ) {
                    ?>
                        <th><?php _e( 'Completed', 'erp-pro' ); ?></th>
                    <?php
                }
            ?>
        </tr>
    </thead>

    <?php
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

    ?>
    <tbody class="erp-incomplete-training">
        <?php
            if ( $incompleted->have_posts() ) {
                $i = 0 ;
                while ( $incompleted->have_posts() ) {
                    $incompleted->the_post();
                    $post_meta        = get_post_meta( get_the_ID(), 'erp_training_incompleted_employee', true );
                    $meta_key         = ( isset( $post_meta[$profile_id] ) ) ? $post_meta[$profile_id] : '';

                    $data             = ( isset( $user_meta[ get_the_ID() ] ) ) ? $user_meta[ get_the_ID() ] : array();
                    $completed_date   = ( isset( $data['erp_training_completed_date'] ) ) ? $data['erp_training_completed_date'] : '';
                    $trainer          = ( isset( $data['erp_training_trainer'] ) ) ? $data['erp_training_trainer'] : '';
                    $trainer_phone    = ( isset( $data['erp_trainer_phone'] ) ) ? $data['erp_trainer_phone'] : '';
                    $training_cost    = ( isset( $data['erp_training_cost'] ) ) ? $data['erp_training_cost'] : '';
                    $training_credit  = ( isset( $data['erp_training_credit'] ) ) ? $data['erp_training_credit'] : '';
                    $training_hour    = ( isset( $data['erp_training_hours'] ) ) ? $data['erp_training_hours'] : '';
                    $training_note    = ( isset( $data['erp_training_note'] ) ) ? $data['erp_training_note'] : '';
                    $subject          = get_post_meta( get_the_ID(), 'training_subject', true );
                    $i++;
                    ?>
                    <tr class="<?php echo ( $i % 2 == 1 ) ? 'alternate' : '' ?>">
                        <td><?php the_title(); ?></td>
                        <td><?php echo $subject; ?></td>

                        <td><input type="checkbox" id="<?php echo $profile_id ?>" class="erp-edit-employee-training" data-id="<?php echo get_the_ID() ?>"></td>
                    </tr>
                    <?php
                }
            } else{
                ?>
                <tr>
                    <td colspan="7"><?php _e( 'No Training Found !', 'erp-pro' ) ?></td>
                </tr>
                <?php
            }

        ?>
    </tbody>
</table>
