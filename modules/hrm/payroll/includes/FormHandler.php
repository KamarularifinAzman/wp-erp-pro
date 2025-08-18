<?php
namespace WeDevs\Payroll;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Handle the form submissions
 *
 * Although our most of the forms uses ajax and popup, some
 * are needed to submit via regular form submits. This class
 * Handles those form submission in this module
 *
 * @package WP ERP
 * @subpackage HRM
 */
class FormHandler {

    use Hooker;

    /**
     * Hook 'em all
     */
    public function __construct() {

        if ( version_compare( WPERP_VERSION, "1.4.0", '<' ) ) {
            $this->action( 'load-payroll_page_erp-hr-payroll-reports', 'export_employee_report_csv' );
            $this->action( 'load-payroll_page_erp-hr-payroll-reports', 'export_summary_report_csv' );

            $this->action( 'load-payroll_page_erp-hr-payroll-reports', 'export_details_report_csv' );
            $this->action( 'load-payroll_page_erp-hr-payroll-reports', 'export_bank_report_csv' );

            $this->action( 'load-payroll_page_erp-hr-payroll-pay-calendar', 'payrun_filter' );
        }
        $this->action( 'admin_init', 'handle_form_submissions' );
    }

    public function handle_form_submissions() {

        $is_hr_page = ( !empty( $_GET['page'] ) && ( 'erp-hr' == $_GET['page'] ) ) ? true : false;
        $is_payroll_page = ( !empty( $_GET['section'] ) && ( 'payroll' == $_GET['section'] ) ) ? true : false;
        $is_calendar = ( !empty( $_GET['sub-section'] ) && ( 'calendar' == $_GET['sub-section'] ) ) ? true : false;

        if ( $is_hr_page && $is_payroll_page && $is_calendar ) {
            $this->payrun_filter();
            return;
        }

        if ( empty( $_GET['func'] ) || empty( $_GET['section'] ) ) {
            return;
        }

        switch ( $_GET['func'] ) {
            case 'payroll-employee-report-csv' :
                $this->export_employee_report_csv();
                break;
            case 'payroll-summary-report-csv' :
                $this->export_summary_report_csv();
                break;
            case 'payroll-details-report-csv' :
                $this->export_details_report_csv();
                break;
            case 'payroll-bank-report-csv' :
                $this->export_bank_report_csv();
                break;

        }
    }

    /**
     * Handle payment method
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function handle_payment_method() {
        $nonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
        $pm    = isset( $_POST['payment_method'] ) ? $_POST['payment_method'] : '';
        if ( !wp_verify_nonce( $nonce, 'erp-settings-nonce' ) ) {
            //die( __( 'Nonce error!', 'erp-pro' ) );
        } elseif ( isset($pm) ) {
            update_option( 'erp_payroll_payment_method_settings', $pm );
        }
    }

    /**
     * Pay run filter
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function payrun_filter() {
        $payrun_list_table = new PayrunListTable();
        $action            = $payrun_list_table->current_action();

        if ( $action == 'filter_payrun_status' ) {
            $redirect = remove_query_arg( array(
                '_wp_http_referer',
                '_wpnonce',
                's',
                'filter_status_button'
            ), wp_unslash( $_SERVER['REQUEST_URI'] ) );
            wp_redirect( $redirect );
            exit();
        }
    }

    /**
     * Export CSV Reports
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function export_employee_report_csv() {
        error_reporting(0);
        if ( isset($_REQUEST['func']) && $_REQUEST['func'] == 'payroll-employee-report-csv' ) {
            global $wpdb;
            $report_data = [];
            $from_date = isset( $_REQUEST['from_date'] ) ? $_REQUEST['from_date'] : '';
            $to_date   = isset( $_REQUEST['to_date'] ) ? $_REQUEST['to_date'] : '';

            $query       = "SELECT
                payrun.*,
                payrun_details.empid,
                wpuser.display_name,
                SUM(
                    CASE WHEN payrun_details.pay_item_amount > 0 THEN payrun_details.pay_item_amount ELSE 0
                END
            ) AS gross_wages,
            CONCAT(
                '[',
                GROUP_CONCAT(
                    JSON_OBJECT(
                        'id',
                        payitem.id,
                        'title',
                        payitem.payitem,
                        'allowance',
                        payrun_details.allowance,
                        'deduction',
                        payrun_details.deduction,
                        'pay_item_add_or_deduct',
                        payrun_details.pay_item_add_or_deduct
                    )
                ),
                ']'
            ) AS payItemBreakDowns
            FROM
                {$wpdb->prefix}erp_hr_payroll_payrun AS payrun
            LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payrun_detail AS payrun_details
            ON
                payrun_details.payrun_id = payrun.id
            LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payitem AS payitem
            ON
                payitem.id = payrun_details.pay_item_id
            LEFT JOIN {$wpdb->prefix}users AS wpuser
            ON
                wpuser.id = payrun_details.empid
            WHERE
                payrun.approve_status = 1 AND payrun_details.approve_status = 1
                AND payrun.payment_date BETWEEN '%s' AND '%s'
            GROUP BY
                payrun_details.empid";


            $reportData    = $wpdb->get_results( $wpdb->prepare( $query, $from_date, $to_date ), OBJECT );

            $finalPaymentDetails = [] ;
            foreach($reportData as $item){

                array_push($finalPaymentDetails,[ // add basic
                    'display_name' => $item->display_name,
                    'gross_wages' => $item->gross_wages,
                    'allowance_item_name' => '',
                    'allowance_amount' => '',
                    'deduction_item_name' => '',
                    'deduction_amount' => '',
                    'tax_item_name' => '',
                    'tax_amount' => '',
                    'net_pay' => $item->gross_wages,
                ]);

                $item->payItemBreakDowns  = json_decode($item->payItemBreakDowns);

                $allowances  = [] ;
                $deductions  = [] ;
                $taxes  = [] ;
                foreach($item->payItemBreakDowns as $payItem){

                    if($payItem->pay_item_add_or_deduct == 1){ // calculate allowance items
                        array_push($allowances, [
                            'display_name' =>'',
                            'gross_wages' => '',
                            'allowance_item_name' => $payItem->title,
                            'allowance_amount' => $payItem->allowance,
                            'deduction_item_name' => '',
                            'deduction_amount' => '',
                            'tax_item_name' => '',
                            'tax_amount' => '',
                            'net_pay' => $payItem->allowance,
                        ]);
                    }

                    if($payItem->pay_item_add_or_deduct == 0){ // calculate deduction items
                        array_push($deductions, [
                            'display_name' =>'',
                            'gross_wages' => '',
                            'allowance_item_name' => '',
                            'allowance_amount' => '',
                            'deduction_item_name' => $payItem->title,
                            'deduction_amount' => $payItem->deduction,
                            'tax_item_name' => '',
                            'tax_amount' => '',
                            'net_pay' => $payItem->deduction,
                        ]);
                    }

                    if($payItem->pay_item_add_or_deduct == 2){ // calculate tax items
                        array_push($taxes, [
                            'display_name' =>'',
                            'gross_wages' => '',
                            'allowance_item_name' => '',
                            'allowance_amount' => '',
                            'deduction_item_name' => '',
                            'deduction_amount' => '',
                            'tax_item_name' => $payItem->title,
                            'tax_amount' => $payItem->deduction,
                            'net_pay' => $payItem->deduction,
                        ]);
                    }

                }

                // all all allowance
                $totalAllowance = 0 ;
                foreach ($allowances as $allowance){
                    array_push($finalPaymentDetails, $allowance) ;
                    $totalAllowance += (float)$allowance['allowance_amount'];
                }

                // all all deduction
                $totalDeduction = 0 ;
                foreach ($deductions as $deduction){
                    array_push($finalPaymentDetails, $deduction) ;
                    $totalDeduction += (float)$deduction['deduction_amount'];
                }

                // all all tax
                $totalTax = 0 ;
                foreach ($taxes as $tax){
                    array_push($finalPaymentDetails, $tax) ;
                    $totalTax += (float)$tax['tax_amount'];
                }


                array_push($finalPaymentDetails,[ // add total payment to a employee
                    'display_name' => '',
                    'gross_wages' => '',
                    'allowance_item_name' => '',
                    'allowance_amount' => '',
                    'deduction_item_name' => '',
                    'deduction_amount' => '',
                    'tax_item_name' => 'Total Paid',
                    'tax_amount' => '',
                    'net_pay' => ((float)$item->gross_wages + $totalAllowance) - ( $totalDeduction + $totalTax),
                ]);

            }

            // create a file pointer connected to the output stream
            //BUILD CSV CONTENT
            $csv         = 'Employee, Gross Wages, Allowance Section, Allowance Amount, Deduction Section, Deduction Amount, Tax Section, Tax Amount, Net Pay' . "\n";
            $grand_total = 0;
            foreach ( $finalPaymentDetails as $ud ) {

                $grand_total += $ud['tax_item_name'] == 'Total Paid' ? $ud['net_pay'] : 0;
                $csv .= $ud['display_name'] . "," . $ud['gross_wages']. "," . $ud['allowance_item_name'] . "," . $ud['allowance_amount'] . "," . $ud['deduction_item_name'] . "," . $ud['deduction_amount'] . "," . $ud['tax_item_name'] . "," . $ud['tax_amount'] . "," . $ud['net_pay'] . "\n";
            }

            $csv .= "," . ","  . "," . "," . "," . "," . "," . "," . $grand_total;

            //NAME THE FILE
            $table = "payroll-employee-report";

            //OUPUT HEADERS
            header( "Pragma: public" );
            header( "Expires: 0" );
            header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
            header( "Cache-Control: private", false );
            header( "Content-Type: application/octet-stream" );
            header( "Content-Disposition: attachment; filename=\"$table.csv\";" );
            header( "Content-Transfer-Encoding: binary" );
            echo($csv);
            exit;
        }
    }

    /**
     * Export CSV Reports
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function export_summary_report_csv() {
        if ( isset($_REQUEST['func']) && $_REQUEST['func'] == 'payroll-summary-report-csv' ) {
            global $wpdb;
            $report_data = [];
            $from_date   = isset( $_REQUEST['from_date'] ) ? $_REQUEST['from_date'] : '';
            $to_date     = isset( $_REQUEST['to_date'] ) ? $_REQUEST['to_date'] : '';

            if ( $from_date != '' && $to_date != '' ) {
                $query       = "SELECT payrun.id as pid,
                                   payrun.payment_date,
                                   payrun_details.empid,
                                   wpuser.display_name,
                                   IFNULL(SUM(payrun_details.pay_item_amount),0) as gross_wages,
                                   IFNULL(SUM(payrun_details.allowance),0) as allowance_amount,
                                   SUM(CASE WHEN payrun_details.pay_item_add_or_deduct=0   THEN payrun_details.deduction ELSE 0 END) AS deduction_amount,
                                   SUM(CASE WHEN payrun_details.pay_item_add_or_deduct=2   THEN payrun_details.deduction ELSE 0 END) AS tax_amount
                            FROM {$wpdb->prefix}erp_hr_payroll_payrun as payrun
                            LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payrun_detail as payrun_details
                            ON payrun_details.payrun_id = payrun.id
                            LEFT JOIN {$wpdb->prefix}users as wpuser
                            ON wpuser.id=payrun_details.empid
                            WHERE payrun.approve_status=1
                            AND payrun.payment_date BETWEEN '%s' AND '%s'
                            AND payrun_details.approve_status=1
                            GROUP BY wpuser.display_name";
                $report_data = $wpdb->get_results( $wpdb->prepare( $query, $from_date, $to_date ), ARRAY_A );

                //BUILD CSV CONTENT
                $csv         = 'Employee, Basic, Allowances, Deductions, Taxes, Net Pay' . "\n";
                $grand_total = 0;
                foreach ( $report_data as $ud ) {
                    $net_pay     = ( (float)$ud['gross_wages'] + (float)$ud['allowance_amount']) - ((float)$ud['deduction_amount'] + (float)$ud['tax_amount']);
                    $grand_total +=   $net_pay  ;

                    $csv         .= $ud['display_name'] . "," . $ud['gross_wages'] . "," . $ud['allowance_amount'] . "," . $ud['deduction_amount'] . "," . $ud['tax_amount'] . "," . $net_pay . "\n";
                }

                $csv .= "," . ",". "," . "," . "," . $grand_total;

                //NAME THE FILE
                $table = "payroll-summary-report";

                //OUPUT HEADERS
                header( "Pragma: public" );
                header( "Expires: 0" );
                header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
                header( "Cache-Control: private", false );
                header( "Content-Type: application/octet-stream" );
                header( "Content-Disposition: attachment; filename=\"$table.csv\";" );
                header( "Content-Transfer-Encoding: binary" );
                echo($csv);
                exit;
            }
        }
    }

    /**
     * Add single payment
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function single_payroll_add_payment() {
        global $wpdb;

        if ( isset($_POST['_wpnonce']) && isset($_POST['empid']) && isset($_POST['payrunid']) ) {
            if ( !wp_verify_nonce( $_POST['_wpnonce'], 'payroll-single-payment-nonce' ) ) {
                wp_die( __( 'Cheating?', 'erp-pro' ) );
            }
            $amount_paid         = $_POST['amount_paid'];
            $paid_date           = $_POST['paid_date'];
            $paid_reference      = $_POST['paid_reference'];
            $payrunid            = $_POST['payrunid'];
            $accountid           = $_POST['paid_account_head'];
            $empid_as_customerid = $_POST['empid'];

            $payment_data = array(
                'type'       => 'expense',
                'form_type'  => 'payment_voucher',
                'status'     => 'paid',
                'account_id' => $accountid,
                'user_id'    => $empid_as_customerid,
                'ref'        => $paid_reference,
                'issue_date' => $paid_date,
                'total'      => $amount_paid
            );

            $items[] = apply_filters( 'erp_ac_transaction_lines', [
                'item_id'     => isset($postdata['items_id'][$key]) ? $postdata['items_id'][$key] : [],
                'journal_id'  => isset($postdata['journals_id'][$key]) ? $postdata['journals_id'][$key] : [],
                'account_id'  => (int)$accountid,
                'description' => 'line desc',
                'qty'         => 0,
                'unit_price'  => erp_ac_format_decimal( 0 ),
                'discount'    => erp_ac_format_decimal( 0 ),
                'tax'         => isset($postdata['line_tax'][$key]) ? $postdata['line_tax'][$key] : 0,
                'tax_rate'    => isset($postdata['tax_rate'][$key]) ? $postdata['tax_rate'][$key] : 0,
                'line_total'  => erp_ac_format_decimal( $amount_paid ),
                'tax_journal' => isset($postdata['tax_journal'][$key]) ? $postdata['tax_journal'][$key] : 0
            ], $key, $postdata );

            $payment_st = erp_ac_insert_transaction( $payment_data, $items );
            if ( $payment_st ) {
                //update status of this employee paid information
                $wpdb->update( $wpdb->prefix . 'erp_hr_payroll_employee', array(
                    'status' => 2
                ), array('emp_id' => $empid_as_customerid), array(
                    '%d'
                ), array('%d') );

                //payment done so redirect to approved list page
                $redirect = add_query_arg( [
                    'page'     => 'erp-hr-payroll-pay-run',
                    'view'     => 'approved_list',
                    'payrunid' => $payrunid
                ], admin_url( 'admin.php' ) );

                wp_redirect( $redirect );
                exit();
            }
        }
    }


    /**
     * Export Employee payrun details report as CSV
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function export_details_report_csv() {
        if ( isset($_REQUEST['func']) && $_REQUEST['func'] == 'payroll-details-report-csv' ) {
            global $wpdb;
            $from_date = isset($_REQUEST['from_date']) ? $_REQUEST['from_date'] : current_time('Y-m-d');
            $to_date = isset($_REQUEST['to_date']) ? $_REQUEST['to_date'] : current_time('Y-m-d');

            $get_payitems = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}erp_hr_payroll_payitem"); // get payrun items

            $allowanceItems = [] ; // allowance item name for head
            $deductionItems = [] ; // deduction item name for head
            $allowances = [] ;    // allowances for item value - default value will be 0
            $deductions = [] ;    // deductions for item value - default value will be 0

            foreach($get_payitems as $item){
                if($item->type === 'Allowance'){
                    $allowances[$item->id]  = 0 ;
                    $allowanceItems[$item->id]  = $item->payitem  ;
                }else{
                    $deductions[$item->id]  = 0 ;
                    $deductionItems[$item->id]  = $item->payitem ;
                }
            }


            $sql = "SELECT
                payrun.*,
                wpuser.display_name,
                designation.title
                FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail as payrun
                LEFT JOIN {$wpdb->prefix}erp_hr_employees as employe  ON employe.id = payrun.empid
                LEFT JOIN {$wpdb->prefix}erp_hr_designations as designation ON designation.id = employe.designation
                LEFT JOIN {$wpdb->prefix}users as wpuser ON wpuser.id = payrun.empid
                WHERE payrun.approve_status=1 AND (payrun.payment_date BETWEEN '{$from_date}' AND '{$to_date}')
                GROUP BY payrun.id";

            $employeePayrunDetails = $wpdb->get_results($sql);


             // format employee payrun details data
            $formattedData = [] ;
            $totalBasic = 0 ;
            $totalPaymentAmount = 0 ;
            $totalDeductAmount = 0 ;
            $totalAllowances =  $allowances ;
            $totalDeductions =  $deductions ;
            foreach($employeePayrunDetails as $payrun){
                if( ! isset( $formattedData[$payrun->empid] ) ) {
                    $totalBasic += (float)$payrun->pay_item_amount ;
                    $totalPaymentAmount += (float)$payrun->pay_item_amount ;
                    $formattedData[$payrun->empid] = [
                        "name"        => $payrun->display_name,
                        "designation" => $payrun->title,
                        "basic"       => $payrun->pay_item_amount,
                        "allowance"   => $allowances,
                        "deductions"   => $deductions
                    ];
                }else{

                    if($payrun->pay_item_add_or_deduct == 1){
                        $formattedData[$payrun->empid]['allowance'][$payrun->pay_item_id] = $payrun->allowance ; //  update allowance item value amount
                        $totalAllowances[$payrun->pay_item_id] += $payrun->allowance ;
                        $totalPaymentAmount += (float)$payrun->allowance ;
                    }else{
                        $formattedData[$payrun->empid]['deductions'][$payrun->pay_item_id] = $payrun->deduction ; //  update deduction item value amount
                        $totalDeductions[$payrun->pay_item_id] += $payrun->deduction ;
                        $totalDeductAmount += (float)$payrun->deduction ;
                    }

                }
            }


            // set header
           $csvData = "SL,Name of Employee,Designation,Basic Payment," .implode(',', $allowanceItems) .",Total Earning," .implode(',', $deductionItems) .",Total Deduction,Net Payable";
            $sl = 1;
            foreach( $formattedData as $datum ){
                $csvData .= "\n";
                $totalPayment = (float)$datum['basic'] + array_sum($datum['allowance']) ;
                $totalDeduction = array_sum($datum['deductions']);
                $csvData .= $sl .',' .$datum['name'] .',' . $datum['designation'] .',' . $datum['basic'] .','. implode(',', $datum['allowance']) .',' .$totalPayment .',' .implode(',', $datum['deductions']) .',' .$totalDeduction .',' .($totalPayment - $totalDeduction) ;
                $sl++;
            }

            $csvData .= "\n";
            //  total in footer
            $csvData .=  ' ,'  .' ,' .'Total,' . $totalBasic .','. implode(',', $totalAllowances) .',' .$totalPaymentAmount .',' .implode(',', $totalDeductions) .',' .$totalDeductAmount .',' .($totalPaymentAmount - $totalDeductAmount) ;

            //NAME THE FILE
            $table = "payroll-detail-report";

            //OUPUT HEADERS
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"$table.csv\";");
            header("Content-Transfer-Encoding: binary");
            echo($csvData);
            exit;

        }
    }

    /**
     * Export CSV Reports for bank
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function export_bank_report_csv() {
        if ( isset($_REQUEST['func']) && $_REQUEST['func'] == 'payroll-bank-report-csv' ) {

            global $wpdb;
            $report_data = [];
            $from_date = isset( $_REQUEST['from_date'] ) ? $_REQUEST['from_date'] : '';
            $to_date   = isset( $_REQUEST['to_date'] ) ? $_REQUEST['to_date'] : '';

            if ( $from_date != '' && $to_date != '' ) {
                $query       = "SELECT payrun.id as pid,
                                   payrun.payment_date,
                                   approved_employee.empid,
                                   wpuser.display_name
                            FROM {$wpdb->prefix}erp_hr_payroll_payrun as payrun
                            LEFT JOIN {$wpdb->prefix}erp_hr_payroll_payrun_detail as approved_employee
                            ON approved_employee.pay_cal_id=payrun.pay_cal_id
                            LEFT JOIN {$wpdb->prefix}users as wpuser
                            ON wpuser.id=approved_employee.empid
                            WHERE payrun.approve_status=1
                            AND payrun.payment_date BETWEEN '%s' AND '%s'
                            AND approved_employee.approve_status=1
                            GROUP BY wpuser.display_name";
                $udata       = $wpdb->get_results( $wpdb->prepare( $query, $from_date, $to_date ), ARRAY_A );
                $gross_total = 0;

                foreach ( $udata as $ud ) {
                    $gross_pay              = 0;
                    $total_allowance_amount = 0;
                    $total_deduction_amount = 0;
                    $total_tax_amount       = 0;
                    $payrunid               = $ud['pid'];
                    $payment_date           = $ud['payment_date'];
                    $empid                  = $ud['empid'];
                    $display_name           = $ud['display_name'];
                    //query for get the total times orinary rate he got
                    $query     = "SELECT SUM(aemp.pay_item_amount) as gp
                            FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail as aemp
                            WHERE aemp.empid='%d'
                            AND pay_item_id='-1'
                            AND payment_date BETWEEN '%s' AND '%s' AND aemp.approve_status=1";
                    $gross_pay = $wpdb->get_var( $wpdb->prepare( $query, $empid, $from_date, $to_date ) );

                    //now query for allowance name and amount with total times paid
                    $query                  = "SELECT IFNULL(SUM(allowance),0) as allowance_amount
                            FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail as aemp
                            WHERE aemp.empid='%d'
                            AND pay_item_id<>'-1'
                            AND pay_item_add_or_deduct=1
                            AND aemp.approve_status=1
                            AND payment_date BETWEEN '%s' AND '%s'";
                    $total_allowance_amount = $wpdb->get_var( $wpdb->prepare( $query, $empid, $from_date, $to_date ) );

                    //now query for deduction name and amount with total times deducted
                    $query                  = "SELECT IFNULL(SUM(deduction),0) as deduction_amount
                            FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail as aemp
                            WHERE aemp.empid='%d'
                            AND pay_item_add_or_deduct=0
                            AND aemp.approve_status=1
                            AND payment_date BETWEEN '%s' AND '%s'";
                    $total_deduction_amount = $wpdb->get_var( $wpdb->prepare( $query, $empid, $from_date, $to_date ) );

                    //now query for tax deduction name and amount with total times tax deduct
                    $query            = "SELECT IFNULL(SUM(deduction),0) as tax_amount
                            FROM {$wpdb->prefix}erp_hr_payroll_payrun_detail as aemp
                            WHERE aemp.empid='%d'
                            AND pay_item_add_or_deduct=2
                            AND aemp.approve_status=1
                            AND payment_date BETWEEN '%s' AND '%s'";
                    $total_tax_amount = $wpdb->get_var( $wpdb->prepare( $query, $empid, $from_date, $to_date ) );

                    //make report row into an array
                    if ( true ) {
                        $report_data[] = [
                            'display_name'     => $display_name,
                            'empid'            => $empid,
                            'pay_period'       => '',
                            'payment_date'     => '',
                            'gross_wages'      => $gross_pay,
                            'allowance_amount' => $total_allowance_amount,
                            'deduction_amount' => $total_deduction_amount,
                            'tax_amount'       => $total_tax_amount,
                            'net_pay'          => $gross_pay + $total_allowance_amount - $total_deduction_amount - $total_tax_amount
                        ];
                        $gross_total += $gross_pay + $total_allowance_amount - $total_deduction_amount - $total_tax_amount;
                    }
                }
            }

            // create a file pointer connected to the output stream
            //BUILD CSV CONTENT
            $csv         = 'SL, Employee, Designation, Account No, Amount' . "\n";
            $grand_total = 0;
            $sl = 1;
            foreach ( $report_data as $ud ) {
                $net_pay = $ud['gross_wages'] + $ud['allowance_amount'] - $ud['deduction_amount'] - $ud['tax_amount'];
                $grand_total += intval( $net_pay );

                $cur_empl = new \WeDevs\ERP\HRM\Employee( $ud['empid'] );

                $csv .= $sl . "," .
                        $ud['display_name'] . "," .
                        $cur_empl->get_designation('view') . "," .
                        get_user_meta( $ud['empid'], 'bank_acc_number' , true ) . "," .
                        $net_pay . "\n";
                $sl++;
            }

            $csv .= "," . "," . "," . "," . $grand_total;

            //NAME THE FILE
            $table = "payroll-bank-report";

            //OUPUT HEADERS
            header( "Pragma: public" );
            header( "Expires: 0" );
            header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
            header( "Cache-Control: private", false );
            header( "Content-Type: application/octet-stream" );
            header( "Content-Disposition: attachment; filename=\"$table.csv\";" );
            header( "Content-Transfer-Encoding: binary" );
            echo($csv);
            exit;
        }
    }

}
