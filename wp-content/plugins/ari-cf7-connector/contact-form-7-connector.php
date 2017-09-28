<?php
/*
	Plugin Name: Contact Form 7 Connector
	Plugin URI: https://wordpress.org/plugins/ari-cf7-connector/
	Description: Integrate Contact Form 7 with popular email marketing and data services: MailChimp, MailerLite, Zapier.
	Version: 1.1.3
	Author: ARI Soft
	Author URI: http://www.ari-soft.com
	Text Domain: contact-form-7-connector
	Domain Path: /languages
	License: GPL2
 */

defined( 'ABSPATH' ) or die( 'Access forbidden!' );

define( 'ARICF7CONNECTOR_EXEC_FILE', __FILE__ );
define( 'ARICF7CONNECTOR_URL', plugin_dir_url( __FILE__ ) );
define( 'ARICF7CONNECTOR_PATH', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'ARI_WP_LEGACY' ) ) {
    $current_wp_version = get_bloginfo( 'version' );
    define( 'ARI_WP_LEGACY', version_compare( $current_wp_version, '4.0', '<' ) );
}

if ( ! function_exists( 'ari_cf7_connector_activation_check' ) ) {
    function ari_cf7_connector_activation_check() {
        $min_php_version = '5.4.0';
        $min_wp_version = '4.0.0';

        $current_wp_version = get_bloginfo( 'version' );
        $current_php_version = PHP_VERSION;

        $is_supported_php_version = version_compare( $current_php_version, $min_php_version, '>=' );
        $is_spl_installed = function_exists( 'spl_autoload_register' );
        $is_supported_wp_version = version_compare( $current_wp_version, $min_wp_version, '>=' );

        if ( ! $is_supported_php_version || ! $is_spl_installed || ! $is_supported_wp_version ) {
            deactivate_plugins( basename( ARICF7CONNECTOR_EXEC_FILE ) );

            $recommendations = array();

            if ( ! $is_supported_php_version )
                $recommendations[] = sprintf(
                    __( 'update PHP version on your server from v. %s to at least v. %s', 'contact-form-7-connector' ),
                    $current_php_version,
                    $min_php_version
                );

            if ( ! $is_spl_installed )
                $recommendations[] = __( 'install PHP SPL extension', 'contact-form-7-connector' );

            if ( ! $is_supported_wp_version )
                $recommendations[] = sprintf(
                    __( 'update WordPress v. %s to at least v. %s', 'contact-form-7-connector' ),
                    $current_wp_version,
                    $min_wp_version
                );

            wp_die(
                sprintf(
                    __( '"Contact Form 7 Connector" can not be activated. It requires PHP version 5.4.0+ with SPL extension and WordPress 4.0+.<br /><br /><b>Recommendations:</b> %s.<br /><br /><a href="%s" class="button button-primary">Back</a>', 'contact-form-7-connector' ),
                    join( ', ', $recommendations ),
                    get_dashboard_url()
                )
            );
        } else {
            add_option( 'ari_cf7connector_redirect', true );

            ari_cf7_connector_init();
        }
    }
}

if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {
    require_once ARICF7CONNECTOR_PATH . 'loader.php';

    add_action( 'plugins_loaded', 'ari_cf7_connector_init' );
} else {
    if ( ! function_exists( 'cf7_connector_requirement_notice' ) ) {
        function cf7_connector_requirement_notice() {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                sprintf(
                    __( '"Contact Form 7 Connector" requires PHP v. 5.4.0+, but PHP version %s is used on the site.', 'contact-form-7-connector' ),
                    PHP_VERSION
                )
            );
        }
    }

    add_action( 'admin_notices', 'cf7_connector_requirement_notice' );
}

register_activation_hook( ARICF7CONNECTOR_EXEC_FILE, 'ari_cf7_connector_activation_check' );
