<div class="wrap erp-hr-allottment">
    <h2>
        <?php _e( 'Allotment', 'erp-pro' ); ?> <a href="#" class="add-new-h2 allot-new"><?php _e( 'New Entry', 'erp-pro' ); ?></a>
    </h2>
    <form method="get">
        <?php if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ): ?>
            <input type="hidden" name="page" value="erp-asset-allottment">
        <?php else: ?>
            <input type="hidden" name="page" value="erp-hr">
            <input type="hidden" name="section" value="asset">
            <input type="hidden" name="sub-section" value="asset-allottment">
        <?php endif?>
        <?php
        $allottment_list_table = new \WeDevs\AssetManagement\AllottmentListTable();
        $allottment_list_table->prepare_items();
        $allottment_list_table->views();
        $allottment_list_table->display();
        ?>
    </form>
</div>
