<?php
namespace Ari_Cf7_Connector_Plugins\Mailerlite\Views\Settings;

use Ari\Views\View as View;

class Html extends View {
    public $form;

    public function display( $tmpl = null ) {
        $data = $this->get_data();

        $this->form = $data['form'];

        parent::display( $tmpl );
    }
}
