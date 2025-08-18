<ul class="asset-form-fields">
    <div class="row" data-selected="{{data[0].category_id}}">
        <li>
            <?php
            erp_html_form_input( array(
                'label'    => __( 'Category', 'erp-pro'),
                'name'     => 'category_id',
                'value'    => '',
                'type'     => 'select',
                'class'    => 'asset-category',
                'options'  => erp_hr_assets_get_categories_dropdown(),
                'required' => true
            ) );
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
            erp_html_form_input( array(
                'label'    => __( 'Item Group', 'erp-pro'),
                'name'     => 'item_group',
                'value'    => '{{data[0].item_group}}',
                'help'     => __( 'Item group (e.g. Apple Macbook Pro)', 'erp-pro' ),
                'required' => true
            ) );
            ?>
        </li>
    </div>

    <input type="hidden" name="asset_type" value="{{data[0].asset_type}}">

</ul>

<div class="erp-employee-form erp-asset-form">
    <div class="single-item-area">
        <# for (var i=0, max = data.length; i<max; i++ ) {
            if ( 'variable' == data[0].asset_type && 0 == i ) { continue }
        #>
            <fieldset>
                <legend><?php _e( 'Item', 'erp-pro' ); ?><span
                        class="item-no"><#if(i!=0){#>{{i}}<#}#></span>&nbsp;<?php _e( 'Details', 'erp-pro' ); ?></legend>

                <ol class="form-fields two-col">
                    <li>
                        <?php
                        erp_html_form_input( array(
                            'label' => __( 'Item Code', 'erp-pro' ),
                            'name'  => 'items[{{i}}][item_code]',
                            'value' => '{{data[i].item_code}}',
                            'class' => 'item-code',
                            'help'  => __( '<br>An unique id to distinguish each item (e.g. LAPTOP0001)'),
                            'required' => true
                        ) );
                        ?>
                    </li>
                    <li>
                        <?php
                        erp_html_form_input( array(
                            'label' => __( 'Model No', 'erp-pro' ),
                            'name'  => 'items[{{i}}][model_no]',
                            'value' => '{{data[i].model_no}}',
                            'class' => 'model-no'
                        ) );
                        ?>
                    </li>
                    <li>
                        <?php
                        erp_html_form_input( array(
                            'label' => __( 'Manufacturer', 'erp-pro' ),
                            'name'  => 'items[{{i}}][manufacturer]',
                            'value' => '{{data[i].manufacturer}}',
                            'class' => 'manufacturer'
                        ) );
                        ?>
                    </li>
                    <li>
                        <?php
                        erp_html_form_input( array(
                            'label' => __( 'Price', 'erp-pro' ),
                            'name'  => 'items[{{i}}][price]',
                            'value' => '{{data[i].price}}',
                            'class' => 'price'
                        ) );
                        ?>
                    </li>
                    <li>
                        <?php
                        erp_html_form_input( array(
                            'label' => __( 'Expiry Date', 'erp-pro'),
                            'name'  => 'items[{{i}}][date_exp]',
                            'value' => '{{data[i].date_expiry}}',
                            'class' => 'assets-date-field expiry-date',
                        ) );
                        ?>
                    </li>
                    <li>
                        <?php
                        erp_html_form_input( array(
                            'label' => __( 'Warranty Till', 'erp-pro'),
                            'name'  => 'items[{{i}}][date_warr]',
                            'value' => '{{data[i].date_warranty}}',
                            'class' => 'assets-date-field warranty-date',
                        ) );
                        ?>
                    </li>
                    <li data-selected="{{data[i].allottable}}">
                        <?php
                        erp_html_form_input( array(
                            'label' => __( 'Allottable?', 'erp-pro'),
                            'name'  => 'items[{{i}}][allottable]',
                            'value' => '',
                            'class' => 'allottable',
                            'type'  => 'checkbox',
                            'help'  => __( 'Check if the asset is allottable', 'erp-pro' ),
                        ) );
                        ?>
                    </li>
                    <li>
                        <?php
                        erp_html_form_input( array(
                            'label' => __( 'Serial/License Info', 'erp-pro' ),
                            'name'  => 'items[{{i}}][item_serial]',
                            'value' => '{{data[i].item_serial}}',
                            'class' => 'serial-info',
                            'type'  => 'textarea',
                        ) );
                        ?>
                    </li>
                    <li>
                        <?php
                        erp_html_form_input( array(
                            'label' => __( 'Description', 'erp-pro'),
                            'name'  => 'items[{{i}}][item_desc]',
                            'value' => '{{data[i].item_desc}}',
                            'class' => 'item-desc',
                            'type'  => 'textarea',
                        ) );
                        ?>
                    </li>
                    <input class="row-id" type="hidden" name="items[{{i}}][id]" value="{{data[i].id}}">
                </ol>
                <button class="delete-item button-secondary"><?php _e( 'Delete', 'erp-pro' ); ?></button>
            </fieldset>
        <# } #>
    </div>
    <div id="extra-item-area"></div>
    <br><br>
    <div class="add-new-item"></div>
</div>
<input type="hidden" name="action" id="erp-assets-action" value="erp-hr-assets-new">
<input type="hidden" name="parent_row_id" value="{{data[0].id}}">
<?php wp_nonce_field( 'wp-erp-hr-asset-new' ) ?>
