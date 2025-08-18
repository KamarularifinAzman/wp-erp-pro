<div id="basic-payroll-info-wrapper" class="wrap basic-payroll-info">

    <div class="basic-info">
        <div class="postbox leads-actions">
            <div class="postbox-header">
                <h3 class="hndle"><?php esc_html_e( 'Todo List', 'erp-pro' ); ?></h3>

                <div class="handle-actions hide-if-no-js">
                    <button type="button" class="handlediv" aria-expanded="true">
                        <span class="screen-reader-text"><?php _e( 'Click to toggle', 'erp-pro' ); ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                </div>
            </div>

            <div class="inside todo-table-container">
                <?php $todo_list = get_todos();?>
                <table class="wp-list-table widefat fixed striped users todo-table">
                    <tr>
                        <th><?php _e( 'To-Do', 'erp-pro' );?></th>
                        <th><?php _e( 'To-Do End Date', 'erp-pro' );?></th>
                        <th><?php _e( 'To-Do Creator', 'erp-pro' );?></th>
                        <th><?php _e( 'To-Do Created Date', 'erp-pro' );?></th>
                    </tr>
                    <?php if ( count( $todo_list ) > 0 ) : ?>
                        <?php foreach ( $todo_list as $tlist ) :?>
                            <tr>
                                <td><?php echo $tlist['title'];?></td>
                                <td><?php echo erp_format_date( $tlist['deadline_date'] ); ?></td>
                                <td><?php echo $tlist['display_name'];?></td>
                                <td><?php echo erp_format_date( $tlist['created_at'] ); ?></td>
                            </tr>
                        <?php endforeach;?>
                    <?php else : ?>
                        <tr>
                            <td><?php _e( 'No to-do found!', 'erp-pro' );?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

</div>
