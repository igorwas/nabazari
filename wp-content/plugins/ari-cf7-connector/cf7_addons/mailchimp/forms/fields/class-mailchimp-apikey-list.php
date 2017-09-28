<?php
namespace Ari_Cf7_Connector_Plugins\Mailchimp\Forms\Fields;

use Ari_Cf7_Connector\Forms\Fields\Cloner as Cloner_Field;
use Ari_Cf7_Connector\Helpers\App_Helper as App_Helper;

class Mailchimp_Apikey_List extends Cloner_Field {
    function __construct( $options = array() ) {
        parent::__construct( $options );
    }

    protected function load_assets() {
        wp_enqueue_script( 'cf7-connector-mailchimp-apikeylist', ARICF7CONNECTOR_URL . 'cf7_addons/mailchimp/assets/apikey-list.js', array( 'ari-cf7connector-cloner', 'ari-button', 'ari-cf7connector-app-helper' ), ARICF7CONNECTOR_VERSION );

        parent::load_assets();
    }

    private function register_messages() {
        App_Helper::add_message( 'mailchimp_key_valid', __( 'The key is valid', 'contact-form-7-connector' ) );
        App_Helper::add_message( 'mailchimp_key_empty', __( 'The key can not be empty', 'contact-form-7-connector' ) );
    }

    public function output() {
        $this->register_messages();

        return parent::output();
    }
}
