<?php
namespace Ari_Cf7_Connector\Controllers\Settings;

use Ari\Controllers\Controller as Controller;
use Ari\Utils\Response as Response;
use Ari\Utils\Request as Request;
use Ari_Cf7_Connector\Helpers\Helper as Helper;
use Ari_Cf7_Connector\Helpers\Settings as Settings;

class Save extends Controller {
    public function execute() {
        $prev_settings = Settings::instance()->full_options();

        $data = stripslashes_deep( Request::get_var( ARICF7CONNECTOR_SETTINGS_NAME ) );
        $result = Settings::instance()->save( $data );

        if ( $result )
            do_action( 'ari-cf7connector-save-settings', $prev_settings );

        if ( $result ) {
            Response::redirect(
                Helper::build_url(
                    array(
                        'page' => 'ari-cf7connector',

                        'action' => 'display',

                        'msg' => __( 'Settings are saved successfully.', 'contact-form-7-connector' ),

                        'msg_type' => ARICF7CONNECTOR_MESSAGETYPE_SUCCESS,
                    )
                )
            );
        } else {
            Response::redirect(
                Helper::build_url(
                    array(
                        'page' => 'ari-cf7connector',

                        'action' => 'display',

                        'msg' => __( 'The settings are not saved. Probably data are corrupted or a database connection is broken.', 'contact-form-7-connector' ),

                        'msg_type' => ARICF7CONNECTOR_MESSAGETYPE_ERROR,
                    )
                )
            );
        }
    }
}
