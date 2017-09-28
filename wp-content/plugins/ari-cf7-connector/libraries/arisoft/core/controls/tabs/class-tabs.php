<?php
namespace Ari\Controls\Tabs;

class Tabs {
    static protected $assets_loaded = false;

    protected $id = null;

    protected $options = null;

    function __construct( $id, $options ) {
        $this->id = $id;
        $this->options = new Tabs_Options( $options );

        $this->prepare_items();
    }

    protected function prepare_items() {
        $has_active = false;

        foreach ( $this->options->items as $item ) {
            if ( $item->active ) {
                if ( $has_active ) {
                    $item->active = false;
                } else {
                    $has_active = true;
                }
            }
        }

        if ( ! $has_active && count( $this->options->items ) > 0 ) {
            $this->options->items[0]->active = true;
        }
    }

    public function render() {
        $this->load_assets();

        require dirname( __FILE__ ) . '/tmpl/tabs.php';
    }

    static public function load_assets() {
        if ( self::$assets_loaded ) {
            return ;
        }

        wp_enqueue_script( 'ari-wp-tabs' );

        self::$assets_loaded = true;
    }
}
