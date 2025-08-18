<?php
namespace WeDevs\ERP_PRO\Admin\Menu;
use WeDevs\ERP_PRO\Traits\Singleton;

// don't call the file directly
if ( ! defined('ABSPATH') ) {
    exit;
}

use WeDevs\ERP\Framework\Traits\Hooker;

class Extensions {

    use Singleton;
    use Hooker;

    private function __construct() {

    }

    public function on_load_page() {
        $this->action( 'admin_enqueue_scripts', 'admin_scripts' );
    }

    public function admin_scripts() {
        wp_enqueue_script( 'erp-toastr' );
        wp_enqueue_style( 'erp-toastr' );
    }

    public function entry() {
        include_once WPERP_VIEWS . '/modules.php';
    }
}
