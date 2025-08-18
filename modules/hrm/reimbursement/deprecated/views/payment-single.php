<?php
$company          = new \WeDevs\ERP\Company();
$user             = new \WeDevs\ERP\People( intval( $transaction->user_id ) );
$status           = $transaction->status == 'draft' ? false : true;
$url              = admin_url( 'admin.php?page=erp-accounting-expense&action=new&type=payment_voucher&transaction_id=' . $transaction->id );
$more_details_url = erp_ac_get_expense_voucher_url( $transaction->id );
$taxinfo          = erp_ac_get_tax_info();
$employee         = get_user_by( 'id', intval( $transaction->user_id ) );

if ( erp_ac_reimbursement_is_hrm_active() && user_can( $transaction->user_id, 'employee' ) ) {
    $employee          = new \WeDevs\ERP\HRM\Employee( intval( $transaction->user_id ) );
    $profile           = $employee->get_details_url();
    $user_display_name = $employee->get_full_name();
} else {
    $user_display_name = $employee->display_name;
    $profile           = admin_url( 'user-edit.php?user_id=' . $transaction->user_id );
}
?>
<div class="wrap">

    <h2>
    <?php
        _e( 'Payment Voucher', 'erp-pro' );

        if ( isset( $popup_status ) ) {
            printf( '<a href="%1$s" class="erp-ac-more-details">%2$s &rarr;</a>', $more_details_url, __('More Details','accounting') );
        }
    ?>
    </h2>

    <div class="invoice-preview-wrap">
        <div class="erp-grid-container">
            <?php
            if ( ! isset( $popup_status ) ) {
                ?>
                <div class="row invoice-buttons erp-hide-print">
                    <div class="col-6">
                        <?php if ( $status ) {
                            ?>
                            <a href="#" class="button button-large erp-ac-print erp-hide-print"><?php _e( 'Print', 'erp-pro' ); ?></a>
                            <?php
                        } else {
                            ?>
                            <a href="<?php echo $url; ?>" class="button button-large"><?php _e( 'Edit Voucher', 'erp-pro' ); ?></a>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>

            <div class="row">
                <div class="invoice-number">
                    <?php
                        printf( __( 'Voucher Number: <strong>%s</strong>', 'erp-pro' ), $transaction->id );
                    ?>
                </div>
            </div>

            <div class="page-header">
                <div class="row">
                    <div class="col-3 company-logo">
                        <?php echo $company->get_logo(); ?>
                    </div>

                    <div class="col-3 align-right">
                        <strong><?php echo $company->name ?></strong>
                        <div><?php echo $company->get_formatted_address(); ?></div>
                    </div>
                </div><!-- .row -->
            </div><!-- .page-header -->

            <hr>

            <div class="row">
                <div class="col-3">
                    <div class="bill-to"><?php _e( 'Bill to:', 'erp-pro' ); ?>
                    <strong><a href="<?php echo $profile; ?>"><?php echo $user_display_name; ?></a></strong>
                    </div>
                </div>
                <div class="col-3 align-right">
                    <table class="table info-table">
                        <tbody>
                            <tr>
                                <th><?php _e( 'Referance Number', 'erp-pro' ); ?>:</th>
                                <td><?php echo $transaction->ref; ?></td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Voucher Date', 'erp-pro' ); ?>:</th>
                                <td><?php echo strtotime( $transaction->issue_date ) < 0 ? '&mdash;' : erp_format_date( $transaction->issue_date ); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Due Date', 'erp-pro' ); ?>:</th>
                                <td><?php echo strtotime( $transaction->due_date ) < 0 ? '&mdash;' : erp_format_date( $transaction->due_date ); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Amount Due', 'erp-pro' ); ?>:</th>
                                <td><?php echo erp_ac_get_price( $transaction->due ); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div><!-- .row -->

            <hr>

            <div class="row align-right">
                <table class="table fixed striped">
                    <thead>
                        <tr>
                            <th class="align-left product-name"><?php _e( 'Product', 'erp-pro' ) ?></th>
                            <th><?php _e( 'Quantity', 'erp-pro' ) ?></th>
                            <th><?php _e( 'Unit Price', 'erp-pro' ) ?></th>
                            <th><?php _e( 'Discount', 'erp-pro' ) ?></th>
                            <th><?php _e( 'Tax(%)', 'erp-pro' ); ?></th>
                            <th><?php _e( 'Amount', 'erp-pro' ) ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ( $transaction->items as $line ) { ?>
                            <tr>
                                <td class="align-left product-name">
                                    <strong><?php _e( 'Unpaid Expense Claims', 'erp-pro' ); ?></strong>
                                    <div class="product-desc"><?php echo $line->description; ?></div>
                                </td>
                                <td><?php echo $line->qty; ?></td>
                                <td><?php echo erp_ac_get_price( $line->unit_price ); ?></td>
                                <td><?php echo $line->discount; ?></td>
                                <td><?php echo $line->tax ? $taxinfo[$line->tax]['name'] .' ('. $taxinfo[$line->tax]['rate'] .'%)' : '0.00'; ?></td>
                                <td><?php echo erp_ac_get_price( $line->line_total ); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div><!-- .row -->

            <div class="row">
                <div class="col-3">
                    <?php echo $transaction->summary; ?>
                </div>
                <div class="col-3">
                    <table class="table info-table align-right">
                        <tbody>
                            <tr>
                                <th><?php _e( 'Sub Total', 'erp-pro' ); ?></th>
                                <td><?php echo erp_ac_get_price( $transaction->sub_total ); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Total', 'erp-pro' ); ?></th>
                                <td><?php echo $transaction->total; ?></td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Total Paid', 'erp-pro' ); ?></th>
                                <td>
                                    <?php
                                    $total_paid = floatval( $transaction->total ) - floatval( $transaction->due );
                                    echo erp_ac_get_price( $total_paid );
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- .erp-grid-container -->
    </div>

    <?php include_once WPERP_ACCOUNTING_VIEWS . '/common/attachment.php'; ?>

</div>

