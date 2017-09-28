<?php
namespace Ari_Cf7_Connector\Views;

use Ari\Views\View as View;
use Ari\Utils\Request as Request;
use Ari_Cf7_Connector\Helpers\App_Helper as App_Helper;

class Base extends View {
    protected $title = '';

    public function display( $tmpl = null ) {
        wp_enqueue_style( 'ari-modal' );
        wp_enqueue_style( 'ari-cf7connector-app' );
        wp_enqueue_script( 'ari-cf7connector-app' );
        wp_enqueue_script( 'ari-cf7connector-app-helper' );

        echo '<div id="ari_cf7connector_plugin" class="wrap ari-theme">';

        $this->render_message();
        $this->render_title();

        parent::display( $tmpl );

        echo '</div>';

        $app_options = $this->get_app_options();

        $global_app_options = App_Helper::get_global_app_options( 'ari_cf7connector_plugin' );
        $global_app_options['app'] = $app_options;

        wp_localize_script( 'ari-cf7connector-app', 'ARI_APP', $global_app_options );
    }

    public function set_title( $title ) {
        $this->title = $title;
    }

    protected function render_title() {
        if ( $this->title )
            printf(
                '<h1 class="wp-heading-inline">%s</h1>',
                $this->title
            );
    }

    protected function render_message() {
        if ( ! Request::exists( 'msg' ) )
            return ;

        $message_type = Request::get_var( 'msg_type', ARICF7CONNECTOR_MESSAGETYPE_NOTICE, 'alpha' );
        $message = Request::get_var( 'msg' );

        printf(
            '<div class="notice notice-%2$s is-dismissible"><p>%1$s</p></div>',
            $message,
            $message_type
        );
    }

    protected function get_app_options() {
        $app_options = null;

        return $app_options;
    }
}
