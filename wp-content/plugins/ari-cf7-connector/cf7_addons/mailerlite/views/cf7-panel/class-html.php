<?php
namespace Ari_Cf7_Connector_Plugins\Mailerlite\Views\Cf7_Panel;

use Ari\Views\View as View;

class Html extends View {
    public $form;

    public function display( $tmpl = null ) {
        wp_enqueue_style( 'ari-select2' );

        wp_enqueue_script( 'cf7-connector-mailerlite-panel', ARICF7CONNECTOR_URL . 'cf7_addons/mailerlite/assets/cf7-panel.js', array( 'ari-cf7connector-cf7-helper', 'ari-button', 'ari-select2' ), ARICF7CONNECTOR_VERSION );
        wp_enqueue_style( 'cf7-connector-mailerlite-panel', ARICF7CONNECTOR_URL . 'cf7_addons/mailerlite/assets/cf7-panel.css', array(), ARICF7CONNECTOR_VERSION );

        $data = $this->get_data();

        $this->form = $data['form'];
        $apikey_field = $this->form->field_by_id( 'apikey' );

        $js_options = array(
            'apiKey' => $apikey_field->get_value(),
        );

        wp_localize_script( 'cf7-connector-mailerlite-panel', 'ARI_CF7C_CF7_MAILERLITE', $js_options );

        parent::display( $tmpl );
    }
}