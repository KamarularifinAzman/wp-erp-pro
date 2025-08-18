<?php
if ( ! function_exists( 'erp_crm_get_life_stage' ) ):

    /**
     * @since 1.0.1
     *
     * @param $contact_id
     *
     * @return mixed|string
     */
    function erp_crm_get_life_stage( $contact_id ) {
        $contact = new \WeDevs\ERP\CRM\Contact( $contact_id );
        if ( is_callable( array( $contact, 'get_life_stage' ) ) ) {
            return $contact->get_life_stage();
        } else {
            return erp_people_get_meta( $contact_id, 'life_stage', true );
        }
    }
endif;

if ( ! function_exists( 'erp_crm_update_life_stage' ) ):

    /**
     * @since 1.0.1
     *
     * @param $contact_id
     * @param $life_stage
     *
     * @return mixed|string
     */
    function erp_crm_update_life_stage( $contact_id, $life_stage ) {
        $contact = new \WeDevs\ERP\CRM\Contact( $contact_id );
        if ( is_callable( array( $contact, 'update_life_stage' ) ) ) {
            return $contact->update_life_stage( $life_stage );
        } else {
            return erp_people_update_meta( $contact_id, 'life_stage', $life_stage );
        }
    }
endif;

if ( ! function_exists( 'erp_crm_get_contact_owner' ) ):

    /**
     * @since 1.0.1
     *
     * @param $contact_id
     *
     * @return mixed|string
     */
    function erp_crm_get_contact_owner( $contact_id ) {
        $contact = new \WeDevs\ERP\CRM\Contact( $contact_id );
        if ( is_callable( array( $contact, 'contact_owner' ) ) ) {
            return $contact->get_contact_owner();
        } else {
            return erp_people_get_meta( $contact_id, 'contact_owner', true );
        }
    }
endif;

if ( ! function_exists( 'erp_crm_update_contact_owner' ) ):

    /**
     * @since 1.0.1
     *
     * @param $contact_id
     * @param $contact_owner
     *
     * @return mixed|string
     */
    function erp_crm_update_contact_owner( $contact_id, $contact_owner ) {
        $contact = new \WeDevs\ERP\CRM\Contact( $contact_id );
        if ( is_callable( array( $contact, 'update_contact_owner' ) ) ) {
            return $contact->update_contact_owner( $contact_owner );
        } else {
            return erp_people_update_meta( $contact_id, 'contact_owner', $contact_owner );
        }
    }
endif;


if ( ! function_exists( 'erp_crm_get_contact_hash' ) ):

    /**
     * @since 1.0.1
     *
     * @param $contact_id
     *
     * @return mixed|string
     */
    function erp_crm_get_contact_hash( $contact_id ) {
        $contact = new \WeDevs\ERP\CRM\Contact( $contact_id );
        if ( is_callable( array( $contact, 'contact_hash' ) ) ) {
            return $contact->get_contact_hash();
        } else {
            return erp_people_get_meta( $contact_id, 'hash', true );
        }
    }
endif;

if ( ! function_exists( 'erp_crm_update_contact_hash' ) ):

    /**
     * @since 1.0.1
     *
     * @param $contact_id
     * @param $contact_hash
     *
     * @return mixed|string
     */
    function erp_crm_update_contact_hash( $contact_id, $contact_hash ) {
        $contact = new \WeDevs\ERP\CRM\Contact( $contact_id );
        if ( is_callable( array( $contact, 'update_contact_hash' ) ) ) {
            return $contact->update_contact_hash( $contact_hash );
        } else {
            return erp_people_update_meta( $contact_id, 'hash', $contact_hash );
        }
    }
endif;
