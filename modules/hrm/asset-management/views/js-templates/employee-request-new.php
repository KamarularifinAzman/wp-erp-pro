<ul class="allot-form-fields">
    <div class="row" data-selected="">
        <#console.log(data);#>
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
        </li>
    </div>

    <div class="row">
        <li>
            <?php
            erp_html_form_input( [
                'label'    => __( 'Item Name', 'erp-pro' ),
                'name'     => 'item_group',
                'value'    => '',
                'type'     => 'select',
                'class'    => 'item-name',
                'options'  => [ '-1' => __( '&mdash;Select Group&mdash;', 'erp-pro' ) ],
                'required' => true
            ] );
            ?>
        </li>
    </div>

    <div class="row">
        <li>
            <?php
            erp_html_form_input( [
                'label' => __( 'If Unavailable', 'erp-pro' ),
                'name'  => 'not_in_list',
                'value' => '',
                'type'  => 'checkbox',
                'class' => 'not_in_list',
                'help'  => __( 'Not in the list?', 'erp-pro' ),
            ] );
            ?>
        </li>
    </div>

    <div class="row" id="request-description" style="display: none">
        <li>
            <?php
            erp_html_form_input( [
                'label' => __( 'Request Details', 'erp-pro' ),
                'name'  => 'request_desc',
                'value' => '',
                'type'  => 'textarea',
                'class' => 'request-desc',
                'help'  => __( 'Write in details about the item you want e.g. item name, model, manufacturer, priority, expected by date etc.' )
            ] );
            ?>
        </li>
    </div>

</ul>

<input type="hidden" name="action" value="erp-hr-asset-request-new">
<input type="hidden" name="row_id" value="{{data.id}}">
<?php wp_nonce_field( 'wp-erp-asset-request-new' ) ?>
