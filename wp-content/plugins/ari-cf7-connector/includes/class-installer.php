<?php
namespace Ari_Cf7_Connector;

use Ari\App\Installer as Ari_Installer;
use Ari_Cf7_Connector\Helpers\Settings as Settings;
use Ari\Utils\Utils as Utils;

class Installer extends Ari_Installer {
    function __construct( $options = array() ) {
        if ( ! isset( $options['installed_version'] ) ) {
            $installed_version = get_option( ARICF7CONNECTOR_VERSION );

            if ( false !== $installed_version) {
                $options['installed_version'] = $installed_version;
            }
        }

        if ( ! isset( $options['version'] ) ) {
            $options['version'] = ARICF7CONNECTOR_VERSION;
        }

        parent::__construct( $options );
    }

    private function init() {
    }

    private function create_log_folder() {
        if ( file_exists( ARICF7CONNECTOR_LOG_PATH ) )
            return false;

        if ( ! @mkdir( ARICF7CONNECTOR_LOG_PATH, 0755 ) )
            return false;

        $htaccess_log_path = ARICF7CONNECTOR_LOG_PATH . '/.htaccess';

        if ( ! file_exists( $htaccess_log_path ) ) {
            if ( ( $fh = @fopen( $htaccess_log_path, 'w+' ) ) ) {
                fwrite( $fh, "<FilesMatch \".*\">\r\n\tOrder Allow,Deny\r\n\tDeny from all\r\n</FilesMatch>" );
                fclose( $fh );
            }
        }
    }

    public function run() {
        $this->init();
        $this->create_log_folder();
        $this->init_settings();

        if ( empty( $this->options->installed_version ) )
            $this->convert_mailchimp_settings();

        if ( ! $this->run_versions_updates() ) {
            return false;
        }

        update_option( ARICF7CONNECTOR_VERSION_OPTION, $this->options->version );

        return true;
    }

    private function init_settings() {
        if ( false !== get_option( ARICF7CONNECTOR_SETTINGS_NAME ) )
            return ;

        add_option( ARICF7CONNECTOR_SETTINGS_NAME, Settings::instance()->get_default_options() );
    }

    private function convert_mailchimp_settings() {
        global $wpdb;

        $mailchimp_form_settings_keys = $wpdb->get_col(
            sprintf(
                'SELECT option_name FROM `%1$soptions` WHERE option_name <> "ari_cf7connector_mailchimp_settings" AND option_name LIKE "ari_cf7connector_mailchimp_%%"',
                $wpdb->prefix
            )
        );

        if ( is_array( $mailchimp_form_settings_keys ) && count( $mailchimp_form_settings_keys ) > 0 ) {
            foreach ( $mailchimp_form_settings_keys as $settings_key ) {
                $mailchimp_form_settings = get_option( $settings_key );

                if ( ! is_array( $mailchimp_form_settings ) || ! isset( $mailchimp_form_settings['subscriptions'] ) || ! is_array( $mailchimp_form_settings['subscriptions'] ) || count( $mailchimp_form_settings['subscriptions'] ) == 0 )
                    continue ;

                $has_modified_subscription = false;
                $converted_subscriptions = array();
                $subscriptions = $mailchimp_form_settings['subscriptions'];
                foreach ( $subscriptions as $subscription ) {
                    if ( ! isset( $subscription->list_id ) ) {
                        $converted_subscriptions[] = $subscription;
                        continue ;
                    }

                    $has_modified_subscription = true;
                    if ( ! is_array( $subscription->list_id ) || count( $subscription->list_id ) == 0 ) {
                        continue ;
                    }

                    $lists_meta = Utils::get_value( $subscription, 'list_meta' );
                    if ( $lists_meta ) {
                        $lists_meta = json_decode( $lists_meta, true );
                        if ( json_last_error() !== JSON_ERROR_NONE ) {
                            $lists_meta = array();
                        }
                    }

                    $converted_subscription = new \stdClass();
                    $converted_subscription->confirm_field = Utils::get_value( $subscription, 'confirm_field', '' );
                    $converted_subscription->lists = array();

                    foreach ( $subscription->list_id as $list_id ) {
                        $list_section = new \stdClass();
                        $list_section->list_id = $list_id;

                        if ( isset( $lists_meta[$list_id] ) )
                            $list_section->list_meta = json_encode( $lists_meta[$list_id] );

                        $list_section->use_custom_fields = Utils::get_value( $subscription, 'use_custom_fields', false );
                        $list_section->custom_fields = Utils::get_value( $subscription, 'custom_fields', array() );

                        $converted_subscription->lists[] = $list_section;
                    }

                    $converted_subscriptions[] = $converted_subscription;
                }

                $mailchimp_form_settings['subscriptions'] = $converted_subscriptions;
                if ( $has_modified_subscription ) {
                    update_option( $settings_key, $mailchimp_form_settings );
                }
            }
        }
    }
}
