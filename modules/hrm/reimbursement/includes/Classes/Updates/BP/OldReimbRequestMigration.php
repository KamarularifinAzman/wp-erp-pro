<?php
namespace WeDevs\Reimbursement\Classes\Updates\BP;

class OldReimbRequestMigration extends \WP_Background_Process {
	protected $action = 'old_reimb_migration';

	protected function task( $id ) {
		global $wpdb;

		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM {$wpdb->prefix}erp_acct_reimburse_requests WHERE id = %d",
			$id
		) );

		if ( $exists ) return;

		$request = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					id,
					user_id,
					ref AS reference,
					summary AS particulars,
					issue_date AS trn_date,
					trans_total AS amount_total,
					files AS attachments
				FROM {$wpdb->prefix}erp_ac_transactions WHERE id = %d",
				$id
			),
			ARRAY_A
		);

		if ( empty( $request ) ) return;

		$status_closed = 7;
		$request['status'] = $status_closed;

		$request_details = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
				description,
				line_total
				FROM {$wpdb->prefix}erp_ac_transaction_items WHERE transaction_id = %d",
				$id
			),
			ARRAY_A
		);

		foreach ( $request_details as $detail ) {
			$request['line_items'][] = [
                'particulars' => $detail['description'],
                'amount'      => $detail['line_total']
			];
		}

		// Insert request
		\erp_acct_reimb_insert_request( $request );

		return false;
	}

	protected function complete() {
		parent::complete();
	}
}
