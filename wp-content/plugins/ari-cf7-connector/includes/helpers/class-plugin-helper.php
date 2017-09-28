<?php
namespace Ari_Cf7_Connector\Helpers;

use Ari\Utils\Object_Factory as Object_Factory;

final class Plugin_Helper {
    static private $plugin_list = null;

    static private $plugins = null;

    static public function get_plugin_list() {
        if ( ! is_null( self::$plugin_list ) )
            return self::$plugin_list;

        $plugins = array(
            'Mailchimp',

            'Mailerlite'
        );

        $plugins = apply_filters( 'ari-cf7connector-plugins', $plugins );

        self::$plugin_list = $plugins;

        return self::$plugin_list;
    }

    static public function get_installed_plugins() {
        if ( ! is_null( self::$plugins ) )
            return self::$plugins;

        $plugins = array();
        $plugin_list = self::get_plugin_list();

        foreach ( $plugin_list as $plugin_slug ) {
            $plugin = Object_Factory::get_object( 'Plugin', 'Ari_Cf7_Connector_Plugins\\' . $plugin_slug );

            if ( ! is_null( $plugin ) ) {
                $plugins[] = $plugin;
            }
        }

        self::$plugins = $plugins;

        return self::$plugins;
    }

    static public function get_active_plugins( $force = false ) {
        $plugins = self::get_installed_plugins();
        $active_plugins = array();

        foreach ( $plugins as $plugin ) {
            if ( $plugin->is_enabled( $force ) )
                $active_plugins[] = $plugin;
        }

        return $active_plugins;
    }
}
