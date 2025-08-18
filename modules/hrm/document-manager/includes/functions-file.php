<?php
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

/**
 * Get directory of en employee
 *
 * @param  integer $eid, integer $parent_id, string $eid_type
 *
 * @return array
 */
function get_dirInfo( $eid, $parent_id, $eid_type ) {
    global $wpdb;

    $cache_key      = "erp-get-dirs-emp-$eid-dir-$parent_id";
    $user_file_data = wp_cache_get( $cache_key, 'erp-document' );

    if ( false === $user_file_data ) {
        $query = "SELECT users.ID, users.user_nicename, dir.dir_id, dir.eid, dir.is_dir, dir.dir_name, dir.created_at, DATE_FORMAT(dir.updated_at, '%Y-%m-%d %h:%i %p') as updated_at
                FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
                LEFT JOIN {$wpdb->prefix}users as users
                ON dir.created_by=users.ID
                WHERE dir.eid='" . $eid . "' AND dir.parent_id='" . $parent_id . "' AND dir.is_dir=1 AND dir.eid_type='{$eid_type}'";

        $udata = $wpdb->get_results( $query, ARRAY_A );

        $user_file_data = [];

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
                'user_nicename' => $ufd['user_nicename'],
                'source'        => 'local',
            );
        }

        wp_cache_set( $cache_key, $user_file_data, 'erp-document' );
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

    $cache_key      = "erp-get-files-emp-$eid-dir-$parent_id";
    $user_file_data = wp_cache_get( $cache_key, 'erp-document' );

    if ( false === $user_file_data ) {
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
                'updated_at'      => date( 'Y-m-d g:i A', strtotime( $ufd['updated_at'] ) ),
                'source'        => 'local',
            );
        }

        wp_cache_set( $cache_key, $user_file_data, 'erp-document' );
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
function dropbox_api_call( $url, $body, $token = null ) {
    $response_data = [];

    if ( $token == null ) {
        $access_token = erp_dropbox_get_option( "dropbox_access_token" );
    } else {
        $access_token = $token;
    }

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

    error_log( 'access token: ' . $access_token );

    $api_base     = "https://api.dropboxapi.com/2/" . $url;
    $response     = wp_remote_post( $api_base, $option );

    if ( (int) $response['response']['code'] !== 200 ) {
        $response_data['error'] = [
            'status'  => $response['response']['code'],
            'message' => $response['response']['message'],
            'body'    => $response['body'],
        ];
    } else {
        $response_data = json_decode( wp_remote_retrieve_body( $response ), true );
    }

    return $response_data;
}

/**
 * Get local shared File of en employee
 *
 * @param  integer $eid
 *
 * @return array
 */
function get_local_shared_files_folders( $emp_id ) {
    global $wpdb;

    $last_changed_shared = erp_cache_get_last_changed( 'hrm', "shared-documents", 'erp-document' );
    $cache_key           = "erp-get-shared-by-emp-$emp_id" . "-$last_changed_shared";
    $user_file_dir_data  = wp_cache_get( $cache_key, 'erp-document' );

    if ( false === $user_file_dir_data ) {
        $sql = "SELECT
                    dfr.dir_id, dfr.eid, dfr.attachment_id, dfr.is_dir, dfr.dir_name, dfr.created_at, DATE_FORMAT(dfr.updated_at, '%Y-%m-%d %h:%i %p') as updated_at, dfr.created_by as created_by
                    FROM {$wpdb->prefix}erp_dir_file_share as dfs
                    LEFT JOIN {$wpdb->prefix}erp_employee_dir_file_relationship as dfr
                    ON dfr.eid = dfs.owner_id AND
                        dfr.dir_id = dfs.dir_file_id
                    WHERE dfs.shared_with_id = {$emp_id} AND dfs.source='local'";

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
                    'updated_at'      => date( 'Y-m-d g:i A', strtotime( $ufd->updated_at ) ),
                    'source'          => 'local',
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
                    'user_nicename' => $get_user_data->data->user_nicename,
                    'source'        => 'local',
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

/**
 * Get dropbox shared File of en employee
 *
 * @param  integer $eid
 *
 * @return array
 */
function get_dropbox_shared_files_folders( $emp_id ) {
    global $wpdb;

    $sql = "SELECT *
                FROM {$wpdb->prefix}erp_dir_file_share as dfs
                WHERE dfs.shared_with_id = {$emp_id} AND dfs.source='dropbox'";

    $udata = $wpdb->get_results( $sql );

    $path_details = [];

    foreach ( $udata as $udt ) {
        $path_details[] = unserialize( $udt->details ) ;
    }

    $user_file_data = [];
    $user_dir_data = [];


    foreach ( $path_details as $ufd ) {

        $ufd = ( object ) $ufd;

        if( $ufd->is_dir == 0 ) {
            $user_file_data[] = $ufd;
        } else {
            $user_dir_data[] = $ufd;
        }
    }

    return [
        'directory' => $user_dir_data,
        'files'     => $user_file_data,
    ];
}

/**
 * Get file mime type based on extension
 *
 * @param  integer $ext
 *
 * @return string
 */
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

/**
 * Get dropbox process data
 *
 * @param  $results, $emp_id, $dir_id, $response_data
 *
 * @return array
 */
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
                'user_nicename' => $get_user_data->data->user_nicename,
                'source'        => 'dropbox',
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
                'updated_at'      => date( 'Y-m-d g:i A', strtotime( $result['client_modified'] ) ),
                'source'          => 'dropbox',
            );
        }
    }

    return $response_data ;
}

/**
 * Get dropbox search process data
 *
 * @param  $results, $emp_id, $dir_id, $response_data
 *
 * @return array
 */
function get_dropbox_search_process_data( $results, $emp_id, $dir_id, $response_data ) {

    $response_data['directory'] = [];
    $response_data['files'] = [];

    foreach ( $results['matches'] as $result ) {
        $get_user_data = get_userdata( $emp_id );
        if( $result['metadata']['metadata']['.tag'] == 'folder' ){
            $response_data['directory'][] = array(
                'dir_file_name' => $result['metadata']['metadata']['name'],
                'dir_id'        => $result['metadata']['metadata']['path_display'],
                'eid'           => $emp_id,
                'is_dir'        => 1,
                'updated_at'    => '-',
                'user_link'     => get_edit_profile_url( $emp_id ),
                'user_id'       => $emp_id,
                'user_nicename' => $get_user_data->data->user_nicename
            );
        }
        if( $result['metadata']['metadata']['.tag'] == 'file' ){
            $response_data['files'][] = array(
                'user_nicename'   => $get_user_data->data->user_nicename,
                'user_id'         => $emp_id,
                'user_link'       => get_edit_profile_url( $emp_id ),
                'dir_id'          => $result['metadata']['metadata']['path_display'],
                'eid'             => $emp_id,
                'is_dir'          => 0,
                'attactment_url'  => '#',
                'attachment_type' => getMimeType ( pathinfo( $result['metadata']['metadata']['name'], PATHINFO_EXTENSION ) ),
                'dir_file_name'   => $result['metadata']['metadata']['name'],
                'file_size'       => number_format(  $result['metadata']['metadata']['size'] / 1024, 2 ) . ' KB',
                'updated_at'      => date( 'Y-m-d g:i A', strtotime( $result['metadata']['metadata']['client_modified'] ) )
            );
        }
    }
    return $response_data ;
}

/**
 * Get dropbox processed path
 *
 * @param  $results, $emp_id, $dir_id, $response_data
 *
 * @return string
 */
function get_dropbox_path( $emp_id, $dir_id ) {
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

    return $path;
}

/**
 * Get employee search files
 *
 * @param  $results, $emp_id, $dir_id, $response_data
 *
 * @return string
 */
function search_employee_owned_dir_files( $employee_id, $search_key = '' ) {
    global $wpdb;

    if ( empty( $search_key ) ) {
        return [
            'directory' => [],
            'files'     => [],
        ];
    }

    $last_changed    = erp_cache_get_last_changed( 'hrm', 'own-documents', 'erp-document' );
    $cache_key       = 'erp-get-documents-' . md5( $employee_id ) . "-$search_key: $last_changed";
    $owned_documents = wp_cache_get( $cache_key, 'erp-document' );

    if ( false === $owned_documents ) {
        $query = "SELECT dfr.dir_id, dfr.eid, dfr.attachment_id, dfr.is_dir, dfr.dir_name, dfr.created_at, DATE_FORMAT(dfr.updated_at, '%Y-%m-%d %h:%i %p') as updated_at, dfr.created_by as created_by
                FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dfr
                WHERE dfr.eid='" . $employee_id . "' AND dfr.dir_name LIKE'%" . $search_key . "%'";

        $udata = $wpdb->get_results( $query );

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

        $owned_documents = [
            'directory' => $user_dir_data,
            'files'     => $user_file_data,
        ];

        wp_cache_set( $cache_key, $owned_documents, 'erp-document' );
    }

    return $owned_documents;
}

/**
 * Search local shared File of an employee
 *
 * @param  integer $emp_id, $search_key
 *
 * @return array
 */
function search_local_shared_files_folders( $emp_id, $search_key = '' ) {
    global $wpdb;

    $sql = "SELECT
                  dfr.dir_id, dfr.eid, dfr.attachment_id, dfr.is_dir, dfr.dir_name, dfr.created_at, DATE_FORMAT(dfr.updated_at, '%Y-%m-%d %h:%i %p') as updated_at, dfr.created_by as created_by
                FROM {$wpdb->prefix}erp_dir_file_share as dfs
                LEFT JOIN {$wpdb->prefix}erp_employee_dir_file_relationship as dfr
                  ON dfr.eid = dfs.owner_id AND
                     dfr.dir_id = dfs.dir_file_id
                WHERE dfs.shared_with_id = {$emp_id}  AND dfr.dir_name LIKE '%{$search_key}%'";

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

    return [
        'directory' => $user_dir_data,
        'files'     => $user_file_data,
    ];
}

/**
 * check duplicate dir
 *
 * @return bool
 */
function check_duplicate_dir( $eid, $parent_id, $dir_name ) {
    global $wpdb;
    $current_user_id = get_current_user_id();
    $eid_type        = (strpos($_SERVER['HTTP_REFERER'], 'contact')) ? 'contact' : 'employee';

    $query = "SELECT *
            FROM {$wpdb->prefix}erp_employee_dir_file_relationship as dir
            WHERE dir.eid='" . $eid . "' AND dir.parent_id='" . $parent_id . "' AND dir.dir_name='" . $dir_name . "' AND dir.is_dir=1 AND dir.eid_type='{$eid_type}'";

    if (count($wpdb->get_results($query, ARRAY_A)) > 0) {
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
function check_duplicate_file( $eid, $parent_id, $dir_name ) {
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
 * check employee directory
 *
 * @return bool
 */
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

/**
 * Upload a file and insert as attachment
 *
 * @param int $post_id
 *
 * @return int|bool
 */
function upload_file( $post_id = 0 ) {
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
function get_file( $attachment_id ) {
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
 * Local file pload process
 *
 * @param int $attachment_id
 *
 * @return array
 */
function upload_local_file( $object_id, $employee_id, $parent_id ) {
    global $wpdb;
    $response        = upload_file( $object_id );
    $current_user_id = get_current_user_id();


    if ( $response['success'] ) {
        $file = get_file( $response['file_id'] );

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

        erp_documents_purge_cache( [ 'dir_id' => $parent_id, 'employee_id' => $employee_id ] );

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
}

/**
 * Update parent folder timestamp
 *
 * @param int $parent_id, $employee_id
 *
 * @return array
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

    erp_documents_purge_cache( [ 'dir_id' => $parent_id, 'employee_id' => $employee_id ] );
}

/**
 * Upload file to dropbox
 *
 * @param $url, $arg, $files
 *
 * @return array
 */
function dropbox_file_upload( $url, $arg, $files ) {

    $file       = @fopen( $files['doc_attachment']['tmp_name'], 'r' );
    $file_size  = $files['doc_attachment']['size'];
    $file_data  = fread( $file, $file_size );

    $access_token = erp_dropbox_get_option( "dropbox_access_token" );

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

/**
 * Rename local files OR directory
 *
 * @param $url, $arg, $files
 *
 * @return array
 */
function rename_local_file_directory( $eid, $parent_id, $dir_name, $type, $target_id ) {

        global $wpdb;
        if ( $type == 'folder' ) {
            $exist_result = check_duplicate_dir( $eid, $parent_id, $dir_name );
        } else {
            $exist_result = check_duplicate_file( $eid, $parent_id, $dir_name );
        }

        if ( $exist_result == true ) {
            return [
                'success' => false,
                'msg'     =>  __( 'Given name already exits!', 'erp-pro' )
            ];
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

            erp_documents_purge_cache( [ 'dir_id' => $parent_id, 'employee_id' => $eid ] );

            return [
                'success' => true,
                'msg'     =>  __( 'Renamed successfully', 'erp-pro' )
            ];
        }

}

/**
 * Delete local files OR directory
 *
 * @param $eid, $parent_id, $selected_dir_file_id
 *
 * @return array
 */
function delete_local_files_dir( $eid, $parent_id, $selected_dir_file_id ) {
        global $wpdb;

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

        erp_documents_purge_cache( [ 'dir_id' => $parent_id, 'employee_id' => $eid ] );

        return true;

}

/**
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
 * Build local file tree
 *
 * return array
 */
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


/**
 * Sending email notification to employees
 *
 * return array
 */
function send_email_notification( $emp_id ) {
    $dm_notification_email = wperp()->emailer->get_email( 'DmFileShareNotification' );

    if ( is_a( $dm_notification_email, '\WeDevs\ERP\Email' ) ) {
        $dm_notification_email->trigger( $emp_id );
    }
}
add_action( 'after_share_file_folder', 'send_email_notification' );

/**
 * Check enable OR disable file folder accessability
 *
 * return string
 */
function get_file_dir_options() {
    $options                = [];
    $is_dropbox_enable      = erp_dropbox_get_option( "enable_dropbox", "yes" );
    $is_local_dir__enable   = erp_dropbox_get_option( "enable_local_directory", "yes" );
    if ( $is_local_dir__enable == 'yes' ) {
        $options['owned_by_me'] = __( "Owned by me", "erp-document" ) ;
    }
    if ( $is_dropbox_enable == 'yes' ) {
        $options['my_dropbox'] = __( "My Dropbox", "erp-document" ) ;
    }

    $options['shared_with_me'] = __( "Shared with me", "erp-document" ) ;

    $option_str = '';

    foreach ( $options as $option_key => $option_value ) {
        $option_str .= '<option value="'. $option_key .'">' . $option_value . '</option>';
    }

    return $option_str;
}

/**
 * Delete shared files when deleting main dir files
 *
 * return string
 */
function delete_shared_dir_files ( $selected_dir_file_id, $eid  ) {
    global $wpdb;

    foreach ( $selected_dir_file_id as $sdfi ) {
        $wpdb->delete(
                $wpdb->prefix . 'erp_dir_file_share',
                array(
                    'owner_id'      => $eid,
                    'dir_file_id'   => $sdfi
                ),
                array(
                    '%d',
                    '%s'
                )
        );

        erp_documents_purge_cache( [ 'employee_id' => $eid, 'shared' => true ] );
    }
}

/**
 * Retrieves dropbox related option value
 *
 * @since 1.3.4
 *
 * @param string $option_name
 * @param string $default
 *
 * @return string
 */
function erp_dropbox_get_option( $option_name, $default = '' ) {
    return erp_get_option( $option_name, 'erp_integration_settings_erp-dm', $default );
}

add_action( 'delete_shared_dir_file', 'delete_shared_dir_files', 10, 2 );

/**
 * Purge cache data for Document addon
 *
 * Remove cache for Document addon
 *
 * @since 1.3.5
 *
 * @param array $args
 *
 * @return void
 */
function erp_documents_purge_cache( $args = [] ) {

    $group = 'erp-document';

    // If Employee ID and Directory ID found, delete files and directory from cache
    if ( isset( $args['employee_id'] ) && isset( $args['dir_id'] ) ) {
        wp_cache_delete( "erp-get-dirs-emp-" . $args['employee_id'] . "-dir-" . $args['dir_id'], $group );
        wp_cache_delete( "erp-get-files-emp-" . $args['employee_id'] . "-dir-" . $args['dir_id'], $group );
        erp_purge_cache( [ 'group' => $group, 'module' => 'hrm', 'list' => 'shared-documents,own-documents' ] );
    }

    // If Employee ID and shared=true found, then remove shared file/folders for this employee
    if( isset( $args['employee_id'] ) && isset( $args['shared'] ) && $args['shared'] ) {
        wp_cache_delete( "erp-get-shared-by-emp-" . $args['employee_id'], $group );
        erp_purge_cache( [ 'group' => $group, 'module' => 'hrm', 'list' => 'shared-documents,own-documents' ] );
    }

    if ( isset( $args['list'] ) ) {
        erp_purge_cache( [ 'group' => $group, 'module' => 'hrm', 'list' => $args['list'] ] );
    }

}
