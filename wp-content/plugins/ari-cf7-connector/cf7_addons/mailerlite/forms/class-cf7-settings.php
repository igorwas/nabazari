<?php
namespace Ari_Cf7_Connector_Plugins\Mailerlite\Forms;

use Ari\Forms\Form as Form;
use Ari_Cf7_Connector\Helpers\Cf7_Helper as Cf7_Helper;

class Cf7_Settings extends Form {
    protected $cf7_form;

    function __construct( $options = array() ) {
        if ( isset( $options['cf7_form'] ) ) {
            $this->cf7_form = $options['cf7_form'];
        }

        if ( ! isset( $options['prefix'] ) ) {
            $options['prefix'] = ARICF7CONNECTOR_MAILERLITE_SETTINGS_NAME;
        }

        if ( ! isset( $options['fields_namespace'] ) ) {
            $options['fields_namespace'] = array(
                '\\Ari_Cf7_Connector\\Forms\\Fields',

                '\\Ari_Cf7_Connector_Plugins\\Mailerlite\\Forms\\Fields',
            );
        }

        parent::__construct( $options );
    }

    protected function setup() {
        $tags = Cf7_Helper::scan_form_tags( $this->cf7_form );

        $this->register_groups(
            array(
                'mailerlite',
            )
        );

        $this->register_fields(
            array(
                array(
                    'id' => 'apikey',

                    'label' => __( 'API Key *', 'contact-form-7-connector' ),

                    'description' => __( 'API key is required for integration with MailerLite. It is used to get lists, fields and add subscribers. If API key is not defined, the integration will not work.', 'contact-form-7-connector' ),

                    'type' => 'mailerlite_apikey_selector',
                ),

                array(
                    'id' => 'name',

                    'label' => __( 'Name', 'contact-form-7-connector' ),

                    'description' => __( 'Values of the selected form element(s) will be used as name of subscriber.', 'contact-form-7-connector' ),

                    'type' => 'cf7_text',

                    'class' => 'large-text',

                    'tags' => $tags,
                ),

                array(
                    'id' => 'email',

                    'label' => __( 'Email *', 'contact-form-7-connector' ),

                    'description' => __( 'Select a form element which contains subscriber\'s email.', 'contact-form-7-connector' ),

                    'type' => 'cf7_text',

                    'class' => 'large-text',

                    'tags' => $tags,

                    'supported_types' => 'email',
                ),
/*
                array(
                    'id' => 'update_existing',

                    'label' => __( 'Update existing', 'contact-form-7-connector' ),

                    'description' => __( 'If the parameter is enabled and subscriber with the specified email exist, subscriber\'s data will be updated otherwise nothing happen and a message will be added to application log.', 'contact-form-7-connector' ),

                    'type' => 'checkbox',
                ),
*/
                array(
                    'id' => 'subscriptions',

                    'label' => __( 'Subscribe to', 'contact-form-7-connector' ),

                    'type' => 'mailerlite_subscription_list',

                    'tags' => $tags,
                ),
            ),

            'mailerlite'
        );
    }
}
