<?php
namespace Ari_Cf7_Connector\Controllers\Log;

use Ari\Controllers\Controller as Controller;
use Ari\Utils\Request as Request;

class Display extends Controller {
    public function execute() {
        $log_type = Request::get_var( 'log' );
        $format = Request::get_var( 'format' );
        $log_path = null;

        switch ( $log_type ) {
            case 'app':
                $log_path = ARICF7CONNECTOR_APP_LOG_PATH;
                break;

            case 'debug':
                $log_path = ARICF7CONNECTOR_DEBUG_LOG_PATH;
                break;
        }

        $content = null;
        if ( empty( $log_path ) ) {
            $content = sprintf(
                __( '`%s` log is not supported.', 'contact-form-7-connector' ),
                $log_type
            );
        } else {
            if ( ! file_exists( $log_path ) ) {
                $content = __( 'The log is empty', 'contact-form-7-connector' );
            } else {
                $content = file_get_contents( $log_path );

                if ( 'html' == $format ) {
                    $content = nl2br( $content );
                }
            }
        }

        ob_clean();
        echo $content;
        exit();
    }
}
