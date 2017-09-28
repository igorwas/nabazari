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

if ( !class_exists( 'YLC_Chat_Logs' ) ) {

    /**
     * Displays the chat logs table in YITH Live Chat tab
     *
     * @class   YLC_Chat_Logs
     * @package Yithemes
     * @since   1.0.0
     * @author  Your Inspiration Themes
     *
     */
    class YLC_Chat_Logs {

        /**
         * Single instance of the class
         *
         * @var \YLC_Chat_Logs
         * @since 1.0.0
         */
        protected static $instance;

        /**
         * Returns single instance of the class
         *
         * @return \YLC_Chat_Logs
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
         * Outputs the chat logs table template
         *
         * @since   1.0.0
         * @return  string
         * @author  Alberto Ruggiero
         */
        public function output() {

            global $wpdb;

            $table = new YITH_Custom_Table(
                array(
                    'singular' => __( 'log', 'yith-live-chat' ),
                    'plural'   => __( 'logs', 'yith-live-chat' )
                )
            );

            $table->options = array(
                'select_table'     => $wpdb->prefix . 'ylc_chat_sessions a LEFT JOIN ' . $wpdb->prefix . 'ylc_chat_visitors b ON a.user_id = b.user_id',
                'select_columns'   => array(
                    'a.conversation_id',
                    'a.user_id',
                    'a.created_at',
                    'a.evaluation',
                    'a.receive_copy',
                    'a.duration',
                    'b.user_name',
                    'b.user_type',
                    'b.user_ip',
                    'b.user_email'
                ),
                'select_where'     => '',
                'select_group'     => 'a.conversation_id',
                'select_order'     => 'a.created_at',
                'select_order_dir' => 'DESC',
                'search_where'     => array(
                    'b.user_name',
                    'b.user_email'
                ),
                'per_page_option'  => 'logs_per_page',
                'count_table'      => $wpdb->prefix . 'ylc_chat_sessions a LEFT JOIN ' . $wpdb->prefix . 'ylc_chat_visitors b ON a.user_id = b.user_id',
                'count_where'      => '',
                'key_column'       => 'conversation_id',
                'view_columns'     => array(
                    'cb'           => '<input type="checkbox" />',
                    'user'         => __( 'User name', 'yith-live-chat' ),
                    'email'        => __( 'E-mail', 'yith-live-chat' ),
                    'ip'           => __( 'IP Address', 'yith-live-chat' ),
                    'created_at'   => __( 'Date', 'yith-live-chat' ),
                    'total_msgs'   => __( 'Total Messages', 'yith-live-chat' ),
                    'duration'     => __( 'Chat duration', 'yith-live-chat' ),
                    'evaluation'   => __( 'Evaluation', 'yith-live-chat' ),
                    'receive_copy' => __( 'Request Copy', 'yith-live-chat' )
                ),
                'hidden_columns'   => array(),
                'sortable_columns' => array(
                    'user'       => array( 'user', false ),
                    'created_at' => array( 'created_at', false ),
                    'total_msgs' => array( 'total_msgs', false ),
                ),
                'custom_columns'   => array(
                    'column_created_at'   => function ( $item, $me ) {

                        return ylc_convert_timestamp( $item['created_at'] );

                    },
                    'column_total_msgs'   => function ( $item, $me ) {

                        global $wpdb;

                        return $wpdb->get_var(
                            $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}ylc_chat_rows WHERE conversation_id = %s", $item['conversation_id'] ) );

                    },
                    'column_user'         => function ( $item, $me ) {

                        $view_query_args = array(
                            'page'   => $_GET['page'],
                            'action' => 'view',
                            'id'     => $item['conversation_id']
                        );
                        $view_url        = esc_url( add_query_arg( $view_query_args, admin_url( 'admin.php' ) ) );

                        $delete_query_args = array(
                            'page'   => $_GET['page'],
                            'action' => 'delete',
                            'id'     => $item['conversation_id']
                        );
                        $delete_url        = esc_url( add_query_arg( $delete_query_args, admin_url( 'admin.php' ) ) );

                        $actions = array(
                            'view'   => '<a href="' . $view_url . '">' . __( 'View conversation', 'yith-live-chat' ) . '</a>',
                            'delete' => '<a href="' . $delete_url . '">' . __( 'Delete', 'yith-live-chat' ) . '</a>',
                        );

                        return '<b>' . $item['user_name'] . '</b> <span style="color:silver">' . $item['user_type'] . '</span>' . $me->row_actions( $actions );

                    },
                    'column_email'        => function ( $item, $me ) {

                        if ( !empty( $item['user_email'] ) ) {
                            return '<a href="mailto:' . $item['user_email'] . '">' . $item['user_email'] . '</a>';
                        }
                        else {
                            return '<span style="color:silver">' . __( 'N/A', 'yith-live-chat' ) . '</span>';
                        }
                    },
                    'column_ip'           => function ( $item, $me ) {

                        return long2ip( $item['user_ip'] );

                    },
                    'column_evaluation'   => function ( $item, $me ) {

                        switch ( $item['evaluation'] ) {
                            case 'good':
                                return sprintf( '<i class="fa fa-thumbs-up ylc-tips" data-tip="%s"></i>', __( 'Good', 'yith-live-chat' ) );
                                break;

                            case 'bad':
                                return sprintf( '<i class="fa fa-thumbs-down ylc-tips" data-tip="%s"></i>', __( 'Bad', 'yith-live-chat' ) );
                                break;

                            default:
                                return '';
                        }

                    },
                    'column_duration'     => function ( $item, $me ) {

                        if ( $item['duration'] == '00:00:00' ) {
                            return $item['duration'] . '<br />' . __( 'Not Started', 'yith-live-chat' );
                        }
                        else {
                            return $item['duration'];
                        }

                    },
                    'column_receive_copy' => function ( $item, $me ) {

                        if ( $item['receive_copy'] == true ) {
                            return sprintf( '<i class="fa fa-check ylc-tips" data-tip="%s"></i>', __( 'Sent', 'yith-live-chat' ) );
                        }
                        else {
                            return sprintf( '<i class="fa fa-times ylc-tips" data-tip="%s"></i>', __( 'Not sent', 'yith-live-chat' ) );
                        }

                    },
                ),
                'bulk_actions'     => array(
                    'actions'   => array(
                        'delete' => __( 'Delete', 'yith-live-chat' ),
                    ),
                    'functions' => array(
                        'function_delete' => function () {
                            global $wpdb;

                            $ids = isset( $_GET['id'] ) ? $_GET['id'] : array();

                            if ( !empty( $ids ) ) {

                                if ( is_array( $ids ) ) {

                                    foreach ( $ids as $id ) {

                                        $wpdb->query(
                                            $wpdb->prepare(
                                                "DELETE FROM {$wpdb->prefix}ylc_chat_sessions WHERE conversation_id = %s LIMIT 1",
                                                $id
                                            )
                                        );

                                        $wpdb->query(
                                            $wpdb->prepare(
                                                "DELETE FROM {$wpdb->prefix}ylc_chat_rows WHERE conversation_id = %s",
                                                $id
                                            )
                                        );

                                    }

                                }
                                else {

                                    $wpdb->query(
                                        $wpdb->prepare(
                                            "DELETE FROM {$wpdb->prefix}ylc_chat_sessions WHERE conversation_id = %s LIMIT 1",
                                            $ids
                                        )
                                    );

                                    $wpdb->query(
                                        $wpdb->prepare(
                                            "DELETE FROM {$wpdb->prefix}ylc_chat_rows WHERE conversation_id = %s",
                                            $ids
                                        )
                                    );

                                }
                            }

                        },
                    )
                ),
            );

            if ( defined( 'YITH_WPV_PREMIUM' ) ) {

                $vendor = yith_get_vendor( 'current', 'user' );

                $table->options['select_columns'][] = 'b.vendor_id';

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
                    $table->options['select_where'] = 'b.vendor_id = ' . $vendor->id;
                    $table->options['count_where']  = 'b.vendor_id = ' . $vendor->id;

                }

            }

            $table->prepare_items();

            $message = '';
            $notice  = '';

            $list_query_args = array(
                'page' => $_GET['page'],
            );

            $list_url = esc_url( add_query_arg( $list_query_args, admin_url( 'admin.php' ) ) );

            if ( 'delete' === $table->current_action() ) {

                $items   = isset( $_GET['id'] ) ? count( $_GET['id'] ) : 0;
                $message = sprintf( _n( '%s conversation deleted successfully', '%s conversations deleted successfully', $items, 'yith-live-chat' ), $items );

            }

            if ( !empty( $notice ) ) : ?>
                <div id="notice" class="error below-h2"><p><?php echo $notice; ?></p></div>
            <?php endif;

            if ( !empty( $message ) ) : ?>
                <div id="message" class="updated below-h2"><p><?php echo $message; ?></p></div>
            <?php endif;

            if ( isset( $_GET['id'] ) && !empty( $_GET['action'] ) && 'view' == $_GET['action'] ) : ?>
                <?php

                $item      = ylc_get_chat_info( $_GET['id'] );
                $chat_logs = ylc_get_chat_conversation( $_GET['id'] );

                ?>

                <h1><?php echo $item['user_name']; ?></h1>
                <div class="chat_info">
                <span>
                    <b>
                        <?php _e( 'User type', 'yith-live-chat' ) ?>:
                    </b>
                    <?php echo ucfirst( $item['user_type'] ); ?>
                </span>
                <span>
                    <b>
                        <?php _e( 'IP Address', 'yith-live-chat' ) ?>:
                    </b>
                    <?php echo long2ip( $item['user_ip'] ); ?>
                </span>
                <span>
                    <b>
                        <?php _e( 'User e-mail', 'yith-live-chat' ) ?>:
                    </b>
                    <?php echo $item['user_email']; ?>
                </span>
                <span>
                    <b><?php _e( 'Chat Evaluation', 'yith-live-chat' ) ?>:</b>
                    <?php

                    switch ( $item['evaluation'] ) {
                        case 'good':
                            echo sprintf( '<i class="fa fa-thumbs-up ylc-tips" data-tip="%s"></i>', __( 'Good', 'yith-live-chat' ) );
                            break;

                        case 'bad':
                            echo sprintf( '<i class="fa fa-thumbs-down ylc-tips" data-tip="%s"></i>', __( 'Bad', 'yith-live-chat' ) );
                            break;

                        default:
                            echo '--';
                    }

                    ?>
                </span>
                    <a class="button-secondary" href="<?php echo $list_url; ?>"><?php _e( 'Return to chat logs', 'yith-live-chat' ); ?></a>
                </div>
                <hr>
                <div class="chat_log">
                    <?php foreach ( $chat_logs as $log ): ?>
                        <p class="chat_row">
					<span class="date">
                        <?php echo ylc_convert_timestamp( $log['msg_time'] ); ?>
                    </span>
                    <span class="message <?php echo $log['user_type']; ?>">
                        <b><?php echo $log['user_name']; ?>: </b>
                        <?php echo stripslashes( $log['msg'] ); ?>
                    </span>
                        </p>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="wrap">
                    <h2>
                        <?php _e( 'Chat Logs', 'yith-live-chat' ); ?>
                    </h2>

                    <form id="custom-table" method="GET" action="<?php echo $list_url; ?>">
                        <?php $table->search_box( __( 'Search Log' ), 'message' ); ?>
                        <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
                        <?php $table->display(); ?>
                    </form>
                </div>
            <?php endif;
        }

        /**
         * Add screen options for list table template
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function add_options() {

            if ( 'yith-live-chat_page_ylc_chat_logs' == get_current_screen()->id && ( !isset( $_GET['action'] ) || ( $_GET['action'] != 'view' ) ) ) {

                $option = 'per_page';
                $args   = array(
                    'label'   => __( 'Conversations', 'yith-live-chat' ),
                    'default' => 10,
                    'option'  => 'logs_per_page'
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

            return ( 'logs_per_page' == $option ) ? $value : $status;

        }

    }

    /**
     * Unique access to instance of YLC_Chat_Logs class
     *
     * @return \YLC_Chat_Logs
     */
    function YLC_Chat_Logs() {

        return YLC_Chat_Logs::get_instance();

    }

    new YLC_Chat_Logs();
}