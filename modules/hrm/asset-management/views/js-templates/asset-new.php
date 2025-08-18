<ul class="asset-form-fields">
    <div class="row" data-selected="{{data.category}}">
        <li>
            <?php
            erp_html_form_input( [
                'label'    => __( 'Category', 'erp-pro' ),
                'name'     => 'category_id',
                'value'    => '',
                'type'     => 'select',
                'class'    => 'asset-category',
                'options'  => erp_hr_assets_get_categories_dropdown(),
                'required' => true
            ] );
            ?>
            &nbsp;
        <a class="asset-add-category" title="<?php _e( 'Add Category', 'erp-pro' ); ?>" style="cursor:pointer"><i class="fa fa-plus fa-lg"></i></a>
        &nbsp;
        <a class="asset-edit-category" title="<?php _e( 'Edit Category', 'erp-pro' ); ?>" style="cursor:pointer"><i class="fa fa-edit fa-lg"></i></a>
        </li>
    </div>

    <div class="row">
        <li>
            <?php
            erp_html_form_input( [
                'label'    => __( 'Item Name', 'erp-pro' ),
                'name'     => 'item_group',
                'value'    => '',
                'required' => true,
                'help'     => __( 'Item Name (e.g. Apple Macbook Pro)', 'erp-pro' )
            ] );
            ?>
        </li>
    </div>

    <div class="row">
        <li>
            <?php
            erp_html_form_input( [
                'label'    => __( 'Asset Type', 'erp-pro' ),
                'name'     => 'asset_type',
                'class'    => 'asset-type',
                'value'    => '',
                'type'     => 'select',
                'options'  => erp_hr_assets_get_asset_type(),
                'required' => true
            ] );
            ?>
        </li>
    </div>

</ul>


<div class="erp-employee-form erp-asset-form">
    <div class="single-item-area">

        <fieldset>
            <legend><?php _e( 'Item', 'erp-pro' ); ?>&nbsp;<span
                    class="item-no"></span>&nbsp;<?php _e( 'Details', 'erp-pro' ); ?></legend>

            <ol class="form-fields two-col">
                <li>
                    <?php
                    erp_html_form_input( [
                        'label'    => __( 'Item Code', 'erp-pro' ),
                        'name'     => 'items[1][item_code]',
                        'value'    => '',
                        'class'    => 'item-code',
                        'required' => true,
                        'help'     => __( '<br>An unique id to distinguish each item (e.g. LAPTOP0001)' )
                    ] );
                    ?>
                </li>
                <li>
                    <?php
                    erp_html_form_input( [
                        'label' => __( 'Model No', 'erp-pro' ),
                        'name'  => 'items[1][model_no]',
                        'value' => '',
                        'class' => 'model-no'
                    ] );
                    ?>
                </li>
                <li>
                    <?php
                    erp_html_form_input( [
                        'label' => __( 'Description', 'erp-pro' ),
                        'name'  => 'items[1][item_desc]',
                        'value' => '',
                        'class' => 'item-desc',
                        'type'  => 'textarea',
                    ] );
                    ?>
                </li>
                <li>
                    <?php
                    erp_html_form_input( [
                        'label' => __( 'Serial/License Info', 'erp-pro' ),
                        'name'  => 'items[1][item_serial]',
                        'value' => '',
                        'class' => 'serial-info',
                        'type'  => 'textarea',
                    ] );
                    ?>
                </li>
                <li>
                    <?php
                    erp_html_form_input( [
                        'label' => __( 'Manufacturer', 'erp-pro' ),
                        'name'  => 'items[1][manufacturer]',
                        'value' => '',
                        'class' => 'manufacturer'
                    ] );
                    ?>
                </li>
                <li>
                    <?php
                    erp_html_form_input( [
                        'label' => __( 'Price', 'erp-pro' ),
                        'name'  => 'items[1][price]',
                        'value' => '',
                        'type'  => 'number',
                        'class' => 'price'
                    ] );
                    ?>
                </li>
                <li>
                    <?php
                    erp_html_form_input( [
                        'label' => __( 'Warranty Till', 'erp-pro' ),
                        'name'  => 'items[1][date_warr]',
                        'value' => '',
                        'class' => 'assets-date-field warranty-date',
                    ] );
                    ?>
                </li>
                <li>
                    <?php
                    erp_html_form_input( [
                        'label' => __( 'Expiry Date', 'erp-pro' ),
                        'name'  => 'items[1][date_exp]',
                        'value' => '',
                        'class' => 'assets-date-field expiry-date',
                        'help'  => __( '<br>Items with a license key may have expiry', 'erp-pro' )
                    ] );
                    ?>
                </li>
                <li>
                    <?php
                    erp_html_form_input( [
                        'label' => __( 'Allottable?', 'erp-pro' ),
                        'name'  => 'items[1][allottable]',
                        'value' => '',
                        'class' => 'allottable',
                        'type'  => 'checkbox',
                        'help'  => __( 'Check if the asset is allottable', 'erp-pro' ),
                    ] );
                    ?>
                </li>
            </ol>
        </fieldset>
    </div>
    <div id="extra-item-area"></div>
    <br><br>
    <div class="add-new-item"></div>
</div>
<input type="hidden" name="action" id="erp-assets-action" value="erp-hr-assets-new">
<?php wp_nonce_field( 'wp-erp-hr-asset-new' ) ?>
