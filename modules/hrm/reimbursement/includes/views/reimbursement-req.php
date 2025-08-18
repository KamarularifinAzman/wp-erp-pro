<?php
$slug = 'erp-accounting&section=reimbursement';

if ( \WeDevs\ERP\Accounting\Reimbursement\Admin::need_backward_compatible() ) {
    $slug = 'erp-accounting-reimbursement';
}

$filter = isset( $_GET['filter'] ) ? $_GET['filter'] : false;
if ( $filter ) {
    $cancel_url   = admin_url('admin.php?page=' . $slug . '&filter=' . $filter );
} else {
    $cancel_url   = admin_url('admin.php?page=' . $slug);
}


if ( $id ) {
    $transaction = erp_ac_get_all_transaction([
        'id'     => $id,
        'join'   => ['journals', 'items'],
        'type'   => ['reimbur'],
        'output_by' => 'array'
    ]);

    $transaction = reset( $transaction );

    foreach ( $transaction['journals'] as $key => $journal ) {

        $journal_id = $journal['id'];

        if ( $journal['type'] == 'main' ) {
            $account_id       = $main_ledger_id  = $journal['ledger_id'];
            $jor_itms['main'] = $journal;

        } else {
            $jor_itms['journal'][] = $journal;
        }
    }

    foreach ( $transaction['items'] as $key => $item ) {
        $journal_id         = $item['journal_id'];
        $jor_itms['item'][] = $item;
    }
}
$reimbur = isset( $transaction ) ? $transaction : false;
$status = isset( $reimbur['status'] ) ? $reimbur['status'] : 'all';

if ( ( $status == 'awaiting_approval' || $status == 'awaiting_payment' ) && erp_ac_reimbur_is_employee() ) {
    return;
}

if ( $id ) {
    $selected_vendor = isset( $transaction['user_id'] ) ? $transaction['user_id'] : '';
} else {
    $selected_vendor = isset( $_GET['vendor_id'] ) ? intval( $_GET['vendor_id'] ) : '';
}

$items_for_tax = isset( $transaction['items'] ) ? $transaction['items'] : [];
$tax_labels    = erp_ac_get_trans_unit_tax_rate( $items_for_tax );

?>
<div class="wrap erp-ac-form-wrap">
    <h2><?php _e( 'Add Receipt', 'erp-pro' ); ?></h2>

    <?php
    $selected_account_id = isset( $_GET['account_id'] ) ? intval( $_GET['account_id'] ) : 0;
    $dropdown = erp_ac_get_chart_dropdown([
        'exclude'  => [1, 2, 4, 5]
    ] );


    $dropdown_html = erp_ac_render_account_dropdown_html( $dropdown, array(
        'name'     => 'line_account[]',
        'selected' => isset( $journal['ledger_id'] ) ? $journal['ledger_id'] : false,
        'class'    => 'erp-select2'
    ) );

    ?>
    <form action="" method="post" class="erp-form erp-ac-transaction-form erp-ac-payment_voucher-form">

        <ul class="form-fields block" style="width:100%;">

            <li>
                <ul class="erp-form-fields two-col block">
                    <?php

                        if ( erp_ac_reimbur_is_employee()  ) {
                            erp_html_form_input( array(
                                'name'  => 'user_id',
                                'type'  => 'hidden',
                                'value' => get_current_user_id(),
                            ) );
                        } else {
                            ?>
                            <li class="erp-form-field erp-ac-replace-wrap">
                                <div class="erp-ac-replace-content">
                                    <?php
                                        erp_html_form_input( array(
                                            'label'       => __( 'Receipt From', 'erp-pro' ),
                                            'name'        => 'user_id',
                                            'type'        => 'select',
                                            'value'       => isset( $reimbur['user_id'] ) ? $reimbur['user_id'] : '',
                                            'required'    => true,
                                            'class'       => 'erp-select2 erp-ac-not-found-in-drop',
                                            'options'     => [ '' => __( '&mdash; Select &mdash;', 'erp-pro' ) ] + erp_reimburs_get_employees(),
                                            'custom_attr' => [ 'data-content' => 'erp-ac-new-vendor-content-pop' ]
                                        ) );
                                    ?>
                                </div>
                            </li>
                        <?php
                        }

                        erp_html_form_input( array(
                            'name'  => 'account_id',
                            'type'  => 'hidden',
                            'value' => 10,
                        ) );

                        erp_html_form_input( array(
                            'name'  => 'id',
                            'type'  => 'hidden',
                            'value' => $id
                        ) );
                    ?>

                </ul>
            </li>

            <li>
                <ul class="erp-form-fields two-col block clearfix">
                    <li class="erp-form-field">
                        <?php
                        erp_html_form_input( array(
                            'label'       => __( 'Receipt Date', 'erp-pro' ),
                            'name'        => 'issue_date',
                            'placeholder' => date( 'Y-m-d' ),
                            'type'        => 'text',
                            'value'       => isset( $transaction['issue_date'] ) ? $transaction['issue_date'] : date( 'Y-m-d', strtotime( current_time( 'mysql' ) ) ),
                            'required' => true,
                            'class'       => 'erp-date-picker-from',
                        ) );
                        ?>
                    </li>

                    <li class="erp-form-field">
                        <?php
                        erp_html_form_input( array(
                            'label'       => __( 'Receipt Due Date', 'erp-pro' ),
                            'name'        => 'due_date',
                            'placeholder' => date( 'Y-m-d' ),
                            'type'        => 'text',
                            'value'       => isset( $transaction['issue_date'] ) ? $transaction['issue_date'] : '',
                            'required'    => true,
                            'class'       => 'erp-date-picker-to'
                        ) );
                        ?>
                    </li>

                    <li class="erp-form-field">
                        <?php
                        erp_html_form_input( array(
                            'label' => __( 'Reference', 'erp-pro' ),
                            'name'  => 'ref',
                            'type'  => 'text',
                            'class' => 'erp-ac-reference-field',
                            'value' => isset( $transaction['ref'] ) ? $transaction['ref'] : '',
                            'addon' => '#',
                        ) );
                        ?>
                    </li>
                </ul>
            </li>

        </ul>
        <div class="erp-ac-voucher-table-wrap">
            <?php  include WPERP_ACCOUNTING_VIEWS . '/common/transaction-table.php'; ?>
        </div>
        <?php include WPERP_ACCOUNTING_VIEWS . '/common/memo.php'; ?>

        <input type="hidden" name="field_id" value="0">
        <input type="hidden" name="type" value="reimbur">
        <input type="hidden" name="form_type" value="reimbur_invoice">
        <input type="hidden" name="page" value="erp-accounting-expense">
        <input type="hidden" name="erp-action" value="ac-new-payment-voucher">
        <?php
            erp_html_form_input( array(
                'name'        => 'id',
                'type'        => 'hidden',
                'value'       => $id
            ) );
        ?>

        <?php wp_nonce_field( 'erp-ac-trans-new' ); ?>

        <input type="submit" name="submit_erp_ac_trans" style="display: none;">
        <input type="hidden" id="erp-ac-btn-status" name="status" value="">
        <input type="hidden" id="erp-ac-redirect" name="redirect" value="0">

        <div class="erp-ac-btn-group-wrap">

            <div class="erp-button-bar-left">
                <?php
                    if ( $status == 'draft' || $status == 'all' ) {
                        ?>
                        <div class="button-group erp-button-group">
                             <button type="button" data-redirect="single_page" data-btn_status="save_and_draft" class="button erp-ac-trns-form-submit-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php _e( 'Save as Draft', 'erp-pro' ); ?>
                            </button>
                            <button type="button" class="button erp-dropdown-toggle" data-toggle="erp-dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="erp-dropdown-menu">
                                <li><a class="erp-ac-trns-form-submit-btn" data-redirect="single_page" data-btn_status="save_and_draft" href="#"><?php _e( 'Save as Draft', 'erp-pro' ); ?></a></li>
                                <!-- <li><a class="erp-ac-trns-form-submit-btn" data-redirect="0" data-btn_status="save_and_submit_for_approval" href="#"><?php _e( 'Save & submit for approval', 'erp-pro' ); ?></a></li> -->
                                <li><a class="erp-ac-trns-form-submit-btn" data-redirect="same_page" data-btn_status="save_and_add_another" href="#"><?php _e( 'Save & add another', 'erp-pro' ); ?></a></li>
                            </ul>

                        </div>
                        <?php
                    }
                ?>
                <a href="<?php echo esc_url( $cancel_url ); ?>" class="button"><?php _e( 'Cancel', 'erp-pro' ); ?></a>
            </div>

            <div class="erp-button-bar-right">
                <?php
                if ( $status == 'draft' || $status == 'all' ) {
                    ?>
                    <div class="button-group erp-button-group">
                        <button  data-redirect="single_page" data-btn_status="awaiting_approval" type="button" class="button button-primary erp-ac-trns-form-submit-btn">
                            <?php _e( 'Submit for approval', 'erp-pro' ); ?>
                        </button>
                        <button type="button" class="button button-primary erp-dropdown-toggle" data-toggle="erp-dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="caret"></span>
                        </button>
                        <ul class="erp-dropdown-menu">
                            <li><a class="erp-ac-trns-form-submit-btn" data-redirect="single_page" data-btn_status="awaiting_approval" href="#"><?php _e( 'Submit for approval', 'erp-pro' ); ?></a></li>
                            <li><a class="erp-ac-trns-form-submit-btn" data-redirect="same_page" data-btn_status="awaiting_approve_and_add_another" href="#"><?php _e( 'Submit for approval & add another', 'erp-pro' ); ?></a></li>
                        </ul>
                    </div>
                    <?php
                }

                if ( $status == 'awaiting_approval' || $status == 'awaiting_payment' || $status == 'void' ) {
                    ?>

                    <div class="button-group erp-button-group">
                        <button  data-redirect="single_page" data-btn_status="awaiting_payment" type="button" class="button button-primary erp-ac-trns-form-submit-btn">
                            <?php _e( 'Submit for payment', 'erp-pro' ); ?>
                        </button>
                        <button type="button" class="button button-primary erp-dropdown-toggle" data-toggle="erp-dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="caret"></span>
                        </button>
                        <ul class="erp-dropdown-menu">
                            <li><a class="erp-ac-trns-form-submit-btn" data-redirect="single_page" data-btn_status="awaiting_payment" href="#"><?php _e( 'Submit for payment', 'erp-pro' ); ?></a></li>
                            <li><a class="erp-ac-trns-form-submit-btn" data-redirect="single_page" data-btn_status="void" href="#"><?php _e( 'Decline', 'erp-pro' ); ?></a></li>
                        </ul>
                    </div>
                    <?php
                }

                ?>
            </div>
        </div>
    </form>

    <div class="erp-ac-voucher-table-wrap-clone" id="erp-ac-new-payment-voucher" style="display: none;">

    <?php
        $dropdown_html = erp_ac_render_account_dropdown_html( $dropdown, array(
            'name'     => 'line_account[]',
            'class'    => 'erp-select2 erp-ac-selece-custom'
        ) );
        $jor_itms = [];

        include WPERP_ACCOUNTING_VIEWS . '/common/transaction-table.php';
    ?>
    </div>
</div>
