<?php
namespace Ari_Cf7_Connector_Plugins\Mailerlite\Helpers;

use Ari\Wordpress\Settings_Generic as Settings_Generic;
use Ari\Utils\Utils as Utils;

class Cf7_Settings extends Settings_Generic {
    protected $default_settings = array(
        'apikey' => '',

        'name' => '',

        'email' => '',

        'subscriptions' => null,
    );

    public function __construct( $form_id ) {
        $form_id = intval( $form_id, 10 );

        if ( $form_id > 0 )
            $this->settings_name = 'ari_cf7connector_mailerlite_' . $form_id;

        parent::__construct();
    }

    public function sanitize( $input, $defaults = false ) {
        $new_input = parent::sanitize( $input, $defaults );

        if ( isset( $input['subscriptions'] ) && is_string( $input['subscriptions']) ) {
            $subscriptions = json_decode( $input['subscriptions'] );
            $filtered_subscriptions = array();

            if ( is_array( $subscriptions ) ) {
                foreach ( $subscriptions as $subscription ) {
                    $list_id = Utils::get_value( $subscription, 'list_id' );

                    if ( empty( $list_id ) || ! is_array( $list_id ) || count( $list_id ) == 0 )
                        continue ;

                    $custom_fields = Utils::get_value( $subscription, 'custom_fields' );
                    if ( is_array( $custom_fields ) && count( $custom_fields ) > 0 ) {
                        $filtered_custom_fields = array();

                        foreach ( $custom_fields as $custom_fields_item ) {
                            $list_field = $custom_fields_item->list_field_id;
                            $form_field = $custom_fields_item->form_field;

                            if ( empty( $list_field ) || empty( $form_field ) )
                                continue ;

                            $filtered_custom_fields[] = $custom_fields_item;
                        }

                        if ( count( $filtered_custom_fields ) != count( $custom_fields ) )
                            $subscription->custom_fields = $filtered_custom_fields;
                    }

                    $filtered_subscriptions[] = $subscription;
                }
            }

            $new_input['subscriptions'] = $filtered_subscriptions;
        }

        return $new_input;
    }
}
