<?php
$slug = 'erp-accounting&section=reimbursement';

if ( \WeDevs\ERP\Accounting\Reimbursement\Admin::need_backward_compatible() ) {
    $slug = 'erp-accounting-reimbursement';
}

$company             = new \WeDevs\ERP\Company();
$payments            = \WeDevs\ERP\Accounting\Model\Payment::where('child', '=', $transaction->id )->get()->toArray();
$partials_id         = wp_list_pluck( $payments, 'transaction_id' );
$partial_transaction = \WeDevs\ERP\Accounting\Model\Transaction::whereIn( 'id', $partials_id )->get()->toArray();

$url                 = admin_url( 'admin.php?page=' . $slug . '&action=new&type=reimbur_invoice&transaction_id=' . $transaction->id );
$more_details_url    = erp_ac_get_slaes_payment_invoice_url( $transaction->id );
$taxinfo             = erp_ac_get_tax_info();

$current_user        = wp_get_current_user();
$sender              = $current_user->user_email;
$email_subject       = __( 'Invoice#', 'erp-pro' ) . $transaction->invoice_number . __( ' from ', 'erp-pro' ) . $company->name;
$link_hash           = erp_ac_get_invoice_link_hash( $transaction );
$readonly_url        = add_query_arg( [ 'query' => 'readonly_invoice', 'trans_id' => $transaction->id, 'auth' => $link_hash ], site_url() );
$employee            = get_user_by( 'id', intval( $transaction->user_id ) );

if ( erp_ac_reimbursement_is_hrm_active() && user_can($transaction->user_id, 'employee' ) ) {
    $employee          = new \WeDevs\ERP\HRM\Employee( intval($transaction->user_id) );
    $user_display_name = $employee->get_full_name();
    $profile           = $employee->get_details_url();
} else {
    $user_display_name = $employee->display_name;
    $profile           = admin_url( 'user-edit.php?user_id=' . $transaction->user_id );
}

?>
<div class="wrap">

    <h2>
    <?php
        _e( 'New Receipt ', 'erp-pro' );
        if ( isset( $popup_status ) ) {
            printf( '<a href="%1$s" class="erp-ac-more-details">%2$s &rarr;</a>', $more_details_url, __('More Details','erp-pro') );
        }
    ?>
    </h2>

    <div class="invoice-preview-wrap">
        <div class="erp-grid-container">
            <?php
            if ( ! isset( $popup_status ) ) {
                ?>
                <div class="row invoice-buttons erp-hide-print" id="invoice-button-container" data-theme="drop-theme-hubspot-popovers">
                    <div class="col-6">
                        <?php
                        if ( $transaction->status == 'draft' ) {
                            ?>
                            <a href="<?php echo $url; ?>" class="button button-large"><?php _e( 'Edit Receipt', 'erp-pro' ); ?></a>

                            <?php
                        } else if ( $transaction->status == 'awaiting_approval' && ! erp_ac_reimbur_is_employee() ) {
                            ?>
                            <a href="<?php echo $url; ?>" class="button button-large"><?php _e( 'Edit Receipt', 'erp-pro' ); ?></a>
                            <?php
                        }
                        ?>
                        <a href="#" class="button button-large erp-ac-print erp-hide-print"><i class="fa fa-print"></i>&nbsp;<?php _e( 'Print', 'erp-pro' ); ?></a>
                    </div>
                </div>
                <template class="more-action-content">
                    <ul>
                        <li><a href="<?php echo wp_nonce_url( admin_url( "admin-ajax.php?action=erp-ac-sales-invoice-export&transaction_id={$transaction->id}" ), 'accounting-invoice-export' ); ?>" class="invoice-export-pdf"><?php _e( 'Export as PDF', 'erp-pro' ); ?></a></li>
                        <li id="get-readonly-link"><a href="#" data-title="<?php _e( 'Get Invoice Link', 'erp-pro' ); ?>" class="invoice-get-link"><?php _e( 'Get Link', 'erp-pro' ); ?></a></li>
                        <li id="copy-readonly-link" style="display: none"><input onClick="this.select();" type="text" value="<?php echo esc_url( $readonly_url ); ?>" id="invoice-readonly-link">&nbsp;<a data-clipboard-target="#invoice-readonly-link" class="copy-readonly-invoice" title="<?php _e('Click to copy', 'erp-pro' ); ?>" id="erp-tips-get-link" style="cursor: pointer"><i class="fa fa-copy"></i></a></li>
                        <li><a href="#" data-url="<?php echo esc_url( $readonly_url ); ?>" data-transaction-id="<?php echo $transaction->id; ?>" data-sender="<?php echo $sender; ?>" data-receiver="<?php echo $user->email; ?>" data-subject="<?php echo $email_subject; ?>" data-title="<?php _e( 'Send Invoice', 'erp-pro' ); ?>" data-button="<?php _e( 'Send', 'erp-pro' ); ?>" data-type="invoice" class="invoice-send-email"><?php _e( 'Send Via Email', 'erp-pro' ); ?></a></li>
                    </ul>
                </template>
                <?php
            }
            ?>
            <div class="row">
                <div class="invoice-number">
                    <?php
                        $ivoice = isset( $transaction->invoice_number ) ? erp_ac_get_invoice_number( $transaction->invoice_number, $transaction->invoice_format ) : $transaction->id;
                        printf( __( 'Invoice: <strong>%s</strong>', 'erp-pro' ), $ivoice );
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
                    <div class="billing-address"><?php echo nl2br( $transaction->billing_address ); ?></div>
                </div>
                <div class="col-3 align-right">
                    <table class="table info-table">
                        <tbody>
                            <tr>
                                <th><?php _e( 'Invoice Date', 'erp-pro' ); ?>:</th>
                                <td><?php echo strtotime( $transaction->issue_date ) < 0 ? '&mdash;' : erp_format_date( $transaction->issue_date ); ?></td>
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

                        <?php foreach ( $transaction->items as $line ) {  ?>
                            <tr>
                                <td class="align-left product-name">
                                    <strong><?php echo $line->journal->ledger->name; ?></strong>
                                    <div class="product-desc"><?php echo $line->description; ?></div>
                                </td>
                                <td><?php echo $line->qty; ?></td>
                                <td><?php echo erp_ac_get_price( $line->unit_price ); ?></td>
                                <td><?php echo $line->discount; ?></td>

                                <td><?php //echo $taxinfo[$line->tax]['name'] .' ('. $taxinfo[$line->tax]['rate'] .'%)'; ?></td>
                                <td><?php echo ( $line->tax_rate * $line->line_total ) / 100; ?></td>
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
                                <td><?php echo erp_ac_get_price( $transaction->total ); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e( 'Total Related Payments', 'erp-pro' ); ?></th>
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
    <?php include_once WPERP_ACCOUNTING_VIEWS . '/common/partial-payments.php'; ?>

</div>

