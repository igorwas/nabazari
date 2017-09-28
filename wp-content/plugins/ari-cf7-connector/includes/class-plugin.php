<?php
namespace Ari_Cf7_Connector;

use Ari\App\Plugin as Ari_Plugin;
use Ari\Utils\Request as Request;
use Ari\Utils\Response as Response;
use Ari_Cf7_Connector\Helpers\App_Helper as App_Helper;
use Ari_Cf7_Connector\Helpers\Settings as Settings;
use Ari_Cf7_Connector\Helpers\Screen as Screen;
use Ari_Cf7_Connector\Helpers\Plugin_Helper as Plugin_Helper;

class Plugin extends Ari_Plugin {
    public function init() {
        $this->load_translations();
        $this->init_plugins();

        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', function() { $this->admin_enqueue_scripts(); } );
            add_action( 'admin_menu', function() { $this->admin_menu(); }, 20 );
            add_action( 'admin_init', function() { $this->admin_init(); } );

            add_filter( 'wpcf7_editor_panels', function( $panels ) {
                return $this->wpcf7_add_panels( $panels );
            } );
            add_action( 'wpcf7_after_save', function( $form ) {
                $this->wpcf7_save_form( $form );
            });
            add_action( 'ari-cf7connector-save-settings', function( $prev_settings ) {
                $this->on_save_settings( $prev_settings );
            });

            add_filter( 'plugin_action_links_' . plugin_basename( ARICF7CONNECTOR_EXEC_FILE ) , function( $links ) {
                return $this->plugin_action_links( $links );
            });
        } else {
            add_action( 'wpcf7_before_send_mail', function( $form ) {
                $this->wpc7_form_submission( $form );
            });
        }

        parent::init();
    }

    private function init_plugins() {
        Plugin_Helper::get_installed_plugins();
    }

    private function load_translations() {
        load_plugin_textdomain( 'contact-form-7-connector', false, ARICF7CONNECTOR_SLUG . '/languages' );
    }

    private function admin_menu() {
        $pages = array();
        $settings_cap = 'manage_options';

        $position = null;
        $wp_version = get_bloginfo( 'version' );

        if ( version_compare( $wp_version, '4.4', '>=' ) ) {
            global $menu;

            if ( is_array( $menu ) ) {
                foreach ( $menu as $menu_pos => $menu_item ) {
                    if ( isset( $menu_item[2] ) && 'wpcf7' === $menu_item[2] ) {
                        $position = $menu_pos;
                        break;
                    }
                }
            }
        }

        $pages[] = add_menu_page(
            __( 'CF7 Connector', 'contact-form-7-connector' ),
            __( 'CF7 Connector', 'contact-form-7-connector' ),
            $settings_cap,
            'ari-cf7connector',
            array( $this, 'display_settings' ),
            ! ARI_WP_LEGACY ? 'dashicons-email' : '',
            $position
        );

        foreach ( $pages as $page ) {
            add_action( 'load-' . $page, function() {
                Screen::register();
            });
        }

        // Hidden pages
        add_submenu_page(
            null,
            '',
            '',
            $settings_cap,
            'ari-cf7connector-log',
            array( $this, 'display_log' )
        );
    }

	private function admin_enqueue_scripts() {
		$options = $this->options;

        wp_register_script( 'ari-modal', $options->assets_url . 'modal/js/modal.js', array( 'jquery' ), $options->version );
        wp_register_style( 'ari-modal', $options->assets_url . 'modal/css/modal.css', array(), $options->version );

        wp_register_script( 'ari-cf7connector-app', $options->assets_url . 'common/app.js', array( 'jquery' ), $options->version );
        wp_register_script( 'ari-cf7connector-app-helper', $options->assets_url . 'common/helper.js', array( 'ari-cf7connector-app', 'ari-modal' ), $options->version );
        wp_register_style( 'ari-cf7connector-app', $options->assets_url . 'common/css/style.css', array(), $options->version );

        wp_register_script( 'ari-scrollto', $options->assets_url . 'scroll_to/jquery.scrollTo.min.js', array( 'jquery' ), $options->version );

        wp_register_script( 'ari-button', $options->assets_url . 'common/button.js', array( 'jquery' ), $options->version );

        wp_register_style( 'ari-qtip', $options->assets_url . 'qtip/css/jquery.qtip.min.css', array(), $options->version );
        wp_register_script( 'ari-qtip', $options->assets_url . 'qtip/js/jquery.qtip.min.js', array( 'jquery' ), $options->version );

        wp_register_style( 'ari-select2', $options->assets_url . 'select2/css/select2.min.css', array(), $options->version );
        wp_register_script( 'ari-select2', $options->assets_url . 'select2/js/select2.min.js', array( 'jquery' ), $options->version );

        wp_register_script( 'ari-cf7connector-cloner', $options->assets_url . 'cloner/js/jquery.cloner.min.js', array( 'jquery', 'jquery-ui-draggable', 'jquery-ui-sortable', 'ari-scrollto' ), $options->version );

        wp_register_script( 'ari-form-elements', $options->assets_url . 'common/form-elements.js', array( 'jquery', 'jquery-ui-slider', 'ari-qtip', 'jquery-ui-spinner', 'ari-cf7connector-cloner' ), $options->version );
        wp_register_script( 'ari-wp-tabs', $options->assets_url . 'common/tabs.js', array( 'jquery' ), $options->version );

        wp_register_script( 'ari-cf7connector-cf7-helper', $options->assets_url . 'cf7/cf7.js', array( 'ari-cf7connector-app-helper', 'ari-qtip' ), $options->version );
        wp_register_style( 'ari-cf7connector-cf7-helper', $options->assets_url . 'cf7/cf7.css', array(), $options->version );
    }

    private function admin_init() {
        if ( get_option( 'ari_cf7connector_redirect', false ) ) {
            delete_option( 'ari_cf7connector_redirect' );
            if ( ! isset( $_GET['activate-multi'] ) ) {
                Response::redirect( admin_url( 'admin.php?page=ari-cf7connector' ) );
            }
        }

        $no_header = (bool) Request::get_var( 'noheader' );

        if ( ! $no_header ) {
            $page = Request::get_var( 'page' );

            if ( $this->options->page_prefix && 0 === strpos( $page, $this->options->page_prefix ) ) {
                ob_start();

                add_action( 'admin_page_' . $page , function() {
                    ob_end_flush();
                }, 99 );
            }
        }
    }

    protected function need_to_update() {
        $installed_version = get_option( ARICF7CONNECTOR_VERSION_OPTION );

        return ( $installed_version != $this->options->version );
    }

    protected function install() {
        $installer = new \Ari_Cf7_Connector\Installer();

        return $installer->run();
    }

    private function wpcf7_add_panels( $panels ) {
        wp_enqueue_style( 'ari-modal' );
        wp_enqueue_style( 'ari-qtip' );
        wp_enqueue_style( 'ari-cf7connector-app' );
        wp_enqueue_style( 'ari-cf7connector-cf7-helper' );
        wp_enqueue_script( 'ari-cf7connector-cf7-helper' );

        $tag_types = \Ari_Cf7_Connector\Helpers\Cf7_Helper::get_tag_types();

        $js_options = array(
            'tag_types' => array_keys( $tag_types ),
        );

        wp_localize_script( 'ari-cf7connector-cf7-helper', 'ARI_CF7C_SETTINGS', $js_options );

        $plugins = Plugin_Helper::get_active_plugins();

        foreach ( $plugins as $plugin ) {
            $panels = $plugin->add_panels_to_wpcf7( $panels );
        }

        add_action( 'wpcf7_admin_footer', function( $post ) {
            $global_app_options = App_Helper::get_global_app_options( 'wpcf7-admin-form-element' );

            wp_localize_script( 'ari-cf7connector-app', 'ARI_APP', $global_app_options );
        });

        return $panels;
    }

    private function wpcf7_save_form( $form ) {
        $plugins = Plugin_Helper::get_active_plugins();

        foreach ( $plugins as $plugin ) {
            $plugin->on_save_cf7_form_settings( $form );
        }
    }

    private function wpc7_form_submission( $form ) {
        $plugins = Plugin_Helper::get_active_plugins();
        $submission = \WPCF7_Submission::get_instance();

        foreach ( $plugins as $plugin ) {
            $plugin->on_cf7_form_submission( $form, $submission );
        }
    }

    private function on_save_settings( $prev_settings ) {
        $prev_disabled_plugins = is_array( $prev_settings['disabled_plugins'] ) ? $prev_settings['disabled_plugins'] : array();
        $plugins = Plugin_Helper::get_active_plugins( true );

        foreach ( $plugins as $plugin ) {
            if ( ! in_array( $plugin->get_slug(), $prev_disabled_plugins ) )
                $plugin->save_settings();
        }
    }

    private function plugin_action_links( $links ) {
        $settings_link = '<a href="admin.php?page=ari-cf7connector">' . __( 'Settings', 'contact-form-7-connector' ) . '</a>';
        $support_link = '<a href="http://www.ari-soft.com/Contact-Form-7-Connector/" target="_blank">' . __( 'Support', 'contact-form-7-connector' ) . '</a>';
        $upgrade_link = '<a href="http://contact-form-7-connector.ari-soft.com/#pricing" target="_blank"><b>' . __( 'Upgrade', 'contact-form-7-connector' ) . '</b></a>';

        $links[] = $settings_link;
        $links[] = $support_link;
        $links[] = $upgrade_link;

        return $links;
    }
}
