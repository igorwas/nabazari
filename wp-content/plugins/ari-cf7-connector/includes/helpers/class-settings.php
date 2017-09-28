<?php
namespace Ari_Cf7_Connector\Helpers;

use Ari\Wordpress\Settings_Generic as Settings_Generic;

class Settings extends Settings_Generic {
    protected $settings_name = ARICF7CONNECTOR_SETTINGS_NAME;

    protected $default_settings = array(
        'disabled_plugins' => array(),

        'app_log' => false,

        'debug_log' => false,

        'clean_uninstall' => false,
    );

    protected function __construct() {
        $this->default_settings = apply_filters( 'ari-cf7connector-settings', $this->default_settings );

        parent::__construct();
    }

    public function sanitize( $input, $defaults = false ) {
        return parent::sanitize( $input, $defaults );
    }
}
