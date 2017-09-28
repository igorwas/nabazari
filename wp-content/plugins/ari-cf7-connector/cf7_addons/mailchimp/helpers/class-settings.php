<?php
namespace Ari_Cf7_Connector_Plugins\Mailchimp\Helpers;

use Ari\Wordpress\Settings_Generic as Settings_Generic;
use Ari\Utils\Utils as Utils;

class Settings extends Settings_Generic {
    protected $settings_name = ARICF7CONNECTOR_MAILCHIMP_SETTINGS_NAME;

    protected $default_settings = array(
        'apikey_list' => null,

        'double_optin' => false,
    );

    public function sanitize( $input, $defaults = false ) {
        $new_input = parent::sanitize( $input, $defaults );

        if ( isset( $input['apikey_list'] ) && is_string( $input['apikey_list']) ) {
            $apikey_list = json_decode( $input['apikey_list'] );
            $filtered_apikey_list = array();

            if ( is_array( $apikey_list ) ) {
                foreach ( $apikey_list as $apikey_item ) {
                    $api_key = trim( Utils::get_value( $apikey_item, 'apikey', '' ) );
                    $id = trim( Utils::get_value( $apikey_item, 'id', '' ) );

                    if ( empty( $api_key ) )
                        continue ;

                    if ( empty( $id ) )
                        $id = Utils::guid();

                    $apikey_item->apikey = $api_key;
                    $apikey_item->id = $id;

                    $filtered_apikey_list[$id] = $apikey_item;
                }
            }

            $new_input['apikey_list'] = $filtered_apikey_list;
        }

        return $new_input;
    }
}
