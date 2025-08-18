<?php
/**
 * Get all file
 *
 * @param $args array
 *
 * @return array
 */
function erp_rec_get_all_file( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'id',
        'order'   => 'ASC',
    );

    $args      = wp_parse_args( $args, $defaults );
    $cache_key = 'file-all';
    $items     = wp_cache_get( $cache_key, 'wp-erp-rec' );

    if ( false === $items ) {
        $items = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'erp_employee_files ORDER BY ' . $args['orderby'] . ' ' . $args['order'] . ' LIMIT ' . $args['offset'] . ', ' . $args['number'] );

        wp_cache_set( $cache_key, $items, 'wp-erp-rec' );
    }

    return $items;
}

/**
 * Fetch all file from database
 *
 * @return array
 */
function erp_rec_get_file_count() {
    global $wpdb;

    return (int)$wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'erp_employee_files' );
}

/**
 * Fetch a single file from database
 *
 * @param int $id
 *
 * @return array
 */
function erp_rec_get_file( $id = 0 ) {
    global $wpdb;

    return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'erp_employee_files WHERE id = %d', $id ) );
}

/**
 * file upload field helper
 *
 * Generates markup for ajax file upload list and prints attached files.
 *
 * @since 0.1
 * @param int   $id comment ID. used for unique edit comment form pick file ID
 * @param array $files attached files
 */
function erp_rec_upload_field( $id, $files = array() ) {
    $id = $id ? '-' . $id : '';
    ?>
<div id="doc-upload-container<?php echo $id; ?>">
    <div class="doc-upload-filelist">
    <?php if ( $files ) { ?><?php foreach ( $files as $file ) {
        $delete   = sprintf( '<a href="#" data-id="%d" class="doc-delete-file button">%s</a>', $file['id'], __( 'Delete File', 'wp-erp-rec' ) );
        $hidden   = sprintf( '<input type="hidden" name="doc_attachment[]" value="%d" />', $file['id'] );
        $file_url = sprintf( '<a href="%1$s" target="_blank"><img src="%2$s" alt="%3$s" /></a>', $file['url'], $file['thumb'], esc_attr( $file['name'] ) );

        //$html = '<div class="doc-uploaded-item">' . $file_url . ' ' . $delete . $hidden . '</div>';
        $html = '<li>
                                <div class="filename-col">
                                    <div class="doc-uploaded-item">' . $file_url . ' ' . $hidden . '</div>
                                </div>
                                <div class="modified">time goes here</div>
                             </li>';
        echo $html;
    } ?><?php } ?>
    </div><?php printf( __( '<a class="fileUpload button button-primary" id="doc-upload-pickfiles%s" href="#"><span class="fa fa-lg fa-upload"></span>Upload</a>', 'wp-erp-rec' ), $id ); ?>
    </div><?php
}

/*
 * file uploader
 * para file array
 * return array
 */
function handle_file_upload( $upload_data ) {
    global $wpdb;
    $uploaded_file = wp_handle_upload( $upload_data, array( 'test_form' => false ) );
    // If the wp_handle_upload call returned a local path for the image
    if ( isset( $uploaded_file['file'] ) ) {
        $file_loc    = $uploaded_file['file'];
        $file_name   = basename( $upload_data['name'] );
        $file_type   = wp_check_filetype( $file_name );
        $attachment  = array(
            'post_mime_type' => $file_type['type'],
            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
            'post_content'   => '',
            'post_status'    => 'erp_hr_rec'
        );
        $attach_id   = wp_insert_attachment( $attachment, $file_loc );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        $wpdb->update(
            $wpdb->prefix . 'posts',
            array( 'post_status' => 'erp_hr_rec' ),
            array( 'ID' => $attach_id ),
            array( '%s' ),
            array( '%d' )
        );
        return array( 'success' => true, 'attach_id' => $attach_id );
    }
    return array( 'success' => false, 'error' => $uploaded_file['error'] );
}

function buildtree( $src_arr, $parent_id = 0, $tree = array() ) {
    foreach ( $src_arr as $idx => $row ) {
        if ( $row['parent_id'] == $parent_id ) {
            foreach ( $row as $k => $v )
                $tree[$row['id']][$k] = $v;
            unset( $src_arr[$idx] );
            $tree[$row['id']]['children'] = array_values( buildtree( $src_arr, $row['id'] ) );
        }
    }
    ksort( $tree );
    return $tree;
}

/*
 * update parent folder timestamp when you are updating something inside a folder
 * para parent_id, employee id
 * return void
 */
function update_parent_folder_timestamp( $parent_id, $employee_id ) {
    global $wpdb;
    $wpdb->update(
        $wpdb->prefix . 'erp_employee_dir_file_relationship',
        array( 'attachment_id' => '1' ),
        array( 'dir_id' => $parent_id, 'eid' => $employee_id ),
        array( '%s' ),
        array( '%d', '%d' )
    );
    $wpdb->update(
        $wpdb->prefix . 'erp_employee_dir_file_relationship',
        array( 'attachment_id' => '0' ),
        array( 'dir_id' => $parent_id, 'eid' => $employee_id ),
        array( '%s' ),
        array( '%d', '%d' )
    );
}

/*
 * get all child nodes
 *
 * return all child to delete
 */
function get_child_dir_ids( $parent_id, &$child_kit ) {
    global $wpdb;

    $query  = "SELECT dir_id FROM {$wpdb->prefix}erp_employee_dir_file_relationship WHERE parent_id=" . $parent_id;
    $dir_id = $wpdb->get_var( $query );
    if ( is_null( $dir_id ) ) {
        return $child_kit;
    } else {
        $child_kit[] = $dir_id;
        get_child_dir_ids( $dir_id, $child_kit );
    }
}

/**
 * Get dir info
 * @param  integer  $employee_id Employee id for get document
 *
 * @param  integer $parent_id
 * @return array
 */
function erp_doc_get_dir_info( $employee_id, $parent_id = 0 ) {

    if ( $employee_id < 1 ) {
        return;
    }

    global $wpdb;
    $current_user_id = get_current_user_id();

    $query = "SELECT users.ID, users.user_nicename, dir.dir_id, dir.eid, dir.is_dir, dir.dir_name, dir.created_at, DATE_FORMAT(dir.updated_at, '%Y-%m-%d %h:%i %p') as updated_at
        FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
        LEFT JOIN {$wpdb->prefix}users as users
        ON dir.created_by=users.ID
        WHERE dir.eid='" . $employee_id . "' AND dir.parent_id='" . $parent_id . "' AND dir.is_dir=1";

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

    return $user_file_data;
}

/**
 * Get file info
 *
 * @param  integer  $employee_id
 *
 * @param  integer $parent_id
 * @return array
 */
function erp_doc_get_file_info( $employee_id, $parent_id = 0 ) {
    if ( $employee_id < 1 ) {
        return;
    }
    global $wpdb;

    $eid             = isset( $_GET['employee_id'] ) || $_GET['employee_id'] != '' ? $_GET['employee_id'] : 0;
    $parent_id       = ( isset( $_GET['dir_id'] ) ? $_GET['dir_id'] : 0 );
    $current_user_id = get_current_user_id();

    $query = "SELECT users.ID, users.user_nicename, file.dir_id, file.eid, file.is_dir, file.dir_name, file.attachment_id, file.updated_at
        FROM {$wpdb->prefix}erp_employee_dir_file_relationship as file
        LEFT JOIN {$wpdb->prefix}users as users
        ON file.created_by=users.ID
        WHERE file.eid='" . $employee_id . "' AND file.parent_id='" . $parent_id . "' AND file.is_dir=0";
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

    return $user_file_data;
}

/**
 * Create Directory
 *
 * @param  integer  $employee_id
 * @param  integer $parent_id
 * @param  string  $dir_name
 * @return object
 */
function erp_doc_create_dir( $employee_id, $parent_id = 0, $dir_name ) {
    global $wpdb;
    $dir_exist_result = erp_doc_check_duplicate_dir( $employee_id, $parent_id, $dir_name );

    $response = '';
    if ( $dir_exist_result == true ) {
        $response = 'Folder name already exits';
    } else {
        $current_user_id = get_current_user_id();
        // get last dir id by employee id
        $query = "SELECT dir.dir_id
        FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
        WHERE dir.eid='" . $employee_id . "'
        ORDER BY dir.dir_id DESC LIMIT 1";

        $last_dir_id = $wpdb->get_var( $query );

        //insert dir name
        $data = array(
            'eid'        => $employee_id,
            'dir_id'     => $last_dir_id + 1,
            'dir_name'   => $dir_name,
            'parent_id'  => $parent_id,
            'is_dir'     => 1,
            'created_by' => get_current_user_id(),
            'created_at' => date( 'Y-m-d H:i:s', time() )
        );

        $format = array(
            '%d',
            '%d',
            '%s',
            '%d',
            '%d',
            '%d',
            '%s'
        );

        $wpdb->insert( $wpdb->prefix . 'erp_employee_dir_file_relationship', $data, $format );
        if ( $parent_id != 0 ) { // if parent is not 0 then update the parent update timestamp
            update_parent_folder_timestamp( $parent_id, $employee_id );
        }
        do_action( 'erp_doc_dir_or_file_new', $data );
        $response = 'Folder created successfully';
    }

    return $response;
}

/**
 * Check duplicate directory
 *
 * @param  integer  $employee_id
 *
 * @param  integer $parent_id
 *
 * @param  string  $dir_name
 * @return bool
 */
function erp_doc_check_duplicate_dir( $employee_id, $parent_id = 0, $dir_name, $is_dir = 1 ) {
    global $wpdb;
    $current_user_id = get_current_user_id();

    $query = "SELECT *
    FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
    WHERE dir.eid='" . $employee_id . "' AND dir.parent_id='" . $parent_id . "' AND dir.dir_name='" . $dir_name . "' AND dir.is_dir=".$is_dir;

    if ( count( $wpdb->get_results( $query, ARRAY_A ) ) > 0 ) {
        return true;
    }

    return false;
}

/**
 * Rename document folder name or file  name
 *
 * @param  integer  $employee_id
 *
 * @param  integer $parent_id
 *
 * @param  integer  $target_id
 *
 * @param  string  $dir_name
 *
 * @param  string  $type
 *
 * @return void
 */
function rename_dir_file( $employee_id, $parent_id = 0, $target_id, $dir_name, $type ) {
    global $wpdb;

    $type = ( $type == 'folder' ) ? 1 : 0;

    $exist = erp_doc_check_duplicate_dir( $employee_id, $parent_id, $dir_name, $type );

    if ( $exist ) {
        return 'Given name already exist';
    }

    $data = array(
        'dir_name'   => $dir_name,
        'created_by' => get_current_user_id()
    );

    $where   = array(
        'eid'    => $employee_id,
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
        update_parent_folder_timestamp( $parent_id, $employee_id );
    }

    return "Successfully Renamed";
}

/**
 * Delete directory or file
 *
 * @param  [type] $employee_id
 *
 * @param  [type] $parent_id
 *
 * @param  array  $selected
 *
 * @return [type]
 */
function erp_doc_delete_dir_file( $user_id, $target_id ) {
    global $wpdb;
    if ( $user_id < 1 ) {
        return;
    }

    $attachment_id = $wpdb->get_var( "SELECT attachment_id FROM " . $wpdb->prefix . 'erp_employee_dir_file_relationship' . " WHERE dir_id={$target_id} AND is_dir=0" );
    wp_delete_attachment( $attachment_id );



    do_action( 'erp_doc_dir_or_file_delete', $target_id, $user_id );

    $wpdb->delete(
        $wpdb->prefix . 'erp_employee_dir_file_relationship',
        array( 'dir_id' => $target_id, 'eid' => $user_id ),
        array( '%d', '%d' )
    );
    $wpdb->delete(
        $wpdb->prefix . 'erp_employee_dir_file_relationship',
        array( 'parent_id' => $target_id, 'eid' => $user_id ),
        array( '%d', '%d' )
    );
}

/**
 * Move file or folder
 *
 * @param  integer $employee_id
 *
 * @param  integer $parent_id
 *
 * @param  array  $selected
 *
 * @return void
 */
function erp_doc_move_dir_file( $employee_id, $parent_id, $selected = [] ) {
    global $wpdb;
    // check selected dir is new parent, if true then send error else do move operation
    if ( in_array( $parent_id, $selected ) ) {
       return 'You cannot move a folder into itself';
    }

    $selected = implode(',', $selected);

    // do_action( 'erp_doc_dir_or_file_move', $where_dir_id, $new_parent_id, $parent_id, $employee_id );
    $sql = "UPDATE {$wpdb->prefix}erp_employee_dir_file_relationship
        SET parent_id={$parent_id}
        WHERE eid={$employee_id} AND dir_id IN ($selected)";

    $wpdb->query( $sql );

    if ( $parent_id != 0 ) {
        update_parent_folder_timestamp( $parent_id, $employee_id );
    }
}

/**
 * Search file or folder
 *
 * @param  integer $employee_id
 *
 * @param  string $search_key
 *
 * @return array
 */
function erp_doc_search_dir_file( $employee_id, $search_key = '' ) {
    global $wpdb;

    if ( empty( $search_key ) ) {
        return;
    }

    $current_user_id = get_current_user_id();

    $query = "SELECT users.ID, users.user_nicename, dir.dir_id, dir.eid, dir.is_dir, dir.dir_name, dir.created_at, DATE_FORMAT(dir.updated_at, '%Y-%m-%d %h:%i %p') as updated_at
        FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
        LEFT JOIN {$wpdb->prefix}users as users
        ON dir.created_by=users.ID
        WHERE dir.eid='" . $employee_id . "' AND dir.dir_name LIKE'%" . $search_key . "%'";

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

    return $user_file_data;
}

/**
 * Get all document of en employee
 *
 * @param  integer $employee_id
 *
 * @return array
 */
function erp_doc_load_dir_file( $employee_id ) {
    global $wpdb;
    $q  = "SELECT dir.dir_id as id, dir.dir_name as text, dir.parent_id as parent_id
            FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
            WHERE dir.eid='" . $employee_id . "' AND dir.is_dir=1";
    $ad = array_values( buildtree( $wpdb->get_results( $q, ARRAY_A ) ) );

    // insert the root as parent id 0 for the tree
    $withroot = array(
        'id'        => 0,
        'text'      => 'Home',
        'parent_id' => 0,
        'children'  => $ad
    );

    return $withroot;
}


/**
 * Get directory of en employee
 *
 * @param  integer $eid, integer $parent_id, string $eid_type
 *
 * @return array
 */
function get_dirInfo( $eid, $parent_id, $eid_type ) {
    global $wpdb;
    $query = "SELECT users.ID, users.user_nicename, dir.dir_id, dir.eid, dir.is_dir, dir.dir_name, dir.created_at, DATE_FORMAT(dir.updated_at, '%Y-%m-%d %h:%i %p') as updated_at
                FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
                LEFT JOIN {$wpdb->prefix}users as users
                ON dir.created_by=users.ID
                WHERE dir.eid='" . $eid . "' AND dir.parent_id='" . $parent_id . "' AND dir.is_dir=1 AND dir.eid_type='{$eid_type}'";

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
    return $user_file_data;
}

/**
 * Get File of en employee
 *
 * @param  integer $eid, integer $parent_id, string $eid_type
 *
 * @return array
 */
function get_fileInfo( $eid, $parent_id, $eid_type ) {
    global $wpdb;
    $query = "SELECT users.ID, users.user_nicename, file.dir_id, file.eid, file.is_dir, file.dir_name, file.attachment_id, file.updated_at
                FROM {$wpdb->prefix}erp_employee_dir_file_relationship as file
                LEFT JOIN {$wpdb->prefix}users as users
                ON file.created_by=users.ID
                WHERE file.eid='" . $eid . "' AND file.parent_id='" . $parent_id . "' AND file.is_dir=0 AND file.eid_type='{$eid_type}'";
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
    return $user_file_data;
}


/**
* Get File of en employee
*
 * @param  integer $eid, integer $parent_id, string $eid_type
*
 * @return array
 */
function dropbox_api_call( $url, $body ) {

    $access_token = get_option( "dropbox_access_token" );

    $option = array(
        'method'      => 'POST',
        'timeout'     => 300,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking'    => true,
        'headers'     => [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $access_token
        ],
        'body'        => json_encode( $body )
    );

    $api_base     = "https://api.dropboxapi.com/2/" . $url;
    $response     = wp_remote_post( $api_base, $option );
    $responceData = json_decode(wp_remote_retrieve_body($response), TRUE);

    return $responceData;

}

/************* Refactor codes below ****************/

function get_local_shared_files_folders( $emp_id ) {
    global $wpdb;

    $cache_key          = "erp-get-shared-by-emp-$emp_id";
    $user_file_dir_data = wp_cache_get( $cache_key, 'erp-document' );

    if ( false === $user_file_dir_data ) {
        $sql = "SELECT
                    dfr.dir_id, dfr.eid, dfr.attachment_id, dfr.is_dir, dfr.dir_name, dfr.created_at, DATE_FORMAT(dfr.updated_at, '%Y-%m-%d %h:%i %p') as updated_at, dfr.created_by as created_by
                    FROM {$wpdb->prefix}erp_dir_file_share as dfs
                    LEFT JOIN {$wpdb->prefix}erp_employee_dir_file_relationship as dfr
                    ON dfr.eid = dfs.owner_id AND
                        dfr.dir_id = dfs.dir_file_id
                    WHERE dfs.shared_with_id = {$emp_id}";

        $udata = $wpdb->get_results( $sql );

        $user_file_data = [];
        $user_dir_data  = [];

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

        $user_file_dir_data = [
            'directory' => $user_dir_data,
            'files'     => $user_file_data,
        ];

        wp_cache_set( $cache_key, $user_file_dir_data, 'erp-document' );
    }

    return $user_file_dir_data;
}

function getMimeType( $ext ){
    $ext = strtolower( $ext );
    if ( !( strpos( $ext, '.' ) !== false ) ) {
        $ext = '.' . $ext ;
    }
    switch ($ext) {
        case '.aac': $mime ='audio/aac'; break; // AAC audio
        case '.abw': $mime ='application/x-abiword'; break; // AbiWord document
        case '.arc': $mime ='application/octet-stream'; break; // Archive document (multiple files embedded)
        case '.avi': $mime ='video/x-msvideo'; break; // AVI: Audio Video Interleave
        case '.azw': $mime ='application/vnd.amazon.ebook'; break; // Amazon Kindle eBook format
        case '.bin': $mime ='application/octet-stream'; break; // Any kind of binary data
        case '.bmp': $mime ='image/bmp'; break; // Windows OS/2 Bitmap Graphics
        case '.bz': $mime ='application/x-bzip'; break; // BZip archive
        case '.bz2': $mime ='application/x-bzip2'; break; // BZip2 archive
        case '.csh': $mime ='application/x-csh'; break; // C-Shell script
        case '.css': $mime ='text/css'; break; // Cascading Style Sheets (CSS)
        case '.csv': $mime ='text/csv'; break; // Comma-separated values (CSV)
        case '.doc': $mime ='application/msword'; break; // Microsoft Word
        case '.docx': $mime ='application/vnd.openxmlformats-officedocument.wordprocessingml.document'; break; // Microsoft Word (OpenXML)
        case '.eot': $mime ='application/vnd.ms-fontobject'; break; // MS Embedded OpenType fonts
        case '.epub': $mime ='application/epub+zip'; break; // Electronic publication (EPUB)
        case '.gif': $mime ='image/gif'; break; // Graphics Interchange Format (GIF)
        case '.htm': $mime ='text/html'; break; // HyperText Markup Language (HTML)
        case '.html': $mime ='text/html'; break; // HyperText Markup Language (HTML)
        case '.ico': $mime ='image/x-icon'; break; // Icon format
        case '.ics': $mime ='text/calendar'; break; // iCalendar format
        case '.jar': $mime ='application/java-archive'; break; // Java Archive (JAR)
        case '.jpeg': $mime ='image/jpeg'; break; // JPEG images
        case '.jpg': $mime ='image/jpeg'; break; // JPEG images
        case '.js': $mime ='application/javascript'; break; // JavaScript (IANA Specification) (RFC 4329 Section 8.2)
        case '.json': $mime ='application/json'; break; // JSON format
        case '.mid': $mime ='audio/midi audio/x-midi'; break; // Musical Instrument Digital Interface (MIDI)
        case '.midi': $mime ='audio/midi audio/x-midi'; break; // Musical Instrument Digital Interface (MIDI)
        case '.mpeg': $mime ='video/mpeg'; break; // MPEG Video
        case '.mpkg': $mime ='application/vnd.apple.installer+xml'; break; // Apple Installer Package
        case '.odp': $mime ='application/vnd.oasis.opendocument.presentation'; break; // OpenDocument presentation document
        case '.ods': $mime ='application/vnd.oasis.opendocument.spreadsheet'; break; // OpenDocument spreadsheet document
        case '.odt': $mime ='application/vnd.oasis.opendocument.text'; break; // OpenDocument text document
        case '.oga': $mime ='audio/ogg'; break; // OGG audio
        case '.ogv': $mime ='video/ogg'; break; // OGG video
        case '.ogx': $mime ='application/ogg'; break; // OGG
        case '.otf': $mime ='font/otf'; break; // OpenType font
        case '.png': $mime ='image/png'; break; // Portable Network Graphics
        case '.pdf': $mime ='application/pdf'; break; // Adobe Portable Document Format (PDF)
        case '.ppt': $mime ='application/vnd.ms-powerpoint'; break; // Microsoft PowerPoint
        case '.pptx': $mime ='application/vnd.openxmlformats-officedocument.presentationml.presentation'; break; // Microsoft PowerPoint (OpenXML)
        case '.rar': $mime ='application/x-rar-compressed'; break; // RAR archive
        case '.rtf': $mime ='application/rtf'; break; // Rich Text Format (RTF)
        case '.sh': $mime ='application/x-sh'; break; // Bourne shell script
        case '.svg': $mime ='image/svg+xml'; break; // Scalable Vector Graphics (SVG)
        case '.swf': $mime ='application/x-shockwave-flash'; break; // Small web format (SWF) or Adobe Flash document
        case '.tar': $mime ='application/x-tar'; break; // Tape Archive (TAR)
        case '.tif': $mime ='image/tiff'; break; // Tagged Image File Format (TIFF)
        case '.tiff': $mime ='image/tiff'; break; // Tagged Image File Format (TIFF)
        case '.ts': $mime ='application/typescript'; break; // Typescript file
        case '.ttf': $mime ='font/ttf'; break; // TrueType Font
        case '.txt': $mime ='text/plain'; break; // Text, (generally ASCII or ISO 8859-n)
        case '.vsd': $mime ='application/vnd.visio'; break; // Microsoft Visio
        case '.wav': $mime ='audio/wav'; break; // Waveform Audio Format
        case '.weba': $mime ='audio/webm'; break; // WEBM audio
        case '.webm': $mime ='video/webm'; break; // WEBM video
        case '.webp': $mime ='image/webp'; break; // WEBP image
        case '.woff': $mime ='font/woff'; break; // Web Open Font Format (WOFF)
        case '.woff2': $mime ='font/woff2'; break; // Web Open Font Format (WOFF)
        case '.xhtml': $mime ='application/xhtml+xml'; break; // XHTML
        case '.xls': $mime ='application/vnd.ms-excel'; break; // Microsoft Excel
        case '.xlsx': $mime ='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'; break; // Microsoft Excel (OpenXML)
        case '.xml': $mime ='application/xml'; break; // XML
        case '.xul': $mime ='application/vnd.mozilla.xul+xml'; break; // XUL
        case '.zip': $mime ='application/zip'; break; // ZIP archive
        case '.3gp': $mime ='video/3gpp'; break; // 3GPP audio/video container
        case '.3g2': $mime ='video/3gpp2'; break; // 3GPP2 audio/video container
        case '.7z': $mime ='application/x-7z-compressed'; break; // 7-zip archive
        default: $mime = 'application/octet-stream' ; // general purpose MIME-type
    }
    return $mime ;

}

function get_dropbox_process_data( $results, $emp_id, $dir_id, $response_data ) {

    $response_data['directory'] = [];
    $response_data['files'] = [];

    foreach ( $results['entries'] as $result ) {
        $get_user_data = get_userdata( $emp_id );
        if( $result['.tag'] == 'folder' ){
            $response_data['directory'][] = array(
                'dir_file_name' => $result['name'],
                'dir_id'        => $result['path_display'],
                'eid'           => $emp_id,
                'is_dir'        => 1,
                'updated_at'    => '-',
                'user_link'     => get_edit_profile_url( $emp_id ),
                'user_id'       => $emp_id,
                'user_nicename' => $get_user_data->data->user_nicename
            );
        }
        if( $result['.tag'] == 'file' ){
            $response_data['files'][] = array(
                'user_nicename'   => $get_user_data->data->user_nicename,
                'user_id'         => $emp_id,
                'user_link'       => get_edit_profile_url( $emp_id ),
                'dir_id'          => $result['path_display'],
                'eid'             => $emp_id,
                'is_dir'          => 0,
                'attactment_url'  => '#',
                'attachment_type' => getMimeType ( pathinfo( $result['name'], PATHINFO_EXTENSION ) ),
                'dir_file_name'   => $result['name'],
                'file_size'       => number_format(  $result['size'] / 1024, 2 ) . ' KB',
                'updated_at'      => date( 'Y-m-d g:i A', strtotime( $result['client_modified'] ) )
            );
        }
    }

    return $response_data ;
}

function create_directory( $eid, $parent_id, $dir_name, $eid_type ) {
    global $wpdb;

    $query = "SELECT dir.dir_id
                FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
                WHERE dir.eid='" . $eid . "'
                ORDER BY dir.dir_id DESC LIMIT 1";

    $last_dir_id = $wpdb->get_var( $query );

    //insert dir name
    $data = array(
        'eid'        => $eid,
        'dir_id'     => $last_dir_id + 1,
        'dir_name'   => $dir_name,
        'parent_id'  => $parent_id,
        'is_dir'     => 1,
        'eid_type'   => $eid_type,
        'created_by' => get_current_user_id(),
        'created_at' => date( 'Y-m-d H:i:s', time() )
    );

    $format = array(
        '%d',
        '%d',
        '%s',
        '%d',
        '%d',
        '%s',
        '%d',
        '%s'
    );

    $wpdb->insert( $wpdb->prefix . 'erp_employee_dir_file_relationship', $data, $format );
    if ( $parent_id != 0 ) { // if parent is not 0 then update the parent update timestamp
        update_parent_folder_timestamp( $parent_id, $eid );
    }
    do_action( 'erp_doc_dir_or_file_new', $data );
    //$this->send_success( __( 'Folder created successfully', 'erp-pro' ) );
    return __( 'Folder created successfully', 'erp-pro' );
}

function dropbox_file_upload( $url, $arg, $files ) {

    $file       = @fopen( $files['doc_attachment']['tmp_name'], 'r' );
    $file_size  = $files['doc_attachment']['size'];
    $file_data  = fread( $file, $file_size );

    $access_token = get_option( "dropbox_access_token" );

    $option = array(
        'method'      => 'POST',
        'timeout'     => 300,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking'    => true,
        'headers'     => [
            'Content-Type'  => 'application/octet-stream',
            'Authorization' => 'Bearer ' . $access_token,
            'Dropbox-API-Arg' => json_encode( $arg )
        ],
        'body'        => $file_data
    );

    $api_base     = "https://content.dropboxapi.com/2/" . $url;
    $response     = wp_remote_post( $api_base, $option );
    $responceData = json_decode(wp_remote_retrieve_body($response), TRUE);

    return $responceData;

}

?>
