<?php
global $post;

$query = new \WP_Query( [
    'post_type'      => $this->post_type,
    'posts_per_page' => -1,
    'order'          => 'DESC',
    'orderby'        => 'post_date',
    'meta_query' => [
	    'relation' => 'OR',
        [
            'key'     => '_expire_date',
            'value'   => date( 'Y-m-d' ),
            'compare' => '>=',
        ],
	    [
		    'key'     => '_expire_date',
		    'value'   => '',
		    'compare' => '=',
	    ],

    ],
] );

$localized_exp_type = [
    'required'  => __( 'Required', 'erp-pro' ),
    'preferred' => __( 'Preferred', 'erp-pro'),
];

if ( $query->have_posts() ): ?>
    <?php $departments = erp_hr_get_departments_dropdown_raw(); unset( $departments['-1'] ); ?>

    <div id="job-ul-list-header">
        <label id="job-ul-list-header-label"><?php _e( 'Job List', 'erp-pro' ); ?></label>
        <select name="department" id="key-department">
            <option value=""><?php _e( '&mdash; Show all &mdash;', 'erp-pro' ); ?></option>
            <?php foreach ( $departments as $id => $title ) : ?>
                <option value="<?php echo $title; ?>"><?php echo $title; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="front-job-list">

        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <?php

            $dep             = get_post_meta( get_the_ID(), '_department', true ) ? get_post_meta( get_the_ID(), '_department', true ) : 0;
            $employment_type = get_post_meta( get_the_ID(), '_employment_type', true ) ? get_post_meta( get_the_ID(), '_employment_type', true ) : '-';
            $min_exp         = get_post_meta( get_the_ID(), '_minimum_experience', true ) ? get_post_meta( get_the_ID(), '_minimum_experience', true ) : '-';
            $exp_field       = get_post_meta( get_the_ID(), '_experience_field', true) ? get_post_meta( get_the_ID(), '_experience_field', true) : '';
            $def_exp_type    = $min_exp !== 'Fresher' ? 'required' : '';
            $exp_type        = get_post_meta( get_the_ID(), '_experience_type', true) ? get_post_meta( get_the_ID(), '_experience_type', true) : $def_exp_type;
            $exp_type        = esc_html( isset( $localized_exp_type[ $exp_type ] ) ? $localized_exp_type[ $exp_type ] : $exp_type );
            $expire_date     = get_post_meta( get_the_ID(), '_expire_date', true ) ? get_post_meta( get_the_ID(), '_expire_date', true ) : '';
            $date            = date('Y-m-d');
            $date            = date('Y-m-d', strtotime('+30 days', strtotime($date)));
            $e_date          = date_create( $expire_date == 'N/A' ? $date : $expire_date );
            $current_date    = date_create( date( 'Y-m-d' ) );
            $diff            = date_diff( $current_date, $e_date );

            $exp_date        = date( 'Y-m-d', strtotime( get_post_meta( get_the_ID(), '_expire_date', true ) ) );
            $dname           = '';

            if ( $dep ) {
                $department_name = new \WeDevs\ERP\HRM\Department( intval( $dep ) );
                $dname = ( $department_name->title != "" ) ? $department_name->title : '';
            }
            ?>
            <div class="erp-rec-job-list <?php echo $dname; ?>">
                <div class="hoverparts"></div>
                <a href="<?php the_permalink(); ?>">
                    <div class="jparts">
                        <span class="job-title"><?php the_title(); ?></span>
                        <span class="department"><?php echo $dname; ?></span>
                    </div>
                    <div class="jparts">
                        <span class="min-exp"><?php echo $min_exp; ?></span>
                        <?php if ( ! empty( $exp_type ) ) : ?>
                            <span class="min-exp"><?php printf( '(%s)', $exp_type ); ?></span>
                        <?php endif; ?>
                        <span class="min-exp-caption"><?php _e( 'Experience', 'erp-pro' ); ?></span>
                    </div>
                    <div class="employment-type jparts"><?php echo $employment_type; ?></div>
                    <div class="expire-date jparts">
                        <?php if(!empty($expire_date)):?>
                        <span><?php echo __( 'Deadline: ', 'erp-pro' ) . date( 'M d, Y', strtotime( $expire_date == 'N/A' ? $date : $expire_date ) ); ?></span>
                        <span class="daysleft"><?php echo $diff->format( "%a " ) . __( 'days left', 'erp-pro' ); ?></span>
                        <?php else: ?>
                            <span>&mdash;</span>
                        <?php endif; ?>
                    </div>
                </a>

            </div>

        <?php endwhile; ?>
    </div>

<?php
endif;
wp_reset_postdata();
