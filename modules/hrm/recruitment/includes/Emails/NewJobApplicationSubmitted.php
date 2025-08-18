<?php
namespace WeDevs\Recruitment\Emails;

use WeDevs\ERP\Email;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * New Job Application
 */
class NewJobApplicationSubmitted extends Email {

    use Hooker;

    /**
     * Class constructor
     */
    function __construct() {
        $this->id          = 'new-job-application-submitted';
        $this->title       = __( 'New Job Application', 'erp-pro' );
        $this->description = __( 'New job application submitted.', 'erp-pro' );
        $this->subject     = __( 'New job application submitted', 'erp-pro' );
        $this->heading     = __( 'New Job Application', 'erp-pro' );

        $this->find = [
            'date'              => '{date}',
            'applicant-name'    => '{applicant_name}',
            'applicant-profile' => '{applicant_profile_link}',
            'position'          => '{position}',
            'position-frontend' => '{position_link}',
            'job-post-backend'  => '{job_post_backend}',
            'applicant-list'    => '{applicant_list}',
            'job-openings'      => '{all_jobs}',
            'more-info'         => '{all_content}',
        ];

        $this->action( 'erp_admin_field_' . $this->id . '_help_texts', 'replace_keys' );

        parent::__construct();
    }

    /**
     * Sets email header and body
     *
     * @return void
     */
    public function get_args() {
        return [
            'email_heading' => $this->heading,
            'email_body'    => wpautop( $this->get_option( 'body' ) ),
        ];
    }

    /**
     * Triggers the email
     *
     * @since 1.4.1
     *
     * @param array $data
     *
     * @return void
     */
    public function trigger( $data = [] ) {

        if ( empty( $data ) ) {
            return;
        }

        $this->recipient = $data['recipient'];
        $this->heading   = $this->get_option( 'heading', $this->heading );
        $this->subject   = $this->get_option( 'subject', $this->subject );

        $this->replace   = [
            'date'              => $data['date'],
            'applicant-name'    => $data['applicant_name'],
            'applicant-profile' => sprintf(
                                    '<a href="%s">%s</a>',
                                    admin_url( 'admin.php?page=erp-hr&section=recruitment&sub-section=applicant_detail&application_id=' . $data['applicant_id'] ), $data['applicant_name']
                                ),
            'position'          => $data['position'],
            'position-frontend' => sprintf(
                                    '<a href="%s">%s</a>',
                                    $data['job_post_url'],
                                    $data['position']
                                ),
            'job-post-backend'  => sprintf(
                                    '<a href="%s">%s</a>',
                                    admin_url( "post.php?post={$data['job_id']}&action=edit" ),
                                    __( 'Job Post (Backend)', 'erp' )
                                ),
            'applicant-list'    => sprintf(
                                    '<a href="%s">%s</a>',
                                    admin_url( 'admin.php?page=erp-hr&section=recruitment&sub-section=jobseeker_list&jobid=' . $data['job_id'] ),
                                    __( 'Applicant List', 'erp' )
                                ),
            'job-openings'      => sprintf(
                                    '<a href="%s">%s</a>',
                                    admin_url( 'admin.php?page=erp-hr&section=recruitment&sub-section=job-opening' ),
                                    __( 'Job List', 'erp' )
                                ),
            'more-info'         => $this->get_additional_info( $data['applicant_id'], $data['applicant_email'] ),
        ];

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }

    /**
     * Generates additional information
     *
     * @param int|string $applicant_id
     * @param string $applicant_email
     *
     * @return string
     */
    private function get_additional_info( $applicant_id, $applicant_email ) {
        global $wpdb;

        $applicant_meta = $wpdb->get_results(
                            $wpdb->prepare(
                                "SELECT meta_key, meta_value
                                FROM {$wpdb->prefix}erp_peoplemeta
                                WHERE erp_people_id = %d",
                                $applicant_id
                            )
                        );

        ob_start();

        ?>
        <h3><?php _e( 'Additional Info', 'erp-pro' ); ?></h3>
        <table class="form-table">
            <tr>
                <th>
                    <?php echo __( 'Email:', 'erp-pro' ); ?>
                </th>
                <td>
                    <?php echo $applicant_email ?>
                </td>
            </tr>
            <?php
            foreach ( $applicant_meta as $meta ) :
                if ( ! in_array( $meta->meta_key, array( 'referred_by', 'status' ) ) && ! empty( $meta->meta_value ) ) :
                    $mkey  = implode( ' ',
                        array_map( function ( $part ) {
                            return ucfirst( $part );
                        }, explode( '_', $meta->meta_key ) )
                    );

                    $value = $meta->meta_value;

                    switch ( $meta->meta_key ) {
                        case 'website':
                            $value = "<a href='$value'>$value</a>";
                            break;

                        case 'attach_id':
                            $mkey  = "Attachment";
                            $url   = esc_url( wp_get_attachment_url( $value ) );
                            $value = "<a href='$url'>Click to view</a>";
                            break;

                        case 'gravater_id':
                            $mkey  = "Gravater";
                            $url   = esc_url( wp_get_attachment_image_url( $value ) );
                            $value = "<img src='$url' style='width:100px;'/>";
                            break;
                    }
                    ?>
                    <tr>
                        <th>
                            <?php echo esc_html( $mkey ); ?>
                        </th>
                        <td>
                            <?php echo $value ?>
                        </td>
                    </tr>
                    <?php
                endif;
            endforeach;
            ?>
        </table>

        <?php
        return ob_get_clean();
    }
}
