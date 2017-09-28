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
    exit; // Exit if accessed directly
}

if ( !function_exists( 'ylc_get_chat_info' ) ) {

    /**
     * Get chat info
     *
     * @since   1.0.0
     *
     * @param   $cnv_id
     *
     * @return  array
     * @author  Alberto ruggiero
     */
    function ylc_get_chat_info( $cnv_id ) {

        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare( "
                            SELECT      a.conversation_id,
                                        a.user_id,
                                        a.evaluation,
                                        a.created_at,
                                        a.duration,
                                        a.receive_copy,
                                        b.user_id,
                                        b.user_type,
                                        b.user_name,
                                        b.user_ip,
                                        b.user_email,
                                        b.last_online,
                                        b.vendor_id
                            FROM        {$wpdb->prefix}ylc_chat_sessions a LEFT JOIN {$wpdb->prefix}ylc_chat_visitors b ON a.user_id = b.user_id
                            WHERE       a.conversation_id = %s
                            GROUP BY    a.conversation_id
                            LIMIT       1
                            ", $cnv_id ), ARRAY_A );

    }

}

if ( !function_exists( 'ylc_get_chat_conversation' ) ) {

    /**
     * Get chat conversation
     *
     * @since   1.0.0
     *
     * @param   $cnv_id
     *
     * @return  array
     * @author  Alberto ruggiero
     */
    function ylc_get_chat_conversation( $cnv_id ) {

        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare( "
                            SELECT      a.message_id,
                                        a.conversation_id,
                                        a.user_id,
                                        a.user_name,
                                        a.msg,
                                        a.msg_time,
                                        IFNULL( b.user_type, 'operator' ) AS user_type
                            FROM        {$wpdb->prefix}ylc_chat_rows a LEFT JOIN {$wpdb->prefix}ylc_chat_visitors b ON a.user_id = b.user_id
                            WHERE       a.conversation_id = %s
                            ORDER BY    a.msg_time
                            ", $cnv_id ), ARRAY_A );

    }

}

if ( !function_exists( 'ylc_count_messages' ) ) {

    /**
     * Count messages in a conversation
     *
     * @since   1.0.0
     *
     * @param   $cnv_id
     *
     * @return  integer
     * @author  Alberto ruggiero
     */
    function ylc_count_messages( $cnv_id ) {

        global $wpdb;

        return $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}ylc_chat_rows WHERE conversation_id = %s", $cnv_id ) );

    }
}

if ( !function_exists( 'ylc_convert_timestamp' ) ) {

    /**
     * Converts a timestamp in a dd/mm/yyyy HH:MM string
     *
     * @since   1.0.0
     *
     * @param   $time
     *
     * @return  string
     * @author  Alberto ruggiero
     */
    function ylc_convert_timestamp( $time ) {

        $gmt_offset = get_option( 'gmt_offset' );
        $timestamp  = ( $time / 1000 ) + ( $gmt_offset * 3600 );

        return date_i18n( 'd/m/Y H:i', $timestamp );

    }

}

if ( !function_exists( 'ylc_update_1_1_0' ) ) {

    /**
     * Add columns for YITH WooCommerce Product Vendors compatibility
     *
     * @since   1.1.0
     * @return  void
     * @author  Alberto ruggiero
     */
    function ylc_update_1_1_0() {

        $ylc_db_option = get_option( 'ylc_db_version' );

        if ( empty( $ylc_db_option ) || version_compare( $ylc_db_option, '1.1.0', '<' ) ) {

            global $wpdb;

            $sql = "ALTER TABLE {$wpdb->prefix}ylc_offline_messages ADD vendor_id INT NOT NULL DEFAULT 0";
            $wpdb->query( $sql );

            $sql = "ALTER TABLE {$wpdb->prefix}ylc_chat_visitors ADD vendor_id INT NOT NULL DEFAULT 0";
            $wpdb->query( $sql );

            update_option( 'ylc_db_version', '1.1.0' );

        }

    }

    add_action( 'admin_init', 'ylc_update_1_1_0' );

}


