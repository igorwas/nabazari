<?php
namespace Ari_Cf7_Connector\Forms;

use Ari\Forms\Form as Form;

class Settings extends Form {
    function __construct( $options = array() ) {
        if ( ! isset( $options['prefix'] ) ) {
            $options['prefix'] = ARICF7CONNECTOR_SETTINGS_NAME;
        }

        if ( ! isset( $options['fields_namespace'] ) ) {
            $options['fields_namespace'] = array( '\\Ari_Cf7_Connector\\Forms\\Fields' );
        }

        parent::__construct( $options );
    }

    protected function setup() {
        $app_log_description = __( 'If the parameter is enabled, the extension will log events like "Add subscriber" and etc.', 'contact-form-7-connector' );
        $debug_log_description = __( 'If the parameter is enabled, all errors will be saved into a log. It is useful if need to investigate a problem.', 'contact-form-7-connector' );
        $view_log_message = __( 'View log', 'contact-form-7-connector' );

        $this->register_groups(
            array(
                'general',
            )
        );

        $this->register_fields(
            array(
                array(
                    'id' => 'disabled_plugins',

                    'label' => __( 'Disabled add-ons', 'contact-form-7-connector' ),

                    'description' => __( 'The selected add-ons will be disabled and not be used with "Contact Form 7" plugin. Disable all add-ons which is not required for your site for better performance.', 'contact-form-7-connector' ),

                    'type' => 'cf7_plugins',
                ),

                array(
                    'id' => 'app_log',

                    'label' => __( 'Application log', 'contact-form-7-connector' ),

                    'description' => $app_log_description,

                    'type' => 'checkbox',

                    'postfix' => sprintf(
                        '%s <a href="%s" target="_blank">%s</a>',
                        $app_log_description,
                        admin_url( 'admin.php?page=ari-cf7connector-log&format=html&log=app' ),
                        $view_log_message
                    ),
                ),

                array(
                    'id' => 'debug_log',

                    'label' => __( 'Debug log', 'contact-form-7-connector' ),

                    'description' => $debug_log_description,

                    'type' => 'checkbox',

                    'postfix' => sprintf(
                        '%s <a href="%s" target="_blank">%s</a>',
                        $debug_log_description,
                        admin_url( 'admin.php?page=ari-cf7connector-log&format=html&log=debug' ),
                        $view_log_message
                    ),
                ),

                array(
                    'id' => 'clean_uninstall',

                    'label' => __( 'Clean uninstall', 'contact-form-7-connector' ),

                    'description' => __( 'If the parameter is enabled, all data will be removed from a database when the plugin is uninstalled. Do not activate the parameter if want to upgrade to PRO version or re-install the plugin and need to save all settings.', 'contact-form-7-connector' ),

                    'type' => 'checkbox',

                    'postfix' => true,
                ),
            ),

            'general'
        );

        do_action( 'ari-cf7connector-options-setup', $this );
    }
}
