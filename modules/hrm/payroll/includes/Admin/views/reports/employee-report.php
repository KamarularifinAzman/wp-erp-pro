<div id="employee-report-wrapper" class="wrap payroll-report-container erp">
    <h1><?php _e( 'Payroll Report', 'erp-pro' ); ?></h1>

    <form method="post">
        <div id="dashboard-widgets-wrap" class="erp-grid-container">
            <div class="row">
                <div class="col-6">
                    <div class="postbox">
                        <h3 class="hndle"><i class="fa fa-bar-chart-o">&nbsp;</i><?php _e( 'Employee Reports', 'erp-pro' ); ?></h3>
                        <div class="inside">
                            <div class="information-container">
                                <div id="candidate-overview-zone">
                                    <input autocomplete="off" type="text" id="from_date" v-datepicker v-model="from_date" name="from_date" value="" placeholder="From date" class="erp-date-field">
                                    <input autocomplete="off" type="text" id="to_date" v-datepicker v-model="to_date" name="to_date" value="" placeholder="To date" class="erp-date-field">
                                    <button class="button" v-on:click.prevent="getEmpReportData"><?php _e( 'Search', 'erp-pro' ); ?></button>
                                    <span class="spinner"></span>

                                    <div id="report-csv-link">
                                        <input type="hidden" id="hidden-base-url" value="<?php echo $_SERVER['REQUEST_URI'] . '&func=payroll-employee-report-csv'; ?>">
                                        <a id="csv-dl-link" class="necessary-link dl-link alignright" href="<?php echo $_SERVER['REQUEST_URI'] . '&func=payroll-employee-report-csv'; ?>">
                                            <i class="fa fa-download">&nbsp;</i><?php _e( 'Export to CSV', 'erp-pro' ); ?>
                                        </a>
                                    </div>

                                    <table id="default-report" class="wp-list-table widefat fixed striped table-rec-reports">
                                        <thead>
                                        <tr>
                                            <th class="align-left"><?php _e( 'Employee / Date', 'erp-pro' ); ?></th>
                                            <th><?php  _e( 'Gross Wages', 'erp-pro' );  ?></th>
                                            <th><?php _e( 'Allowance Section', 'erp-pro' ); ?></th>
                                            <th><?php _e( 'Allowance Amount', 'erp-pro' ); ?></th>
                                            <th><?php _e( 'Deduction Section', 'erp-pro' ); ?></th>
                                            <th><?php _e( 'Deduction Amount', 'erp-pro' ); ?></th>
                                            <th><?php _e( 'Tax Title', 'erp-pro' ); ?></th>
                                            <th><?php _e( 'Tax Amount', 'erp-pro' ); ?></th>
                                            <th><?php _e( 'Net Pay', 'erp-pro' ); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody class="not-loaded">
                                        <tr v-for="rdata in payempdata">
                                            <td class="">{{ rdata.display_name }}</td>
                                            <td class="align-center" v-if=" rdata.gross_wages == '' "></td>
                                            <td class="align-center" v-if=" rdata.gross_wages != '' ">{{ rdata.gross_wages | custom_currency }}</td>
                                            <td class="align-center">{{ rdata.allowance_item_name }}</td>
                                            <td class="align-center" v-if=" rdata.allowance_amount == '' "></td>
                                            <td class="align-center" v-if=" rdata.allowance_amount != '' ">{{ rdata.allowance_amount | custom_currency }}</td>
                                            <td class="align-center">{{ rdata.deduction_item_name }}</td>
                                            <td class="align-center" v-if=" rdata.deduction_amount == '' "></td>
                                            <td class="align-center" v-if=" rdata.deduction_amount != '' ">{{ rdata.deduction_amount | custom_currency }}</td>
                                            <td class="align-center" v-html="rdata.tax_item_name"></td>
                                            <td class="align-center" v-if=" rdata.tax_amount == '' "></td>
                                            <td class="align-center" v-if=" rdata.tax_amount != '' ">{{ rdata.tax_amount | custom_currency }}</td>
                                            <td class="align-center" v-if=" rdata.net_pay == '' "></td>
                                            <td class="align-center" v-if=" rdata.net_pay != '' ">{{ rdata.net_pay | custom_currency }}</td>
                                        </tr>
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                        <!-- inside -->
                    </div>
                    <!-- postbox -->
                </div>
                <!-- col-6 -->
            </div>
            <!-- row -->
        </div>
        <!-- erp-grid-container -->
    </form>
</div>
