<?php
namespace Ari_Cf7_Connector\Helpers;

class Screen {
    static public function register() {
        $screen = get_current_screen();

        $screen->add_help_tab(
            array(
                'id' => 'ari_cf7connector_help_tab',
                'title'	=> __( 'Help', 'contact-form-7-connector' ),
                'content' => sprintf(
                    '<p>' . __( 'User\'s guide is available <a href="%s" target="_blank">here</a>.', 'contact-form-7-connector' ) . '</p>',
                    'http://www.ari-soft.com/docs/wordpress/contact-form-7-connector/v1/en/index.html'
                )
            )
        );
    }
}
