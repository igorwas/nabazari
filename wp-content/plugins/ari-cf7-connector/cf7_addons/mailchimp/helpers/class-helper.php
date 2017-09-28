<?php
namespace Ari_Cf7_Connector_Plugins\Mailchimp\Helpers;

use Ari_Cf7_Connector_Plugins\Mailchimp\Forms\Settings as Settings_Form;
use Ari_Cf7_Connector_Plugins\Mailchimp\Forms\Cf7_Settings as Cf7_Settings_Form;
use Ari_Cf7_Connector_Plugins\Mailchimp\Helpers\Settings as Settings;
use Ari_Cf7_Connector_Plugins\Mailchimp\Helpers\Cf7_Settings as Cf7_Settings;
use Ari\Utils\Array_Helper as Array_Helper;
use Ari\Utils\Utils as Utils;

class Helper {
    public static function get_cf7_settings_form( $form ) {
        $id = $form->id();

        $form = new Cf7_Settings_Form(
            array(
                'cf7_form' => $form,
            )
        );
        $settings = new Cf7_Settings( $id );
        //$form->bind( Array_Helper::to_flat_array( $settings->full_options() ) );
        $form->bind( $settings->full_options() );

        if ( empty( $id ) ) {
            $double_optin = Settings::instance()->get_option( 'double_optin' );
            $form->bind(
                array(
                    'double_optin' => $double_optin,
                )
            );
        }

        return $form;
    }

    public static function get_settings_form() {
        $form = new Settings_Form();
        $form->bind( Settings::instance()->full_options() );

        return $form;
    }

    public static function is_predefined_apikey( $apikey ) {
        return strlen( $apikey ) > 0 && preg_match( '/^\{\{.+}}$/', $apikey );
    }

    public static function resolve_apikey( $apikey ) {
        if ( ! self::is_predefined_apikey( $apikey ) )
            return $apikey;

        $apikey_id = preg_replace( '/^\{\{(.+)}}$/', '$1', $apikey );
        $apikey_list = Settings::instance()->get_option( 'apikey_list' );

        return is_array( $apikey_list ) && isset( $apikey_list[$apikey_id] ) ? Utils::get_value( $apikey_list[$apikey_id], 'apikey', '' ) : null;
    }
}
