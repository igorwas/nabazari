<?php
namespace Ari_Cf7_Connector\Forms\Fields;

use Ari\Forms\Field as Field;
use Ari\Utils\Object as Object_Helper;

class Cloner extends Field {
    static protected $assets_loaded = false;

    protected $template = '';

    protected $template_file;

    function __construct( $options = array() ) {
        if ( ! isset( $options['template'] ) ) {
            if ( empty( $this->template_file ) ) {
                $template_file = Object_Helper::get_path( $this ) . '/tmpl/' . str_replace( '_', '-', strtolower( Object_Helper::extract_name( $this ) ) ) . '.php';

                if ( file_exists( $template_file ) )
                    $this->template_file = $template_file;
            }

            if ( file_exists( $this->template_file ) ) {
                ob_start();

                require $this->template_file;

                $options['template'] = ob_get_clean();
            }
        }

        parent::__construct( $options );
    }

    public function __set( $name, $value ) {
        switch ( $name ) {
            case 'template':
                $this->template = (string) $value;
                break;

            default:
                parent::__set( $name, $value );
        }
    }

    protected function load_assets() {
        if ( self::$assets_loaded )
            return ;

        wp_enqueue_script( 'ari-cf7connector-cloner' );
        wp_enqueue_script( 'ari-form-elements' );

        self::$assets_loaded = true;
    }

    public function output() {
        $this->load_assets();

        $id = $this->get_id();
        $cloner_id = $id . '_cloner';
        $value = $this->value;

        if ( is_string( $value ) && strlen( $value ) == 0 ) {
            $value = null;
        }

        if ( ! is_null( $value ) ) {
            $value = array(
                $this->id => $value,
            );
        }

        $output = array();
        $output[] = sprintf(
            '<div data-cloner-metadata-container="%1$s" data-cloner-storage-id="%2$s">',
            $cloner_id,
            $id
        );
        $output[] = str_replace(
            '{$id}',
            $cloner_id,
            $this->template
        );

        $output[] = sprintf(
            '<input type="hidden" id="%1$s" name="%2$s" value="%3$s" />',
            $id,
            $this->get_name(),
            htmlspecialchars( json_encode( $value, JSON_NUMERIC_CHECK ), ENT_COMPAT, 'UTF-8' )
        );

        $output[] = '</div>';

        return implode( $output );
    }
}
