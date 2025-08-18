<?php
error_reporting(0);
$user_id             = isset( $_REQUEST['id'] ) ? intval( wp_unslash( $_REQUEST['id'] ) ) : '';
$selected_query_time = isset( $_REQUEST['query_time'] ) ? $_REQUEST['query_time'] : 'this_month';
$is_report_available = ( $selected_query_time == 'this_month' && '1' == date( 'd' ) ) ? false : true;

if( $selected_query_time != 'custom' ){
    $duration        = erp_att_get_start_end_date( $selected_query_time );
    $cal             = 'display:none';
} else {
    $duration        = [
          'start'   =>  $_REQUEST['start'],
          'end'     =>  $_REQUEST['end'],
    ];
    $cal             = 'display:inline-block';
}

$query_times         = erp_att_get_query_times();
$get_report          = get_employee_att_report( $user_id, $duration );

$datediff=date_diff(
    date_create($duration['start']),
    date_create($duration['end'])
);

$workingdays         = ! empty( $get_report['attendance_summary']['working_days'] ) ? $get_report['attendance_summary']['working_days'] : ' - ';

$total_perc         = ( ! empty ( $get_report['attendance_summary']['present'] ) ) ? ( $get_report['attendance_summary']['present'] / $workingdays ) * 100 : '-' ;
$total_absent       = ( ! empty ( $get_report['attendance_summary']['present'] ) ) ? ( $workingdays - $get_report['attendance_summary']['present'] ) : $workingdays ;

?>
<div class="wrap">
    <div id="att-status-single-emp" style="width:100%;">

        <div style="text-align: right;">
            <div class="att-query-form">
            <form method="get">
                <?php if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ) :?>
                    <input type="hidden" name="page" value="erp-hr-employee">
                <?php elseif ( $user_id === get_current_user_id() ) : ?>
                    <input type="hidden" name="page" value="erp-hr">
                    <input type="hidden" name="section" value="my-profile">
                <?php else : ?>
                    <input type="hidden" name="page" value="erp-hr">
                    <input type="hidden" name="section" value="people">
                    <input type="hidden" name="sub-section" value="employee">
                <?php endif ?>
                <input type="hidden" name="tab" value="attendance">
                <input type="hidden" name="action" value="view">
                <input type="hidden" name="id" value=<?php echo $user_id ?>>
                <select name="query_time" class="query_time" id="att-filter-duration">
					<?php
					foreach ( $query_times as $key => $value ) {
						echo '<option value="' . $key . '"' . selected( $selected_query_time, $key ) . '>' . $value . '</option>';
					}
					?>
                </select>
                <span id="custom-input" style="<?php echo $cal ;?>">
                    <span>From </span><input name="start" class="attendance-date-field hasDatepicker" type="date" id="dp1569920002314" value="<?php echo $duration['start']; ?>">&nbsp;
                    <span>To </span><input name="end" class="attendance-date-field hasDatepicker" type="date" id="dp1569920002315" value="<?php echo $duration['end']; ?>">
                </span>
				<?php wp_nonce_field( 'epr-attendance-filter' ); ?>
                <button type="submit" class="button-secondary" name="filter_attendance"
                        value="filter_attendance"><?php _e( 'Filter', 'erp' ); ?></button>
            </form>
        </div>
        </div>
        <?php if ( $is_report_available ): ?>
        <div id="emp-single-att-stacked-chart" style="width:100%;height:600px;"></div>
		<?php endif; ?>

        <div class="att-summary-print" style="text-align: right;margin-bottom: 10px;">
            <button class="button-primary" onclick="window.print()">
                <i class="fa fa-print"></i>&nbsp;
				<?php _e( 'Print Summary', 'erp-pro' ); ?>
            </button>
        </div>

        <div class="postbox leads-actions">
            <div class="postbox-header">
                <h3 class="hndle"><?php esc_html_e( 'Summary', 'erp-pro' ); ?></h3>

                <div class="handle-actions hide-if-no-js">
                    <button type="button" class="handlediv" aria-expanded="true">
                        <span class="screen-reader-text"><?php _e( 'Click to toggle', 'erp-pro' ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </div>
            </div>

            <div class="inside" style="overflow: hidden;">
				<?php if ( $is_report_available ): ?>
                    <div class="erp-grid-container">
                        <div class="col-3">
                            <table class="erp-table">
                                <tr>
                                    <th><?php _e( 'Total Working Days', 'erp-pro' ); ?></th>
                                    <td class="width-20">:</td>
                                    <td><?php /*echo ! empty( $get_report['attendance_summary']['dates'] ) ? $get_report['attendance_summary']['dates'] : ' - ';*/ ?>
                                    <?php echo ( $datediff->days + 1 ) ;?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Days Worked', 'erp-pro' ); ?></th>
                                    <td class="width-20">:</td>
                                    <td><?php echo $workingdays ; ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Holidays', 'erp-pro' ); ?></th>
                                    <td class="width-20">:</td>
                                    <td><?php echo ! empty( $get_report['holidays'] ) ? count( $get_report['holidays'] ) : ' - '; ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Present', 'erp-pro' ); ?></th>
                                    <td class="width-20">:</td>
                                    <td><?php echo ! empty( $get_report['attendance_summary']['present'] ) ? $get_report['attendance_summary']['present'] : ' - '; ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Time Off', 'erp-pro' ); ?></th>
                                    <td class="width-20">:</td>
                                    <td><?php echo ! empty( $get_report['user_leave'] ) ? count($get_report['user_leave']) : ' - '; ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Absent', 'erp-pro' ); ?></th>
                                    <td class="width-20">:</td>
                                    <td><?php echo $total_absent; ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Late Check-In', 'erp-pro' ); ?></th>
                                    <td class="width-20">:</td>
                                    <td><?php echo ! empty( $get_report['attendance_summary']['late'] ) ? $get_report['attendance_summary']['late'] : ' - '; ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Early Check-Out', 'erp-pro' ); ?></th>
                                    <td class="width-20">:</td>
                                    <td><?php echo ! empty( $get_report['attendance_summary']['early_left'] ) ? count ($get_report['attendance_summary']['early_left'] ) : ' - '; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-3">
                            <table class="erp-table">
                                <tr>
                                    <th><?php _e( 'Time Worked', 'erp-pro' ); ?></th>
                                    <td class="width-20">:</td>
                                    <td><?php echo ! empty( $get_report['attendance_summary']['worktime'] ) ? ( $get_report['attendance_summary']['worktime'] ) : ' - '; ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Overtime', 'erp-pro' ); ?></th>
                                    <td class="width-20">:</td>
                                    <td><?php echo ! empty( $get_report['attendance_summary']['overtime'] ) ? ( $get_report['attendance_summary']['overtime'] ) : ' - '; ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Present Percentage', 'erp-pro' ); ?></th>
                                    <td class="width-20">:</td>
                                    <td><?php echo sprintf( '%0.2f', $total_perc ) . '%'; ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Missing Checkout', 'erp-pro' ); ?></th>
                                    <td class="width-20">:</td>
                                    <td><?php echo ! empty( $get_report['attendance_summary']['missing_checkout'] ) ? $get_report['attendance_summary']['missing_checkout'] : ' - '; ?></td>
                                </tr>

                                <tr>
                                    <th><?php _e( 'Avg Time Worked', 'erp-pro' ); ?></th>
                                    <td class="width-20">:</td>
                                    <td><?php echo ! empty( $get_report['attendance_summary']['avg_worktime'] ) ? ( $get_report['attendance_summary']['avg_worktime'] ) : ' - '; ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Avg Check-In Time', 'erp-pro' ); ?></th>
                                    <td class="width-20">:</td>
                                    <td><?php echo ! empty( $get_report['attendance_summary']['avg_checkin'] ) ?  $get_report['attendance_summary']['avg_checkin']  : ' - '; ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Avg Check-Out Time', 'erp-pro' ); ?></th>
                                    <td class="width-20">:</td>
                                    <td><?php echo ! empty( $get_report['attendance_summary']['avg_checkout'] ) ? $get_report['attendance_summary']['avg_checkout'] : ' - '; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
				<?php else: ?>
                    <p><?php _e( 'Insufficient data to generate report.', 'erp-pro' ); ?></p>
				<?php endif; ?>
            </div>
        </div><!-- .postbox -->

        <div id="employee-attendance-table">
			<?php if ( $is_report_available ): ?>
                <table class="widefat striped">
                    <thead>
                    <tr>
                        <th><?php _e( 'Date', 'erp-pro' ); ?></th>
                        <th><?php _e( 'Status', 'erp-pro' ); ?></th>
                        <th><?php _e( 'Checkin', 'erp-pro' ); ?></th>
                        <th><?php _e( 'Checkout', 'erp-pro' ); ?></th>
                        <th><?php _e( 'Time Worked', 'erp-pro' ); ?></th>
                        <th><?php _e( 'Comment', 'erp-pro' ); ?></th>
                        <th style="width: 150px;"><?php _e( 'Log', 'erp-pro' ); ?></th>
                    </tr>
                    </thead>
                    <tbody>
					<?php
					foreach ( $get_report['attendance_report'] as $attendance ) {
                        $attendance                  =   (array) $attendance;
                        $weekly_holidays             =   unserialize($attendance['ash_holidays']);
                        $todays_weekday              =   strtolower(date('D', strtotime($attendance['ds_date'])));
                        $is_today_weekday            =   in_array( $todays_weekday,  $weekly_holidays);
                        $is_today_leaveday           =   in_array( $attendance['ds_date'],  $get_report['user_leave']);
                        $is_today_global_holidayday  =   in_array( $attendance['ds_date'],  $get_report['holidays']);
                    ?>

                        <tr>
                            <td><?php echo $attendance['ds_date']; ?></td>

                            <?php if ( ! $is_today_weekday && ! $is_today_global_holidayday && ! $is_today_leaveday ) : ?>
                            <td>
                                <?php if ( $attendance['ds_present'] == 1 ) { ?>
                                    <span style="background-color: #0B9017;display: inline-block;height: 10px;width: 10px;"></span> Present
                                <?php } else { ?>
                                    <span style="background-color: #dc3232;display: inline-block;height: 10px;width: 10px;"></span> Absent
                                <?php } ?>
                            </td>
                            <?php endif; ?>

                            <?php if ( $is_today_leaveday ) : ?>
                            <td> <span style="background-color: #23282d;display: inline-block;height: 10px;width: 10px;"></span> Leave </td>
                            <?php endif; ?>

                            <?php if ( $is_today_weekday || $is_today_global_holidayday ) : ?>
                                <td> <span style="background-color: #dcc532;display: inline-block;height: 10px;width: 10px;"></span> Holiday </td>
                            <?php endif; ?>

                            <td><?php echo date("H:i:s",strtotime($attendance['al_min_checkin'])); ?></td>
                            <td><?php echo date("H:i:s",strtotime($attendance['al_max_checkout'])); ?></td>
                            <td><?php echo gmdate("H:i:s",$attendance['al_time']); ?></td>
                            <td><?php
                                $comment = '';
                                $comment.= ( $attendance['ds_late'] > 0 && $attendance['ds_late'] != null ) ? 'Late Entry,' : '';
                                $comment.= ( $attendance['ds_early_left'] > 0 && $attendance['ds_early_left'] != null ) ? 'Early Left,' : '';
                                $comment.= ( $attendance['al_overtime'] > 0 && $attendance['al_overtime'] != null ) ? 'Overtime,' : '';
                                echo rtrim( $comment, ',' );
                                ?>
                            </td>
                            <td><button onclick="getLog(<?php echo $attendance['ds_user_id'] ;?>,'<?php echo $attendance['ds_date'] ;?>')" class="button-primary"><?php _e( 'View Log', 'erp-pro' ); ?></button></td>
                        </tr>

                    <?php
					}
					?>
                    </tbody>
                </table>

			<?php endif; ?>
        </div>
    </div>
</div>

<script type="text/html" id="view_date_log">
    <div>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>Checkin</th>
                    <th>Checkout</th>
                    <th>Log Time</th>
                </tr>
            </thead>
            <tbody id="view_date_log_tbody">
                <tr>
                    <td colspan="4"></td>
                </tr>
            </tbody>
        </table>
    </div>
</script>

<?php
$checkins_js    = [];
$early_entry_js = [];
$late_js        = [];
$worktime_js    = [];
$early_left_js  = [];
$extra_time_js  = [];
$holiday_js     = [];
$absent_js      = [];
foreach ( $get_report['attendance_report'] as $att ) {
    $att = (array) $att;
    $js_date = strtotime( $att['ds_date'] ) * 1000;
    $att['checkin'] = strtotime((date('H:i:s',strtotime($att['al_min_checkin'])))) - strtotime('00:00:00');
    $start = strtotime(($att['ash_start_time'])) - strtotime('00:00:00');
    $end   = strtotime(($att['ash_start_time'])) - strtotime('00:00:00');

    if ( $att['al_overtime'] <= 0 ) {
        $att['al_overtime'] = 0;
    }


    if ( $att['ds_present'] == 1 ) {
        $checkins_js[]    = [ $js_date, $att['checkin'] * 1000 ];
        $early_entry_js[] = $start > $att['checkin'] ? [ $js_date, ( $start - $att['checkin'] ) * 1000 ] : [ $js_date, 0 ];
        $late_js[]        = [ $js_date, $att['ds_late'] * 1000 ];
        $worktime_js[]    = [ $js_date, ($att['al_time'] - $att['al_overtime'] ) * 1000 ];
        $early_left_js[]  = [ $js_date, $att['ds_early_left'] * 1000 ];
        $extra_time_js[]  = [ $js_date, $att['al_overtime'] * 1000 ];
        $holiday_js[]     = [ $js_date, 0 ];
        $leave_js[]       = [ $js_date, 0 ];
        $absent_js[]      = [ $js_date, 0 ];

    } else {
        $checkins_js[]    = [ $js_date, $start * 1000 ];
        $early_entry_js[] = [ $js_date, 0 ];
        $late_js[]        = [ $js_date, 0 ];
        $worktime_js[]    = [ $js_date, 0 ];
        $early_left_js[]  = [ $js_date, 0 ];
        $extra_time_js[]  = [ $js_date, 0 ];
        $holiday_js[]     = [ $js_date, 0 ];
        $leave_js[]       = [ $js_date, 0 ];
        $absent_js[]      = [ $js_date, $end * 1000 ];
    }
}
?>
<script>
    ;
    (function ($) {
        $(document).ready(function () {
            var resdata = [
                {
                    data: <?php echo json_encode( $checkins_js ); ?>,
                    color: '#FAFAFA'
                },
                {
                    label: "<?php _e( 'Early Check-In', 'erp-pro' ); ?>",
                    data: <?php echo json_encode( $early_entry_js ); ?>,
                    color: '#558dd6'
                },
                {
                    label: "<?php _e( 'Late Check-In', 'erp-pro' ); ?>",
                    data: <?php echo json_encode( $late_js ); ?>,
                    color: '#ff3000'
                },
                {
                    label: "<?php _e( 'Worktime', 'erp-pro' ); ?>",
                    data: <?php echo json_encode( $worktime_js ); ?>,
                    color: '#1e487b',
                },
                {
                    label: "<?php _e( 'Early  Check-Out', 'erp-pro' ); ?>",
                    data: <?php echo json_encode( $early_left_js ); ?>,
                    color: '#ff3000',
                },
                {
                    label: "<?php _e( 'Overtime', 'erp-pro' ); ?>",
                    data: <?php echo json_encode( $extra_time_js ); ?>,
                    color: '#558dd6',
                },
                {
                    label: "<?php _e( 'Absent', 'erp-pro' ); ?>",
                    data: <?php echo json_encode( $absent_js ); ?>,
                    color: '#dfe0da',
                },
                {
                    label: "<?php _e( 'Holiday', 'erp-pro' ); ?>",
                    data: <?php echo json_encode( $holiday_js ); ?>,
                    color: '#dfe0da',
                },
                {
                    label: "<?php _e( 'Time off', 'erp-pro' ); ?>",
                    data: <?php echo json_encode( $leave_js ); ?>,
                    color: '#dfe0da',
                }
            ];

            $.plot('#emp-single-att-stacked-chart', resdata, {
                series: {
                    //lines: { show: true },
                    // points: {
                    //     show: true,
                    //     radius: 4
                    // },
                    bars: {
                        show: true,
                        barWidth: 18 * 3600000,
                        fill: 1,
                        lineWidth: 0,
                    },

                    stack: true
                },
                bars: {
                    align: 'center'
                },
                valueLabels: {

                    show: true
                },
                yaxis: {
                    axisLabel: "Time",
                    axisLabelUseCanvas: true,
                    axisLabelFontSizePixels: 14,
                    axisLabelFontFamily: 'Verdana, Arial',
                    axisLabelPadding: 10,
                    mode: "time",
                    tickSize: [1, "hour"],
                    twelveHourClock: true,
                    //min: <?php /*echo ( ! empty( $checkins ) ) ? min( $checkins ) : 0;*/ ?> * 1000 - 1800000,
                    //max: <?php /*echo  ( ! empty( $checkouts ) ) ? max( $checkouts ) : 0;*/ ?> * 1000 + 7200000,
                    min : 0,
                    max : 86400000,
                    tickColor : '#eee'
        },
            xaxis:{
                axisLabel:"Days",
                    axisLabelUseCanvas:true,
                    axisLabelFontSizePixels:14,
                    axisLabelFontFamily:'Verdana, Arial',
                    axisLabelPadding:10,
                    mode:"time",
                    tickSize: 0,
                    rotateTicks:130,
            },
            grid:{
                hoverable:true,
                    clickable:true,
                    borderColor:'#000',
                    borderWidth:{
                    left:2,
                        bottom:2,
                        right:2,
                        top:2,
                },
                backgroundColor:{
                    colors:[
                        "#ffffff",
                        "#F9FBFC"
                    ]
                }
            },
            legend:{
                position:'ne',
                    show:true,
                    //labelBoxBorderColor:'#FF5722',
                    backgroundColor:'#fff'
            },

        } );
            $("#emp-single-att-stacked-chart").UseTooltip();


        });

        var previousPoint = null, previousLabel = null;

        $.fn.UseTooltip = function () {
            $(this).bind("plothover", function (event, pos, item) {

                if (item) {

                    if ((previousLabel != item.series.label) || (previousPoint != item.dataIndex)) {
                        previousPoint = item.dataIndex;
                        previousLabel = item.series.label;
                        $("#tooltip").remove();

                        var date = new Date(item.datapoint[0]).toString().split(' ').splice(1, 3).join(' ');
                        var data1 = item.datapoint[1];
                        var data2 = item.datapoint[2];
                        var label = item.series.label;
                        var color = item.series.color;

                        if (undefined != label) {
                            showTooltip(item.pageX,
                                item.pageY,
                                color,
                                "<?php _e( ' Date :', 'erp-pro' ); ?><strong>" + date + "</strong><br>" + label + " :<strong>" + calculateTime(data1, data2, label) + "</strong>");
                        }
                    }
                } else {
                    $("#tooltip").remove();
                    previousPoint = null;
                }
            });
        };

        function showTooltip(x, y, color, contents) {
            $('<div id="tooltip">' + contents + '</div>').css({
                position: 'absolute',
                display: 'none',
                top: y - 40,
                left: x - 120,
                border: '2px solid ' + color,
                padding: '3px',
                'font-size': '9px',
                'border-radius': '5px',
                'background-color': '#fff',
                'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
                opacity: 0.9
            }).appendTo("body").fadeIn(200);
        }

        function calculateTime(data1, data2, label) {

            var totalSec = (data1 / 1000) - (data2 / 1000);
            var hours = parseInt(totalSec / 3600) % 24;
            var minutes = parseInt(totalSec / 60) % 60;
            var result = (hours < 10 ? "" + hours : hours) + "h " + (minutes < 10 ? "0" + minutes : minutes) + "m";
            return result;
        }

        jQuery('#att-filter-duration').change(function(){
            if ( this.value == 'custom' ) {
                jQuery('#custom-input').css('display','inline-block')
            } else {
                jQuery('#custom-input').css('display','none')
            }
        });

    })(jQuery);

    function getLog( user_id, date) {

        jQuery.erpPopup({
            title: "View Log",
            id: "view_date_log_modal",
            extraClass : 'erp_att_log_popup',
            content: jQuery( '#view_date_log' ).html(),
            onReady: function() {
                var modal = this;
                jQuery('header', modal).after(jQuery('<div class="loader"></div>').show());

                var data = {
                    _wpnonce  : wpErp.nonce,
                    action    : 'erp_hr_get_employee_log',
                    user_id   : user_id,
                    date      : date
                };

                jQuery.post(wpErp.ajaxurl, data).done(function(response) {

                    var str     = '';
                    var counter = 1;
                    response.forEach( function( data ) {
                        str += '<tr>' +
                            '<td>'+ counter +'</td>' +
                            '<td>'+ data.checkin +'</td>' +
                            '<td>'+ data.checkout +'</td>' +
                            '<td>'+ data.log_time +'</td>' +
                            '</tr>';
                        counter++;
                    } );

                    if ( response.length == 0 ) {
                        str += '<tr><td colspan="4" style="text-align: center;">There is no information about this right now. Please make your first checkin.</td></tr>';
                    }

                    jQuery( '#view_date_log_tbody' ).html( str );
                    jQuery( '.loader', modal).remove();
                }).always(function() {

                });
            }
        });
    }

</script>
<style>
    #view_date_log_modal span.activate { display: none }
</style>
