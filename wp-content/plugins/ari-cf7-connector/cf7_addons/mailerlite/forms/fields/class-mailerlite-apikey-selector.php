<?php
namespace Ari_Cf7_Connector_Plugins\Mailerlite\Forms\Fields;

use Ari\Forms\Field as Field;
use Ari_Cf7_Connector\Helpers\App_Helper as App_Helper;

class Mailerlite_Apikey_Selector extends Field {
    private function register_messages() {
        App_Helper::add_message( 'mailerlite_key_valid', __( 'The key is valid', 'contact-form-7-connector' ) );
        App_Helper::add_message( 'mailerlite_key_empty', __( 'The key can not be empty', 'contact-form-7-connector' ) );
    }

    public function output() {
        $this->register_messages();

        $tmpl_file = dirname( __FILE__ ) . '/tmpl/mailerlite-apikey-selector.php';

        ob_start();

        require $tmpl_file;

        $output = ob_get_clean();

        return $output;
    }
}
