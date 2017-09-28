<?php
namespace Ari_Cf7_Connector_Plugins\Mailerlite\Helpers;

require_once ARICF7CONNECTOR_MAILERLITE_3RDPARTY_LOADER;

use \MailerLiteApi\Common\RestClient as Rest_Client;

class Mailerlite_Api_Rest_Client_Wrapper extends Rest_Client {
    private $last_response;

    protected function send( $method, $endpointUri, $body = null, array $headers = [] ) {
        $res = parent::send( $method, $endpointUri, $body, $headers );

        $this->last_response = $res;

        return $res;
    }

    public function is_error() {
        if ( empty( $this->last_response ) )
            return false;

        return $this->last_response['status_code'] >= 400;
    }

    public function get_last_error() {
        if ( empty( $this->last_response['body'] ) )
            return '';

        $error = '';
        $body = $this->last_response['body'];
        if ( isset( $body->error ) && isset( $body->error->message ) )
            $error = $body->error->message;

        return $error;
    }
}
