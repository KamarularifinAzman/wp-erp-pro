<?php

namespace WeDevs\HelpScout;

use WeDevs\ERP\Framework\Models\People;
use WeDevs\ERP\CRM\Contact;

class Customer extends User {
    /**
     * @since 1.0.0
     * @var Contact
     */
    public $contact;


    public function __construct( array $args ) {
        parent::__construct( $args );
    }

    /**
     * Customer details
     *
     * @since 1.0.0
     * @return mixed|string
     */
    public function get_details() {
        $contact = $this->contact();

        if ( ! $contact ) {
            return ( __( 'Could not retrieve customer details', 'erp-pro' ) );
        }

        if ( ! $contact instanceof Contact ) {
            $contact = new Contact( $contact->id );
        }

        $full_name       = $contact->get_first_name() . ' ' . $contact->get_last_name();
        $company         = '';
        $phone           = $contact->get_phone();
        $website         = $contact->get_website();
        $location        = $this->get_customer_location( $contact );
        $source          = $contact->get_source();
        $life_stage      = $contact->get_life_stage();
        $c_owner         = $this->get_contact_owner( $contact->get_contact_owner() );
        $recent_activity = $this->get_activities( $contact->id );

        $customer_details = [
            'id'              => $contact->id,
            'name'            => $full_name,
            'company'         => $this->sanitize_field( $company ),
            'phone'           => $this->sanitize_field( $phone ),
            'website'         => $this->sanitize_field( $website ),
            'location'        => $this->sanitize_field( $location ),
            'source'          => $this->sanitize_field( $source ),
            'life_stage'      => $this->sanitize_field( $life_stage ),
            'c_owner'         => $this->sanitize_field( $c_owner ),
            'recent_activity' => $recent_activity,
        ];

        // if deals plugins installed

        return apply_filters( 'erp_helpscout_customer_details', $customer_details, $contact );
    }

    /**
     * Sanitize field
     *
     * @since 1.0.0
     *
     * @param $thing
     *
     * @return string
     */
    protected function sanitize_field( $thing ) {
        $mod_thing = str_replace( '—', '', $thing );

        if ( empty( trim( $mod_thing ) ) ) {
            return __( 'Not available', 'erp-pro' );
        } else {
            return $thing;
        }
    }

    /**
     * Customer location
     *
     * @since 1.0.0
     *
     * @param $customer
     *
     * @return string
     */
    protected function get_customer_location( $customer ) {
        $location = [];
        if ( $customer->get_city() ) {
            $location[] = $customer->get_city();
        }

        if ( $customer->get_country() ) {
            $location[] = $customer->get_country();
        }

        return implode( ', ', $location );
    }

    /**
     * Get website
     *
     * @since 1.0.0
     *
     * @param $customer
     *
     * @return mixed
     */
    protected function get_customer_website( $customer ) {
        return $customer->get_website();
    }

    /**
     * Contact Owner
     *
     * @since 1.0.0
     *
     * @param $user_id
     *
     * @return string
     */
    protected function get_contact_owner( $user_id ) {
        $user = get_user_by( 'id', $user_id );

        return $user->display_name;
    }

    /**
     * Get activity of the customer
     * @since 1.0.0
     *
     * @param $customer_id
     *
     * @return array
     */
    protected function get_activities( $customer_id ) {
        $logged_activities = [];

        if ( ! function_exists( 'erp_crm_get_feed_activity' ) ) {
            return $logged_activities;
        }

        $activities = erp_crm_get_feed_activity( [
			'customer_id' => $customer_id,
			'limit' => '6',
			'offset' => '0',
		] );

        foreach ( $activities as $activity ) {
            $completed     = true;
            $current_time  = current_time( 'timestamp' );
            $activity_time = strtotime( $activity['created_at'] );
            if ( $activity_time > $current_time ) {
                $completed = false;
            }

            $event = __( 'logged', 'erp-pro' );
            if ( ! $completed ) {
                $event = __( 'scheduled', 'erp-pro' );
            }

            $event_type = $activity['log_type'];

            if ( ! $event_type ) {
                continue;
            }

            $created_by = ucfirst( $activity['created_by']['display_name'] );

            $date_time = date( 'jS M y, h:i A', strtotime( $activity['start_date'] ) );

            $date = '<span class="muted">' . $date_time . '</span>';

            $text = sprintf( __( '%1$s  <br> %2$s %3$s %4$s', 'erp-pro' ), $date, $created_by, $event, $event_type );

            $logged_activities[] = $text;
        }

        return $logged_activities;
    }

}
