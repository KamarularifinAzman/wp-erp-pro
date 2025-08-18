<div class="wrap erp erp-hr-org-chart-wrap">
    <h2><?php esc_html_e( 'People', 'erp-pro' ); ?></h2>

    <?php do_action( 'erp_hr_people_menu' ); ?>

    <div class="erp-wrap chart-container">
        <select name="orgc_filter" id="erp-orgc-filter" class="erp-hrm-select2">
            <?php foreach( $departments as $id => $title ) : ?>
                <option value="<?php echo esc_attr( $id ); ?>"><?php echo $title; ?></option>
            <?php endforeach; ?>
        </select>

        <div class="erp-orgc-btn">
            <button id="erp-orgc-zoom-in"><span class="dashicons dashicons-plus-alt2"></span></button>
            
            <button id="erp-orgc-zoom-out"><span class="dashicons dashicons-minus"></span></button>
        </div>

        <div id="erp-orgc-zoom"></div>

        <div id="erp-hr-orgc-canvas">
            <div id="erp-hr-org-chart"></div>
            
            <div class="erp-ajax-loader-bg"></div>         
            
            <div class="erp-ajax-loader"></div>
        </div>
    </div>
</div>