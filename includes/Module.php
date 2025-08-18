<?php
namespace WeDevs\ERP_PRO;

use WeDevs\ERP\ErpErrors;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Module {

    use \WeDevs\ERP_PRO\Traits\Singleton;

    /**
     * Contains chainable class instances
     *
     * @var array
     */
    protected $container = [];

    /**
     * Is to hold update instant
     */
    protected $update;

    /**
     * The wp option key which contains active module ids
     *
     * @since 0.0.1
     *
     * @var string
     */
    const ACTIVE_MODULES_DB_KEY = 'erp_pro_active_modules';

    /**
     * Active module ids
     *
     * @since 0.0.1
     *
     * @var array
     */
    private $active_modules = [];

    /**
     * Contains all module information
     *
     * @since 0.0.1
     *
     * @var array
     */
    private $erp_pro_modules = [];

    /**
     * Tells us if modules activated or not
     *
     * @since 0.0.1
     *
     * @var bool
     */
    private static $modules_activated = false;

    /**
     * Magic getter to get chainable container instance
     *
     * @since 0.0.1
     *
     * @param string $prop
     *
     * @return mixed
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }
    }

    /**
     * Module constructor.
     *
     * @since 0.0.1
     * @since 1.1.0 Added erp_crm_integration_menu hook
     * @since 1.1.2 Added erp_pro_modules_details hook
     *
     * @return void
     */
    private function __construct() {
        $this->update = isset( wp_erp_pro()->update )
            ? wp_erp_pro()->update
            : \WeDevs\ERP_PRO\Admin\Update::init();

        add_action( 'erp_crm_integration_menu', [ $this, 'integration_menu' ] );
        add_filter( 'erp_pro_modules_details', [ $this, 'get_all_modules' ] );
    }

    /**
     * Update db option containing active module ids
     *
     * @param array $value
     *
     * @return bool
     * @since 0.0.1
     */
    protected function update_db_option( $value ) {
        return update_option( self::ACTIVE_MODULES_DB_KEY, $value );
    }

    /**
     * Load active modules
     *
     * @param array $newly_activated_modules Useful after module activation
     *
     * @return void
     * @since 0.0.1
     */
    public function load_active_modules( $newly_activated_modules = [] ) {
        if ( self::$modules_activated ) {
            return;
        }

        $active_modules = $this->get_active_modules();
        $erp_pro_modules = $this->get_all_modules();

        if ( ! $this->update->is_valid_license() ) {
            return;
        }

        if ( $this->update->get_licensed_user() < $this->update->count_users() ) {
            return;
        }

        $licensed_modules = $this->update->get_licensed_extensions();

        foreach ( $active_modules as $module_id ) {
            $module = isset( $erp_pro_modules[ $module_id ] ) ? $erp_pro_modules[ $module_id ] : '';

            if ( empty( $module ) ) {
                continue;
            }

            if ( file_exists( $module['module_file'] ) ) {
                // check if licensed already purchased
                if ( false === $module['is_pro'] && ! in_array( $module['path'], $licensed_modules ) ) {
                    continue;
                }

                require_once $module['module_file'];

                $module_class = $module['module_class'];
                $this->container[ $module_id ] = $module_class::init();

                if ( in_array( $module_id, $newly_activated_modules ) ) {
                    /**
                     * Module activation hook
                     *
                     * @param object $module Module class instance
                     * @since 0.0.1
                     */
                    do_action( 'erp_pro_activated_module_' . $module_id, $this->container[ $module_id ] );
                }
            }
        }

        self::$modules_activated = true;
    }

    /**
     * List of ERP Pro modules
     *
     * @return array
     * @since 0.0.1
     */
    public function get_all_modules( $modules = [] ) {
        if ( ! $this->erp_pro_modules ) {
            $thumbnail_dir  = WPERP_ASSETS . '/images/modules';
            $file_type      = version_compare( WPERP_VERSION, '1.8.4', '<=' ) ? '.svg' : '.png';

            $this->erp_pro_modules = apply_filters( 'wp_erp_pro_modules', [
                'inventory' => [
                    'id'            => 'inventory',
                    'version'       => '1.4.1',
                    'path'          => 'accounting/inventory',
                    'old_path'      => 'erp-inventory/wp-erp-inventory.php',
                    'name'          => __( 'Inventory', 'erp-pro' ),
                    'description'   => __( 'Manage and display your products purchase, order and stock.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/inventory{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/accounting/inventory/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\Accounting\Inventory\Module',
                    'is_pro'        => false,
                    'is_hrm'        => false,
                    'is_crm'        => false,
                    'is_acc'        => true,
                    'category'      => [ 'accounting' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/accounting-add-ons/inventory/',
                    'module_link'   => 'https://wperp.com/downloads/inventory/',
                ],
                'payment_gateway' => [
                    'id'            => 'payment_gateway',
                    'version'       => '1.2.1',
                    'path'          => 'accounting/payment-gateway',
                    'old_path'      => 'erp-payment-gateway/erp-payment-gateway.php',
                    'name'          => __( 'Payment Gateway', 'erp-pro' ),
                    'description'   => __( 'Manage all payment gateways for ERP Accounting module.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/payment-gateway{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/accounting/payment-gateway/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\Accounting\PaymentGateway\Module',
                    'is_pro'        => false,
                    'is_hrm'        => false,
                    'is_crm'        => false,
                    'is_acc'        => true,
                    'category'      => [ 'accounting' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/accounting-add-ons/payment-gateway/',
                    'module_link'   => 'https://wperp.com/downloads/payment-gateway/',
                ],
                'woocommerce' => [
                    'id'            => 'woocommerce',
                    'version'       => '1.5.1',
                    'path'          => 'accounting/woocommerce',
                    'old_path'      => 'erp-woocommerce/erp-woocommerce.php',
                    'name'          => __( 'WooCommerce', 'erp-pro' ),
                    'description'   => __( 'WooCommerce integration with CRM and Accounting modules in ERP.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/woocommerce{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/accounting/woocommerce/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\Accounting\woocommerce\Module',
                    'is_pro'        => false,
                    'is_hrm'        => false,
                    'is_crm'        => false,
                    'is_acc'        => true,
                    'category'      => [ 'crm', 'accounting' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/accounting-add-ons/woocommerce-integration/',
                    'module_link'   => 'https://wperp.com/downloads/woocommerce-crm/',
                ],
                'deals' => [
                    'id'            => 'deals',
                    'version'       => '1.2.1',
                    'path'          => 'crm/deals',
                    'old_path'      => 'erp-deals/wp-erp-deals.php',
                    'name'          => __( 'Deals', 'erp-pro' ),
                    'description'   => __( 'Deal Management add-on for WP ERP - CRM Module.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/deals{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/crm/deals/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\CRM\Deals\Module',
                    'is_pro'        => false,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/erp-deals/',
                    'module_link'   => 'https://wperp.com/downloads/deals/',
                ],
                'asset_management' => [
                    'id'            => 'asset_management',
                    'version'       => '1.2.1',
                    'path'          => 'hrm/asset-management',
                    'old_path'      => 'erp-asset-management/erp-asset-management.php',
                    'name'          => __( 'Asset Manager', 'erp-pro' ),
                    'description'   => __( 'Manage assets, allocate to employees and keep track.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/asset-management{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/hrm/asset-management/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\AssetManagement\Module',
                    'is_pro'        => true,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hrm-add-ons/asset-manager/',
                    'module_link'   => 'https://wperp.com/downloads/asset-manager/',
                ],
                'attendance' => [
                    'id'            => 'attendance',
                    'version'       => '2.2.1',
                    'path'          => 'hrm/attendance',
                    'old_path'      => 'erp-attendance/erp-attendance.php',
                    'name'          => __( 'Attendance', 'erp-pro' ),
                    'description'   => __( 'Employee Attendance Add-On for WP ERP.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/attendance{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/hrm/attendance/Module.php',
                    'module_class'  => 'WeDevs\ERP_PRO\HRM\Attendance\Module',
                    'is_pro'        => true,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hrm-add-ons/attendance-management/',
                    'module_link'   => 'https://wperp.com/downloads/attendance/',
                ],
                'custom_field_builder' => [
                    'id'            => 'custom_field_builder',
                    'version'       => '1.5.1',
                    'path'          => 'hrm/custom-field-builder',
                    'old_path'      => 'erp-field-builder/erp-field-builder.php',
                    'name'          => __( 'Custom Field Builder', 'erp-pro' ),
                    'description'   => __( 'Adds extra custom fields to employee, contacts, companies and other people types.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/custom-field-builder{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/hrm/custom-field-builder/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\CustomFieldBuilder\Module',
                    'is_pro'        => true,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hrm-add-ons/custom-field-builder/',
                    'module_link'   => 'https://wperp.com/downloads/custom-field-builder/',
                ],
                'document_manager' => [
                    'id'            => 'document_manager',
                    'version'       => '1.4.1',
                    'path'          => 'hrm/document-manager',
                    'old_path'      => 'erp-document/erp-document.php',
                    'name'          => __( 'Document Manager', 'erp-pro' ),
                    'description'   => __( 'Manage your employee and company documents.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/document-manager{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/hrm/document-manager/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\DocumentManager\Module',
                    'is_pro'        => true,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hrm-add-ons/document-manager/',
                    'module_link'   => 'https://wperp.com/downloads/document-manager/',
                ],
                'hr_training' => [
                    'id'            => 'hr_training',
                    'version'       => '1.2.1',
                    'path'          => 'hrm/hr-training',
                    'old_path'      => 'erp-hr-training/erp-hr-training.php',
                    'name'          => __( 'HR Training', 'erp-pro' ),
                    'description'   => __( 'Employee Training Add-On for WP-ERP.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/hr-training{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/hrm/hr-training/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\HrTraining\Module',
                    'is_pro'        => true,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hrm-add-ons/training/',
                    'module_link'   => 'https://wperp.com/downloads/training/',
                ],
                'payroll' => [
                    'id'            => 'payroll',
                    'version'       => '2.1.1',
                    'path'          => 'hrm/payroll',
                    'old_path'      => 'erp-payroll/erp-payroll.php',
                    'name'          => __( 'Payroll', 'erp-pro' ),
                    'description'   => __( 'Manage your employee payroll.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/payroll{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/hrm/payroll/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\Payroll\Module',
                    'is_pro'        => true,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hrm-add-ons/payroll/',
                    'module_link'   => 'https://wperp.com/downloads/payroll/',
                ],
                'recruitment' => [
                    'id'            => 'recruitment',
                    'version'       => '1.6.1',
                    'path'          => 'hrm/recruitment',
                    'old_path'      => 'erp-recruitment/wp-erp-recruitment.php',
                    'name'          => __( 'Recruitment', 'erp-pro' ),
                    'description'   => __( 'Recruitment solution for WP-ERP. Create job posting and hire employee for your company.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/recruitment{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/hrm/recruitment/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\Recruitment\Module',
                    'is_pro'        => true,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hrm-add-ons/recruitment/',
                    'module_link'   => 'https://wperp.com/downloads/recruitment/',
                ],
                'reimbursement' => [
                    'id'            => 'reimbursement',
                    'version'       => '1.3.1',
                    'path'          => 'hrm/reimbursement',
                    'old_path'      => 'erp-reimbursement/reimbursement.php',
                    'name'          => __( 'Reimbursement', 'erp-pro' ),
                    'description'   => __( 'Reimbursement addon for WP ERP - Accounting module.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/reimbursement{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/hrm/reimbursement/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\Reimbursement\Module',
                    'is_pro'        => true,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => true,
                    'category'      => [ 'hrm', 'accounting' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/accounting-add-ons/reimbursement/',
                    'module_link'   => 'https://wperp.com/downloads/reimbursement/',
                ],
                'sms_notification' => [
                    'id'            => 'sms_notification',
                    'version'       => '1.2.1',
                    'path'          => 'hrm/sms-notification',
                    'old_path'      => 'erp-sms-notification/erp-sms-notification.php',
                    'name'          => __( 'SMS Notification', 'erp-pro' ),
                    'description'   => __( 'Send SMS notifications to employees and CRM contacts.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/sms-notification{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/hrm/sms-notification/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\SmsNotification\Module',
                    'is_pro'        => true,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/sms-notification/',
                    'module_link'   => 'https://wperp.com/downloads/sms-notification/',
                ],
                'workflow' => [
                    'id'            => 'workflow',
                    'version'       => '1.3.1',
                    'path'          => 'hrm/workflow',
                    'old_path'      => 'erp-workflow/erp-workflow.php',
                    'name'          => __( 'Workflow', 'erp-pro' ),
                    'description'   => __( 'Workflow Automation System.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/workflow{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/hrm/workflow/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\HRM\Workflow\Module',
                    'is_pro'        => true,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/accounting-add-ons/workflow/',
                    'module_link'   => 'https://wperp.com/downloads/workflow/',
                ],
                'advanced_leave' => [
                    'id'            => 'advanced_leave',
                    'version'       => '1.1.1',
                    'path'          => 'pro/advanced-leave',
                    'old_path'      => '',
                    'name'          => __( 'Advanced Leave Management', 'erp-pro' ),
                    'description'   => __( 'Advanced Leave Management for WP ERP.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/advance-leave-management{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/pro/advanced-leave/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\AdvancedLeave\Module',
                    'is_pro'        => true,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hr/advanced-leave-management/',
                    'module_link'   => 'https://wperp.com/downloads/advanced-leave-management/',
                ],
                'awesome_support' => [
                    'id'            => 'awesome_support',
                    'version'       => '1.1.1',
                    'path'          => 'pro/awesome-support',
                    'old_path'      => 'erp-awesome-support/erp-awesome-support.php',
                    'name'          => __( 'Awesome Support', 'erp-pro' ),
                    'description'   => __( 'WP ERP and Awesome Support integration.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/awesome-support{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/pro/awesome-support/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\AwesomeSupport\Module',
                    'is_pro'        => true,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/awesome-support-sync/',
                    'module_link'   => 'https://wperp.com/downloads/awesome-support-sync/',
                ],
                'gravity_forms' => [
                    'id'            => 'gravity_forms',
                    'version'       => '1.2.1',
                    'path'          => 'pro/gravity_forms',
                    'old_path'      => 'erp-gravityforms/erp-gravityforms.php',
                    'name'          => __( 'Gravity Forms Integration', 'erp-pro' ),
                    'description'   => __( 'Gravity Forms integration for WP ERP.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/gravity-forms{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/pro/gravity_forms/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\GravityForms\Module',
                    'is_pro'        => true,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/gravity-forms/',
                    'module_link'   => 'https://wperp.com/downloads/crm-gravity-forms/',
                ],
                'help_scout' => [
                    'id'            => 'help_scout',
                    'version'       => '1.2.1',
                    'path'          => 'pro/help-scout',
                    'old_path'      => 'erp-helpscout/erp-helpscout.php',
                    'name'          => __( 'HelpScout Integration', 'erp-pro' ),
                    'description'   => __( 'HelpScout integration for WP ERP.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/help-scout{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/pro/help-scout/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\HelpScout\Module',
                    'is_pro'        => true,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/help-scout/',
                    'module_link'   => 'https://wperp.com/downloads/help-scout-integration/',
                ],
                'hr_frontend' => [
                    'id'            => 'hr_frontend',
                    'version'       => '2.2.1',
                    'path'          => 'pro/hr-frontend',
                    'old_path'      => 'erp-hr-frontend/erp-hr-frontend.php',
                    'name'          => __( 'HR Frontend', 'erp-pro' ),
                    'description'   => __( 'Provides a brand new dashboard experience for WordPress ERP.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/hr-frontend{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/pro/hr-frontend/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\HrFrontend\Module',
                    'is_pro'        => true,
                    'is_hrm'        => true,
                    'is_crm'        => false,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'hrm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/hrm-add-ons/hr-frontend/',
                    'module_link'   => 'https://wperp.com/downloads/hr-frontend/',
                ],
                'hubspot' => [
                    'id'            => 'hubspot',
                    'version'       => '1.2.1',
                    'path'          => 'pro/hubspot',
                    'old_path'      => 'erp-hubspot/erp-hubspot.php',
                    'name'          => __( 'HubSpot Contacts Sync', 'erp-pro' ),
                    'description'   => __( 'Sync your CRM contacts with HubSpot.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/hubspot{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/pro/hubspot/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\Hubspot\Module',
                    'is_pro'        => true,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/hubspot-contacts-sync/',
                    'module_link'   => 'https://wperp.com/downloads/hubspot-contacts-sync/',
                ],
                'mailchimp' => [
                    'id'            => 'mailchimp',
                    'version'       => '1.3.1',
                    'path'          => 'pro/mailchimp',
                    'old_path'      => 'erp-mailchimp/erp-mailchimp.php',
                    'name'          => __( 'Mailchimp Contacts Sync', 'erp-pro' ),
                    'description'   => __( 'Sync your CRM contacts with mailchimp.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/mailchimp-contacts-sync{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/pro/mailchimp/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\Mailchimp\Module',
                    'is_pro'        => true,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/mailchimp-contacts-sync/',
                    'module_link'   => 'https://wperp.com/downloads/mailchimp-contacts-sync/',
                ],
                'salesforce' => [
                    'id'            => 'salesforce',
                    'version'       => '1.2.1',
                    'path'          => 'pro/salesforce',
                    'old_path'      => 'erp-salesforce/erp-salesforce.php',
                    'name'          => __( 'Salesforce Contacts Sync', 'erp-pro' ),
                    'description'   => __( 'Sync your CRM contacts with salesforce.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/salesforce-contacts-sync{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/pro/salesforce/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\Salesforce\Module',
                    'is_pro'        => true,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/salesforce-contacts-sync/',
                    'module_link'   => 'https://wperp.com/downloads/salesforce-contact-sync/',
                ],
                'zendesk' => [
                    'id'            => 'zendesk',
                    'version'       => '1.2.1',
                    'path'          => 'pro/zendesk',
                    'old_path'      => 'erp-zendesk/erp-zendesk.php',
                    'name'          => __( 'Zendesk', 'erp-pro' ),
                    'description'   => __( 'Zendesk integration for WP ERP.', 'erp-pro' ),
                    'thumbnail'     => $thumbnail_dir . "/zendesk{$file_type}",
                    'module_file'   => ERP_PRO_MODULE_DIR . '/pro/zendesk/Module.php',
                    'module_class'  => '\WeDevs\ERP_PRO\PRO\Zendesk\Module',
                    'is_pro'        => true,
                    'is_hrm'        => false,
                    'is_crm'        => true,
                    'is_acc'        => false,
                    'category'      => [ 'pro', 'crm' ],
                    'doc_id'        => 0,
                    'doc_link'      => 'https://wperp.com/docs/crm-add-ons/zendesk-integration/',
                    'module_link'   => 'https://wperp.com/downloads/zendesk-integration/',
                ],
            ]);
        }

        return $this->erp_pro_modules;
    }

    /**
     * Set ERP Pro modules
     *
     * @param array $modules
     *
     * @return void
     * @since 0.0.1
     */
    public function set_modules( $modules ) {
        $this->erp_pro_modules = $modules;
    }

    /**
     * Get a list of module ids
     *
     * @return array
     * @since 0.0.1
     */
    public function get_all_module_ids() {
        static $module_ids = [];

        if ( ! $module_ids ) {
            $modules = $this->get_all_modules();
            $module_ids = array_keys( $modules );
        }

        return $module_ids;
    }

    /**
     * Get a list of all available module in module_path=>name pair
     *
     * @return array
     * @since 0.0.1
     */
    public function get_modules_path() {
        $modules = [];
        foreach ( $this->get_all_modules() as $module ) {
            $modules[ $module['path'] ] = $module['name'];
        }
        return $modules;
    }

    /**
     * Get a list of all available module in module_path=>name pair
     *
     * @return array
     * @since 0.0.1
     */
    public function test_get_modules_path() {
        $modules = [];
        foreach ( $this->get_all_modules() as $module ) {
            $modules[ $module['path'] ] = [
                'name' => $module['name'],
                'version' => $module['version'],
            ];
        }
        return $modules;
    }

    /**
     * Get a list of all module version as module_id=>module_version pair
     *
     * @return array
     * @since 0.0.1
     */
    public function get_paid_modules_version() {
        $module_version = [];
        foreach ( $this->get_all_modules() as $module ) {
            if ( $module['is_pro'] === true ) {
                continue;
            }
            $module_version[ $module['path'] ] = $module['version'];
        }

        ksort( $module_version );

        return $module_version;
    }

    public function get_paid_modules_info() {
        $module_info = [];
        foreach ( $this->get_all_modules() as $module ) {
            if ( $module['is_pro'] === true ) {
                continue;
            }
            $module_info[ $module['path'] ] = $module;
        }

        ksort( $module_info );

        return $module_info;
    }

    /**
     * Get a list of all module version as module_id=>module_version pair
     *
     * @return array
     * @since 0.0.1
     */
    public function get_purchased_paid_modules_version() {
        $module_version = [];
        foreach ( $this->get_available_modules( true ) as $module ) {
            if ( $module['is_pro'] === true ) {
                continue;
            }
            $module_version[ $module['path'] ] = $module['version'];
        }

        ksort( $module_version );

        return $module_version;
    }

    /**
     * This method will return a module information.
     *
     * @since 1.0.0
     * @param $module_id string
     * @return bool|object false if $module_id doesn't match any available module id
     */
    public function get_module_info( $module_id ) {
        $modules = $this->get_all_modules();
        if ( array_key_exists( $module_id, $modules ) ) {
            return (object) $modules[ $module_id ];
        }
        return false;
    }

    /**
     * Get ERP Pro active modules
     *
     * @return array
     * @since 0.0.1
     */
    public function get_active_modules() {
        if ( $this->active_modules ) {
            return $this->active_modules;
        }

        $this->active_modules = get_option( self::ACTIVE_MODULES_DB_KEY, [] );

        if ( empty( $this->active_modules ) ) {
            return [];
        }

        return $this->active_modules;
    }

    /**
     * Get a list of available modules
     *
     * @return array
     * @since 0.0.1
     */
    public function get_available_modules( $all = false ) {
        $modules = $this->get_all_modules();
        $available_modules = [];

        foreach ( $modules as $module_id => $module ) {
            if ( file_exists( $module['module_file'] ) ) {
                $available_modules[] = $all ? $module : $module['id'];
            }
        }

        return $available_modules;
    }

    /**
     * Activate ERP Pro modules
     *
     * @param array $modules
     *
     * @return array
     * @since 0.0.1
     */
    public function activate_modules( $modules ) {
        // return if erp is not installed
        if ( ! class_exists( 'WeDevs_ERP' ) ) {
            return $this->active_modules;
        }

        $active_modules = $this->get_active_modules();
        $modules_to_active = [];
        $error_obj = new ErpErrors( 'erp_pro_extension_error' );

        // check for valid license
        if ( ! $this->update->is_valid_license() ) {
            // set error message.
            $error_notice = sprintf( __( '<p>Please <a href="%s">enter</a> your license key to activate your purchase.</p>', 'erp-pro' ), admin_url( 'admin.php?page=erp-license' ) );
            $error = new \WP_Error( 'invalid_license', $error_notice );
            $error_obj->add( $error );
            $error_obj->save();

            return $this->active_modules;
        }

        $licensed_modules = $this->update->get_licensed_extensions();

        foreach ( $modules as $module_id ) {
            // check module already active
            if ( in_array( $module_id, $active_modules ) ) {
                continue;
            }

            // get module info.
            $module_info = $this->get_module_info( $module_id );

            // check $module_id is valid
            if ( empty( $module_info ) ) {
                $error = new \WP_Error( 'invalid_module_id', __( 'No module found with given module id: ', 'erp-pro' ) . $module_id );
                $error_obj->add( $error );
                continue;
            }

            // check this modules belongs to purchased extensions
            if ( false === $module_info->is_pro && ! in_array( $module_info->path, $licensed_modules ) ) {
                $msg = sprintf( __( '<strong>%s</strong> does not belongs to current purchased package.', 'erp-pro' ), $module_info->name );
                $error = new \WP_Error( 'invalid_module_id', $msg );
                $error_obj->add( $error );
                continue;
            }

            // check this modules belongs to purchased extensions and file exists on local
            if ( false === $module_info->is_pro && in_array( $module_info->path, $licensed_modules ) && ! file_exists( $module_info->module_file ) ) {
                $msg = sprintf( __( 'Please update <strong>WP ERP PRO</strong> plugin to latest version. <strong>%s</strong> is not included in current purchased package.', 'erp-pro' ), $module_info->name );
                $error = new \WP_Error( 'invalid_module_id', $msg );
                $error_obj->add( $error );
                continue;
            }

            // check this modules belongs to which parent module
            $core_module_inactive = false;
            if ( $module_info->is_hrm && ! wperp()->modules->is_module_active( 'hrm' ) ) {
                $msg = sprintf( '%s <strong>%s</strong> %s.',
                    __( 'You need to activate <strong>HR Management</strong> module first in order to activate ', 'erp-pro' ),
                    $module_info->name,
                    __( 'Module', 'erp-pro' )
                );

                $error = new \WP_Error( 'hrm_inactive_module_' . $module_id, $msg );
                $error_obj->add( $error );

                $core_module_inactive = true;
            }

            if ( $module_info->is_crm && ! wperp()->modules->is_module_active( 'crm' ) ) {
                $msg = sprintf( '%s <strong>%s</strong> %s.',
                    __( 'You need to activate <strong>CR Management</strong> module first in order to activate ', 'erp-pro' ),
                    $module_info->name,
                    __( 'Module', 'erp-pro' )
                );

                $error = new \WP_Error( 'crm_inactive_module_' . $module_id, $msg );
                $error_obj->add( $error );

                $core_module_inactive = true;
            }

            if ( $module_info->is_acc && ! wperp()->modules->is_module_active( 'accounting' ) ) {
                $msg = sprintf( '%s <strong>%s</strong> %s.',
                    __( 'You need to activate <strong>Accounting</strong> module first in order to activate ', 'erp-pro' ),
                    $module_info->name,
                    __( 'Module', 'erp-pro' )
                );

                $error = new \WP_Error( 'acc_inactive_module_' . $module_id, $msg );
                $error_obj->add( $error );

                $core_module_inactive = true;
            }

            if ( $core_module_inactive ) {
                continue;
            }

            // check older version of $module_id already installed on current installation
            if ( is_plugin_active( $module_info->old_path ) ) {
                deactivate_plugins( $module_info->old_path );
            }

            // now $module_id is safe to activate
            $modules_to_active[] = $module_id;
        }

        if ( $error_obj->has_error() ) {
            $error_obj->save();
        }

        if ( empty( $modules_to_active ) ) {
            return $this->active_modules;
        }

        // starting module activation process

        $this->active_modules = array_unique( array_merge( $active_modules, $modules_to_active ) );

        sort( $this->active_modules );

        $this->update_db_option( $this->active_modules );

        self::$modules_activated = false;

        $this->load_active_modules( $modules_to_active );

        return $this->active_modules;
    }

    /**
     * Deactivate ERP Pro modules
     *
     * @param array $modules
     *
     * @return array
     * @since 0.0.1
     */
    public function deactivate_modules( $modules ) {
        $active_modules = $this->get_active_modules();

        foreach ( $modules as $module_id ) {
            $active_modules = array_diff( $active_modules, [ $module_id ] );
        }

        $active_modules = array_values( $active_modules );

        $this->active_modules = $active_modules;

        $this->update_db_option( $this->active_modules );

        add_action( 'shutdown', function () use ( $modules ) {
            foreach ( $modules as $module_id ) {
                /**
                 * Module deactivation hook
                 *
                 * @param object $module deactivated module class instance
                 * @since 0.0.1
                 */
                do_action( 'erp_pro_deactivated_module_' . $module_id, wp_erp_pro()->module->$module_id );
            }
        });

        return $this->active_modules;
    }

    /**
     * Checks if a module is active or not
     *
     * @param string $module_id
     *
     * @return bool
     * @since 0.0.1
     */
    public function is_active( $module_id ) {
        $active_modules = $this->get_active_modules();

        if ( in_array( $module_id, $active_modules ) ) {
            return true;
        }

        return false;
    }

    /**
     * Integration menu handler
     *
     * @since 1.1.0
     *
     * @param string $selected
     *
     * @return void
     */
    public function integration_menu( $selected ) {
        $dropdown = [
            'hubspot'         => __( 'Hubspot', 'erp-pro' ),
            'mailchimp'       => __( 'Mailchimp', 'erp-pro' ),
            'salesforce'      => __( 'Salesforce', 'erp-pro' ),
            'help_scout'      => __( 'Helpscout', 'erp-pro' ),
            'awesome_support' => __( 'Awesome Support', 'erp-pro' ),
        ];

        $dropdown = apply_filters( 'erp_crm_integration_menu_items', $dropdown );

        ob_start();
        ?>

        <div class="erp-custom-menu-container">
            <ul class="erp-nav">
                <?php foreach ( $dropdown as $key => $value ) : ?>
                    <?php if ( $this->is_active( $key ) ) : ?>
                        <?php if ( 'awesome_support' !== $key ) : ?>
                        <li class="<?php echo $key === $selected ? 'active' : ''; ?>"><a href="<?php echo add_query_arg( array( 'sub-section' => $key ), admin_url( 'admin.php?page=erp-crm&section=integration' ) ); ?>" class="" data-key="<?php echo $key; ?>"><?php echo $value; ?></a></li>
                        <?php else : ?>
                        <li><a href="<?php echo admin_url( 'admin.php?page=erp-settings#/erp-integration' ); ?>" class="" data-key="<?php echo $key; ?>"><?php echo $value; ?></a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>

        <?php
        echo ob_get_clean();
    }
}
