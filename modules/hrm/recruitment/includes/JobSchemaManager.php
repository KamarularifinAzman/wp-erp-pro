<?php
namespace WeDevs\Recruitment;

class JobSchemaManager{

        public function __construct() {
            add_action('wp_head',[$this, 'schema_markup']);
        }

        public function schema_data_generator(){
            global $post;

            if ( empty( $post ) ) {
                return [];
            }

            $job_id            = $post->ID;
            $employment_type   = get_post_meta( $job_id, '_employment_type', true);
            $expire_date       = get_post_meta( $job_id, '_expire_date', true);
            $expire_timestamp  = !empty( $expire_date ) ? strtotime( $expire_date ) : false;
            $location          = get_post_meta( $job_id, '_location', true );
            $street_address    = get_post_meta( $job_id, '_street_address', true);
            $address_locality  = get_post_meta( $job_id, '_address_locality', true);
            $postal_code       = get_post_meta( $job_id, '_postal_code', true);
            $address_country   = get_post_meta( $job_id, '_address_country', true);
            $currency          = get_post_meta( $job_id, '_currency', true);
            $salary            = get_post_meta( $job_id, '_salary', true);
            $salary_type       = get_post_meta( $job_id, '_salary_type', true);
            $src_emp_arr       = [
                'permanent' => 'FULL_TIME',
                'parttime'  => 'PART_TIME',
                'contract'  => 'CONTRACTOR',
                'temporary' => 'TEMPORARY',
                'trainee'   => 'INTERN'
            ];

            if (get_post_field('post_type', $job_id) != 'erp_hr_recruitment') {
                return [];
            }

            $data = [
                "@context"              => "https://schema.org/",
                "@type"                 => "JobPosting",
                "title"                 => get_the_title($job_id),
                "description"           =>  empty( ! get_post_field( 'post_content', $job_id )  ) ? addslashes( get_post_field( 'post_content', $job_id ) ) : 'N/A' ,
                "hiringOrganization"    => [
                    "@type"     => "Organization",
                    "name"      => get_bloginfo( 'name' ),
                    "sameAs"    => get_bloginfo( 'url' ),
                    "logo"      => get_custom_logo()
                ],
                "employmentType"        => isset( $src_emp_arr[ $employment_type ] ) ? $src_emp_arr[ $employment_type ] : 'OTHER' ,
                "validThrough"          => (date( 'Y-m-d', $expire_timestamp ) != '1970-01-01' ) ? get_post_time( erp_get_date_format( 'Y-m-d' ) ) : current_time( erp_get_date_format( 'Y-m-d' ) ),
                "datePosted"            => get_post_time( erp_get_date_format( 'Y-m-d' ) ),
                "jobLocation"           => [
                    "@type"     => "Place",
                    "address"   => [
                        "@type"             => "PostalAddress",
                        "streetAddress"     => ( ! empty( $street_address ) ? $street_address : 'N/A' ),
                        "addressLocality"   => ( ! empty( $address_locality ) ? $address_locality : 'N/A' ),
                        "postalCode"        => ( ! empty( $postal_code ) ? $postal_code : 'N/A' ),
                        "addressCountry"    => ( ! empty( $address_country ) ? $address_country : 'N/A' ),
                        "addressRegion"     => ( ! empty( $location ) ? $location : 'N/A' )
        ]
                ],
                "baseSalary"            => [
                    "@type"     => "MonetaryAmount",
                    "currency"  =>  ( ! empty( $currency ) ? $currency : 'N/A' ),
                    "value"     => [
                        "@type"             => "QuantitativeValue",
                        "value"             => ( ! empty( $salary ) ? $salary : 'N/A' ),
                        "unitText"          => ( ! empty( $salary_type ) ? $salary_type : 'N/A' )
                    ]
                ]
            ];
            return $data;
        }

        public function schema_markup() {
            $output        = "\n\n";
            $output        .= "\n";
            $output        .= '<script type="application/ld+json">';
            $output        .= json_encode( $this->schema_data_generator(), JSON_UNESCAPED_UNICODE );
            $output        .= '</script>';
            $output        .= "\n\n";
            echo $output;
        }


}
