<ul>
    <?php
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'opening_reports';
        $tabs = array(
            'opening_reports'       =>  __( 'Opening Report', 'erp-pro' ),
            'candidate_reports'     =>  __( 'Candidate Report', 'erp-pro' ),
            'csv_reports'           =>  __( 'CSV Report', 'erp-pro' )
        );
        $url = version_compare( WPERP_VERSION , '1.4.0', '<' ) ?  'page=opening_reports' :'page=erp-hr&section=recruitment&sub-section=reports';
        foreach ( $tabs as $key =>  $value ) {
            $active = ( $tab === $key ) ? 'left-menu-current-item' : '';
            echo '<li><span id="'. strtolower( str_replace( '', '-', $value ) ) .'" class="'. $active .'"><a href="'. admin_url('admin.php?'. $url .'&tab='. $key ) .'">' . $value . '</a></span></li>';
        }
    ?>
</ul>
