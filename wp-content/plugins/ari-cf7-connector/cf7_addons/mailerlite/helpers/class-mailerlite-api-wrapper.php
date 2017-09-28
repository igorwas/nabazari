<?php
namespace Ari_Cf7_Connector_Plugins\Mailerlite\Helpers;

require_once ARICF7CONNECTOR_MAILERLITE_3RDPARTY_LOADER;

use \MailerLiteApi\MailerLite as MailerLiteApi;

class Mailerlite_Api_Wrapper extends MailerLiteApi {
    public function __construct(
        $apiKey = null,
        HttpClient $httpClient = null
    ) {
        parent::__construct( $apiKey, $httpClient );

        $this->restClient = new Mailerlite_Api_Rest_Client_Wrapper(
            $this->getBaseUrl(),
            $apiKey,
            $httpClient
        );
    }

    public function is_error() {
        return $this->restClient->is_error();
    }

    public function get_last_error() {
        return $this->restClient->get_last_error();
    }
}
