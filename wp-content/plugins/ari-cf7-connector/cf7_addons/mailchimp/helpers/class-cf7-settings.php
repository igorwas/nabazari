<?php
namespace Ari_Cf7_Connector_Plugins\Mailchimp\Helpers;

use Ari\Wordpress\Settings_Generic as Settings_Generic;
use Ari\Utils\Utils as Utils;

class Cf7_Settings extends Settings_Generic {
    protected $default_settings = array(
        'apikey' => '',

        'name' => '',

        'email' => '',

        'double_optin' => false,

        'update_existing' => false,

        'subscriptions' => null,
    );

    public function __construct( $form_id ) {
        $form_id = intval( $form_id, 10 );

        if ( $form_id > 0 )
            $this->settings_name = 'ari_cf7connector_mailchimp_' . $form_id;

        parent::__construct();
    }

    public function sanitize( $input, $defaults = false ) {
        $new_input = parent::sanitize( $input, $defaults );

        if ( isset( $input['subscriptions'] ) && is_string( $input['subscriptions']) ) {
            $subscriptions = json_decode( $input['subscriptions'] );
            $filtered_subscriptions = array();

            if ( is_array( $subscriptions ) ) {
                foreach ( $subscriptions as $subscription ) {
                    if ( is_array( $subscription->lists ) ) {
                        $filtered_lists = array();
                        foreach ( $subscription->lists as $list ) {
                            $list_id = Utils::get_value( $list, 'list_id' );

                            if ( ! is_string( $list_id ) || strlen( $list_id ) === 0 )
                                continue ;

                            $custom_fields = Utils::get_value( $list, 'custom_fields' );
                            if ( is_array( $custom_fields ) && count( $custom_fields ) > 0 ) {
                                $filtered_custom_fields = array();

                                foreach ( $custom_fields as $custom_fields_item ) {
                                    $mailchimp_field = $custom_fields_item->mailchimp_field_id;
                                    $form_field = $custom_fields_item->form_field;

                                    if ( empty( $mailchimp_field ) || empty( $form_field) )
                                        continue ;

                                    $filtered_custom_fields[] = $custom_fields_item;
                                }

                                if ( count( $filtered_custom_fields ) != count( $custom_fields ) )
                                    $list->custom_fields = $filtered_custom_fields;
                            }

                            $filtered_lists[] = $list;
                        }

                        if ( count( $filtered_lists) > 0 ) {
                            $subscription->lists = $filtered_lists;
                            $filtered_subscriptions[] = $subscription;
                        }
                    }
                }
            }

            $new_input['subscriptions'] = $filtered_subscriptions;
        }

        return $new_input;
    }
}
