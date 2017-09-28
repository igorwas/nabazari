<?php
namespace Ari_Cf7_Connector_Plugins\Mailerlite\Controllers\Mailerlite;

use Ari_Cf7_Connector\Controllers\Ajax_Delegate as Ajax_Delegate_Controller;
use Ari\Utils\Request as Request;
use Ari_Cf7_Connector_Plugins\Mailerlite\Helpers\Mailerlite as MailerLite_API;

class Ajax_Check_Apikey extends Ajax_Delegate_Controller {
    public function execute() {
        $result = array(
            'valid' => false,

            'message' => '',
        );

        if ( $this->options->nopriv ) {
            return $result;
        }

        $api_key = Request::get_var( 'api_key' );

        if ( strlen( $api_key ) == 0 ) {
            $result['message'] = __( 'An empty API key is provided.', 'contact-form-7-connector' );

            return $result;
        }

        $mailerLite = new MailerLite_API( $api_key );
        $is_valid = $mailerLite->check_apikey();

        if ( ! $is_valid )
            $result['message'] = $mailerLite->get_last_error();
        else
            $result['valid'] = $is_valid;

        return $result;
    }
}
