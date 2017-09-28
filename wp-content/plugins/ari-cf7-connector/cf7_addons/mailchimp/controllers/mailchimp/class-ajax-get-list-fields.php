<?php
namespace Ari_Cf7_Connector_Plugins\Mailchimp\Controllers\Mailchimp;

use Ari_Cf7_Connector\Controllers\Ajax_Delegate as Ajax_Delegate_Controller;
use Ari\Utils\Request as Request;
use Ari_Cf7_Connector_Plugins\Mailchimp\Helpers\Mailchimp as MailChimp_API;

class Ajax_Get_List_Fields extends Ajax_Delegate_Controller {
    public function execute() {
        $lists = null;

        if ( $this->options->nopriv ) {
            return $lists;
        }

        $api_key = Request::get_var( 'api_key' );
        $list_id = Request::get_var( 'list_id' );

        $mailchimp = new MailChimp_API( $api_key );
        $list_fields = $mailchimp->get_list_fields( $list_id );

        if ( empty( $list_fields ) && $mailchimp->is_error() ) {
            throw new \Exception( $mailchimp->get_last_error() );
        }

        return $list_fields;
    }
}
