<?php
namespace Ari\Controls\Tabs;

use Ari\Utils\Array_Helper as Array_Helper;

class Tabs_Options {
    public $options = null;

    public $items = array();

    function __construct( $options = array() ) {
        $tabs_options = Array_Helper::get_value( $options, 'options', array() );
        $items = Array_Helper::get_value( $options, 'items', array() );

        $this->options = new Tabs_Main_Options( $tabs_options );

        foreach ( $items as $item ) {
            $this->items[] = new Tabs_Item_Options( $item );
        }
    }
}
