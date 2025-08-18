<?php $reimburs = erp_ac_reimbursement_register_form_types(); ?>
<div class="wrap erp-accounting">
    <h2>
        <?php
        $slug = 'erp-accounting&section=reimbursement';
        if ( current_user_can( 'employee' ) ) {
            $slug = 'erp-hr&section=reimbursement';
        }

        if ( \WeDevs\ERP\Accounting\Reimbursement\Admin::need_backward_compatible() ) {
            $slug = 'erp-accounting-reimbursement';
        }
        _e( 'Reimbursements', 'erp-pro' );

        foreach ( $reimburs as $key => $reimbur ) {
            printf( '<a class="add-new-h2" href="%s%s" title="%s">%s</a>',
                admin_url( 'admin.php?page=' . $slug . '&action=new&type=' ),
                $key,
                esc_attr( $reimbur['description'] ),
                $reimbur['label'] );
        }
        ?>
    </h2>
    <?php include_once dirname( __FILE__ ) . '/transaction-chart.php'; ?>

    <form method="get">
        <?php
        if ( \WeDevs\ERP\Accounting\Reimbursement\Admin::need_backward_compatible() ) { ?>
            <input type="hidden" name="page" value="erp-accounting-reimbursement">
        <?php } else { ?>
            <input type="hidden" name="page" value="erp-accounting">
            <input type="hidden" name="section" value="reimbursement">
        <?php
        }
        ?>
        <?php
        $list_table = new WeDevs\ERP\Accounting\Reimbursement\ReimbursementTransactionListTable();
        $list_table->prepare_items();
        $list_table->views();

        $list_table->display();
        ?>
    </form>

</div>

