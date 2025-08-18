<div class="wrap erp-asset-requests">
    <h2>
        <?php _e( 'Asset Requests', 'erp-pro' ); ?>
    </h2>
    <form method="get">
        <?php if( version_compare( WPERP_VERSION, '1.4.0', '<' ) ) : ?>
            <input type="hidden" name="page" value="asset-request">
        <?php else: ?>
            <input type="hidden" name="page" value="erp-hr">
            <input type="hidden" name="section" value="asset">
            <input type="hidden" name="sub-section" value="asset-request">
        <?php endif?>
        <?php
        $request_list_table = new \WeDevs\AssetManagement\RequestListTable();
        $request_list_table->prepare_items();
        $request_list_table->views();
        $request_list_table->display();
        ?>
    </form>
</div>
