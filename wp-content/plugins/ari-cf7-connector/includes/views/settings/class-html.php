<?php
namespace Ari_Cf7_Connector\Views\Settings;

use Ari_Cf7_Connector\Views\Base as Base;
use Ari\Controls\Tabs\Tabs as Tabs;
use Ari_Cf7_Connector\Helpers\Plugin_Helper as Plugin_Helper;

class Html extends Base {
    public $tabs;

    public function display( $tmpl = null ) {
        $this->set_title( sprintf( __( 'Contact Form 7 Connector - Settings v. %s', 'contact-form-7-connector' ), ARICF7CONNECTOR_VERSION ) );

        wp_enqueue_style( 'ari-qtip' );

        $this->init_tabs();

        parent::display( $tmpl );
    }

    protected function groups_output( $groups ) {
        $data = $this->get_data();
        $form = $data['form'];

        return $form->groups_output( $groups );
    }

    public function init_tabs() {
        $plugins = Plugin_Helper::get_active_plugins();
        $tabs = array(
            array(
                'id' => 'general',

                'title' => __( 'General', 'contact-form-7-connector' ),

                'content' => function() {
                    $groups = array(
                        'general',
                    );

                    return $this->groups_output( $groups );
                }
            )
        );

        foreach ( $plugins as $plugin ) {
            if ( ! $plugin->has_settings_ui() )
                continue ;

            $tab = array(
                'id' => 'plugin-' . $plugin->get_slug(),

                'title' => $plugin->get_title(),

                'content' => function() use ( $plugin ) {
                    return $plugin->settings_panel();
                }
            );

            $tabs[] = $tab;
        }

        $tabs[] = array(
            'id' => 'upgrade',

            'title' => __( 'Upgrade', 'contact-form-7-connector' ),

            'content' => '<p>Like the plugin, but need more features? <a href="http://contact-form-7-connector.ari-soft.com/#pricing" target="_blank">Upgrade to PRO</a> version:
                <ul class="ari-features">
                    <li><strong>Zapier</strong> integration. Zapier platform helps to connect CF7 with 500+ popular services.</li>
                    <li><strong>Extended MailChimp functionality</strong>: segmentation support.</li>
                    <li><strong>Advanced subscription</strong>: subscribe users to different lists / groups depends on selected form element.</li>
                    <li>and other awesome features.</li>
                </ul>
            </p>'
        );

        $tabs_options = array(
            'items' => $tabs,
        );

        $tabs_options = apply_filters( 'ari-cf7connector-tabs-options', $tabs_options );

        $tabs = new Tabs(
            'cf7c_settings_tabs',

            $tabs_options
        );

        $this->tabs = $tabs;
    }
}
