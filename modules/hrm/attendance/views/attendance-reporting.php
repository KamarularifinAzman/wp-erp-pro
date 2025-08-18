<?php
global $wpdb;

$selected_query_time = isset( $_REQUEST['query_time'] ) ? $_REQUEST['query_time'] : 'this_month';
$selected_location   = isset( $_REQUEST['location'] ) ? $_REQUEST['location'] : '';
$selected_department = isset( $_REQUEST['department'] ) ? $_REQUEST['department'] : '';
$date_range_start    = isset( $_REQUEST['start'] ) ? $_REQUEST['start'] : '';
$date_range_end      = isset( $_REQUEST['end'] ) ? $_REQUEST['end'] : '';

if ( 'custom' != $selected_query_time ) {
	$duration         = erp_att_get_start_end_date( $selected_query_time );
	$date_range_start = $duration['start'];
	$date_range_end   = $duration['end'];
}

$sql = "SELECT user_id FROM {$wpdb->prefix}erp_hr_employees WHERE status='active' AND user_id != '0' ";

//if filter by location
if ( $selected_location && '-1' != $selected_location ) {
	$sql .= " AND location = '{$selected_location}' ";
}

if ( $selected_department && '-1' != $selected_department ) {
	$sql .= " AND department = '{$selected_department}' ";
}

$employees         = $wpdb->get_col( "{$sql} order by user_id" );
$locations         = erp_company_get_location_dropdown_raw();
$dept_dropdown_raw = erp_hr_get_departments_dropdown_raw();
$query_times       = erp_att_get_query_times();

$attendances = [];
// If there are no employees found based on current query, then attendances must be empty.
if ( ! empty( $employees ) ){
    $attendances = erp_get_attendance_summary( $employees, $date_range_start, $date_range_end, 'date_based' );
}

$export_link = add_query_arg( [
	'action'     => 'erp_att_export_attendance_csv',
	'nonce'      => wp_create_nonce( 'export_attendance_csv' ),
	'type'       => 'date_based',
	'location'   => isset( $_GET['location'] ) ? intval( $_GET['location'] ) : '',
	'department' => isset( $_GET['department'] ) ? intval( $_GET['department'] ) : '',
	'query_time' => isset( $_GET['query_time'] ) ? esc_attr( $_GET['query_time'] ) : 'this_month',
	'start'      => isset( $_GET['start'] ) ? esc_attr( $_GET['start'] ) : '',
	'end'        => isset( $_GET['end'] ) ? esc_attr( $_GET['end'] ) : '',
], admin_url( 'admin-post.php' ) );

?>
<div class="wrap">
    <div class="att-hr-report-area" style="width:95%">
        <h2><?php _e( 'Attendance Reports', 'erp-pro' ); ?></h2>

        <div class="att-reportquery-form">
            <form method="get" style="display: inline-block;">
                <?php if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ): ?>
                    <input type="hidden" name="page" value="erp-hr-reporting">
                <?php else: ?>
                    <input type="hidden" name="page" value="erp-hr">
                    <input type="hidden" name="section" value="report">
                <?php endif?>
                <input type="hidden" name="type" value="attendance-report">
                <select name="location">
					<?php
					foreach ( $locations as $id => $name ) {
						echo '<option value="' . $id . '"' . selected( $selected_location, $id ) . ' > ' . $name . '</option>';
					}
					?>
                </select>
                <select name="department">
					<?php
					foreach ( $dept_dropdown_raw as $id => $name ) {
						echo '<option value="' . $id . '"' . selected( $selected_department, $id ) . ' > ' . $name . '</option>';
					}
					?>
                </select>
                <select name="query_time" id="att-reporting-query">
					<?php
					foreach ( $query_times as $key => $value ) {
						echo '<option value="' . $key . '"' . selected( $selected_query_time, $key ) . '>' . $value . '</option>';

					}
					?>
                </select>

				<?php
				if ( 'custom' == $selected_query_time ) {
					?>
                    <span id="custom-input">
                        <span>From </span>
                        <input name="start" class="attendance-date-field" type="text" value="<?php echo $date_range_start; ?>">&nbsp;
                        <span>To </span>
                        <input name="end" class="attendance-date-field" type="text" value="<?php echo $date_range_end ?>"/>
                    </span>&nbsp;
					<?php
				}
				?>
				<?php wp_nonce_field( 'epr-att-hr-reporting-filter' ); ?>
                <button type="submit" class="button-secondary" name="filter_att_hr_reporting"><?php _e( 'Filter', 'erp' ); ?></button>
            </form>
            <a class="button-secondary" href="<?php echo $export_link; ?>" target="_blank"><?php _e( 'Export CSV', 'erp' ); ?></a>
        </div>


        <div id="employee-attendance-table">
            <table class="widefat striped">
                <thead>
                <tr>
                    <th><?php _e( 'Date', 'erp-pro' ); ?></th>
                    <th><?php _e( 'Total', 'erp-pro' ); ?></th>
                    <th><?php _e( 'Present', 'erp-pro' ); ?></th>
                    <th><?php _e( 'Leave', 'erp-pro' ); ?></th>
                    <th><?php _e( 'Absent', 'erp-pro' ); ?></th>
                    <th><?php _e( 'Comment', 'erp-pro' ); ?></th>
                </tr>
                </thead>
                <tbody>

				<?php
                if ( !empty( $attendances ) ) {
                    foreach ( $attendances as $attendance ) {
                        echo '<tr>';
                        echo '<td>' . erp_format_date( $attendance['date'] ) . '</td>';
                        echo isset( $attendance['total'] ) ? '<td>' . $attendance['total'] . '</td>' : '<td>0</td>';
                        echo isset( $attendance['total_present'] ) ? '<td>' . $attendance['total_present'] . '</td>' : '<td>0</td>';
                        echo isset( $attendance['total_leaves'] ) ? '<td>' . $attendance['total_leaves'] . '</td>' : '<td>0</td>';
                        echo isset( $attendance['total_absent'] ) ? '<td>' . $attendance['total_absent'] . '</td>' : '<td>0</td>';
                        echo isset( $attendance['comment'] ) ? '<td>' . $attendance['comment'] . '</td>' : '<td>&mdash;</td>';
                        echo '</tr>';
                    }
                }
				?>
                </tbody>
            </table>
        </div>
    </div>
</div>

