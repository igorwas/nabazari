<?php
namespace Ari_Cf7_Connector\Forms\Fields;

use Ari\Forms\Fields\Checkbox_Group as Checkbox_Group;
use Ari_Cf7_Connector\Helpers\Plugin_Helper as Plugin_Helper;

class Cf7_Plugins extends Checkbox_Group {
    function __construct( $options = array() ) {
        $items = array();
        $plugins = Plugin_Helper::get_installed_plugins();

        foreach ( $plugins as $plugin ) {
            $items[] = array(
                'value' => $plugin->get_slug(),

                'label' => $plugin->get_title(),
            );
        }

        $options['options'] = $items;

        parent::__construct( $options );
    }
}