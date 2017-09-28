<?php
namespace Ari_Cf7_Connector\Helpers;

use Ari\Utils\Object as Object_Helper;
use Ari\Utils\Filter as Filter;
use Ari_Cf7_Connector\Helpers\Settings as Cf7_Connector_Settings;

abstract class Plugin {
    protected $slug;

    protected $settings = array();

    protected $options;

    protected $enabled = null;

    function __construct( $options = array() ) {
        if ( ! isset( $options['class_prefix'] ) ) {
            $options['class_prefix'] = Object_Helper::get_namespace( $this );
        }

        if ( ! isset( $options['path'] ) ) {
            $options['path'] = Object_Helper::get_path( $this );
        }

        if ( ! isset( $options['view_path'] ) ) {
            $options['view_path'] = $options['path'] . '/views/';
        }

        $this->options = new Plugin_Options( $options );

        if ( empty( $this->slug ) ) {
            $class = strtolower( get_class( $this ) );
            $class = explode( '\\', $class );
            $this->slug = $class[ count( $class) - 2 ];
        }

        $this->init();
    }

    protected function init() {
        if ( count( $this->settings ) > 0 ) {
            add_filter( 'ari-cf7connector-settings', function( $default_settings ) {
                $default_settings['plugins'][$this->slug] = $this->settings;

                return $default_settings;
            });
        }
    }

    public function get_title() {
        return '';
    }

    public function get_slug() {
        return $this->slug;
    }

    public function is_enabled( $force = false ) {
        if ( ! $force && is_bool( $this->enabled ) )
            return $this->enabled;

        $disabled_plugins = Cf7_Connector_Settings::instance()->get_option( 'disabled_plugins' );

        $this->enabled = ( ! is_array( $disabled_plugins ) || ! in_array( $this->get_slug(), $disabled_plugins ) );

        return $this->enabled;
    }

    public function has_settings_ui() {
        return true;
    }

    public function settings_panel() {
        return $this->get_controller_output( 'settings' );
    }

    public function add_panels_to_wpcf7( $panels ) {
        $new_panels = array(
            'cf7connector-' . $this->slug => array(
                'title' => $this->get_title(),

                'callback' => function( $form ) {
                    $this->show_cf7_panel( $form );
                }
            )
        );

        return array_merge( $panels, $new_panels );
    }

    public function on_save_cf7_form_settings( $form ) {
    }

    public function on_cf7_form_submission( $form, $submission ) {
    }

    public function save_settings() {
        return true;
    }

    protected function show_cf7_panel( $form ) {
        $options = array(
            'model_options' => array(
                'state' => array(
                    'form' => $form,
                ),
            ),
        );

        $this->execute_controller( 'cf7_panel', 'display', $options );
    }

    protected function show_template( $tmpl, $data = null ) {
        require $tmpl;
    }

    protected function output_template( $tmpl, $data = null ) {
        ob_start();

        $this->show_template( $tmpl, $data );

        $output = ob_get_clean();

        return $output;
    }

    protected function execute_controller( $ctrl_name, $ctrl_action = 'display', $options = array() ) {
        $ctrl_name = Filter::filter_cmd( $ctrl_name );
        $ctrl_action = Filter::filter_cmd( $ctrl_action );

        if ( empty( $ctrl_name ) || empty( $ctrl_action ) ) {
            throw new \BadMethodCallException(
                sprintf(
                    __CLASS__ . '::' . __METHOD__ . ': Could not execute "%1$s" method in "%2$s" controller',
                    $ctrl_action,
                    $ctrl_name
                )
            );
        }

        $ctrl_name = \Ari_Loader::prepare_name( $ctrl_name );
        $ctrl_action = \Ari_Loader::prepare_name( $ctrl_action );

        $controller_class = $this->options->class_prefix . '\\Controllers\\' . $ctrl_name . '\\' . $ctrl_action;
        $ctrl_options = array(
            'class_prefix' => $this->options->class_prefix,

            'domain' => $ctrl_name,

            'path' => $this->options->path,

            'view_path' => $this->options->view_path,
        );

        if ( is_array( $options ) && count( $options ) > 0 ) {
            $ctrl_options = array_merge_recursive(
                $ctrl_options,
                $options
            );
        }

        $ctrl = new $controller_class( $ctrl_options );
        $ctrl->execute();
    }

    protected function get_controller_output( $ctrl_name, $ctrl_action = 'display', $options = array() ) {
        ob_start();

        $this->execute_controller( $ctrl_name, $ctrl_action, $options );

        $content = ob_get_clean();

        return $content;
    }
}

