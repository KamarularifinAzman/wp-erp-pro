<?php
namespace WeDevs\DocumentManager;

use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Ajax handler
 *
 * @package WP-ERP
 */
class AjaxHandler {

    use Ajax;
    use Hooker;

    /**
     * Bind all the ajax event for HRM
     *
     * @since 0.1
     *
     * @return void
     */
    public function __construct() {

        //Get files and folders
        $this->action( 'wp_ajax_wp-erp-rec-get-files-folders', 'get_files_folders' );
        //Create directory
        $this->action( 'wp_ajax_wp-erp-rec-createDir', 'create_dir' );
        //Upload files
        $this->action( 'wp_ajax_file_dir_ajax_upload', 'upload_employee_file' );
        //Rename file OR directory
        $this->action( 'wp_ajax_wp-erp-rec-renameDirFile', 'rename_dir_file' );
        //Delete Files
        $this->action( 'wp_ajax_wp-erp-rec-deleteDirFile', 'delete_dir_file' );
        // load parent dir
        $this->action( 'wp_ajax_wp-erp-doc-loadParentNodes', 'load_dir_tree' );
        // move function
        $this->action( 'wp_ajax_wp-erp-doc-moveNow', 'move_now' );
        //Share
        $this->action( 'wp_ajax_wp-erp-rec-share-files-folders', 'share_files_folders' );
        //Sync employees to dropbox
        $this->action( 'wp_ajax_wp-erp-sync-employees-dropbox', 'sync_employees_dropbox' );
        //Download files from dropbox
        $this->action( 'wp_ajax_wp-erp-download-files-from-dropbox', 'download_file_from_dropbx' );

    }

    /**
     * Return files & folders based on condition
     *
     * @since 0.1
     *
     * @return void
     */
    public function get_files_folders() {
        //Check valid nonce
       /* if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'doc_form_builder_nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp-pro' ) );
        }*/

        $dir_id         = ( isset( $_POST['dir_id'] ) && ! empty( $_POST['dir_id'] ) ) ? sanitize_text_field( $_POST['dir_id'] ) : 0;
        $emp_id         = ( isset( $_POST['employee_id'] ) && ! empty( $_POST['employee_id'] ) ) ? intval( $_POST['employee_id'] ) : get_current_user_id();
        $source         = ( isset( $_POST['source'] ) && ! empty( $_POST['source'] ) ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : 'owned_by_me';
        $search_string  = ( isset( $_POST['search_string'] ) && ! empty( $_POST['search_string'] ) ) ? sanitize_text_field( wp_unslash( $_POST['search_string'] ) ) : null;
        $direct_link    = ( isset( $_POST['direct_link'] ) && ! empty( $_POST['direct_link'] ) ) ? sanitize_text_field( wp_unslash( $_POST['direct_link'] ) ) : null;
        $eid_type       = ( strpos( $_SERVER['HTTP_REFERER'], 'contact' ) ) ? 'contact' : 'employee';
        $response_data  = [];

        if( $search_string != null ) {
            if( $source == 'owned_by_me' ) {
                $response_data  = search_employee_owned_dir_files( $emp_id, $search_string );
            }
            if( $source == 'shared_with_me' ) {
                $response_data  = search_local_shared_files_folders( $emp_id, $search_string );
            }
            if( $source == 'my_dropbox' ) {
                $path       = get_dropbox_path( $emp_id, "0"  );
                $results    = dropbox_api_call( "files/search_v2", [
                    'query'              => $search_string,
                    'include_highlights' => false,
                    'options'            => [
                        'path' => $path
                    ]
                ] );

                if( !isset( $results['error'] ) ) {
                    $response_data = get_dropbox_search_process_data($results, $emp_id, "0", $response_data);
                } else {
                    $response_data['directory'] = [];
                    $response_data['files']     = [];
                    $response_data['error']     = $results['error'];
                }
            }
            $this->send_success( $response_data );
        }

        if( ( $source == 'owned_by_me' && $direct_link == null ) ) {
            $response_data['directory'] = get_dirInfo( $emp_id, $dir_id, $eid_type );
            $response_data['files']     = get_fileInfo( $emp_id, $dir_id, $eid_type );
        }

        if( $source == 'shared_with_me' && $direct_link == null ) {
            $response_data_local   = get_local_shared_files_folders( $emp_id );
            $response_data_dropbox = get_dropbox_shared_files_folders( $emp_id );

            $response_data['directory'] = array_merge( $response_data_local['directory'], $response_data_dropbox['directory'] );
            $response_data['files'] = array_merge( $response_data_local['files'], $response_data_dropbox['files'] );
        }

        if( $source == 'shareed_dropbox' && $direct_link == null ) {
            $response_data = get_dropbox_shared_files_folders( $emp_id );
        }

        if( ( $source == 'owned_by_me' || $source == 'shared_with_me' ) && $direct_link == 'yes' ) {
            $response_data['directory'] = get_dirInfo( $emp_id, $dir_id, $eid_type );
            $response_data['files']     = get_fileInfo( $emp_id, $dir_id, $eid_type );
        }

        if( $source == 'my_dropbox'  && $direct_link == null ) {
            $path       = get_dropbox_path( $emp_id, $dir_id  );
            error_log( 'path: ' . $path );
            $results    = dropbox_api_call( "files/list_folder", [ 'path' => $path ] );

            if( !isset( $results['error'] ) ) {
                $response_data = get_dropbox_process_data($results, $emp_id, $dir_id, $response_data);
            } else {
                $response_data['directory'] = [];
                $response_data['files']     = [];
                $response_data['error']     = $results['error'];
            }
        }

        if( $source == 'my_dropbox'  && $direct_link == 'yes' ) {

            $path       = get_dropbox_path( $emp_id, $dir_id  );
            $results    = dropbox_api_call( "files/list_folder", [ 'path' => $path ] );

            if( !isset( $results['error'] ) ) {
                $response_data = get_dropbox_process_data($results, $emp_id, $dir_id, $response_data);
            } else {
                $response_data['directory'] = [];
                $response_data['files']     = [];
                $response_data['error']     = $results['error'];
            }
        }

        if( $source == 'shareed_dropbox'  && $direct_link == 'yes' ) {

            $path       = get_dropbox_path( $emp_id, $dir_id  );
            $results    = dropbox_api_call( "files/list_folder", [ 'path' => $path ] );

            if( !isset( $results['error'] ) ) {
                $response_data = get_dropbox_process_data($results, $emp_id, $dir_id, $response_data);
            } else {
                $response_data['directory'] = [];
                $response_data['files']     = [];
                $response_data['error']      = $results['error'];
            }
        }

        $this->send_success( $response_data );

    }

    /**
     * create dir
     *
     * @return json
     */
    public function create_dir() {

        $dir_id         = ( isset( $_POST['parent_id'] ) && ! empty( $_POST['parent_id'] ) ) ? sanitize_text_field( $_POST['parent_id'] ) : 0;
        $emp_id         = ( isset( $_POST['employee_id'] ) && ! empty( $_POST['employee_id'] ) ) ? intval( $_POST['employee_id'] ) : get_current_user_id();
        $source         = ( isset( $_POST['source'] ) && ! empty( $_POST['source'] ) ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : 'owned_by_me';
        $dir_name       = ( isset( $_POST['dirName'] ) && ! empty( $_POST['dirName'] ) ) ? sanitize_text_field( wp_unslash( $_POST['dirName'] ) ) : 'New Folder';
        $eid_type       = ( strpos( $_SERVER['HTTP_REFERER'], 'contact' ) ) ? 'contact' : 'employee';

        if( $source == 'owned_by_me' ) {
            $dir_exist_result = check_duplicate_dir( $emp_id, $dir_id, $dir_name );
            if ( $dir_exist_result == true ) {
                $this->send_error( __( 'Folder name already exits', 'erp-pro' ) );
            } else {
                $result = create_directory( $emp_id, $dir_id, $dir_name, $eid_type );
                erp_documents_purge_cache( [ 'dir_id' => $dir_id, 'employee_id' => $emp_id ] );
                $this->send_success( $result );
            }
        }

        if( $source == 'my_dropbox' ) {
            $path       = get_dropbox_path( $emp_id, $dir_id  );
            $results    = dropbox_api_call( "files/create_folder_v2", [ 'path' => $path . "/" . $dir_name ] );
            if( isset( $results['error'] ) ){
                $this->send_error( __( 'Folder name already exits', 'erp-pro' ) );
            } else {
                $this->send_success( __( 'Folder created successfully', 'erp-pro' ) );
            }
        }
    }

    /**
     * file upload for employee
     *
     * @return void
     */
    public function upload_employee_file() {
        $this->verify_nonce( 'file_upload_nonce' );
        $object_id   = isset( $_REQUEST['object_id'] ) ? intval( $_REQUEST['object_id'] ) : 0;
        $parent_id   = isset( $_REQUEST['parent_id'] ) ? intval( $_REQUEST['parent_id'] ) : 0;
        $employee_id = isset( $_REQUEST['employee_id'] ) && ! empty( $_REQUEST['employee_id'] ) ? intval( $_REQUEST['employee_id'] ) : get_current_user_id();
        $source      = isset( $_REQUEST['source'] ) && ! empty( $_REQUEST['source'] ) && $_REQUEST['source'] != 'undefined' ? sanitize_text_field( wp_unslash( $_REQUEST['source'] ) ) : 'owned_by_me';

        if ( 'owned_by_me' === $source ) {
            upload_local_file( $object_id, $employee_id, $parent_id) ;
        }

        if ( 'my_dropbox' === $source ) {
            $path    = get_dropbox_path( $employee_id, $parent_id  );
            $results = dropbox_file_upload( 'files/upload', [
                'path'       => $path . "/" . $_FILES['doc_attachment']['name'],
                'mode'       => 'add',
                'autorename' => true,
                'mute'       => false,
            ], $_FILES );

            if( isset( $results['error'] ) ){
                $this->send_error( __( 'Upload failed. Please try again.', 'erp-pro' ) );
            } else {
                $this->send_success(  __( 'File uploaded successfully', 'erp-pro' ) );
            }
        }
    }

    /**
     * rename dir or file
     *
     * @return json
     */
    public function rename_dir_file() {

        $eid                   = isset( $_GET['employee_id'] ) && !empty( $_GET['employee_id'] ) ? $_GET['employee_id'] : get_current_user_id() ;
        $parent_id             = $_GET['parent_id'];
        $target_id             = $_GET['target_dir_id'];
        $dir_name              = $_GET['dirName'];
        $type                  = $_GET['dir_file_type'];
        $dir_exist_result      = false;
        $filename_exist_result = false;

        if( isset( $_GET['source'] ) && $_GET['source'] == 'owned_by_me' ){
            $response = rename_local_file_directory( $eid, $parent_id, $dir_name, $type, $target_id );
            if ( $response['success'] == true ) {
                $this->send_success( $response['msg'] );
            } else {
                $this->send_error( $response['msg'] );
            }
        }
        if( isset( $_GET['source'] ) && $_GET['source'] == 'my_dropbox' ){

            $source_id = $target_id;
            $target_data = explode( '/', $target_id );
            array_pop($target_data );
            $target_data = implode( '/', $target_data ) ;
            $target_data = $target_data . "/" . $dir_name;
            $result = dropbox_api_call( 'files/move_v2', [
                "from_path"                => $source_id,
                "to_path"                  => $target_data,
                "allow_shared_folder"      => false,
                "autorename"               => false,
                "allow_ownership_transfer" => false,
            ] );

            if(isset( $result['error'] ) ){
                $this->send_error( __( 'Rename failed', 'erp-pro' ) );
            } else {
                $this->send_success( __( 'Successfully renamed', 'erp-pro' ) );
            }
        }
    }

    /**
     * delete dir or file
     *
     * @return bool
     */
    public function delete_dir_file() {

        $eid                  = isset( $_POST['employee_id'] ) && ! empty( $_POST['employee_id'] ) ? $_POST['employee_id'] : get_current_user_id();
        $parent_id            = isset( $_POST['parent_id'] ) || $_POST['parent_id'] != '' ? $_POST['parent_id'] : 0;
        $source               = isset( $_POST['source'] ) || $_POST['source'] != '' ? $_POST['source'] : 'owned_by_me';
        $selected_dir_file_id = json_decode( stripslashes( $_POST['selected_dir_file_id'] ) );

        do_action( 'delete_shared_dir_file', $selected_dir_file_id, $eid );

        if ( $source == 'owned_by_me' ) {
            $result = delete_local_files_dir( $eid, $parent_id, $selected_dir_file_id );
            if( $result ) {
                $this->send_success(__( "Deleted successfully", "erp-document" ) );
            }
        }

        if ( $source == 'my_dropbox' ) {
            $sdfa = []; // selected directory file array
            foreach ( $selected_dir_file_id as $id ) {
                $sdfa[]['path'] = $id;
            }
            $results    = dropbox_api_call( "files/delete_batch ", [ 'entries' => $sdfa ] );
            if ( isset( $results['async_job_id'] ) ) {
                $this->send_success(__( "Deleted successfully from dropbox", "erp-document" ) );
            }
        }

        $this->send_error(__( "Something went wrong. Please try again later.", "erp-document" ) );

    }

    /**
     * Get parent directories to make tree template
     *
     * @since 0.1
     *
     * @return void
     */
    public function load_dir_tree() {
        if ( isset( $_GET['employee_id'] ) ) {
            global $wpdb;
            $eid = ( isset( $_GET['employee_id'] ) && !empty( $_GET['employee_id'] ) ) ? $_GET['employee_id'] : get_current_user_id();

            $q  = "SELECT dir.dir_id as id, dir.dir_name as text, dir.parent_id as parent_id
                    FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
                    WHERE dir.eid='" . $eid . "' AND dir.is_dir=1";
            $ad = array_values( buildtree( $wpdb->get_results( $q, ARRAY_A ) ) );
            // insert the root as parent id 0 for the tree
            $withroot = array(
                'id'        => 0,
                'text'      => 'Home',
                'parent_id' => 0,
                'children'  => $ad );

            echo json_encode( $withroot );
            exit;
            //echo wp_json_encode($data, JSON_PRETTY_PRINT);
            //$this->send_success($data, JSON_PRETTY_PRINT);
        } else {
            $this->send_success( [ ] );
        }
    }

    /**
     * Dir file Move function
     *
     * @since 0.1
     *
     * @return void
     */
    public function move_now() {
        if ( isset( $_POST['employee_id'] ) ) {
            global $wpdb;
            $eid       = $_POST['employee_id'];
            $parent_id = $_POST['parent_id'];
            if ( empty( $eid ) || $eid == '' ) {
                $eid = get_current_user_id();
            }

            erp_documents_purge_cache( [ 'dir_id' => $parent_id, 'employee_id' => $eid ] );

            $new_parent_id   = is_numeric( $_POST['new_parent_id'] ) ? $_POST['new_parent_id'] : 0;
            $selectedDirFile = json_decode( stripslashes( $_POST['selectedDirFile'] ) );
            // check selected dir is new parent, if true then send error else do move operation
            if ( in_array( $new_parent_id, $selectedDirFile ) ) {
                $this->send_error( 'You cannot move a folder into itself' );
            }

            $where_dir_id = implode( ',', $selectedDirFile );

            erp_documents_purge_cache( [ 'dir_id' => $new_parent_id, 'employee_id' => $eid ] );

            do_action( 'erp_doc_dir_or_file_move', $where_dir_id, $new_parent_id, $parent_id, $eid );
            $wpdb->query(
                "UPDATE {$wpdb->prefix}erp_employee_dir_file_relationship
                SET parent_id={$new_parent_id}
                WHERE eid={$eid} AND dir_id IN ($where_dir_id)"
            );

            if ( $parent_id != 0 ) {
                update_parent_folder_timestamp( $parent_id, $eid );
            }

            $this->send_success( __( 'Moved successfully', 'erp-pro' ) );
        } else {
            $this->send_success( [ ] );
        }
    }

    /**
     * Share files & folders
     *
     * @since 1.3.1
     *
     * @return void
     */
    public function share_files_folders() {
        global $wpdb;

        $formdata        = ( isset( $_POST['formdata'] ) ) ? $_POST['formdata'] : '';
        $selectedDirFile = ( isset( $_POST['selectedDirFile'] ) ) ? json_decode( stripslashes ( sanitize_text_field( $_POST['selectedDirFile'] ) ) ) : '';
        $shared_source = ( isset( $_POST['shared_source'] ) ) ? $_POST['shared_source'] : 'owned_by_me';

        $file_data = ( isset( $_POST['file_data'] ) ) ? json_decode( stripslashes ( sanitize_text_field( $_POST['file_data'] ) ) ) : '';
        $dir_data = ( isset( $_POST['dir_data'] ) ) ? json_decode( stripslashes ( sanitize_text_field( $_POST['dir_data'] ) ) ) : '';

        if ( $shared_source == 'owned_by_me' ) {
            $source = 'local';
        }

        if ( $shared_source == 'my_dropbox' ) {
            $source = 'dropbox';
        }

        parse_str( $formdata, $param );

        if ( $param['share_by'] == 'all_employees' ) {
            $args = array(
                'status' => 'active',
                'number' => -1
            );
        }

        if ( $param['share_by'] == 'by_department' ) {
            $args = array(
                'status'     => 'active',
                'department' => $param['department'],
                'number'     => -1
            );
        }

        if ( $param['share_by'] == 'by_designation' ) {
            $args = array(
                'status'      => 'active',
                'designation' => $param['designation'],
                'number'      => -1
            );
        }

        if ( $param['share_by'] == 'by_employee' ) {
            $emp_ids = $param['selected_emp'];
        } else {
            $employees = erp_hr_get_employees( $args );
            $emp_ids   = wp_list_pluck( $employees, 'user_id' );
        }

        $file_dir_data = array_merge( $dir_data, $file_data );

        $new_sl_dir_file = [];

        foreach ( $file_dir_data as $fdd ) {
            if ( in_array( $fdd->dir_id, $selectedDirFile ) ) {
                $new_sl_dir_file[] = $fdd;
            }
        }


        foreach ( $emp_ids as $emp_id ) {
            foreach ( $new_sl_dir_file as $sdf ) {

                $sadf_str = serialize( (array) $sdf );

                $sql = "INSERT INTO {$wpdb->prefix}erp_dir_file_share ( owner_id, shared_with_id, dir_file_id, source, eid_type, details, created_at, updated_at ) VALUES
                  ( %d, %d, %s, %s, %s, %s, %s, %s ) ON DUPLICATE KEY UPDATE shared_with_id = %d AND dir_file_id = %d AND owner_id = %d";
                $sql = $wpdb->prepare( $sql, $param['owner_id'], $emp_id, $sdf->dir_id, $source, 'employee', $sadf_str ,current_time( 'Y-m-d H:i:s' ), current_time( 'Y-m-d H:i:s' ), $emp_id, $sdf->dir_id, $param['owner_id'] );
                $wpdb->query( $sql );
            }

            erp_documents_purge_cache( ['employee_id' => $emp_id, 'shared' => true ] );

            do_action( 'after_share_file_folder', $emp_id );
        }
        wp_send_json_success( esc_html__( 'Successfully shared.', 'erp-pro' ) );
    }

    /**
     * Sync dropbox files & folders
     *
     * @since 1.3.1
     *
     * @return void
     */
    public function sync_employees_dropbox() {
        $this->verify_nonce( 'erp-settings-nonce' );

        $token = isset( $_POST['dropbox_access_token'] ) ? sanitize_text_field( $_POST['dropbox_access_token'] ) : null;

        $user_folders = array();
        foreach ( get_users() as $user ) {
            $user_folders[] = "/employees/" . $user->data->user_email;
        }

        $results = dropbox_api_call( "files/create_folder_batch", [ 'paths' => $user_folders ], $token );

        if ( ! empty( $results['error'] ) ) {
            $this->send_error( $results['error']['body'] );
        }

        $this->send_success( [ 'message' => __( 'Dropbox connected successfully', 'erp-pro' ) ] );
    }

    /**
     * Download
     *
     * @since 1.3.1
     *
     * @return void
     */
    public function download_file_from_dropbx() {

        $file_path  = ( isset( $_POST['dir_id'] ) && ! empty( $_POST['dir_id'] ) ) ? $_POST['dir_id'] : '';
        $results    = dropbox_api_call( "files/get_temporary_link", [ 'path' => $file_path ] );
        $url        = $results['link'];

        $this->send_success( $url );
    }

}
