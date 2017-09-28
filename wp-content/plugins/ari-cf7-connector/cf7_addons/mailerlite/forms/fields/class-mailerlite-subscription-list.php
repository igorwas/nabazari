<?php
namespace Ari_Cf7_Connector_Plugins\Mailerlite\Forms\Fields;

use Ari_Cf7_Connector\Forms\Fields\Cloner as Cloner_Field;

class Mailerlite_Subscription_List extends Cloner_Field {
    function __construct( $options = array() ) {
        parent::__construct( $options );
    }

    public function __set( $name, $value ) {
        switch ( $name ) {
            case 'tags':
                if ( ! is_array( $value ) )
                    $value = array();

                $this->$name = $value;
                break;

            default:
                parent::__set( $name, $value );
        }
    }

    protected function tags() {
        return $this->tags;
    }

    public function output() {
        $output = parent::output();

        $tags = $this->tags();
        $tags_options = array();

        if ( is_array( $tags ) ) {
            foreach ( $tags as $tag ) {
                $tag_name = $tag['name'];
                if ( empty( $tag_name ) )
                    continue ;

                $tags_options[] = sprintf(
                    '<option value="%1$s">%2$s</option>',
                    esc_attr( '[' . $tag_name . ']' ),
                    '[' . $tag_name . ']'
                );
            }
        }

        $output = str_replace( '{{tags_options}}', implode( $tags_options ), $output );

        return $output;
    }
}
