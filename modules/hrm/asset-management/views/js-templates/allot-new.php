<ul class="allot-form-fields">
    <div class="row" data-selected="">
        <li data-selected="{{data.category_id}}">
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
        <li data-selected="{{data.item_group}}">
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
        <li data-selected="{{data.item_id}}">
            <?php
            erp_html_form_input( [
                'label'    => __( 'Item', 'erp-pro' ),
                'name'     => 'item',
                'value'    => '',
                'type'     => 'select',
                'class'    => 'item',
                'options'  => [ '-1' => __( '&mdash;Select Item&mdash;', 'erp-pro' ) ],
                'required' => true
            ] );
            ?>
        </li>
    </div>

    <div class="row">
        <li data-selected="{{data.allotted_to}}">
            <?php
            erp_html_form_input( [
                'label'    => __( 'Allot To', 'erp-pro' ),
                'name'     => 'allotted_to',
                'class'    => 'allotted_to',
                'value'    => '',
                'type'     => 'select',
                'options'  => erp_hr_get_employees_dropdown_raw(),
                'required' => true
            ] );
            ?>
        </li>
    </div>

    <div class="row">
        <li>
            <?php
            erp_html_form_input( [
                'label'    => __( 'Given Date', 'erp-pro' ),
                'name'     => 'given_date',
                'value'    => '{{data.date_given}}',
                'class'    => 'assets-date-field',
                'required' => true
            ] );
            ?>
        </li>
    </div>

    <div class="row">
        <li>
            <?php
            erp_html_form_input( [
                'label' => __( 'Returnable?'),
                'name'  => 'is_returnable',
                'value' => '',
                'type'  => 'checkbox',
                'class' => 'is_returnable',
                'help'  => __( 'Is this item returnable?', 'erp-pro' ),
            ] );
            ?>
        </li>
    </div>

    <div class="row" id="return-date" style="display: none">
        <li>
            <?php
            erp_html_form_input( [
                'label'    => __( 'Return Date', 'erp-pro' ),
                'name'     => 'return_date',
                'value'    => '{{data.date_return_proposed}}',
                'class'    => 'assets-date-field',
                'required' => true
            ] );
            ?>
        </li>
    </div>

</ul>

<input type="hidden" name="action" value="erp-hr-allottment-new">
<input type="hidden" name="row_id" value="{{data.id}}">
<?php wp_nonce_field( 'wp-erp-hr-allot-new' ) ?>
