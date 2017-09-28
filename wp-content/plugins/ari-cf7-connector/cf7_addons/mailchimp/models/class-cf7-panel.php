<?php
namespace Ari_Cf7_Connector_Plugins\Mailchimp\Models;

use Ari\Models\Model as Model;
use Ari_Cf7_Connector_Plugins\Mailchimp\Helpers\Helper as Helper;

class Cf7_Panel extends Model {
    public function data() {
        $cf7_form = $this->get_state( 'form' );

        $form = Helper::get_cf7_settings_form( $cf7_form );

        $data = array(
            'form' => $form,
        );

        return $data;
    }
}
