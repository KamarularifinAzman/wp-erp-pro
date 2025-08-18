<?php
global $wpdb;

$filter_category     = isset( $_REQUEST['category'] ) ? $_REQUEST['category'] : '-1';
$selected_query_time = isset( $_REQUEST['query_time'] ) ? $_REQUEST['query_time'] : '-1';

if ( $selected_query_time && '-1' != $selected_query_time ) {
    $duration   = erp_asset_get_start_end_date( $selected_query_time );
    $date_start = $duration['start'];
    $date_end   = $duration['end'];
}

if ( $filter_category && '-1' != $filter_category ) {
    $sql_count = "SELECT
        (SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_assets WHERE status != '' AND category_id = $filter_category ) AS count_total,
        (SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_assets WHERE status = 'stock' AND category_id = $filter_category) AS count_stock,
        (SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_assets WHERE status = 'allotted' AND category_id = $filter_category) AS count_allotted,
        (SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_assets WHERE status = 'dissmissed' AND category_id = $filter_category) AS count_dissmissed";
} else {
    $sql_count = "SELECT
        (SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_assets WHERE status != '') AS count_total,
        (SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_assets WHERE status = 'stock') AS count_stock,
        (SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_assets WHERE status = 'allotted') AS count_allotted,
        (SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_assets WHERE status = 'dissmissed') AS count_dissmissed";
}

$result_count = $wpdb->get_row( $sql_count );

$query_sql = "SELECT cat.cat_name, ass.price, ass.date_reg
              FROM {$wpdb->prefix}erp_hr_assets AS ass
              LEFT JOIN {$wpdb->prefix}erp_hr_assets_category AS cat
              ON ass.category_id = cat.id
              WHERE 1=1";

if ( $selected_query_time && '-1' != $selected_query_time ) {
    $query_sql .= " AND date_reg >= \"$date_start\" AND date_reg <= \"$date_end\"";
}

if ( '-1' != $filter_category ) {
    $query_sql = "SELECT item_group as cat_name, price, date_reg
                    FROM {$wpdb->prefix}erp_hr_assets
                    WHERE 1=1";

    $query_sql .= " AND category_id = $filter_category";

    if ( isset( $date_start ) && isset( $date_end ) ) {
        $query_sql .= " AND date_reg >= \"$date_start\" AND date_reg <= \"$date_end\"";
    }
}

$final_query = "SELECT root.cat_name, SUM(root.price) AS total_price FROM ($query_sql) AS root GROUP BY cat_name";

$result_price = $wpdb->get_results( $final_query );

$categories             = erp_hr_assets_get_categories_dropdown();
$query_times            = erp_asset_get_query_times();
$count_category         = 1;
$bar_expenditure_ticks  = [];
$bar_expenditure_prices = [];

unset( $categories[-1] );

foreach ( $result_price as $single_price ) {
    $bar_expenditure_ticks[]  = [ $count_category, $single_price->cat_name ];
    $bar_expenditure_prices[] = [ $single_price->total_price, $count_category ];
    $count_category++;
}

?>

<div class="wrap">
    <h2><?php _e( 'Asset Report', 'erp-pro' ); ?></h2>
    <div style="text-align: right">
        <form method="get">
            <?php if ( version_compare( ERP_PRO_PLUGIN_VERSION, '1.3.0', '<' ) ): ?>
                <input type="hidden" name="page" value="erp-hr-reporting">
            <?php else: ?>
                <input type="hidden" name="page" value="erp-hr">
                <input type="hidden" name="section" value="report">
            <?php endif?>
            <input type="hidden" name="type" value="asset-report">
            <select name="query_time" id="asset-reporting-query">
                <?php
                foreach ( $query_times as $key => $value ) {
                    echo '<option value="' . $key . '"' . selected( $selected_query_time, $key ) . '>' . $value . '</option>' ;
                }
                ?>
            </select>

            <select name="category">
                <option value="-1">&mdash;<?php _e( ' All Categories ', 'erp-pro' ); ?>&mdash;</option>
                <?php
                foreach ( $categories as $key => $value ) {
                    echo '<option value="'. $key .'"' .selected( $filter_category, $key ). '>'. $value .'</option>';
                }
                ?>
            </select>
            <?php wp_nonce_field('asset-hr-reporting', 'asset-report-nonce'); ?>
            <button name="submit-asset-category" class="button-secondary"><?php _e( 'Filter', 'erp-pro' ); ?></button>
        </form>
    </div>

    <div class="erp-single-container">
        <div class="erp-area-left" id="poststuff">
            <?php
            echo erp_admin_dash_metabox( __( '<i class="fa fa-bar-chart"></i> Asset Expenditure', 'erp-pro' ), function() use ($count_category) {
                ?>
                <div style="padding-left:20px;display:flex;min-height: 300px">
                    <div id="asset-expenditure-report" style="align-self:flex-end;width:80%;height:<?php echo $count_category * 50; ?>px;display:inline-block"></div>
                </div>
                <?php
            } );
            ?>
        </div>
    </div>

    <div class="erp-single-container">
        <div class="erp-area-left" id="poststuff">
            <?php
            echo erp_admin_dash_metabox( __( '<i class="fa fa-pie-chart"></i> Asset Current Status Count', 'erp-pro' ), function() use( $categories, $filter_category ) {
                ?>

                <div id="asset-count-report" style="width:40%;height:400px;"></div>
                <?php
            } );
            ?>
        </div>
    </div>

</div>

<script>
    (function( $ ) {
        $('document').ready(function() {
            var pieStatusData = [
                 {
                     label: "<?php _e( 'In Stock', 'erp-pro' ) ?>",
                     data: <?php echo intval($result_count->count_stock); ?>,
                     color: '#ae82bd'
                 },

                {
                    label: "<?php _e( 'Allotted', 'erp-pro' ) ?>",
                    data: <?php echo intval($result_count->count_allotted); ?>,
                    color: '#67c4cc'
                },
                {
                    label: "<?php _e( 'Dissmissed', 'erp-pro' ) ?>",
                    data: <?php echo intval($result_count->count_dissmissed); ?>,
                    color: '#fcc1b6'
                }
            ];

            var pieStatusOptions = {
                series: {
                    pie: {
                        show: true,
                        label: {
                            show: true,
                            radius: 0.8,
                            background: {
                                opacity: 0.8,
                                color: '#000'
                            }
                        }
                    }
                }
            };

            $.plot($("#asset-count-report"), pieStatusData, pieStatusOptions);

            var barExpenditureData = [{
                color: '#23bfaa',
                data: <?php echo json_encode( $bar_expenditure_prices ); ?>
            }, ];

            var barExpenditureOptions = {
                series: {
                    bars: {
                        order: 1,
                        show: 1,
                        barWidth: 0.6,
                        fill: 0.6,
                        align: 'center',
                        horizontal: 1
                    },
                },
                grid: {
                    hoverable: true,
                    borderWidth: 0
                },
                legend: {
                    show: false
                },
                xaxis: {
                    axisLabel: 'Expenditure',
                    axisLabelPadding: 10,
                    axisLabelUseCanvas: 1,
                    tickFormatter: function(v,axis) {
                        return "$" + v;
                    }
                },
                yaxis: {
                    font: {
                        color: '#000',

                    },
                    ticks: <?php echo json_encode($bar_expenditure_ticks); ?>,
                    tickSize: 1,
                    tickColor: '#fff',
//                    axisLabel: "Categories",
                    axisLabelPadding: 10,
                    axisLabelUseCanvas: 1
                },
                grid: {
                    hoverable: true,
                    clickable: true,
                    borderColor: '#000',
                    borderWidth: {
                        left: 0,
                        bottom: 2,
                        right: 0,
                        top: 0,
                    },
                }
            };

            $.plot($("#asset-expenditure-report"), barExpenditureData, barExpenditureOptions);
            $("#asset-expenditure-report").UseTooltip();

        });

        var previousPoint = null, previousLabel = null;

        $.fn.UseTooltip = function () {
            $(this).bind("plothover", function (event, pos, item) {
                if (item) {
                    if ((previousLabel != item.series.label) || (previousPoint != item.dataIndex)) {
                        previousPoint = item.dataIndex;
                        previousLabel = item.series.label;
                        $("#tooltip").remove();

                        var x = item.datapoint[0];
                        var y = item.datapoint[0] - item.datapoint[2];

                        var color = item.series.color;

                        showTooltip(item.pageX,
                            item.pageY,
                            color,
                            "<strong>" + item.series.yaxis.ticks[item.dataIndex].label + "</strong><br><?php _e( 'Total Expense : ', 'erp-pro' ); ?> <strong>" + y + "</strong>");
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
    })(jQuery);
</script>
