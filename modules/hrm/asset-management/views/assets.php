<div class="wrap erp-hr-assets">
    <h2>
        <?php _e( 'Assets', 'erp-pro' ); ?> <a href="#" class="add-new-h2 asset-new"><?php _e( 'New Entry', 'erp-pro' ); ?></a>
    </h2>
    <div class="assets-list-table">
        <form method="get">
            <?php if ( version_compare( WPERP_VERSION, '1.4.0', '<' ) ):?>
                <input type="hidden" name="page" value="erp-hr-asset">
            <?php else: ?>
            <input type="hidden" name="page" value="erp-hr">
            <input type="hidden" name="section" value="asset">
            <input type="hidden" name="sub-section" value="asset">
            <?php endif; ?>
            <?php
            $asset_list_table = new \WeDevs\AssetManagement\AssetsListTable();
            $asset_list_table->prepare_items();
            $asset_list_table->display();
            ?>
        </form>
    </div>
</div>
