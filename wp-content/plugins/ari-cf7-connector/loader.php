<?php
defined( 'ABSPATH' ) or die( 'Access forbidden!' );

if ( ! function_exists( 'ari_cf7_connector_init' ) ) {
    function ari_cf7_connector_init() {
        if ( defined( 'ARICF7CONNECTOR_INITED' ) )
            return ;

        define( 'ARICF7CONNECTOR_INITED', true );

        require_once ARICF7CONNECTOR_PATH . 'includes/defines.php';
        require_once ARICF7CONNECTOR_PATH . 'libraries/arisoft/loader.php';

        Ari_Loader::register_prefix( 'Ari_Cf7_Connector', ARICF7CONNECTOR_PATH . 'includes' );
        Ari_Loader::register_prefix( 'Ari_Cf7_Connector_Plugins', ARICF7CONNECTOR_PATH . 'cf7_addons' );

        $plugin = new \Ari_Cf7_Connector\Plugin(
            array(
                'class_prefix' => 'Ari_Cf7_Connector',

                'version' => ARICF7CONNECTOR_VERSION,

                'path' => ARICF7CONNECTOR_PATH,

                'url' => ARICF7CONNECTOR_URL,

                'assets_url' => ARICF7CONNECTOR_ASSETS_URL,

                'view_path' => ARICF7CONNECTOR_PATH . 'includes/views/',

                'main_file' => __FILE__,

                'page_prefix' => 'ari-cf7connector',
            )
        );
        $plugin->init();
    }
}
