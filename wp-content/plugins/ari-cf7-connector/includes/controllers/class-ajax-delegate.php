<?php
namespace Ari_Cf7_Connector\Controllers;

use Ari\Controllers\Controller as Controller;

class Ajax_Delegate extends Controller {
    function __construct( $options = array() ) {
        $this->options = new Ajax_Delegate_Options( $options );
    }
}
