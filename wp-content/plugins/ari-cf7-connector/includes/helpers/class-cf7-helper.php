<?php
namespace Ari_Cf7_Connector\Helpers;

class Cf7_Helper {
    static public function scan_form_tags( $cf7_form, $cond = null ) {
        if ( empty( $cf7_form ) )
            return array();

        $tags = null;
        if ( method_exists( $cf7_form, 'scan_form_tags' ) ) {
            $tags = $cf7_form->scan_form_tags( $cond );
        } else if ( method_exists( $cf7_form, 'form_scan_shortcode' ) ) {
            $tags = $cf7_form->form_scan_shortcode( $cond );
        } else {
            $tags = array();
        }

        return $tags;
    }

    static public function get_tag_types() {
        if ( ! class_exists( 'WPCF7_FormTagsManager' ) ) {
            return array();
        }

        $tag_manager = \WPCF7_FormTagsManager::get_instance();

        $refl_class = new \ReflectionClass( 'WPCF7_FormTagsManager' );
        $refl_tag_types = $refl_class->getProperty( 'tag_types' );
        $refl_tag_types->setAccessible( true );
        $tag_types = $refl_tag_types->getValue( $tag_manager );

        return $tag_types;
    }

    static public function resolve_tags( $content, $data, $is_html = false ) {
        $tag_regex = '/\[\s*([A-Z_][A-Z0-9:._-]*)\s*\]/i';
        $matches = null;

        $content = preg_replace_callback(
            $tag_regex,
            function( $match ) use ( $data, $is_html ) {
                if ( empty( $match[1] ) || ! isset( $data[$match[1]] ) )
                    return $match[0];

                $tag_name = $match[1];
                if ( ! isset( $data[$tag_name] ) ) {
                    if ( $special_tag = apply_filters( 'wpcf7_special_mail_tags', '', $tag_name ) )
                        return $special_tag;

                    return $match[0];
                }

                $tag_data = $data[$tag_name];
                $replace_value = null;

                if ( is_array( $tag_data ) ) {
                    $replace_value = implode( ', ', $tag_data );
                } else {
                    $replace_value = $tag_data;
                }

                if ( $is_html ) {
                    $replace_value = wptexturize( strip_tags( $replace_value ) );
                }

                $replace_value = apply_filters( 'wpcf7_mail_tag_replaced', $replace_value, $tag_data );

                return stripslashes( $replace_value );
            },
            $content
        );

        return $content;
    }
}
