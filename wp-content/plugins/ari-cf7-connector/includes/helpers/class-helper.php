<?php
namespace Ari_Cf7_Connector\Helpers;

use Ari_Cf7_Connector\Forms\Settings as Settings_Form;
use Ari_Cf7_Connector\Helpers\Settings as Settings_Helper;
use Ari\Utils\Array_Helper as Array_Helper;

class Helper {
    private static $system_args = array(
        'action',

        'msg',

        'msg_type',

        'noheader',
    );

    public static function build_url( $add_args = array(), $remove_args = array(), $remove_system_args = true, $encode_args = true ) {
        if ( $remove_system_args ) {
            $remove_args = array_merge( $remove_args, self::$system_args );
        }

        if ( $encode_args )
            $add_args = array_map( 'rawurlencode', $add_args );

        return add_query_arg( $add_args, remove_query_arg( $remove_args ) );
    }

    public static function get_settings_form() {
        $form = new Settings_Form();
        $form->bind( Array_Helper::to_flat_array( Settings_Helper::instance()->full_options() ) );

        return $form;
    }

    public static function get_ajax_delegate_url( $delegate ) {
        return admin_url( 'admin-ajax.php?action=ari_cf7_connector&ctrl=plugin-dispatcher_request&delegate=' . $delegate );
    }

    public static function get_cf7form_log_message_prefix( $form ) {
        if ( empty( $form ) )
            return '';

        return sprintf(
            '"%1$s" [%2$d]: ',
            $form->title(),
            $form->id()
        );
    }
}
