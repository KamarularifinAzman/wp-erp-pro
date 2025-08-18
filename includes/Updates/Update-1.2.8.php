<?php

namespace WeDevs\ERP_PRO\Updates;

/**
 * Class to handle updates for version 1.2.8
 *
 * @since 1.2.8
 */
class Update_1_2_8 {

    /**
     * Class constructor.
     *
     * @since 1.2.8
     *
     * @return void
     */
    public function __construct() {
        $this->modify_qty_columns();
        $this->change_slug_column_size_of_life_stage_table();
        $this->perform_experience_type_migration();
    }

    /**
     * Modifies `qty` columns in different tables.
     *
     * @since 1.2.8
     *
     * @return void
     */
    private function modify_qty_columns() {
        global $wpdb;

        $tables = [
            'erp_acct_sales_return_details',
            'erp_acct_purchase_return_details'
        ];

        foreach ( $tables as $table ) {
            $wpdb->query( "ALTER TABLE {$wpdb->prefix}{$table} MODIFY COLUMN qty decimal(10,2) DEFAULT NULL" );
        }
    }

    /**
     * Run experience type change/update migrator
     *
     * @since 1.2.8
     *
     * @retun void
     */
    function perform_experience_type_migration() {
        $recruitments = get_posts(
            [
                'post_type'      => 'erp_hr_recruitment',
                'posts_per_page' => -1,
            ]
        );

        global $erp_recruitment_type_migrator_1_2_8;

        foreach ( $recruitments as $recruitment ) {
            $erp_recruitment_type_migrator_1_2_8->push_to_queue( $recruitment->ID );
        }

        $erp_recruitment_type_migrator_1_2_8->save();
        $erp_recruitment_type_migrator_1_2_8->dispatch();
    }

    /**
     * Change column width of people life stage's slug column
     *
     * @since 1.2.8
     */
    function change_slug_column_size_of_life_stage_table() {
        global $wpdb;

        $sql = "ALTER TABLE {$wpdb->prefix}erp_people_life_stages MODIFY slug varchar(255)";
        $wpdb->query( $sql );
    }
}

new Update_1_2_8();
