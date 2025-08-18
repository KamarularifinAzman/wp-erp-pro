<?php
/**
 * Get active shifts
 * @since 2.0.0
 *
 * @return array
 *
 */
function erp_attendance_get_shifts($args = []) {
	global $wpdb;

    $defaults = [
        'limit'  => 20,
        'offset' => 0,
        'order'  => 'DESC',
        'count'  => false
	];

    $args = wp_parse_args( $args, $defaults );

    $last_changed = erp_cache_get_last_changed( 'hrm', 'shifts', 'erp-attendance' );
    $cache_key    = 'erp-get-shifts-' . md5( serialize( $args ) ) . " : $last_changed";
    $shifts       = wp_cache_get( $cache_key, 'erp-attendance' );

    $cache_key_count  = 'erp-get-shifts-counts-' . md5( serialize( $args ) ) . " : $last_changed";
    $shifts_count     = wp_cache_get( $cache_key_count, 'erp-attendance' );

    if( false === $shifts ) {
        $limit = '';

        $table = "{$wpdb->prefix}erp_attendance_shifts";

        if ( $args['count'] ) {
            $shifts_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 1" );
            wp_cache_set( $cache_key_count, $shifts_count, 'erp-attendance' );
            return $shifts_count;
        }

        if ( ! empty( $args['limit'] ) ) {
            $limit = "LIMIT {$args['offset']}, {$args['limit']}";
        }

        $query  = "SELECT * FROM {$table} WHERE status = 1 ORDER BY id {$args['order']} {$limit}";
        $shifts = $wpdb->get_results( $query );

        wp_cache_set( $cache_key, $shifts, 'erp-attendance' );
    }

    if ( $args['count'] ) {
        return $shifts_count;
    }

	return $shifts;
}

/**
 * Check validity of a time
 * @since 1.0.0
 *
 * @param $time 23:00:00
 *
 * @return false|int
 *
 */
function erp_attendance_is_valid_time( $time ) {
	return preg_match( '#^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$#', $time );
}

/**
 * Get grace checkin time
 *
 * @since 1.0.0
 *
 * @return string
 *
 */
function erp_attendance_get_grace_checkin_time() {

	// $starting_time = get_option( 'erp_attendance_grace_checkin_time', $grace_before_checkin );
    $starting_time = ( get_option( 'grace_before_checkin' , '15' ) * 60 );

	return apply_filters( 'erp_attendance_grace_checkin_time', $starting_time );
}

/**
 * Update grace checkin time
 *
 * @since 1.0.0
 *
 * @param $time
 *
 * @return void
 *
 */
function erp_attendance_update_grace_checkin_time( $time ) {
	update_option( 'erp_attendance_grace_checkin_time', intval( $time ) );
}

/**
 * Get punching shift
 *
 * @since 2.0.0
 *
 * @param int $user_id
 * @param string $timestamp
 *
 * @return Object
 *
 */
function erp_attendance_get_punching_shift( $user_id, $timestamp = null ) {
	global $wpdb;
	$grace_checkin_time = erp_attendance_get_grace_checkin_time();


	if ( ! $timestamp ) {
		$timestamp = current_time( 'timestamp' );
	}


	//@todo check if user is active employee or not

    $user_shift = erp_attendance_get_user_shift( $user_id );


	if ( empty( $user_shift ) ) {
		$subject = $user_id === get_current_user_id() ? __( 'You are ', 'erp_attendance' ) : __( 'The user is ', 'erp_attendance' );

		return new WP_Error( 'action-not-permitted', sprintf( __( 'Logging not permitted because %s not assigned to any shift.', 'erp_attendance' ), $subject ) );
    }

	$date       = date( 'Y-m-d H:i:s', $timestamp );
    $grace_date = date('Y-m-d H:i:s',strtotime('+'. $grace_checkin_time .' seconds',strtotime($date)));


    //$date_shift = $wpdb->get_row( sprintf( "SELECT * FROM {$wpdb->prefix}erp_attendance_date_shift WHERE user_id='%s' AND date='%s' limit 1", intval( $user_id ), $date ) );

    $date_shift = $wpdb->get_row( sprintf( "SELECT * FROM {$wpdb->prefix}erp_attendance_date_shift WHERE user_id='%s' AND start_time <'%s' AND end_time > '%s' limit 1", intval( $user_id ), $grace_date, $grace_date ) );

    //@todo auto assign shift if result not fouund

    if ( empty( $date_shift ) ) {
        return new WP_Error( 'invalid-time', __( 'Sorry !!! No shift found.', 'erp_attendance' ) );
    }


	$min_allowed_time             = strtotime( "- $grace_checkin_time minutes", strtotime( $date_shift->start_time ) );
	$full_time_without_grace_time = 24 * 60 - $grace_checkin_time;
    $max_allowed_time             = strtotime( "+ $full_time_without_grace_time minutes", strtotime( $date_shift->start_time ) );

	if ( $timestamp < $min_allowed_time ) {
		//$date       = date( 'Y-m-d', strtotime( '-1 day', $timestamp ) );
        //$date_shift = $wpdb->get_row( sprintf( "SELECT * FROM {$wpdb->prefix}erp_attendance_date_shift WHERE user_id='%s' AND date='%s' limit 1", intval( $user_id ), $date ) );
    }

    $date_shift->user_shift_duration = $user_shift->duration;

	return $date_shift;
}

/**
 * Employee punch
 *
 * @since  2.0.0
 *
 * @param  int $user_id
 * @param  sting $timestamp
 *
 * @return Object
 *
 */
function erp_attendance_punch( $user_id, $timestamp = null ) {
	global $wpdb;
	$action = 'checkin';

    erp_attendance_purge_cache( ['list' => 'attendance'] );

	$user = new WP_User( $user_id );
	if ( ! user_can( $user, 'employee' ) ) {
		return new WP_Error( 'invalid-user-type', __( 'You are not allowed for this feature. Please contact admin.', 'erp_attendance' ) );
	}

	if ( empty( $timestamp ) ) {
		$timestamp = current_time( 'timestamp' );
	}


	if ( $timestamp > current_time( 'timestamp' ) ) {
		return new WP_Error( 'invalid-time', __( 'Future time is not allowed.', 'erp_attendance' ) );
    }

    $date_shift = erp_attendance_get_punching_shift( $user_id, $timestamp );

	if ( is_wp_error( $date_shift ) ) {
        return new WP_Error( 'invalid-time', __( 'Date shift is not found.', 'erp_attendance' ) );
    }


	$checin_record = $wpdb->get_row( sprintf( "SELECT * FROM {$wpdb->prefix}erp_attendance_log WHERE date_shift_id='%s' ORDER BY created_at DESC", $date_shift->id ) );

	if ( $checin_record && ( empty( $checin_record->checkout ) || $checin_record->checkout == '0000-00-00 00:00:00' ) ) {
		$action = 'checkout';
    }

	if ( $action == 'checkin' ) {

        /****** Check & prevent if user try to checkin so quickly after checkout -- Start ******/

        if ( $checin_record && ( ! empty( $checin_record->checkout ) || $checin_record->checkout != '0000-00-00 00:00:00' ) ) {

            $last_checkout   = strtotime( $checin_record->checkout );
            $last_check_diff = $timestamp - $last_checkout;

            $diff_threshhold = get_option( 'erp_att_diff_threshhold', 60 );

            if ( $last_check_diff < $diff_threshhold ) {
                return new WP_Error( 'invalid-time', __( 'Too early checkin after last checkout.', 'erp_attendance' ) );
            }
        }

        /****** Check & prevent if user try to checkin so quickly after checkout -- End ******/

	    $grace_after_checkin  = get_option( 'grace_after_checkin', 15 ) * 60 ;
		$start_time_timestamp = strtotime($date_shift->start_time) + $grace_after_checkin;
		$late                 = 0;

		if ( $start_time_timestamp < $timestamp ) {
			$late = $timestamp - $start_time_timestamp;
            if ( $date_shift->present == 1 && $date_shift->late != NULL) {
                $late = $date_shift->late ;
            }
		}

		$checkin_sql = sprintf( "INSERT INTO {$wpdb->prefix}erp_attendance_log (user_id, checkin,checkout,date_shift_id) VALUES ('%s', '%s', '%s', '%d')", intval( $user_id ), date( 'Y-m-d H:i:s', $timestamp ), null, intval( $date_shift->id ) );

        $checkin_entry = $wpdb->query( $checkin_sql );

		$wpdb->query(sprintf("UPDATE {$wpdb->prefix}erp_attendance_date_shift SET late='%s', present='%s', early_left=NULL WHERE id='%s'", $late,'1', $date_shift->id ));

		return $checkin_entry;
    }

    /**
     * Early left
     */
    $early_left = null;
    $shift_end_time = ( strtotime( $date_shift->start_time ) + $date_shift->user_shift_duration );

    if ( $shift_end_time > $timestamp ) {
        $early_left = $shift_end_time - $timestamp;
    }

    $wpdb->query( sprintf( "UPDATE {$wpdb->prefix}erp_attendance_date_shift SET early_left='%s' WHERE id='%d'", $early_left, $date_shift->id ) );

    /**
     * FInally checkout
     */
	$time = $timestamp - strtotime( $checin_record->checkin );

	if ( $time <= 0 ) {
        return new WP_Error( 'invalid-time', __( 'Checkout must be prior to checkin', 'erp_attendance' ) );
    }

	return $wpdb->query( sprintf( "UPDATE {$wpdb->prefix}erp_attendance_log SET checkout ='%s', time = '%s' WHERE id='%d'", date( 'Y-m-d H:i:s', $timestamp ), $time, $checin_record->id ) );
}

/**
 * Removean employee from shift
 *
 * @since  2.0.0
 *
 * @param  int $shift_id
 * @param  int $users_id
 *
 * @return void
 *
 */
function erp_att_remove_employees_from_shift( $shift_id, $users_id ) {
	global $wpdb;

    erp_attendance_purge_cache( ['list' => 'attendance'] );

	$wpdb->query("DELETE FROM {$wpdb->prefix}erp_attendance_shift_user WHERE shift_id = {$shift_id} AND user_id IN ({$users_id})");
	$wpdb->query("DELETE FROM {$wpdb->prefix}erp_attendance_date_shift WHERE shift_id = {$shift_id} AND user_id IN ({$users_id})");
}
