<div class="category-edit">
    <table class="widefat striped">
        <thead>
            <tr>
                <th><?php _e( 'Category Name', 'erp-asset' ); ?></th>
                <th><?php _e( 'Action', 'erp-asset' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
                $categories = erp_hr_get_asset_categories();

                foreach ( $categories as $id => $value ) {
            ?>
                <tr>
                    <td><?php echo $value; ?></td>
                    <td>
                        <a href="#" title="<?php _e( 'Edit Category', 'erp-pro' ); ?>"
                           data-value="<?php echo $value; ?>" data-id="<?php echo $id; ?>"
                           class="asset-category-edit"><span class="dashicons dashicons-edit"></span></a>&nbsp;
                        <a href="#" title="<?php _e( 'Delete Category', 'erp-pro' ); ?>"
                           data-value="<?php echo $value; ?>" data-id="<?php echo $id; ?>"
                           class="asset-category-delete"><span class="dashicons dashicons-trash"></span></a>
                    </td>
                </tr>


            <?php
                }
            ?>
        </tbody>
    </table>
</div>

<input type="hidden" name="action" id="erp-assets-action" value="erp-hr-assets-edit-category">
<?php wp_nonce_field( 'erp-hr-asset-edit-category' ) ?>
