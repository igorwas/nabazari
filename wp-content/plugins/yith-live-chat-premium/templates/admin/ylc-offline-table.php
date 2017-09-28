<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'YLC_Offline_Messages' ) ) {

    /**
     * Displays the offline messages table in YITH Live Chat tab
     *
     * @class   YLC_Offline_Messages
     * @package Yithemes
     * @since   1.0.0
     * @author  Your Inspiration Themes
     *
     */
    class YLC_Offline_Messages {

        /**
         * Single instance of the class
         *
         * @var \YLC_Offline_Messages
         * @since 1.0.0
         */
        protected static $instance;

        /**
         * Returns single instance of the class
         *
         * @return \YLC_Offline_Messages
         * @since 1.0.0
         */
        public static function get_instance() {

            if ( is_null( self::$instance ) ) {

                self::$instance = new self( $_REQUEST );

            }

            return self::$instance;
        }

        /**
         * Constructor
         *
         * @since   1.0.0
         * @return  mixed
         * @author  Alberto Ruggiero
         */
        public function __construct() {

            add_filter( 'set-screen-option', array( $this, 'set_options' ), 10, 3 );
            add_action( 'current_screen', array( $this, 'add_options' ) );

        }

        /**
         * Outputs the offline messages table template
         *
         * @since   1.0.0
         * @author  Alberto Ruggiero
         * @return  string
         */
        public function output() {

            global $wpdb;

            $table = new YITH_Custom_Table( array(
                                                'singular' => __( 'message', 'yith-live-chat' ),
                                                'plural'   => __( 'messages', 'yith-live-chat' )
                                            ) );

            $table->options = array(
                'select_table'     => $wpdb->prefix . 'ylc_offline_messages',
                'select_columns'   => array(
                    'id',
                    'user_name',
                    'user_email',
                    'user_message',
                    'user_info',
                    'mail_date',
                    'mail_read',
                ),
                'select_where'     => '',
                'select_group'     => '',
                'select_order'     => 'mail_date',
                'select_order_dir' => 'DESC',
                'search_where'     => array(
                    'user_name',
                    'user_email',
                    'user_message'
                ),
                'per_page_option'  => 'msg_per_page',
                'count_table'      => $wpdb->prefix . 'ylc_offline_messages',
                'count_where'      => '',
                'key_column'       => 'id',
                'view_columns'     => array(
                    'cb'           => '<input type="checkbox" />',
                    'mail_read'    => __( 'Mail Status', 'yith-live-chat' ),
                    'mail_date'    => __( 'Date', 'yith-live-chat' ),
                    'user_name'    => __( 'User', 'yith-live-chat' ),
                    'user_email'   => __( 'E-mail', 'yith-live-chat' ),
                    'user_message' => __( 'Message', 'yith-live-chat' ),
                ),
                'hidden_columns'   => array(),
                'sortable_columns' => array(
                    'mail_read'  => array( 'mail_read', false ),
                    'mail_date'  => array( 'mail_date', false ),
                    'user_name'  => array( 'user_name', false ),
                    'user_email' => array( 'user_email', false ),
                ),
                'custom_columns'   => array(
                    'column_mail_read'    => function ( $item, $me ) {

                        if ( $item['mail_read'] != true ) {

                            return sprintf( '<i class="fa fa-envelope ylc-tips" data-tip="%s"></i>', __( 'Unread', 'yith-live-chat' ) );

                        }
                        else {

                            return sprintf( '<i class="fa fa-envelope-o ylc-tips" data-tip="%s"></i>', __( 'Read', 'yith-live-chat' ) );

                        }

                    },
                    'column_mail_date'    => function ( $item, $me ) {

                        if ( $item['mail_read'] != true ) {

                            return '<b>' . $item['mail_date'] . '</b>';

                        }
                        else {

                            return $item['mail_date'];

                        }

                    },
                    'column_user_name'    => function ( $item, $me ) {

                        $user_name = '';

                        if ( $item['mail_read'] != true ) {

                            $user_name = '<b>' . $item['user_name'] . '</b>';

                        }
                        else {

                            $user_name = $item['user_name'];

                        }

                        $view_query_args = array(
                            'page'   => $_GET['page'],
                            'action' => 'view',
                            'id'     => $item['id']
                        );
                        $view_url        = esc_url( add_query_arg( $view_query_args, admin_url( 'admin.php' ) ) );

                        $read_query_args = array(
                            'page'   => $_GET['page'],
                            'action' => 'read',
                            'id'     => $item['id']
                        );
                        $read_url        = esc_url( add_query_arg( $read_query_args, admin_url( 'admin.php' ) ) );

                        $unread_query_args = array(
                            'page'   => $_GET['page'],
                            'action' => 'unread',
                            'id'     => $item['id']
                        );
                        $unread_url        = esc_url( add_query_arg( $unread_query_args, admin_url( 'admin.php' ) ) );

                        $delete_query_args = array(
                            'page'   => $_GET['page'],
                            'action' => 'delete',
                            'id'     => $item['id']
                        );
                        $delete_url        = esc_url( add_query_arg( $delete_query_args, admin_url( 'admin.php' ) ) );

                        $actions = array(
                            'view'   => '<a href="' . $view_url . '">' . __( 'View message', 'yith-live-chat' ) . '</a>',
                            'read'   => '<a href="' . $read_url . '">' . __( 'Mark as "read"', 'yith-live-chat' ) . '</a>',
                            'unread' => '<a href="' . $unread_url . '">' . __( 'Mark as "unread"', 'yith-live-chat' ) . '</a>',
                            'delete' => '<a href="' . $delete_url . '">' . __( 'Delete', 'yith-live-chat' ) . '</a>',
                        );

                        return $user_name . $me->row_actions( $actions );

                    },
                    'column_user_email'   => function ( $item, $me ) {

                        if ( $item['mail_read'] != true ) {

                            return '<b>' . $item['user_email'] . '</b>';

                        }
                        else {

                            return $item['user_email'];

                        }
                    },
                    'column_user_message' => function ( $item, $me ) {

                        $message = ( strlen( $item['user_message'] ) > 100 ) ? substr( $item['user_message'], 0, 97 ) . '...' : $item['user_message'];

                        if ( $item['mail_read'] != true ) {

                            return '<b>' . $message . '</b>';

                        }
                        else {

                            return $message;

                        }
                    },
                ),
                'bulk_actions'     => array(
                    'actions'   => array(
                        'delete' => __( 'Delete', 'yith-live-chat' ),
                        'read'   => __( 'Mark as "read"', 'yith-live-chat' ),
                        'unread' => __( 'Mark as "unread"', 'yith-live-chat' ),
                    ),
                    'functions' => array(
                        'function_delete' => function () {
                            global $wpdb;

                            $ids = isset( $_GET['id'] ) ? $_GET['id'] : array();
                            if ( is_array( $ids ) ) {
                                $ids = implode( ',', $ids );
                            }

                            if ( !empty( $ids ) ) {
                                $wpdb->query( "DELETE FROM {$wpdb->prefix}ylc_offline_messages WHERE id IN ( $ids )" );
                            }
                        },
                        'function_unread' => function () {
                            global $wpdb;

                            $ids = isset( $_GET['id'] ) ? $_GET['id'] : array();
                            if ( is_array( $ids ) ) {
                                $ids = implode( ',', $ids );
                            }

                            if ( !empty( $ids ) ) {
                                $wpdb->query( "UPDATE {$wpdb->prefix}ylc_offline_messages SET mail_read = 0 WHERE id IN ( $ids )" );
                            }
                        },
                        'function_read'   => function () {
                            global $wpdb;

                            $ids = isset( $_GET['id'] ) ? $_GET['id'] : array();
                            if ( is_array( $ids ) ) {
                                $ids = implode( ',', $ids );
                            }

                            if ( !empty( $ids ) ) {
                                $wpdb->query( "UPDATE {$wpdb->prefix}ylc_offline_messages SET mail_read = 1 WHERE id IN ( $ids )" );
                            }
                        }
                    )
                ),
            );

            if ( defined( 'YITH_WPV_PREMIUM' ) ) {

                $vendor = yith_get_vendor( 'current', 'user' );

                $table->options['select_columns'][] = 'vendor_id';

                if ( $vendor->id == 0 ) {

                    //If current user is a global admin show the column with vendors name
                    $table->options['view_columns']['vendor']          = __( 'Vendor', 'yith-live-chat' );
                    $table->options['custom_columns']['column_vendor'] = function ( $item, $me ) {

                        if ( $item['vendor_id'] != 0 ) {

                            $vendor = yith_get_vendor( $item['vendor_id'], 'vendor' );

                            return '<b>' . $vendor->term->name . '</b>';

                        }
                        else {

                            return '-';

                        }

                    };

                }
                else {

                    //If current user is a vendor admin show only the emails to that vendor
                    $table->options['select_where'] = 'vendor_id = ' . $vendor->id;
                    $table->options['count_where']  = 'vendor_id = ' . $vendor->id;

                }

            }

            $table->prepare_items();

            $message = '';
            $notice  = '';

            $list_query_args = array(
                'page' => $_GET['page'],
            );
            $list_url        = esc_url( add_query_arg( $list_query_args, admin_url( 'admin.php' ) ) );

            if ( 'delete' === $table->current_action() ) {

                $items   = isset( $_GET['id'] ) ? count( $_GET['id'] ) : 0;
                $message = sprintf( _n( '%s message deleted successfully', '%s messages deleted successfully', $items, 'yith-live-chat' ), $items );

            }
            elseif ( 'read' === $table->current_action() || 'unread' === $table->current_action() ) {

                $items   = isset( $_GET['id'] ) ? count( $_GET['id'] ) : 0;
                $message = sprintf( _n( '%s message updated successfully', '%s messages updated successfully', $items, 'yith-live-chat' ), $items );

            }

            ?>
            <div class="wrap">
                <h2>
                    <?php _e( 'Offline messages', 'yith-live-chat' ); ?>
                </h2>
                <?php

                if ( !empty( $notice ) ) : ?>
                    <div id="notice" class="error below-h2"><p><?php echo $notice; ?></p></div>
                <?php endif;

                if ( !empty( $message ) ) : ?>
                    <div id="message" class="updated below-h2"><p><?php echo $message; ?></p></div>
                <?php endif;

                if ( isset( $_GET['id'] ) && !empty( $_GET['action'] ) && 'view' == $_GET['action'] ) : ?>
                    <?php

                    $wpdb->update(
                        $wpdb->prefix . 'ylc_offline_messages',
                        array(
                            'mail_read' => 1,
                        ),
                        array( 'id' => $_GET['id'] ),
                        array(
                            '%d'
                        ),
                        array( '%d' )
                    );

                    $select_table   = $table->options['select_table'];
                    $select_columns = implode( ',', $table->options['select_columns'] );
                    $item           = $wpdb->get_row( $wpdb->prepare( "SELECT $select_columns FROM $select_table WHERE id = %d", $_GET['id'] ), ARRAY_A );
                    $user_info      = maybe_unserialize( $item['user_info'] );

                    ?>
                    <div class="message_box">

                        <div class="mail_head">
                            <b><?php _e( 'Message Sender' ) ?>:</b>
                            <a href="mailto:<?php echo esc_attr( $item['user_email'] ); ?>">
                                <?php echo esc_attr( $item['user_name'] ) ?> (<?php echo esc_attr( $item['user_email'] ); ?>)
                            </a>
                        </div>
                        <div class="mail_body">
                            <?php echo esc_attr( $item['user_message'] ); ?>
                        </div>
                        <div class="user_info">
                            <h3><?php _e( 'User Info', 'yith-live-chat' ) ?></h3>

                            <div class="info">
                                <span><b><?php _e( 'IP Address', 'yith-live-chat' ) ?>:</b> <?php echo $user_info['ip'] ?></span>
                                <span><b><?php _e( 'OS', 'yith-live-chat' ) ?>:</b> <?php echo $user_info['os'] ?></span>
                                <span><b><?php _e( 'Browser', 'yith-live-chat' ) ?>:</b> <?php echo $user_info['browser'] . ' ' . $user_info['version'] ?></span>
                                <span><b><?php _e( 'Page', 'yith-live-chat' ) ?>:</b> <?php echo $user_info['page'] ?></span>
                            </div>
                            <div class="btn">
                                <a class="button-secondary" href="<?php echo $list_url; ?>"><?php _e( 'Return to message list', 'yith-live-chat' ); ?></a>
                            </div>
                        </div>
                    </div>
                    <br class="clear">
                <?php else : ?>
                    <form id="custom-table" method="GET" action="<?php echo $list_url; ?>">
                        <?php $table->search_box( __( 'Search Message' ), 'message' ); ?>
                        <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
                        <?php $table->display(); ?>
                    </form>
                <?php endif; ?>
            </div>
            <?php
        }

        /**
         * Add screen options for list table template
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function add_options() {

            if ( 'yith-live-chat_page_ylc_offline_messages' == get_current_screen()->id && ( !isset( $_GET['action'] ) || ( $_GET['action'] != 'view' ) ) ) {

                $option = 'per_page';
                $args   = array(
                    'label'   => __( 'Messages', 'yith-live-chat' ),
                    'default' => 10,
                    'option'  => 'msg_per_page'
                );
                add_screen_option( $option, $args );

            }

        }

        /**
         * Set screen options for list table template
         *
         * @since   1.0.0
         *
         * @param   $status
         * @param   $option
         * @param   $value
         *
         * @return  mixed
         * @author  Alberto Ruggiero
         */
        public function set_options( $status, $option, $value ) {

            return ( 'msg_per_page' == $option ) ? $value : $status;

        }

    }

    /**
     * Unique access to instance of YLC_Offline_Messages class
     *
     * @return \YLC_Offline_Messages
     */
    function YLC_Offline_Messages() {

        return YLC_Offline_Messages::get_instance();

    }

    new YLC_Offline_Messages();
}