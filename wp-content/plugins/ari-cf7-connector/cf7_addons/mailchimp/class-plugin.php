<?php
namespace Ari_Cf7_Connector_Plugins\Mailchimp;

define( 'ARICF7CONNECTOR_MAILCHIMP_SETTINGS_NAME', 'ari_cf7connector_mailchimp_settings' );
define( 'ARICF7CONNECTOR_MAILCHIMP_3RDPARTY_LOADER', dirname( __FILE__ ) . '/libraries/vendor/vendor/autoload.php' );

use Ari_Cf7_Connector\Helpers\Plugin as CF7_Connector_Plugin;
use Ari_Cf7_Connector\Helpers\Cf7_Helper as Cf7_Helper;
use Ari_Cf7_Connector_Plugins\Mailchimp\Helpers\Cf7_Settings as Cf7_Settings;
use Ari_Cf7_Connector_Plugins\Mailchimp\Helpers\Settings as Settings;
use Ari_Cf7_Connector\Log\App_Log as App_Log;
use Ari_Cf7_Connector\Log\System_Log as System_Log;
use Ari\Utils\Request as Request;
use Ari\Utils\Utils as Utils;
use Ari_Cf7_Connector_Plugins\Mailchimp\Helpers\Mailchimp as MailChimp_API;
use Ari_Cf7_Connector\Helpers\Helper as CF7C_Helper;

class Plugin extends CF7_Connector_Plugin {
    protected $slug = 'mailchimp';

    public function get_title() {
        return __( 'MailChimp', 'contact-form-7-connector' );
    }

    public function save_settings() {
        $data = stripslashes_deep( Request::get_var( ARICF7CONNECTOR_MAILCHIMP_SETTINGS_NAME ) );

        return Settings::instance()->save( $data );
    }

    public function on_save_cf7_form_settings( $form ) {
        $data = stripslashes_deep( Request::get_var( ARICF7CONNECTOR_MAILCHIMP_SETTINGS_NAME ) );

        $cf7_settings = new Cf7_Settings( $form->id() );
        $cf7_settings->save( $data );
    }

    public function on_cf7_form_submission( $form, $submission ) {
        $settings = new Cf7_Settings( $form->id() );

        $api_key = $settings->get_option( 'apikey' );

        if ( empty( $api_key ) )
            return ;

        $submission_data = $submission->get_posted_data();
        $email = Cf7_Helper::resolve_tags( $settings->get_option( 'email' ), $submission_data );
        $name = Cf7_Helper::resolve_tags( $settings->get_option( 'name' ), $submission_data );

        $subscriptions = $settings->get_option( 'subscriptions' );
        $lists = array();

        if ( is_array( $subscriptions ) ) {
            foreach ( $subscriptions as $subscription ) {
                $confirm_field = $subscription->confirm_field;

                if ( $confirm_field ) {
                    $confirm_field_value = Cf7_Helper::resolve_tags( $confirm_field, $submission_data );

                    if ( $confirm_field === $confirm_field_value || strlen( $confirm_field_value ) === 0 )
                        continue ;
                }

                if ( is_array( $subscription->lists ) ) {
                    foreach ( $subscription->lists as $list ) {
                        $list_id = trim( $list->list_id );

                        if ( empty( $list_id ) )
                            continue ;

                        $lists[$list_id] = array(
                            'id' => $list_id,

                            'fields' => array(),
                        );

                        $use_custom_fields = (bool) Utils::get_value( $list, 'use_custom_fields', false );

                        if ( $use_custom_fields ) {
                            $custom_fields = Utils::get_value( $list, 'custom_fields' );

                            if ( is_array( $custom_fields ) && count( $custom_fields ) > 0 ) {
                                foreach ( $custom_fields as $custom_fields_item ) {
                                    $mailchimp_field_id = trim( $custom_fields_item->mailchimp_field_id );
                                    $form_field = trim( $custom_fields_item->form_field );
                                    $meta_field = $custom_fields_item->mailchimp_field_meta;

                                    if ( strlen( $mailchimp_field_id ) == 0 || strlen( $form_field ) == 0 || strlen( $meta_field ) == 0 )
                                        continue ;

                                    list( $list_id ) = explode( '_', $mailchimp_field_id );

                                    if ( ! $list_id || ! isset( $lists[$list_id] ) )
                                        continue ;

                                    $meta_field = json_decode( $meta_field );

                                    if ( ! $meta_field )
                                        continue ;

                                    $mailchimp_field_tag = Utils::get_value( $meta_field, 'tag' );
                                    if ( strlen( $mailchimp_field_tag ) == 0 )
                                        continue ;

                                    $field_value = Cf7_Helper::resolve_tags( $form_field, $submission_data );

                                    $lists[$list_id]['fields'][$mailchimp_field_tag] = $field_value;
                                }
                            }
                        }
                    }
                }
            }
        }

        if ( count( $lists ) == 0 )
            return ;

        $subscription_options = array(
            'double_optin' => $settings->get_option( 'double_optin' ),

            'update_existing' => $settings->get_option( 'update_existing' ),
        );

        $mailchimp = new MailChimp_API( $api_key );
        if ( ! $mailchimp->subscribe( $email, $name, $lists, $subscription_options ) ) {
            System_Log::error( CF7C_Helper::get_cf7form_log_message_prefix( $form ) . 'MailChimp subscription is failed: Error: ' . $mailchimp->get_last_error(), array( 'email' => $email, 'name' => $name, 'lists' => $lists ) );
        } else {
            App_Log::info( CF7C_Helper::get_cf7form_log_message_prefix( $form ) . 'Subscribe a user to MailChimp list(s).', array( 'email' => $email, 'name' => $name, 'lists' => $lists ) );
        }
    }
}
