<?php
namespace Ari_Cf7_Connector\Helpers;

final class App_Helper {
    static private $messages = array();

    static public function add_message( $key, $message ) {
        self::$messages[$key] = $message;
    }

    static public function get_messages() {
        return self::$messages;
    }

    static public function get_global_app_options( $container_id = '' ) {
        $messages = array_merge(
            array(
                'ok' => __( 'OK', 'contact-form-7-connector' ),

                'request_failed' => __( 'The request is failed.', 'contact-form-7-connector' ),
            ),

            self::get_messages()
        );

        $app_helper_options = array(
            'ajaxUrl' => admin_url( 'admin-ajax.php?action=ari_cf7_connector' ),

            'messages' => $messages,
        );

        $global_app_options = array(
            'options' => $app_helper_options,

            'containerId' => $container_id,
        );

        return $global_app_options;
    }
}