<?php
$selected_query_time       = isset( $_REQUEST['query_time'] ) ? $_REQUEST['query_time'] : 'this_month';
$selected_location         = isset( $_REQUEST['location'] ) ? $_REQUEST['location'] : '';
$selected_department       = isset( $_REQUEST['department'] ) ? $_REQUEST['department'] : '';
$selected_start            = isset( $_REQUEST['start'] ) ? $_REQUEST['start'] : '';
$selected_end              = isset( $_REQUEST['end'] ) ? $_REQUEST['end'] : '';
$duration                  = [];
if ( 'custom' == $selected_query_time ) {
    $duration['start'] = $selected_start;
    $duration['end']   = $selected_end;
} else {
    $duration = erp_att_get_start_end_date( $selected_query_time );
}
$locations                 = erp_company_get_location_dropdown_raw();
$departments               = erp_hr_get_departments();
$dept_dropdown_raw         = erp_hr_get_departments_dropdown_raw();
$query_times               = erp_att_get_query_times();
?>

<div class="att-reportquery-form" style="display: inline;">
    <form method="get" style="display: inline;">
        <?php if ( version_compare( WPERP_VERSION , '1.4.0', '<' ) ): ?>
            <input type="hidden" name="page" value="erp-hr-reporting">
        <?php else: ?>
            <input type="hidden" name="page" value="erp-hr">
            <input type="hidden" name="section" value="report">
        <?php endif?>
        <input type="hidden" name="type" value="<?php echo $_REQUEST['type'];?>">
        <select name="location">
            <?php
            foreach ( $locations as $id => $name ) {
                echo '<option value="' . $id . '"' . selected( $selected_location, $id ) . '>' . $name . '</option>' ;
            }
            ?>
        </select>
        <select name="department">
            <?php
            foreach ( $dept_dropdown_raw as $id => $name ) {
                echo '<option value="' . $id . '"' . selected( $selected_department, $id ) . '>' . $name . '</option>' ;
            }
            ?>
        </select>
        <select name="query_time" id="att-reporting-query">
            <?php
            foreach ( $query_times as $key => $value ) {
                echo '<option value="' . $key . '"' . selected( $selected_query_time, $key ) . '>' . $value . '</option>' ;

            }
            ?>
        </select>

        <?php
        if ( 'custom' == $selected_query_time ) {
            ?>
            <span id="custom-input"><span>From </span><input name="start" class="attendance-date-field" type="text" value="<?php echo $selected_start; ?>">&nbsp;<span>To </span><input name="end" class="attendance-date-field" type="text" value="<?php echo $selected_end ?>"></span>&nbsp;
            <?php
        }
        ?>
        <?php wp_nonce_field( 'epr-att-hr-reporting-filter' ); ?>
        <button type="submit" class="button-secondary" name="filter_att_hr_reporting"><?php _e( 'Filter', 'erp' ); ?></button>

    </form>
</div>