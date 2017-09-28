<?php
namespace Ari_Cf7_Connector\Forms\Fields;

use Ari\Forms\Fields\Text as Text;
use Ari\Utils\Array_Helper as Array_Helper;

class Cf7_Text extends Text {
    public function __set( $name, $value ) {
        switch ( $name ) {
            case 'supported_types':
                $this->$name = Array_Helper::ensure_array( $value );
                break;

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
        $tags = $this->tags;
        $supported_types = $this->supported_types;

        if ( is_null( $supported_types ) || in_array( '*', $supported_types ) )
            return $tags;

        $filtered_tags = array();

        foreach ( $tags as $tag ) {
            if ( ! in_array( $tag['basetype'], $supported_types ) )
                continue ;

            $filtered_tags[] = $tag;
        }

        return $filtered_tags;
    }

    public function output() {
        $output = parent::output();

        $tags = $this->tags();
        $id = $this->get_id();

        $tags_output = array();
        $tags_output[] = '<div class="ari-cf7c-tags-panel">';

        foreach ( $tags as $tag ) {
            $tag_name = $tag['name'];
            if ( empty( $tag_name ) )
                continue ;

            $tag_formatted = '[' . $tag['name'] . ']';

            $tags_output[] = sprintf(
                '<span class="ari-cf7c-tag button button-small ari-cf7c-select-all ari-cf7c-insert-content" data-insert-content="%1$s" data-insert-control="%3$s">%2$s</span>',
                esc_attr( $tag_formatted ),
                $tag_formatted,
                esc_attr( '#' . $id )
            );
        }

        $tags_output[] = '</div>';

        return $output . implode( $tags_output );
    }
}
