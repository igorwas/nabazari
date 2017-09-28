<?php
namespace Ari_Cf7_Connector_Plugins\Mailchimp\Forms;

use Ari\Forms\Form as Form;

class Settings extends Form {
    function __construct( $options = array() ) {
        if ( ! isset( $options['prefix'] ) ) {
            $options['prefix'] = ARICF7CONNECTOR_MAILCHIMP_SETTINGS_NAME;
        }

        if ( ! isset( $options['fields_namespace'] ) ) {
            $options['fields_namespace'] = array(
                '\\Ari_Cf7_Connector\\Forms\\Fields',

                '\\Ari_Cf7_Connector_Plugins\\Mailchimp\\Forms\\Fields',
            );
        }

        parent::__construct( $options );
    }

    protected function setup() {
        $this->register_groups(
            array(
                'mailchimp',
            )
        );

        $this->register_fields(
            array(
                array(
                    'id' => 'apikey_list',

                    'label' => __( 'API Key', 'contact-form-7-connector' ),

                    'description' => __( 'Mailchimp API key is required for integration with Mailchimp. If want to integrate several forms with Mailchimp, you can enter a key only once here and then use it in the forms. API key can also be entered directly in form settings.', 'contact-form-7-connector' ),

                    'type' => 'mailchimp_apikey_list',
                ),

                array(
                    'id' => 'double_optin',

                    'label' => __( 'Double opt-in', 'contact-form-7-connector' ),

                    'description' => __( 'If the parameter is enabled, a user will be added to list(s) when click by a confirmation link from invitation mail otherwise the user will be added automatically.', 'contact-form-7-connector' ),

                    'type' => 'checkbox',

                    'postfix' => true,
                ),
            ),

            'mailchimp'
        );
    }
}
