<?php
namespace WeDevs\DocumentManager;

use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Ajax handler
 *
 * @package WP-ERP
 */
class AjaxHandlerBack {

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
        // delete attachment file in file system
        $this->action( 'wp_ajax_erp_hr_attachment_delete_file', 'delete_hr_attachment' );
        // get first level dir info by employee id
        $this->action( 'wp_ajax_wp-erp-rec-get-dir-info', 'get_dirInfo' );
        // get first level file info by employee id
        $this->action( 'wp_ajax_wp-erp-rec-get-file-info', 'get_fileInfo' );
        // check duplicate dir name in a level
        $this->action( 'wp_ajax_wp-erp-rec-createDir', 'createDir' );
        // check duplicate dir name in a level
        $this->action( 'wp_ajax_wp-erp-rec-deleteDirFile', 'deleteDirFile' );
        // check duplicate dir name in a level
        $this->action( 'wp_ajax_file_dir_ajax_upload', 'uploadEmployeeFile' );
        // check duplicate dir name in a level
        $this->action( 'wp_ajax_wp-erp-rec-renameDirFile', 'renameDirFile' );
        // load parent dir
        $this->action( 'wp_ajax_wp-erp-doc-loadParentNodes', 'loadDirTree' );
        // move function
        $this->action( 'wp_ajax_wp-erp-doc-moveNow', 'moveNow' );
        // search
        $this->action( 'wp_ajax_wp-erp-rec-search-dir-info', 'searchDirNow' );
        $this->action( 'wp_ajax_wp-erp-rec-search-file-info', 'searchFileNow' );
        //Share
        $this->action( 'wp_ajax_wp-erp-rec-share-files-folders', 'share_files_folders' );
        $this->action( 'wp_ajax_wp-erp-rec-get-share-files-folders', 'get_share_files_folders' );

        //Get files and folders
        $this->action( 'wp_ajax_wp-erp-rec-get-files-folders', 'get_files_folders' );

        //Sync employees to dropbox
        $this->action( 'wp_ajax_wp-erp-sync-employees-dropbox', 'sync_employees_dropbox' );
    }

    /**
    * search dir info by search key
    *
    * @return array
    */
    public function searchDirNow() {
        if ( isset( $_GET['employee_id'] ) ) {
            global $wpdb;
            $eid             = isset( $_GET['employee_id'] ) && !empty( $_GET['employee_id'] ) ? $_GET['employee_id'] : get_current_user_id();
            $search_key      = ( isset( $_GET['skey'] ) ? $_GET['skey'] : '' );
            $current_user_id = get_current_user_id();

            $query = "SELECT users.ID, users.user_nicename, dir.dir_id, dir.eid, dir.is_dir, dir.dir_name, dir.created_at, DATE_FORMAT(dir.updated_at, '%Y-%m-%d %h:%i %p') as updated_at
                FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
                LEFT JOIN {$wpdb->prefix}users as users
                ON dir.created_by=users.ID
                WHERE dir.eid='" . $eid . "' AND dir.dir_name LIKE'%" . $search_key . "%' AND dir.is_dir=1";

            $udata = $wpdb->get_results( $query, ARRAY_A );

            $user_file_data = [ ];
            foreach ( $udata as $ufd ) {
                $user_file_data[] = array(
                    //'dir_name'      => $ufd['dir_name'],
                    'dir_file_name' => $ufd['dir_name'],
                    'dir_id'        => $ufd['dir_id'],
                    'eid'           => $ufd['eid'],
                    'is_dir'        => $ufd['is_dir'],
                    'updated_at'    => date( 'Y-m-d g:i A', strtotime( $ufd['updated_at'] ) ),
                    'user_link'     => get_edit_profile_url( $ufd['ID'] ),
                    'user_id'       => $ufd['ID'],
                    'user_nicename' => $ufd['user_nicename']
                );
            }
            $this->send_success( $user_file_data );
        } else {
            $this->send_success( [ ] );
        }
    }

    /**
    * search file info by search key
    *
    * @return array
    */
    public function searchFileNow() {
        if ( isset( $_GET['employee_id'] ) ) {
            global $wpdb;
            $eid             = isset( $_GET['employee_id'] ) && !empty( $_GET['employee_id'] ) ? $_GET['employee_id'] : get_current_user_id();
            $search_key      = ( isset( $_GET['skey'] ) ? $_GET['skey'] : '' );
            $current_user_id = get_current_user_id();

            $query = "SELECT users.ID, users.user_nicename, file.dir_id, file.eid, file.is_dir, file.dir_name, file.attachment_id, file.updated_at
                FROM {$wpdb->prefix}erp_employee_dir_file_relationship as file
                LEFT JOIN {$wpdb->prefix}users as users
                ON file.created_by=users.ID
                WHERE file.eid='" . $eid . "' AND file.dir_name LIKE'%" . $search_key . "%' AND file.is_dir=0";
            $udata = $wpdb->get_results( $query, ARRAY_A );

            $user_file_data = [ ];
            foreach ( $udata as $ufd ) {
                $user_file_data[] = array(
                    'user_nicename'   => $ufd['user_nicename'],
                    'user_id'         => $ufd['ID'],
                    'user_link'       => get_edit_profile_url( $ufd['ID'] ),
                    'dir_id'          => $ufd['dir_id'],
                    'eid'             => $ufd['eid'],
                    'is_dir'          => $ufd['is_dir'],
                    'attactment_url'  => wp_get_attachment_url( $ufd['attachment_id'] ),
                    'attachment_type' => get_post_mime_type( $ufd['attachment_id'] ),
                    //'file_name'       => $ufd['dir_name'],
                    'dir_file_name'   => $ufd['dir_name'],
                    'file_size'       => number_format( filesize( get_attached_file( $ufd['attachment_id'] ) ) / 1024, 2 ) . ' KB',
                    'updated_at'      => date( 'Y-m-d g:i A', strtotime( $ufd['updated_at'] ) )
                );
            }
            $this->send_success( $user_file_data );
        } else {
            $this->send_success( [ ] );
        }
    }

    /**
     * remove attachment
     *
     * @return void
     */
    public function delete_hr_attachment() {
        $this->verify_nonce( 'doc_form_builder_nonce' );

        if ( isset( $_POST['file_id'] ) ) {
            $attachmentid = $_POST['file_id'];
            if ( false === wp_delete_attachment( $attachmentid, false ) ) {
                $this->send_error( __( 'The file could not deleted', 'erp-pro' ) );
            } else {
                $this->send_success( __( 'File has been deleted', 'erp-pro' ) );
            }
        } else {
            $this->send_error( __( 'File could not deleted', 'erp-pro' ) );
        }
    }

    /**
    * get dir file info by employee id
    *
    * @return array
    */
    public function get_dirInfo() {
        if ( isset( $_GET['employee_id'] ) ) {
            global $wpdb;
            $eid             = isset( $_GET['employee_id'] ) && !empty( $_GET['employee_id'] ) ? $_GET['employee_id'] : get_current_user_id();
            $parent_id       = ( isset( $_GET['dir_id'] ) ? $_GET['dir_id'] : 0 );
            $current_user_id = get_current_user_id();
            $eid_type        = ( strpos( $_SERVER['HTTP_REFERER'], 'contact' ) ) ? 'contact' : 'employee';

            $user_file_data = get_dirInfo( $eid, $parent_id, $eid_type );

            $this->send_success( $user_file_data );
        } else {
            $this->send_success( [ ] );
        }
    }

    /**
    * get dir file info by employee id
    *
    * @return array
    */
    public function get_fileInfo() {
        if ( isset( $_GET['employee_id'] ) ) {
            global $wpdb;

            $eid             = isset( $_GET['employee_id'] ) && !empty( $_GET['employee_id'] ) ? $_GET['employee_id'] : get_current_user_id();
            $parent_id       = ( isset( $_GET['dir_id'] ) ? $_GET['dir_id'] : 0 );
            $current_user_id = get_current_user_id();
            $eid_type        = ( strpos( $_SERVER['HTTP_REFERER'], 'contact' ) ) ? 'contact' : 'employee';

            $user_file_data = get_fileInfo( $eid, $parent_id, $eid_type );

            $this->send_success( $user_file_data );
        } else {
            $this->send_success( [ ] );
        }
    }

    /**
    * delete dir or file
    *
    * @return bool
    */
    public function deleteDirFile() {
        if ( isset( $_POST['employee_id'] ) ) {
            global $wpdb;
            $eid                  = isset( $_POST['employee_id'] ) && ! empty( $_POST['employee_id'] ) ? $_POST['employee_id'] : get_current_user_id();
            $parent_id            = isset( $_POST['parent_id'] ) || $_POST['parent_id'] != '' ? $_POST['parent_id'] : 0;
            $selected_dir_file_id = json_decode( stripslashes( $_POST['selected_dir_file_id'] ) );

            // delete attachment file first
            foreach ( $selected_dir_file_id as $sdfid ) {
                $attachment_id = $wpdb->get_var( "SELECT attachment_id FROM " . $wpdb->prefix . 'erp_employee_dir_file_relationship' . " WHERE dir_id={$sdfid} AND is_dir=0" );
                wp_delete_attachment( $attachment_id );
            }

            foreach ( $selected_dir_file_id as $fd_id ) {
                do_action( 'erp_doc_dir_or_file_delete', $fd_id, $eid );

                // find the deepest nodes if have any
                $child_kit = [ ];
                get_child_dir_ids( $fd_id, $child_kit );
                $wpdb->delete(
                    $wpdb->prefix . 'erp_employee_dir_file_relationship',
                    array( 'dir_id' => $fd_id, 'eid' => $eid ),
                    array( '%d', '%d' )
                );
                $wpdb->delete(
                    $wpdb->prefix . 'erp_employee_dir_file_relationship',
                    array( 'parent_id' => $fd_id, 'eid' => $eid ),
                    array( '%d', '%d' )
                );
                //delete all child nodes now
                if ( count($child_kit) > 0 ) {
                    $dir_id_to_be_deleted = implode( ",", $child_kit );
                    $query                = "DELETE FROM {$wpdb->prefix}erp_employee_dir_file_relationship WHERE eid={$eid} AND parent_id IN($dir_id_to_be_deleted)";
                    $wpdb->query($query);
                }
            }

            if ( $parent_id != 0 ) {
                update_parent_folder_timestamp( $parent_id, $eid );
            }

            $this->send_success( __( 'Deleted successfully', 'erp-pro' ) );
        } else {
            $this->send_success( __( 'Could not deleted', 'erp-pro' ) );
        }
    }

    /**
    * create dir
    *
    * @return json
    */
    public function createDir() {

        $dir_id         = ( isset( $_POST['parent_id'] ) && ! empty( $_POST['parent_id'] ) ) ? sanitize_text_field( $_POST['parent_id'] ) : 0;
        $emp_id         = ( isset( $_POST['employee_id'] ) && ! empty( $_POST['employee_id'] ) ) ? intval( $_POST['employee_id'] ) : get_current_user_id();
        $source         = ( isset( $_POST['source'] ) && ! empty( $_POST['source'] ) ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : 'owned_by_me';
        $dir_name       = ( isset( $_POST['dirName'] ) && ! empty( $_POST['dirName'] ) ) ? sanitize_text_field( wp_unslash( $_POST['dirName'] ) ) : 'New Folder';
        $eid_type       = ( strpos( $_SERVER['HTTP_REFERER'], 'contact' ) ) ? 'contact' : 'employee';

        if( $source == 'owned_by_me' ) {
            $dir_exist_result = $this->checkDuplicateDir( $emp_id, $dir_id, $dir_name );
            if ( $dir_exist_result == true ) {
                $this->send_error( __( 'Folder name already exits', 'erp-pro' ) );
            } else {
                $result = create_directory( $emp_id, $dir_id, $dir_name, $eid_type );
                $this->send_success( $result );
            }
        }

        if( $source == 'my_dropbox' ) {

            $user       = get_userdata( $emp_id );
            $user_roles = $user->roles; //array of roles the user is part of.

            if( in_array( 'erp_hr_manager' , $user_roles ) ) {
                if( $dir_id == '0' ) {
                    $path       = "/" . $dir_name;
                } else {
                    $path       = $dir_id . "/" . $dir_name;
                }
            } else {
                if( $dir_id == '0' ) {
                    $path       = "/employees/" . $user->data->user_email . "/" . $dir_name;
                } else {
                    $path       = $dir_id . "/" . $dir_name;
                }
            }

            $results    = dropbox_api_call( "files/create_folder_v2", [ 'path' => $path ] );
            if( isset( $results['error'] ) ){
                $this->send_error( __( 'Folder name already exits', 'erp-pro' ) );
            } else {
                $this->send_success( __( 'Folder created successfully', 'erp-pro' ) );
            }
        }
    }

    /**
    * rename dir or file
    *
    * @return json
    */
    public function renameDirFile() {

        $eid                   = isset( $_GET['employee_id'] ) && !empty( $_GET['employee_id'] ) ? $_GET['employee_id'] : get_current_user_id() ;
        $parent_id             = $_GET['parent_id'];
        $target_id             = $_GET['target_dir_id'];
        $dir_name              = $_GET['dirName'];
        $type                  = $_GET['dir_file_type'];
        $dir_exist_result      = false;
        $filename_exist_result = false;

        if( isset( $_GET['source'] ) && $_GET['source'] == 'owned_by_me' ){
            if ( isset( $_GET['employee_id'] ) ) {
                global $wpdb;
                if ( $type == 'folder' ) {
                    $dir_exist_result = $this->checkDuplicateDir( $eid, $parent_id, $dir_name );
                } else {
                    $filename_exist_result = $this->checkDuplicateFile( $eid, $parent_id, $dir_name );
                }

                if ( $dir_exist_result == true || $filename_exist_result == true ) {
                    $this->send_error( __( 'Given name already exits!', 'erp-pro' ) );
                } else {
                    do_action( 'erp_doc_dir_or_file_rename', $type, $dir_name, $target_id, $eid );
                    //update dir or file name
                    $data         = array(
                        'dir_name'   => $dir_name,
                        'created_by' => get_current_user_id()
                    );
                    $where        = array(
                        'eid'    => $eid,
                        'dir_id' => $target_id
                    );
                    $data_format  = array(
                        '%s',
                        '%d'
                    );
                    $where_format = array(
                        '%d'
                    );
                    $wpdb->update( $wpdb->prefix . 'erp_employee_dir_file_relationship', $data, $where, $data_format, $where_format );
                    if ( $parent_id != 0 ) {
                        update_parent_folder_timestamp( $parent_id, $eid );
                    }

                    $this->send_success( __( 'Renamed successfully', 'erp-pro' ) );
                }

            } else {
                $this->send_success( [ ] );
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
    * check duplicate dir
    *
    * @return bool
    */
    public function checkDuplicateDir( $eid, $parent_id, $dir_name ) {
        global $wpdb;
        $current_user_id = get_current_user_id();
        $eid_type        = ( strpos( $_SERVER['HTTP_REFERER'], 'contact' ) ) ? 'contact' : 'employee';

        $query = "SELECT *
            FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
            WHERE dir.eid='" . $eid . "' AND dir.parent_id='" . $parent_id . "' AND dir.dir_name='" . $dir_name . "' AND dir.is_dir=1 AND dir.eid_type='{$eid_type}'";

        if ( count( $wpdb->get_results( $query, ARRAY_A ) ) > 0 ) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * check duplicate file name
    *
    * @return bool
    */
    public function checkDuplicatefile( $eid, $parent_id, $dir_name ) {
        global $wpdb;
        $current_user_id = get_current_user_id();

        $query = "SELECT *
            FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
            WHERE dir.eid='" . $eid . "' AND dir.parent_id='" . $parent_id . "' AND dir.dir_name='" . $dir_name . "' AND dir.is_dir=0";

        if ( count( $wpdb->get_results( $query, ARRAY_A ) ) > 0 ) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * file upload for employee
    *
    * @return void
    */
    public function uploadEmployeeFile() {
        global $wpdb;
        $this->verify_nonce( 'file_upload_nonce' );
        $object_id       = isset( $_REQUEST['object_id'] ) ? intval( $_REQUEST['object_id'] ) : 0;
        $parent_id       = isset( $_REQUEST['parent_id'] ) ? $_REQUEST['parent_id'] : 0;
        $employee_id     = isset( $_REQUEST['employee_id'] ) && !empty( $_REQUEST['employee_id'] ) ? $_REQUEST['employee_id'] : get_current_user_id();
        $source          = isset( $_REQUEST['source'] ) ? $_REQUEST['source'] : 'owned_by_me';

        if( $source == 'owned_by_me' ) {
            $response        = $this->upload_file( $object_id );
            $current_user_id = get_current_user_id();


            if ( $response['success'] ) {
                $file = $this->get_file( $response['file_id'] );

                $delete   = sprintf( '<a href="#" data-id="%d" class="doc-delete-file button">%s</a>', $file['id'], __( 'Delete File', 'wp-erp-rec' ) );
                $hidden   = sprintf( '<input type="hidden" name="doc_attachment[]" value="%d" />', $file['id'] );
                $file_url = sprintf( '<input class="dir_file_chkbox dir_chkbox" type="checkbox" value="0" v-model="dir_file_checkboxx"><a class="filelink" href="%1$s" target="_blank"><img src="%2$s" alt="%3$s" /></a>', $file['url'], $file['thumb'], esc_attr( $file['name'] ) );

                // get last dir id by employee id
                $query = "SELECT dir.dir_id
                FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
                WHERE dir.eid='" . $employee_id . "'
                ORDER BY dir.dir_id DESC LIMIT 1";

                $last_dir_id = $wpdb->get_var( $query );
                /*********end of last dir id******/
                // insert attach id to wp_erp_employee_dir_file_relationship
                //insert applicant attach cv id
                $data = array(
                    'eid'           => $employee_id,
                    'dir_id'        => $last_dir_id + 1,
                    'dir_name'      => $file['name'],
                    'attachment_id' => $file['id'],
                    'parent_id'     => $parent_id,
                    'is_dir'        => 0,
                    'eid_type'      => ( strpos( $_SERVER['HTTP_REFERER'], 'contact' ) ) ? 'contact' : 'employee',
                    'created_by'    => get_current_user_id(),
                    'created_at'    => current_time( 'Y-m-d H:i:s' )
                );

                $format = array(
                    '%d',
                    '%d',
                    '%s',
                    '%d',
                    '%d',
                    '%d',
                    '%s',
                    '%d',
                    '%s'
                );

                $wpdb->insert( $wpdb->prefix . 'erp_employee_dir_file_relationship', $data, $format );

                if ( $parent_id != 0 ) {
                    update_parent_folder_timestamp( $parent_id, $employee_id );
                }
                do_action( 'erp_doc_dir_or_file_new', $data );

                //$html = '<div class="doc-uploaded-item">' . $file_url . ' ' . $delete . $hidden . '</div>';
                $html = '<li><div class="filename-col">' . $file_url . ' ' . $hidden . '</div>
                        <div class="modified-time">' . $file['upload_time'] . '</div>
                     </li>';
                echo json_encode( array(
                    'success' => true,
                    'content' => $html
                ) );

                exit;
            }

            echo json_encode( array(
                'success' => false,
                'error'   => $response['error']
            ) );

            exit;
        }

        if( $source == 'my_dropbox' ) {


            $user       = get_userdata( $employee_id );
            $user_roles = $user->roles; //array of roles the user is part of.

            if( in_array( 'erp_hr_manager' , $user_roles ) ) {
                if( $parent_id == '0' ) {
                    $path       = "/" . $_FILES['doc_attachment']['name'];
                } else {
                    $path       = $parent_id . "/" . $_FILES['doc_attachment']['name'];
                }
            } else {
                if( $parent_id == '0' ) {
                    $path       = "/employees/" . $user->data->user_email . "/" . $_FILES['doc_attachment']['name'];
                } else {
                    $path       = $parent_id . "/" . $_FILES['doc_attachment']['name'];
                }
            }

            $results = dropbox_file_upload( 'files/upload', [
                'path'       => $path,
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
     * Upload a file and insert as attachment
     *
     * @param int $post_id
     *
     * @return int|bool
     */
    public function upload_file( $post_id = 0 ) {
        global $wpdb;
        if ( $_FILES['doc_attachment']['error'] > 0 ) {
            return false;
        }

        $upload = array(
            'name'     => $_FILES['doc_attachment']['name'],
            'type'     => $_FILES['doc_attachment']['type'],
            'tmp_name' => $_FILES['doc_attachment']['tmp_name'],
            'error'    => $_FILES['doc_attachment']['error'],
            'size'     => $_FILES['doc_attachment']['size']
        );

        $uploaded_file = wp_handle_upload( $upload, array( 'test_form' => false ) );

        if ( isset( $uploaded_file['file'] ) ) {
            $file_loc  = $uploaded_file['file'];
            $file_name = basename( $_FILES['doc_attachment']['name'] );
            $file_type = wp_check_filetype( $file_name );

            $attachment = array(
                'post_mime_type' => $file_type['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                'post_content'   => '',
                'post_status'    => 'erp_hr_rec'
            );

            $attach_id   = wp_insert_attachment( $attachment, $file_loc );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            /*extra work for updating post status inherit to wp-erp-rec*/
            $wpdb->update(
                $wpdb->prefix . 'posts',
                array( 'post_status' => 'erp_hr_rec' ),
                array( 'ID' => $attach_id ),
                array( '%s' ),
                array( '%d' )
            );

            do_action( 'doc_after_upload_file', $attach_id, $attach_data, $post_id );
            return array( 'success' => true, 'file_id' => $attach_id );
        }

        return array( 'success' => false, 'error' => $uploaded_file['error'] );
    }

    /**
     * Get an attachment file
     *
     * @param int $attachment_id
     *
     * @return array
     */
    public function get_file( $attachment_id ) {
        $file = get_post( $attachment_id );

        if ( $file ) {
            $response = array(
                'id'          => $attachment_id,
                //'name'        => get_the_title($attachment_id),
                'name'        => basename( get_attached_file( $attachment_id ) ),
                'url'         => wp_get_attachment_url( $attachment_id ),
                'upload_time' => $file->post_date
            );

            if ( wp_attachment_is_image( $attachment_id ) ) {

                $thumb             = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
                $response['thumb'] = $thumb[0];
                $response['type']  = 'image';
            } else {
                $response['thumb'] = wp_mime_type_icon( $file->post_mime_type );
                $response['type']  = 'file';
            }

            return $response;
        }

        return false;
    }

    /**
     * Get parent directories to make tree template
     *
     * @since 0.1
     *
     * @return void
     */
    public function loadDirTree() {
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
    public function moveNow() {
        if ( isset( $_POST['employee_id'] ) ) {
            global $wpdb;
            $eid       = $_POST['employee_id'];
            $parent_id = $_POST['parent_id'];
            if ( empty( $eid ) || $eid == '' ) {
                $eid = get_current_user_id();
            }
            $new_parent_id   = is_numeric( $_POST['new_parent_id'] ) ? $_POST['new_parent_id'] : 0;
            $selectedDirFile = json_decode( stripslashes( $_POST['selectedDirFile'] ) );
            // check selected dir is new parent, if true then send error else do move operation
            if ( in_array( $new_parent_id, $selectedDirFile ) ) {
                $this->send_error( 'You cannot move a folder into itself' );
            }

            $where_dir_id = implode( ',', $selectedDirFile );
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

        foreach ( $emp_ids as $emp_id ) {
            foreach ( $selectedDirFile as $sdf ) {

                $sql = "INSERT INTO {$wpdb->prefix}erp_dir_file_share ( owner_id, shared_with_id, dir_file_id, source, eid_type, created_at, updated_at ) VALUES
                  ( %d, %d, %d, %s, %s, %s, %s ) ON DUPLICATE KEY UPDATE shared_with_id = %d AND dir_file_id = %d AND owner_id = %d";
                $sql = $wpdb->prepare( $sql, $param['owner_id'], $emp_id, $sdf, 'local', 'employee', current_time( 'Y-m-d H:i:s' ), current_time( 'Y-m-d H:i:s' ), $emp_id, $sdf, $param['owner_id'] );
                $wpdb->query( $sql );
            }
        }
        wp_send_json_success( esc_html__( 'Successfully shared.', 'erp-pro' ) );
    }

    /**
     * Get shared files & folders
     *
     * @since 1.3.1
     *
     * @return void
     */
    public function get_share_files_folders() {
        global $wpdb;
        $emp_id = ( isset( $_POST['emp_id'] ) && ! empty( $_POST['emp_id'] ) ) ? sanitize_text_field( wp_unslash( $_POST['emp_id'] ) ) : get_current_user_id();

        $sql = "SELECT
                  dfr.dir_id, dfr.eid, dfr.attachment_id, dfr.is_dir, dfr.dir_name, dfr.created_at, DATE_FORMAT(dfr.updated_at, '%Y-%m-%d %h:%i %p') as updated_at, dfr.created_by as created_by
                FROM {$wpdb->prefix}erp_dir_file_share as dfs
                LEFT JOIN {$wpdb->prefix}erp_employee_dir_file_relationship as dfr
                  ON dfr.eid = dfs.owner_id AND
                     dfr.dir_id = dfs.dir_file_id
                WHERE dfs.shared_with_id = {$emp_id}";

        $udata = $wpdb->get_results( $sql );

        $user_file_data = [];
        $user_dir_data = [];


        foreach ( $udata as $ufd ) {

            $get_user_data = get_userdata( $ufd->created_by );

            if( $ufd->is_dir == 0 ) {
                $user_file_data[] = array(
                    'user_nicename'   => $get_user_data->data->user_nicename,
                    'user_id'         => $ufd->created_by,
                    'user_link'       => get_edit_profile_url( $ufd->created_by ),
                    'dir_id'          => $ufd->dir_id,
                    'eid'             => $ufd->eid,
                    'is_dir'          => $ufd->is_dir,
                    'attactment_url'  => wp_get_attachment_url( $ufd->attachment_id ),
                    'attachment_type' => get_post_mime_type( $ufd->attachment_id ),
                    'dir_file_name'   => $ufd->dir_name,
                    'file_size'       => number_format( filesize( get_attached_file( $ufd->dir_id ) ) / 1024, 2 ) . ' KB',
                    'updated_at'      => date( 'Y-m-d g:i A', strtotime( $ufd->updated_at ) )
                );
            } else {
                $user_dir_data[] = array(
                    'dir_file_name' => $ufd->dir_name,
                    'dir_id'        => $ufd->dir_id,
                    'eid'           => $ufd->eid,
                    'is_dir'        => $ufd->is_dir,
                    'updated_at'    => date( 'Y-m-d g:i A', strtotime( $ufd->updated_at ) ),
                    'user_link'     => get_edit_profile_url( $ufd->dir_id ),
                    'user_id'       => $ufd->created_by,
                    'user_nicename' => $get_user_data->data->user_nicename
                );
            }
        }

        wp_send_json_success( [
            'folders' => $user_dir_data,
            'files'   => $user_file_data,
        ] );
    }


    /*********** Below code refactor is on going **********/

    public function get_files_folders() {
        //Check valid nonce
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'doc_form_builder_nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp-pro' ) );
        }

        $dir_id         = ( isset( $_POST['dir_id'] ) && ! empty( $_POST['dir_id'] ) ) ? sanitize_text_field( $_POST['dir_id'] ) : 0;
        $emp_id         = ( isset( $_POST['employee_id'] ) && ! empty( $_POST['employee_id'] ) ) ? intval( $_POST['employee_id'] ) : get_current_user_id();
        $source         = ( isset( $_POST['source'] ) && ! empty( $_POST['source'] ) ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : 'owned_by_me';
        $search_string  = ( isset( $_POST['search_string'] ) && ! empty( $_POST['search_string'] ) ) ? sanitize_text_field( wp_unslash( $_POST['search_string'] ) ) : null;
        $direct_link    = ( isset( $_POST['direct_link'] ) && ! empty( $_POST['direct_link'] ) ) ? sanitize_text_field( wp_unslash( $_POST['direct_link'] ) ) : null;
        $eid_type       = ( strpos( $_SERVER['HTTP_REFERER'], 'contact' ) ) ? 'contact' : 'employee';
        $response_data  = [];

        if( $search_string != null ) {
            if( $source == 'owned_by_me' ) {

            }
            if( $source == 'shared_with_me' ) {

            }
            if( $source == 'my_dropbox' ) {

            }
            $this->send_success( $response_data );
        }



        if( ( $source == 'owned_by_me' && $direct_link == null ) ) {
            $response_data['directory'] = get_dirInfo( $emp_id, $dir_id, $eid_type );
            $response_data['files']     = get_fileInfo( $emp_id, $dir_id, $eid_type );
        }

        if( $source == 'shared_with_me' && $direct_link == null ) {
            $response_data = get_local_shared_files_folders( $emp_id );
        }

        if( $source == 'my_dropbox' && $direct_link == null ) {

            $user_meta  = get_userdata( $emp_id );
            $user_roles = $user_meta->roles; //array of roles the user is part of.

            if( in_array( 'erp_hr_manager' , $user_roles ) ) {
                $path = '';
            } else {
                $path = '/employees/' . $user_meta->data->user_email;
            }

            $results        = dropbox_api_call( "files/list_folder", [ 'path' => $path ] );

            if( !isset( $results['error'] ) ) {
                $response_data = get_dropbox_process_data($results, $emp_id, $dir_id, $response_data);
            } else {
                $response_data['directory'] = [];
                $response_data['files']     = [];
            }
        }

        if( ( $source == 'owned_by_me' || $source == 'shared_with_me' ) && $direct_link == 'yes' ) {
            $response_data['directory'] = get_dirInfo( $emp_id, $dir_id, $eid_type );
            $response_data['files']     = get_fileInfo( $emp_id, $dir_id, $eid_type );
        }

        if( $source == 'my_dropbox' && $direct_link == 'yes' ) {

            $user_meta  = get_userdata( $emp_id );
            $user_roles = $user_meta->roles; //array of roles the user is part of.

            if( $dir_id == '0' ){
                if( in_array( 'erp_hr_manager' , $user_roles ) ) {
                    $path = '';
                } else {
                    $path = '/employees/' . $user_meta->data->user_email;
                }
            } else {
                $path = $dir_id;
            }
            $results        = dropbox_api_call( "files/list_folder", [ 'path' => $path ] );
            $response_data  = get_dropbox_process_data( $results, $emp_id, $dir_id, $response_data );
        }

        $this->send_success( $response_data );

    }


    public function sync_employees_dropbox() {
        //Check valid nonce
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'erp-nonce' ) ) {
            $this->send_error( __( 'Error: Nonce verification failed', 'erp-pro' ) );
        }

        $user_folders = array();
        foreach ( get_users() as $user ) {
            $user_folders[] = "/employees/" . $user->data->user_email;
        }

        $results        = dropbox_api_call( "files/create_folder_batch", [ 'paths' => $user_folders ] );
        $this->send_success( $results );
    }

}
