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

if ( !function_exists( 'ylc_ajax_offline_form' ) ) {

    /**
     * Offline Form management
     *
     * @since   1.0.0
     *
     * @param   $form_data
     *
     * @throws  Exception
     * @return  array
     * @author  Alberto Ruggiero
     */
    function ylc_ajax_offline_form( $form_data ) {

        $resp = array(
            'offline-fail' => false,
            'user-fail'    => false,
            'db-fail'      => false,
        );

        $error_msg     = __( 'Something went wrong. Please try again', 'yith-live-chat' );
        $default_email = get_option( 'admin_email' );
        $options       = YITH_Live_Chat()->options;
        $user          = new YLC_User;
        $ip_address    = ylc_get_ip_address();
        $from          = ( !empty ( $options['offline-mail-sender'] ) ) ? $options['offline-mail-sender'] : $default_email;
        $page          = $_SERVER['HTTP_REFERER'];

        // Send offline message
        $to        = ( !empty ( $options['offline-mail-addresses'] ) ) ? esc_html( $options['offline-mail-addresses'] ) : $default_email;
        $subject   = __( 'New offline message', 'yith-live-chat' );
        $mail_body = __( 'You have received an offline message', 'yith-live-chat' );

        if ( defined( 'YITH_WPV_PREMIUM' ) && get_option( 'yith_wpv_vendors_option_live_chat_management' ) == 'yes' ) {

            $to .= ylc_get_vendor_admins_email( $form_data['vendor_id'] );

        }

        if ( !ylc_send_offline_msg( $from, $to, $subject, $user, $ip_address, $form_data, $mail_body, $page ) ) {
            $resp['offline-fail'] = true;
        }

        if ( !$resp['offline-fail'] ) {

            $send_visitor_mail = $options['offline-send-visitor'];

            if ( $send_visitor_mail == 'yes' ) {

                $message_body = esc_html( $options['offline-message-body'] );

                //Send a copy to user
                $to        = $form_data['email'];
                $subject   = __( 'We have received your offline message', 'yith-live-chat' );
                $mail_body = wp_strip_all_tags( $message_body ) . '<br /><br />' . __( 'Here follows a recap of the details you have entered', 'yith-live-chat' ) . ':';

                if ( !ylc_send_offline_msg( $from, $to, $subject, $user, $ip_address, $form_data, $mail_body, $page, true ) ) {
                    $resp['user-fail'] = true;
                }

            }

            // Add offline message to db
            $args = array(
                'user_name'    => $form_data['name'],
                'user_email'   => $form_data['email'],
                'user_message' => $form_data['message'],
                'user_info'    => array(
                    'os'      => $user->info( 'os' ),
                    'browser' => $user->info( 'browser' ),
                    'version' => $user->info( 'version' ),
                    'ip'      => $ip_address,
                    'page'    => $page
                ),
                'vendor_id'    => $form_data['vendor_id']
            );

            if ( !ylc_add_offline_message( $args ) ) {
                $resp['db-fail'] = true;
            }

            if ( $resp['db-fail'] ) {

                return array( 'error' => $error_msg );

            }
            else {
                if ( !$resp['db-fail'] && $resp['user-fail'] ) {

                    return array( 'warn' => __( 'An error occurred while sending a copy of your message. However, administrators received it correctly.', 'yith-live-chat' ) );

                }
            }

        }
        else {

            return array( 'error' => $error_msg );

        }

        return array( 'msg' => __( 'Successfully sent! Thank you', 'yith-live-chat' ) );

    }

}

if ( !function_exists( 'ylc_add_offline_message' ) ) {

    /**
     * Insert offline message into database
     *
     * @since   1.0.0
     *
     * @param   $args
     *
     * @return  bool
     * @author  Alberto Ruggiero
     */
    function ylc_add_offline_message( $args ) {

        global $wpdb;

        $result = $wpdb->insert(
            $wpdb->prefix . 'ylc_offline_messages',
            array(
                'user_name'    => $args['user_name'],
                'user_email'   => $args['user_email'],
                'user_message' => stripslashes( $args['user_message'] ),
                'user_info'    => maybe_serialize( $args['user_info'] ),
                'mail_date'    => date( 'Y-m-d', strtotime( current_time( 'mysql' ) ) ),
                'vendor_id'    => $args['vendor_id']
            ),
            array( '%s', '%s', '%s', '%s', '%s', '%d' )
        );

        if ( $result === false ) {

            return false;

        }
        else {

            return true;

        }

    }

}

if ( !function_exists( 'ylc_ajax_evaluation' ) ) {

    /**
     * Updates Chat evaluation
     *
     * @since   1.0.0
     *
     * @param   $data
     *
     * @throws  Exception
     * @return  array
     * @author  Alberto Ruggiero
     */
    function ylc_ajax_evaluation( $data ) {

        $error_msg = __( 'Something went wrong. Please try again', 'yith-live-chat' );

        global $wpdb;

        $resp = $wpdb->update(
            $wpdb->prefix . 'ylc_chat_sessions',
            array(
                'evaluation'   => $data['evaluation'],
                'receive_copy' => $data['receive_copy'],
            ),
            array( 'conversation_id' => $data['conversation_id'] ),
            array(
                '%s',
                '%d'
            ),
            array( '%s' )
        );

        if ( $resp === false ) {
            return array( 'error' => $error_msg );
        }

        if ( ylc_count_messages( $data['conversation_id'] ) != 0 ) {

            $resp = ylc_send_chat_data_user( $data['conversation_id'], $data['receive_copy'], $data['user_email'] );

            if ( $resp === false ) {
                return array( 'error' => $error_msg );
            }

            /* $resp = ylc_send_chat_data_admin( $data['conversation_id'], $data['chat_with'], 'visitor' );

             if ( $resp === false ) {
                 return array( 'error' => $error_msg );
             }*/

        }

        return array( 'msg' => __( 'Successfully saved!', 'yith-live-chat' ) );

    }

}

if ( !function_exists( 'ylc_save_chat_data' ) ) {

    /**
     * Save chat transcripts
     *
     * @since   1.0.0
     *
     * @param   $data
     *
     * @return  array
     * @author  Alberto Ruggiero
     */
    function ylc_save_chat_data( $data ) {

        $error_msg = __( 'Something went wrong. Please try again', 'yith-live-chat' );

        global $wpdb;

        $user_data = array(
            'user_id'     => $data['user_id'],
            'user_type'   => $data['user_type'],
            'user_name'   => @$data['user_name'],
            'user_ip'     => sprintf( '%u', ip2long( $data['user_ip'] ) ), // Support 32bit systems as well not to show up negative val.
            'user_email'  => @$data['user_email'],
            'last_online' => @$data['last_online'] || 0,
            'vendor_id'   => @$data['vendor_id']
        );

        $resp = $wpdb->replace( $wpdb->prefix . 'ylc_chat_visitors', $user_data, array( '%s', '%s', '%s', '%d', '%s', '%s', '%d' ) );

        if ( $resp === false ) {
            return array( 'error' => $error_msg );
        }

        $cnv_data = array(
            'conversation_id' => $data['conversation_id'],
            'user_id'         => $data['user_id'],
            'created_at'      => $data['created_at'],
            'evaluation'      => $data['evaluation'],
            'duration'        => $data['duration'],
            'receive_copy'    => $data['receive_copy']
        );

        $resp = $wpdb->replace( $wpdb->prefix . 'ylc_chat_sessions', $cnv_data, array( '%s', '%s', '%s', '%s', '%s', '%d' ) );

        if ( $resp === false ) {
            return array( 'error' => $error_msg );
        }

        if ( !empty( $data['msgs'] ) ) {

            foreach ( $data['msgs'] as $msg_id => $msg ) {

                $msg_data = array(
                    'message_id'      => $msg_id,
                    'conversation_id' => $msg['conversation_id'],
                    'user_id'         => $msg['user_id'],
                    'user_name'       => $msg['user_name'],
                    'msg'             => $msg['msg'],
                    'msg_time'        => $msg['msg_time']
                );

                $resp = $wpdb->replace( $wpdb->prefix . 'ylc_chat_rows', $msg_data, array( '%s', '%s', '%s', '%s', '%s', '%s' ) );

                if ( $resp === false ) {
                    return array( 'error' => $error_msg );
                }

            }

        }

        if ( isset( $data['send_email'] ) && $data['send_email'] == 'true' && ylc_count_messages( $data['conversation_id'] ) != 0 ) {

            $resp = ylc_send_chat_data_user( $data['conversation_id'], $data['receive_copy'], $data['user_email'] );

            if ( $resp === false ) {
                return array( 'error' => $error_msg );
            }

            $resp = ylc_send_chat_data_admin( $data['conversation_id'], $data['chat_with'], 'operator' );

            if ( $resp === false ) {
                return array( 'error' => $error_msg );
            }

        }

        return array( 'msg' => __( 'Successfully saved!', 'yith-live-chat' ) );

    }

}

if ( !function_exists( 'ylc_send_chat_data_user' ) ) {

    /**
     * Send chat transcripts to user
     *
     * @since   1.0.0
     *
     * @param   $cnv_id
     * @param   $receive_copy
     * @param   $user_email
     *
     * @return  boolean
     * @author  Alberto Ruggiero
     */
    function ylc_send_chat_data_user( $cnv_id, $receive_copy, $user_email ) {

        $options = YITH_Live_Chat()->options;

        $transcript_send = $options['transcript-send'];

        if ( $transcript_send == 'yes' && ( $receive_copy == 'true' || $receive_copy == '1' ) ) {

            $from = ( !empty ( $options['transcript-mail-sender'] ) ) ? $options['transcript-mail-sender'] : get_option( 'admin_email' );

            $transcript_message = esc_html( $options['transcript-message-body'] );

            return ylc_send_chat_data( $cnv_id, $from, $user_email, $transcript_message );

        }
        else {

            return true;

        }

    }

}

if ( !function_exists( 'ylc_send_chat_data_admin' ) ) {

    /**
     * Send chat transcripts to admin
     *
     * @since   1.0.0
     *
     * @param   $cnv_id
     * @param   $chat_with
     * @param   $closed_by
     *
     * @return  boolean
     * @author  Alberto Ruggiero
     */
    function ylc_send_chat_data_admin( $cnv_id, $chat_with, $closed_by ) {

        $options = YITH_Live_Chat()->options;

        $transcript_send = $options['transcript-send-admin'];

        if ( $transcript_send == 'yes' ) {

            if ( $chat_with == 'free' ) {

                $op_name = __( 'No operator has replied', 'yith-live-chat' );

            }
            else {

                $op_id       = str_replace( 'ylc-op-', '', $chat_with );
                $op_nickname = get_the_author_meta( 'ylc_operator_nickname', $op_id );
                $op_name     = ( $op_nickname != '' ) ? $op_nickname : get_the_author_meta( 'nickname', $op_id );

            }

            $item          = ylc_get_chat_info( $cnv_id );
            $default_email = get_option( 'admin_email' );

            $from      = ( !empty ( $options['transcript-mail-sender'] ) ) ? $options['transcript-mail-sender'] : $default_email;
            $to        = ( !empty ( $options['transcript-send-admin-emails'] ) ) ? esc_html( $options['transcript-send-admin-emails'] ) : $default_email;
            $message   = __( 'Below you can find a copy of the chat conversation', 'yith-live-chat' );
            $chat_data = array(
                'operator'   => $op_name,
                'user_name'  => $item['user_name'],
                'user_ip'    => long2ip( $item['user_ip'] ),
                'user_email' => $item['user_email'],
                'duration'   => $item['duration'],
                'evaluation' => ( $item['evaluation'] == '' ) ? __( 'Not received', 'yith-live-chat' ) : ucfirst( $item['evaluation'] ),
                'closed_by'  => ( $closed_by == 'operator' ) ? __( 'Operator', 'yith-live-chat' ) : __( 'User', 'yith-live-chat' )
            );

            if ( defined( 'YITH_WPV_PREMIUM' ) && get_option( 'yith_wpv_vendors_option_live_chat_management' ) == 'yes' ) {

                $to .= ylc_get_vendor_admins_email( $item['vendor_id'] );

            }

            return ylc_send_chat_data( $cnv_id, $from, $to, $message, $chat_data, $item['user_name'] );

        }
        else {

            return true;

        }

    }

}

if ( !function_exists( 'ylc_get_vendor_admins_email' ) ) {

    /**
     * Get vendor admins email
     *
     * @since   1.1.0
     *
     * @param   $vendor_id
     *
     * @return  string
     * @author  Alberto Ruggiero
     */
    function ylc_get_vendor_admins_email( $vendor_id ) {

        $vendor        = yith_get_vendor( $vendor_id, 'vendor' );
        $vendor_admins = $vendor->get_admins();
        $vendor_emails = '';

        foreach ( $vendor_admins as $admin_id ) {

            $admin = get_userdata( $admin_id );

            $vendor_emails .= ', ' . $admin->user_email;

        }

        return $vendor_emails;

    }

}
