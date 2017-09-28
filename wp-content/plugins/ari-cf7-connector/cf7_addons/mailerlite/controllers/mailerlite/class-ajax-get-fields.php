<?php
namespace Ari_Cf7_Connector_Plugins\Mailerlite\Controllers\Mailerlite;

use Ari_Cf7_Connector\Controllers\Ajax_Delegate as Ajax_Delegate_Controller;
use Ari\Utils\Request as Request;
use Ari_Cf7_Connector_Plugins\Mailerlite\Helpers\Mailerlite as MailerLite_API;

class Ajax_Get_Fields extends Ajax_Delegate_Controller {
    public function execute() {
        $lists = null;

        if ( $this->options->nopriv ) {
            return $lists;
        }

        $api_key = Request::get_var( 'api_key' );
        $reload = (bool) Request::get_var( 'reload', false );

        $mailerlite = new MailerLite_API( $api_key );
        $fields = $mailerlite->get_fields( $reload );

        if ( false === $fields )
            throw new \Exception( $mailerlite->get_last_error() );

        return $fields;
    }
}
