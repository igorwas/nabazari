<?php
namespace Ari_Cf7_Connector_Plugins\Mailchimp\Views\Cf7_Panel;

use Ari\Views\View as View;
use Ari_Cf7_Connector\Helpers\App_Helper as App_Helper;

class Html extends View {
    public $form;

    public function display( $tmpl = null ) {
        wp_enqueue_style( 'ari-select2' );

        wp_enqueue_script( 'cf7-connector-mailchimp-panel', ARICF7CONNECTOR_URL . 'cf7_addons/mailchimp/assets/cf7-panel.js', array( 'ari-cf7connector-cf7-helper', 'ari-button', 'ari-select2' ), ARICF7CONNECTOR_VERSION );
        wp_enqueue_style( 'cf7-connector-mailchimp-panel', ARICF7CONNECTOR_URL . 'cf7_addons/mailchimp/assets/cf7-panel.css', array(), ARICF7CONNECTOR_VERSION );

        $data = $this->get_data();

        $this->form = $data['form'];
        $apikey_field = $this->form->field_by_id( 'apikey' );

        $js_options = array(
            'apiKey' => $apikey_field->get_value(),
        );

        wp_localize_script( 'cf7-connector-mailchimp-panel', 'ARI_CF7C_CF7_MAILCHIMP', $js_options );

        App_Helper::add_message( 'mailchimp_remove_list', __( 'Do you want to remove the selected list section?', 'contact-form-7-connector' ) );

        parent::display( $tmpl );
    }
}