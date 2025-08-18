<?php
namespace WeDevs\AssetManagement;

use \WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Log handler
 *
 * @since 1.1.3
 *
 * @package WP-ERP-ASSETS
 */
class Log {

    use Hooker;

    /**
     * Load autometically when class instantiate
     *
     * @since 1.1.3
     *
     * @return void
     */
    public function __construct() {
        $this->action( 'erp_hr_log_asset_new', 'create_asset' );
        $this->action( 'erp_hr_log_asset_update', 'update_asset', 10, 2 );
        $this->action( 'erp_hr_log_asset_del', 'delete_asset' );
        $this->action( 'erp_hr_log_asset_cat_new', 'create_asset_cat' );
        $this->action( 'erp_hr_log_asset_cat_edit', 'update_asset_cat', 10, 2 );
        $this->action( 'erp_hr_log_asset_cat_del', 'delete_asset_cat' );
        $this->action( 'erp_hr_log_asset_allot_new', 'create_asset_allot' );
        $this->action( 'erp_hr_log_asset_allot_edit', 'update_asset_allot', 10, 2 );
        $this->action( 'erp_hr_log_asset_allot_del', 'delete_asset_allot' );
        $this->action( 'erp_hr_log_asset_req_new', 'create_asset_req' );
        $this->action( 'erp_hr_log_asset_req_edit', 'update_asset_req', 10, 2 );
        $this->action( 'erp_hr_log_asset_req_del', 'delete_asset_req' );
    }

    /**
     * Add log when new asset created
     *
     * @since 1.1.3
     *
     * @param  array $data
     *
     * @return void
     */
    public function create_asset( $data ) {
        $log_data = [
            'component'     => 'HRM',
            'sub_component' => 'Asset',
            'changetype'    => 'add',
            'message'       => sprintf( __( 'An asset <strong>%1$s</strong> under group <strong>%2$s</strong> has been added', 'erp-pro' ), $data['item_code'], $data['item_group'] ),
            'created_by'    => get_current_user_id()
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Add log when an asset updated
     *
     * @since 1.1.3
     *
     * @param array $data
     * @param int $id
     *
     * @return void
     */
    public function update_asset( $old_data, $id ) {
        $new_data             = erp_hr_get_asset( $id );

        if ( isset( $new_data['category_id'] ) ) {
            $new_data['category'] = erp_hr_get_asset_category( $new_data['category_id'] );
            unset( $new_data['category_id'] );
        }

        if ( isset( $old_data['category_id'] ) ) {
            $old_data['category'] = erp_hr_get_asset_category( $old_data['category_id'] );
            unset( $old_data['category_id'] );
        }

        $array_diff = erp_get_array_diff( $new_data, $old_data );
        $log_data   = [
            'component'     => 'HRM',
            'sub_component' => 'Asset',
            'changetype'    => 'edit',
            'message'       => sprintf(
                __( 'An asset %1$sunder group %2$shas been updated', 'erp-pro' ),
                isset( $old_data['item_code'] ) ? "<strong>{$old_data['item_code']}</strong> " : '',
                isset( $old_data['item_group'] ) ? "<strong>{$old_data['item_group']}</strong> " : ''
            ),
            'created_by'    => get_current_user_id(),
            'old_value'     => $array_diff['old_value'],
            'new_value'     => $array_diff['new_value'],
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Add log when an asset is deleted
     *
     * @since 1.1.3
     *
     * @param array $data
     *
     * @return void
     */
    public function delete_asset( $data ) {
        $log_data = [
            'component'     => 'HRM',
            'sub_component' => 'Asset',
            'changetype'    => 'delete',
            'message'       => sprintf( __( 'An asset <strong>%1$s</strong> under group <strong>%2$s</strong> has been deleted', 'erp-pro' ), $data['item_code'], $data['item_group'] ),
            'created_by'    => get_current_user_id()
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Add log when new asset catagory
     *
     * @since 1.1.3
     *
     * @param  string $name
     *
     * @return void
     */
    public function create_asset_cat( $name ) {
        $log_data = [
            'component'     => 'HRM',
            'sub_component' => 'Asset Category',
            'changetype'    => 'add',
            'message'       => sprintf( __( 'A new asset category named <strong>%1$s</strong> has been added', 'erp-pro' ), $name ),
            'created_by'    => get_current_user_id()
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Add log when an asset category updated
     *
     * @since 1.1.3
     *
     * @param string $new_name
     * @param string $old_name
     *
     * @return void
     */
    public function update_asset_cat( $new_name, $old_name ) {
        $array_diff = erp_get_array_diff( [ 'cat_name' => $new_name ], [ 'cat_name' => $old_name ] );

        $log_data = [
            'component'     => 'HRM',
            'sub_component' => 'Asset Category',
            'changetype'    => 'edit',
            'message'       => __( 'An asset category has been updated', 'erp-pro' ),
            'old_value'     => $array_diff['old_value'],
            'new_value'     => $array_diff['new_value'],
            'created_by'    => get_current_user_id()
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Add log when an asset category deleted
     *
     * @since 1.1.3
     *
     * @param string $name
     *
     * @return void
     */
    public function delete_asset_cat( $name ) {
        $log_data = [
            'component'     => 'HRM',
            'sub_component' => 'Asset Category',
            'changetype'    => 'delete',
            'message'       => sprintf( __( 'An asset category named <strong>%1$s</strong> has been deleted', 'erp-pro' ), $name ),
            'created_by'    => get_current_user_id()
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Adds log when asset is alloted
     *
     * @since 1.1.3
     *
     * @param array $data
     *
     * @return void
     */
    function create_asset_allot( $data ) {
        $employee  = new \WeDevs\ERP\HRM\Employee( $data['allotted_to'] );
        $asset     = erp_hr_get_asset( $data['item_id'] );

        $log_data  = [
            'component'     => 'HRM',
            'sub_component' => 'Asset Allotment',
            'changetype'    => 'add',
            'message'       => sprintf(
                __( 'An asset %1$shas been allotted to <strong>%2$s</strong>', 'erp-pro' ),
                isset( $asset['item_code'] ) ? "<strong>{$asset['item_code']}</strong> " : '',
                $employee->get_full_name()
            ),
            'created_by'    => get_current_user_id()
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Adds log when asset allotment is updated
     *
     * @since 1.1.3
     *
     * @param array $data
     *
     * @return void
     */
    function update_asset_allot( $old_data, $new_data ) {
        $employee                = new \WeDevs\ERP\HRM\Employee( $old_data['allotted_to'] );
        $employee_new            = new \WeDevs\ERP\HRM\Employee( $new_data['allotted_to'] );
        $asset                   = erp_hr_get_asset( $old_data['item_id'] );
        $asset_new               = erp_hr_get_asset( $new_data['item_id'] );
        $category                = erp_hr_get_asset_category( $old_data['category_id'] );
        $category_new            = erp_hr_get_asset_category( $new_data['category_id'] );

        $old_data['allotted_to'] = $employee->get_full_name();
        $new_data['allotted_to'] = $employee_new->get_full_name();
        $old_data['category']    = $category;
        $new_data['category']    = $category_new;
        $old_data['item_group']  = $asset['item_group'];
        $new_data['item_group']  = $asset_new['item_group'];
        $old_data['item']        = $asset['item_code'];
        $new_data['item']        = $asset_new['item_code'];
        $old_data['model']       = $asset['model_no'];
        $new_data['model']       = $asset_new['model_no'];

        unset( $old_data['item_id'] );
        unset( $new_data['item_id'] );
        unset( $old_data['category_id'] );
        unset( $new_data['category_id'] );

        $array_diff = erp_get_array_diff( $new_data, $old_data );

        $log_data  = [
            'component'     => 'HRM',
            'sub_component' => 'Asset Allotment',
            'changetype'    => 'edit',
            'message'       => sprintf( __( 'An allotment of asset <strong>%1$s</strong> to <strong>%2$s</strong> has been updated', 'erp-pro' ), $asset['item_code'], $employee->get_full_name() ),
            'created_by'    => get_current_user_id(),
            'old_value'     => $array_diff['old_value'],
            'new_value'     => $array_diff['new_value'],
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Adds log when asset allotment is removed
     *
     * @since 1.1.3
     *
     * @param array $data
     *
     * @return void
     */
    public function delete_asset_allot( $data ) {
        $employee  = new \WeDevs\ERP\HRM\Employee( $data['allotted_to'] );
        $asset     = erp_hr_get_asset( $data['item_id'] );

        $log_data  = [
            'component'     => 'HRM',
            'sub_component' => 'Asset Allotment',
            'changetype'    => 'add',
            'message'       => sprintf( __( 'An allotment of asset <strong>%1$s</strong> to <strong>%2$s</strong> has been removed', 'erp-pro' ), $asset['item_code'], $employee->get_full_name() ),
            'created_by'    => get_current_user_id()
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Adds log when asset is requested
     *
     * @since 1.1.3
     *
     * @param array $data
     *
     * @return void
     */
    public function create_asset_req( $data ) {
        $employee  = new \WeDevs\ERP\HRM\Employee( $data['user_id'] );
        $asset     = erp_hr_get_asset( $data['item_group'] );

        $log_data  = [
            'component'     => 'HRM',
            'sub_component' => 'Asset Request',
            'changetype'    => 'add',
            'message'       => sprintf( __( 'An asset <strong>%1$s</strong> has been requested by <strong>%2$s</strong>', 'erp-pro' ), $asset['item_group'], $employee->get_full_name() ),
            'created_by'    => get_current_user_id()
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Adds log when asset request is deleted
     *
     * @since 1.1.3
     *
     * @param array $data
     *
     * @return void
     */
    public function delete_asset_req( $data ) {
        $employee  = new \WeDevs\ERP\HRM\Employee( $data['user_id'] );
        $asset     = erp_hr_get_asset( $data['item_group'] );

        $log_data  = [
            'component'     => 'HRM',
            'sub_component' => 'Asset Request',
            'changetype'    => 'delete',
            'message'       => sprintf( __( 'An asset request of <strong>%1$s</strong> by <strong>%2$s</strong> has been deleted', 'erp-pro' ), $asset['item_group'], $employee->get_full_name() ),
            'created_by'    => get_current_user_id()
        ];

        erp_log()->add( $log_data );
    }

    /**
     * Adds log when asset request is updated
     *
     * @since 1.1.3
     *
     * @param array $old_data
     * @param array $new_data
     *
     * @return void
     */
    public function update_asset_req( $old_data, $new_data ) {
        $employee          = new \WeDevs\ERP\HRM\Employee( $old_data['user_id'] );

        $new_item_group    = erp_hr_get_asset( $new_data['item_group'] );
        $old_item_group    = erp_hr_get_asset( $old_data['item_group'] );

        $new_data['item_group'] = $new_item_group['item_group'];
        $old_data['item_group'] = $old_item_group['item_group'];

        $array_diff        = erp_get_array_diff( $new_data, $old_data );

        $log_data          = [
            'component'     => 'HRM',
            'sub_component' => 'Asset Request',
            'changetype'    => 'edit',
            'message'       => sprintf( __( 'An asset request by <strong>%1$s</strong> has been updated', 'erp-pro' ), $employee->get_full_name() ),
            'created_by'    => get_current_user_id(),
            'old_value'     => $array_diff['old_value'],
            'new_value'     => $array_diff['new_value'],
        ];

        erp_log()->add( $log_data );
    }
}
