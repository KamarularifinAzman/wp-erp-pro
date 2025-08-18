 <?php
    global $wpdb;

//        $wpdb->hide_errors();


    $parent_sql = $wpdb->prepare( "SELECT  cat.cat_name,
                                    asset.*,
                                    ( SELECT COUNT(parent) FROM {$wpdb->prefix}erp_hr_assets WHERE parent = %d ) as count_all,
                                    ( SELECT SUM(price) FROM {$wpdb->prefix}erp_hr_assets WHERE (parent = %d OR id = %d) AND price IS NOT NULL ) as sum_all,
                                    ( SELECT COUNT(parent) FROM {$wpdb->prefix}erp_hr_assets WHERE parent = %d AND status ='stock' ) as count_stock,
                                    ( SELECT COUNT(parent) FROM {$wpdb->prefix}erp_hr_assets WHERE parent = %d AND status ='allotted' ) as count_allotted,
                                    ( SELECT COUNT(parent) FROM {$wpdb->prefix}erp_hr_assets WHERE parent = %d AND status ='dissmissed' ) as count_dissmissed
                                    FROM {$wpdb->prefix}erp_hr_assets AS asset
                                    RIGHT JOIN {$wpdb->prefix}erp_hr_assets_category AS cat
                                    ON asset.category_id = cat.id
                                    WHERE asset.id = %d" , $id, $id, $id, $id, $id, $id, $id );
    $parent = $wpdb->get_row( $parent_sql );

    if ( !$parent ) {
        die( __( 'No Items Found', 'erp-pro' ) );
    }
 ?>

 <div class="wrap asset-single-page">
     <h2>
     <?php echo $parent->item_group; ?>

     <?php
     if ( current_user_can( 'erp_hr_manager' ) ) {
         ?>
         <a href="#" data-id="<?php echo $parent->id; ?>" class="add-new-h2 asset-edit"><?php _e( 'Edit', 'erp-pro' ); ?></a>
         <?php
     }
     ?>
     </h2>
    <div id="poststuff">

        <div id="post-body" class="metabox-holder columns-2">

            <div id="post-body-content">
            <!-- main content -->
                <div class="meta-box-sortables ui-sortable">

        <?php if ( $parent->count_all <= 0 ){ ?>

                    <div class="postbox">

                        <div class="handlediv" title="Click to toggle"><br></div>
                        <!-- Toggle -->

                        <h2 class="hndle"><span><span class="dashicons dashicons-screenoptions"></span>&nbsp;<?php esc_attr_e( 'Item Details', 'erp-pro' ); ?></span>
                        </h2>

                        <div class="inside">
                            <ul class="erp-list two-col separated">
                                <li><?php erp_print_key_value( __( 'Item Code', 'erp-pro' ), $parent->item_code ); ?></li>
                                <li><?php erp_print_key_value( __( 'Model No', 'erp-pro' ), $parent->model_no ); ?></li>
                                <li><?php erp_print_key_value( __( 'Manufacturer', 'erp-pro' ), $parent->manufacturer ); ?></li>
                                <li><?php erp_print_key_value( __( 'Price', 'erp-pro' ), $parent->price ); ?></li>
                                <li><?php erp_print_key_value( __( 'Registration Date', 'erp-pro' ), '0000-00-00' == $parent->date_reg ? '' : $parent->date_reg ); ?></li>
                                <li><?php erp_print_key_value( __( 'Expiry Date', 'erp-pro' ), '0000-00-00' == $parent->date_expiry ? '' : $parent->date_expiry ); ?></li>
                                <li><?php erp_print_key_value( __( 'Warranty Till', 'erp-pro' ), '0000-00-00' == $parent->date_warranty ? '' : $parent->date_warranty ); ?></li>
                                <li><?php erp_print_key_value( __( 'Allotable?', 'erp-pro' ), 'on' == $parent->allottable? __( 'Yes', 'erp-pro' ) : __( 'No', 'erp-pro') ); ?></li>
                                <li><?php erp_print_key_value( __( 'Status', 'erp-pro' ), ucfirst( $parent->status ) ); ?></li>
                                <li><?php erp_print_key_value( __( 'Serial Info', 'erp-pro' ), '<textarea style="resize:none" readonly>' . $parent->item_serial . '</textarea>' ); ?></li>
                            </ul>
                            <div class="asset-single-buttons">
                                <button data-id="<?php echo $parent->id ?>" class="button-secondary single-item-delete"><?php _e( 'Delete', 'erp-pro' ); ?></button>
                                <a data-id="<?php echo $parent->id ?>" href="#" class="deletesubmit single-item-dissmiss"><?php _e( 'Dismiss', 'erp_asset_management' ); ?></a>
                            </div>

                        </div><!-- .inside -->

                    </div><!-- .postbox -->


            <?php } else {

            $child_sql = $wpdb->prepare( "SELECT *
                                FROM {$wpdb->prefix}erp_hr_assets
                                WHERE parent = %d", $id );
            $child     = $wpdb->get_results( $child_sql );
            $count     = 1;

            foreach ( $child as $single ) {
            ?>
                <div class="postbox">
                    <div class="handlediv" title="Click to toggle"><br></div>
                    <!-- Toggle -->
                    <h2 class="hndle"><span><span class="dashicons dashicons-screenoptions"></span>&nbsp;<?php esc_attr_e( 'Item', 'erp-pro' ); echo '&nbsp;' . $count . '&nbsp;';  esc_attr_e( 'Details', 'erp-pro' ); ?></span>
                    </h2>

                    <div class="inside">
                        <ul class="erp-list two-col separated">
                            <li><?php erp_print_key_value( __( 'Item Code', 'erp-pro' ), $single->item_code ); ?></li>
                            <li><?php erp_print_key_value( __( 'Model No', 'erp-pro' ), $single->model_no ); ?></li>
                            <li><?php erp_print_key_value( __( 'Manufacturer', 'erp-pro' ), $single->manufacturer ); ?></li>
                            <li><?php erp_print_key_value( __( 'Price', 'erp-pro' ), $single->price ); ?></li>
                            <li><?php erp_print_key_value( __( 'Registration Date', 'erp-pro' ), '0000-00-00' == $single->date_reg ? '' : $single->date_reg ); ?></li>
                            <li><?php erp_print_key_value( __( 'Expiry Date', 'erp-pro' ), '0000-00-00' == $single->date_expiry ? '' : $single->date_expiry ); ?></li>
                            <li><?php erp_print_key_value( __( 'Warranty Till', 'erp-pro' ), '0000-00-00' == $single->date_warranty ? '' : $single->date_warranty ); ?></li>
                            <li><?php erp_print_key_value( __( 'Allotable?', 'erp-pro' ), 'on' == $single->allottable? __( 'Yes', 'erp-pro' ) : __( 'No', 'erp-pro') ); ?></li>
                            <li><?php erp_print_key_value( __( 'Status', 'erp-pro' ), ucfirst( $single->status ) ); ?></li>
                            <li><?php erp_print_key_value( __( 'Serial Info', 'erp-pro' ), '<textarea style="resize:none" readonly>' . $single->item_serial . '</textarea>' ); ?></li>
                        </ul>
                        <div class="asset-single-buttons">
                            <button data-id="<?php echo $single->id ?>" class="button-secondary single-item-delete"><?php _e( 'Delete', 'erp-pro' ); ?></button>
                            <a data-id="<?php echo $single->id ?>" href="#" class="deletesubmit single-item-dissmiss"><?php _e( 'Dissmiss', 'erp_asset_management' ); ?></a>
                        </div>
                    </div><!-- .inside -->
                </div><!-- .postbox -->
        <?php
                $count++;
            }
        }
        ?>

        <?php
            $employees  = erp_hr_get_employees( [ 'no_object' => true ] );
            $emp_sorted = [];

            $query = "SELECT * FROM
                      (SELECT CONCAT(ass.item_code, ' - ', ass.model_no) AS item_name, his.date_given AS date, his.allotted_to AS employee_id, 'given' AS action
                      FROM {$wpdb->prefix}erp_hr_assets_history AS his
                      LEFT JOIN {$wpdb->prefix}erp_hr_assets AS ass
                      ON his.item_id = ass.id
                      WHERE his.item_id  IN  (SELECT item_id FROM {$wpdb->prefix}erp_hr_assets_history WHERE item_group = $id)
                      UNION
                      SELECT CONCAT(ass.item_code, ' - ', ass.model_no) AS item_name, his.date_return_real date, his.allotted_to AS employee_id, 'returned' AS action
                      FROM {$wpdb->prefix}erp_hr_assets_history AS his
                      LEFT JOIN {$wpdb->prefix}erp_hr_assets AS ass
                      ON his.item_id = ass.id
                      WHERE date_return_real IS NOT NULL AND his.item_id  IN  (SELECT item_id FROM {$wpdb->prefix}erp_hr_assets_history WHERE item_group = $id)
                      UNION
                      SELECT CONCAT(item_code, ' - ', model_no) AS item_name, date_dissmissed AS date, 'none' AS employee_id, 'dissmissed' AS action
                      FROM {$wpdb->prefix}erp_hr_assets
                      WHERE status = 'dissmissed' AND id  IN  (SELECT item_id FROM {$wpdb->prefix}erp_hr_assets_history WHERE item_group = $id)) AS level_one
                      ORDER BY date DESC";

            $query_result = $wpdb->get_results($query);

            foreach ( $employees as $employee ) {
                $emp_sorted[$employee->user_id] = $employee->display_name;
            }
        ?>
            <div class="postbox">
                <div class="handlediv" title="Click to toggle"><br></div>
                <h2 class="hndle"><span><span class="dashicons dashicons-backup"></span>&nbsp;<?php esc_attr_e( 'Item Timeline', 'erp-pro' ); ?></span></h2>
                <div class="inside">
                    <table class="widefat striped">
                        <thead>
                            <tr>

                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            if ( $query_result ) {
                                foreach ( $query_result as $each ) {
                                    if ( 'given' == $each->action ) {

                                        echo '<tr>';
                                        echo '<td>';
                                        _e( 'Item ', 'erp-pro' );
                                        echo '<b>' . $each->item_name . '</b>';
                                        _e( ' was alloted to ', 'erp-pro' );
                                        echo '<a href="#">' . $emp_sorted[$each->employee_id] . '</a>';
                                        _e( ' on ', 'erp-pro' );
                                        echo erp_format_date( $each->date );
                                        echo '</td>';
                                        echo '</tr>';
                                    }

                                    if ( 'returned' == $each->action ) {

                                        echo '<tr>';
                                        echo '<td>';
                                        _e( 'Item ', 'erp-pro' );
                                        echo '<b>' . $each->item_name . '</b>';
                                        _e( ' was returned from ', 'erp-pro' );
                                        echo '<a href="#">' . $emp_sorted[$each->employee_id] . '</a>';
                                        _e( ' on ', 'erp-pro' );
                                        echo erp_format_date( $each->date );
                                        echo '</td>';
                                        echo '</tr>';
                                    }

                                    if ( 'dissmissed' == $each->action ) {
                                        echo '<tr>';
                                        echo '<td>';
                                        _e( 'Item ', 'erp-pro' );
                                        echo '<b>' . $each->item_name . '</b>';
                                        _e( ' was dismissed on ', 'erp-pro' );
                                        echo erp_format_date( $each->date );
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                }
                            } else {
                                echo '<tr>';
                                echo '<td>';
                                _e( 'No record found', 'erp-pro' );
                                echo '</td>';
                                echo '</tr>';
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- .meta-box-sortables .ui-sortable -->

            </div><!-- post-body-content -->
            <!-- sidebar -->
            <div id="postbox-container-1" class="postbox-container">

                <div class="meta-box-sortables">

                    <div class="postbox">

                        <div class="handlediv" title="Click to toggle"><br></div>
                        <!-- Toggle -->

                        <h2 class="hndle"><span><span class="dashicons dashicons-networking"></span>&nbsp;<?php esc_attr_e( 'Summary', 'erp-pro' ); ?></span></h2>

                        <div class="inside">
                            <ul class="erp-list separated">
                                <li><?php erp_print_key_value( __( 'Category', 'erp-pro' ), $parent->cat_name ); ?></li>
                                <li><?php erp_print_key_value( __( 'Item Group', 'erp-pro' ), $parent->item_group ); ?></li>
                                <li><?php erp_print_key_value( __( 'Total Item', 'erp-pro' ), $parent->count_all ? $parent->count_all : 1 ); ?></li>
                                <li><?php erp_print_key_value( __( 'In Stock', 'erp-pro' ), !$parent->count_stock ? 'stock' == $parent->status ? 1 : 0 : $parent->count_stock ); ?></li>
                                <li><?php erp_print_key_value( __( 'Alloted', 'erp-pro' ), !$parent->count_allotted ? 'allotted' == $parent->status ? 1 : 0 : $parent->count_allotted ); ?></li>
                                <li><?php erp_print_key_value( __( 'Dismissed', 'erp-pro' ), !$parent->count_dissmissed ? 'dissmissed' == $parent->status ? 1 : 0 : $parent->count_dissmissed ); ?></li>
                                <li><?php erp_print_key_value( __( 'Total Amount', 'erp-pro' ), $parent->sum_all ); ?></li>
                            </ul>
                        </div>
                        <!-- .inside -->

                    </div>
                    <!-- .postbox -->

                </div>
                <!-- .meta-box-sortables -->

            </div>
            <!-- #postbox-container-1 .postbox-container -->

        </div>
        <!-- #post-body .metabox-holder .columns-2 -->

        <br class="clear">
    </div>
    <!-- #poststuff -->
</div>

