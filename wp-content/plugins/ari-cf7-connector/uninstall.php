<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();

$log_dir = WP_CONTENT_DIR . '/cf7connector-log';

if ( file_exists( $log_dir) && is_dir( $log_dir ) ) {
    array_map( 'unlink', glob( $log_dir . '/{,.}[!.,!..]*', GLOB_MARK | GLOB_BRACE ) );
    @rmdir( $log_dir );
}

function ari_cf7connector_clear_site_settings() {
    $settings = get_option( 'ari_cf7connector_settings' );
    $clean_uninstall = isset( $settings['clean_uninstall'] ) ? (bool) $settings['clean_uninstall'] : false;

    if ( ! $clean_uninstall )
        return ;

    global $wpdb;

    $settings_id_list = $wpdb->get_col(
        sprintf(
            'SELECT option_name FROM `%1$soptions` WHERE option_name LIKE "ari_cf7connector%%"',
            $wpdb->prefix
        )
    );

    if ( is_array( $settings_id_list ) ) {
        foreach ( $settings_id_list as $settings_id ) {
            delete_option( $settings_id );
        }
    }
}

if ( ! is_multisite() ) {
	ari_cf7connector_clear_site_settings();
} else {
    global $wpdb;

    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    $original_blog_id = get_current_blog_id();

    foreach ( $blog_ids as $blog_id )   {
        switch_to_blog( $blog_id );

		ari_cf7connector_clear_site_settings();
    }

    switch_to_blog( $original_blog_id );
}
