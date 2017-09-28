<?php
namespace Ari_Cf7_Connector_Plugins\Mailerlite\Forms;

use Ari\Forms\Form as Form;

class Settings extends Form {
    function __construct( $options = array() ) {
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
        $this->register_groups(
            array(
                'mailerlite',
            )
        );

        $this->register_fields(
            array(
                array(
                    'id' => 'apikey_list',

                    'label' => __( 'API Key', 'contact-form-7-connector' ),

                    'description' => __( 'MailerLite API key is required for integration with MailerLite. If want to integrate several forms with MailerLite, you can enter a key only once here and then use it in the forms. API key can also be entered directly in form settings.', 'contact-form-7-connector' ),

                    'type' => 'mailerlite_apikey_list',
                ),
            ),

            'mailerlite'
        );
    }
}
