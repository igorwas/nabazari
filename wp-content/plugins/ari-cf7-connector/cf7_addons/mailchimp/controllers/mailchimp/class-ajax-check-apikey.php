<?php
namespace Ari_Cf7_Connector_Plugins\Mailchimp\Controllers\Mailchimp;

use Ari_Cf7_Connector\Controllers\Ajax_Delegate as Ajax_Delegate_Controller;
use Ari\Utils\Request as Request;
use Ari_Cf7_Connector_Plugins\Mailchimp\Helpers\Mailchimp as MailChimp_API;

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

        $mailchimp = new MailChimp_API( $api_key );
        $is_valid = $mailchimp->check_apikey();

        if ( ! $is_valid )
            $result['message'] = $mailchimp->get_last_error();
        else
            $result['valid'] = $is_valid;

        return $result;
    }
}
