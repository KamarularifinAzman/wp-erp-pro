<div class="wrap">
    <h2><?php _e( 'Workflows', 'erp-pro' ); ?> <?php echo sprintf( '<a href="?page=erp-workflow-new" class="add-new-h2">Add New</a>',  esc_attr( $_REQUEST['page'] ), 'new' ); ?></h2>

    <form method="post">
        <?php if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ): ?>
        <input type="hidden" name="page" value="workflows">
        <?php else: ?>
            <input type="hidden" name="page" value="erp-workflow">
        <?php endif; ?>
        <?php
            $this->workflows_list_table_obj->prepare_items();
            $this->workflows_list_table_obj->search_box( __( 'Search Workflow', 'erp-pro' ), 'workflow' );
            $this->workflows_list_table_obj->views();
            $this->workflows_list_table_obj->display();
        ?>
    </form>
</div>
