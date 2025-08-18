<div class="wrap">
    <div class="att-hr-report-area" style="width:95%">
        <h2><?php use WeDevs\Attendance\AttendanceReportEmployeeBased;

            _e( 'Attendance Report', 'erp-pro' ); ?></h2>
		<?php include WPERP_ATTEND_VIEWS . '/filter-toolbar.php'; ?>
		<?php

		$export_link = add_query_arg( [
			'action'     => 'erp_att_export_attendance_csv',
			'nonce'      => wp_create_nonce( 'export_attendance_csv' ),
			'location'   => isset( $_GET['location'] ) ? intval( $_GET['location'] ) : '',
			'department' => isset( $_GET['department'] ) ? intval( $_GET['department'] ) : '',
			'query_time' => isset( $_GET['query_time'] ) ? esc_attr( $_GET['query_time'] ) : 'this_month',
			'start'      => isset( $_GET['start'] ) ? esc_attr( $_GET['start'] ) : '',
			'end'        => isset( $_GET['end'] ) ? esc_attr( $_GET['end'] ) : '',
		], admin_url( 'admin-post.php' ) );

		?>

        <a class="button-secondary" href="<?php echo $export_link; ?>" target="_blank"><?php _e( 'Export CSV', 'erp' ); ?></a>
		<?php
		$attendance = new AttendanceReportEmployeeBased();
		$attendance->prepare_items();
		$attendance->display();
		?>

    </div>
</div>

