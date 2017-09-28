<?php
namespace Ari_Cf7_Connector\Controllers\Plugin_Dispatcher;

use Ari\Controllers\Ajax as Ajax_Controller;
use Ari\Utils\Request as Request;
use Ari_Cf7_Connector\Log\System_Log as System_Log;

class Ajax_Request extends Ajax_Controller {
    protected function process_request() {
        $delegate = Request::get_var( 'delegate' );

        if ( empty( $delegate ) ) {
            wp_die();
        }

        $name_parts = explode( '_', $delegate );

        if ( count( $name_parts ) < 3 ) {
            wp_die();
        }

        $plugin_name = \Ari_Loader::prepare_name( $name_parts[0] );
        $class_name = \Ari_Loader::prepare_name( $name_parts[1] );
        $ctrl_name = \Ari_Loader::prepare_name( $name_parts[2] );

        $delegate_class = '\\Ari_Cf7_Connector_Plugins\\' . $plugin_name . '\\Controllers\\' . $class_name . '\\Ajax_' . $ctrl_name;
        if ( ! class_exists( $delegate_class ) ) {
            wp_die();
        }

        $delegate_options = array(
            'nopriv' => $this->options->nopriv,
        );

        $result = null;
        try {
            $sub_ctrl = new $delegate_class( $delegate_options );
            $result = $sub_ctrl->execute();
        } catch ( \Exception $ex ) {
            System_Log::error( $ex->getMessage() );
            throw $ex;
        }

        return $result;
    }
}
