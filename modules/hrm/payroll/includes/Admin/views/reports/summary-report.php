<div id="summary-reports-wrapper" class="wrap payroll-report-container erp">
    <h1><?php _e( 'Payroll Report', 'erp-pro' ); ?></h1>

    <div id="dashboard-widgets-wrap" class="erp-grid-container">
        <div class="row">
            <div class="col-6">
                <div class="postbox">
                    <h3 class="hndle"><i class="fa fa-bar-chart-o">&nbsp;</i><?php _e( 'Summary Reports', 'erp-pro' ); ?></h3>
                    <div class="inside">
                        <div id="summary-reports-wrapper" class="information-container">
                            <div id="candidate-overview-zone">
                                <input autocomplete="off" type="text" id="from_date" v-datepicker v-model="from_date" name="from_date" value="" placeholder="From date" class="erp-date-field">
                                <input autocomplete="off" type="text" id="to_date" v-datepicker v-model="to_date" name="to_date" value="" placeholder="To date" class="erp-date-field">
                                <button class="button" v-on:click.prevent="getSumReportData"><?php _e( 'Search', 'erp-inventory' ); ?></button>
                                <!--<button class="button" v-on:click.prevent="clearData"><?php /*_e( 'Clear', 'erp-pro' ); */?></button>-->
                                <span class="spinner"></span>

                                <div id="report-csv-link">
                                    <input type="hidden" id="hidden-base-url" value="<?php echo $_SERVER['REQUEST_URI'] ; ?>">
                                    <a style="margin-left: 18px" id="csv-dr-link" class="necessary-link dl-link alignright" href="<?php echo $_SERVER['REQUEST_URI'] . '&func=payroll-details-report-csv'; ?>">
                                        <i class="fa fa-download">&nbsp;</i><?php _e( 'Details to CSV', 'erp-pro' ); ?>
                                    </a>
                                    <a style="margin-left: 18px" id="csv-br-link" class="necessary-link dl-link alignright" href="<?php echo $_SERVER['REQUEST_URI'] . '&func=payroll-bank-report-csv'; ?>">
                                        <i class="fa fa-download">&nbsp;</i><?php _e( 'Bank report to CSV', 'erp-pro' ); ?>
                                    </a>
                                    <a id="csv-dl-link" class="necessary-link dl-link alignright" href="<?php echo $_SERVER['REQUEST_URI'] . '&func=payroll-summary-report-csv'; ?>">
                                        <i class="fa fa-download">&nbsp;</i><?php _e( 'Export to CSV', 'erp-pro' ); ?>
                                    </a>
                                </div>

                                <table id="default-report" class="wp-list-table widefat fixed striped table-rec-reports">
                                    <thead>
                                    <tr>
                                        <th><?php _e( 'Employee', 'erp-inventory' ); ?></th>
                                        <th><?php _e( 'Basic', 'erp-inventory' );?></th>
                                        <th><?php _e( 'Allowances', 'erp-inventory' ); ?></th>
                                        <th><?php _e( 'Deductions', 'erp-inventory' ); ?></th>
                                        <th><?php _e( 'Taxes', 'erp-inventory' ); ?></th>
                                        <th><?php _e( 'Net Pay', 'erp-inventory' ); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody class="not-loaded">
                                    <tr v-for="rdata in paymentData">
                                        <td class="align-center">{{ rdata.display_name }}</td>
                                       <td class="align-center">{{ rdata.gross_wages | custom_currency }}</td>
                                        <td class="align-center">{{ rdata.allowance_amount | custom_currency }}</td>
                                        <td class="align-center">{{ rdata.deduction_amount | custom_currency }}</td>
                                        <td class="align-center">{{ rdata.tax_amount | custom_currency }}</td>
                                        <td class="align-center">{{ rdata.net_pay | custom_currency }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="align-center"><?php _e( 'Total :', 'erp-inventory' ); ?></td>
                                       <td class="align-center"></td>
                                        <td class="align-center"></td>
                                        <td class="align-center">{{ grossTotal | custom_currency }}</td>
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
</div>
