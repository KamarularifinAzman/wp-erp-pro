<div id="payrun-wrapper" class="wrap erp-payrun not-loaded">
    <h1><?php _e( 'Pay Run List','erp-pro' );?><span class="spinner"></span></h1>
    <div class="col-6">
        <form method="get">
            <!--<input type="hidden" name="page" value="<?php /*echo erp_payroll_get_admin_link( 'payrun' ) */?>">-->
            <input type="hidden" name="page" value="erp-hr">
            <input type="hidden" name="section" value="payroll">
            <input type="hidden" name="sub-section" value="payrun">
            <?php
                $payrun_list_table = new WeDevs\Payroll\PayrunListTable();
                $payrun_list_table->prepare_items();
                $payrun_list_table->search_box( __( 'Search', 'erp-pro' ), 'payrun-search' );
                $payrun_list_table->views();
                $payrun_list_table->display();
            ?>
        </form>
    </div>
</div>
