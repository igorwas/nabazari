<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'ylc_send_email_message' ) ) {

	/**
	 * Send email message
	 *
	 * @since   1.0.0
	 *
	 * @param   $from
	 * @param   $to
	 * @param   $subject
	 * @param   $message
	 * @param   $reply_to
	 * @param   $from_name
	 *
	 * @return  bool
	 * @author  Alberto Ruggiero
	 */
	function ylc_send_email_message( $from, $to, $subject, $message, $reply_to, $from_name = '' ) {

		$headers   = array();
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'content-type: text/html';
		$headers[] = 'charset=utf-8';
		$headers[] = 'From: [' . ( $from_name != '' ? $from_name : get_option( 'blogname' ) ) . '] <' . apply_filters( 'ylc_sender_override', $from ) . '>';
		$headers[] = 'Reply-To: ' . $reply_to;

		return wp_mail( $to, $subject, $message, $headers );

	}

}

if ( ! function_exists( 'ylc_get_mail_body' ) ) {

	/**
	 * Get mail body
	 *
	 * @since   1.0.0
	 *
	 * @param   $template
	 * @param   $args
	 *
	 * @return  string
	 * @author  Alberto Ruggiero
	 */
	function ylc_get_mail_body( $template, $args ) {

		ob_start();

		include( YLC_TEMPLATE_PATH . '/email/' . $template . '.php' );

		return ob_get_clean();

	}

}

if ( ! function_exists( 'ylc_send_chat_data' ) ) {

	/**
	 * Send chat transcripts
	 *
	 * @since   1.0.0
	 *
	 * @param   $cnv_id
	 * @param   $from
	 * @param   $to
	 * @param   $message
	 * @param   $chat_data
	 * @param   $user
	 *
	 * @return  array
	 * @author  Alberto Ruggiero
	 */
	function ylc_send_chat_data( $cnv_id, $from, $to, $message, $chat_data = array(), $user = '' ) {

		$subject = __( 'Chat conversation copy', 'yith-live-chat' ) . ( ( $user != '' ) ? ': ' . $user : '' );

		$args = array(
			'subject'   => $subject,
			'mail_body' => wp_strip_all_tags( $message ),
			'cnv_id'    => $cnv_id,
			'chat_data' => $chat_data
		);

		$message = ylc_get_mail_body( 'chat-copy', $args );

		return ylc_send_email_message( $from, $to, $subject, $message, $from );

	}

}

if ( ! function_exists( 'ylc_send_offline_msg' ) ) {

	/**
	 * Send offline message
	 *
	 * @since   1.0.0
	 *
	 * @param   $from
	 * @param   $to
	 * @param   $subject
	 * @param   $user
	 * @param   $ip_address
	 * @param   $form_data
	 * @param   $mail_body
	 * @param   $page
	 * @param   $user_copy
	 *
	 * @return  bool
	 * @author  Alberto Ruggiero
	 */
	function ylc_send_offline_msg( $from, $to, $subject, $user, $ip_address, $form_data, $mail_body, $page, $user_copy = false ) {

		$args = array(
			'subject'    => $subject,
			'mail_body'  => $mail_body,
			'name'       => $form_data['name'],
			'email'      => $form_data['email'],
			'message'    => $form_data['message'],
			'os'         => $user->info( 'os' ),
			'browser'    => $user->info( 'browser' ),
			'version'    => $user->info( 'version' ),
			'ip_address' => $ip_address,
			'page'       => $page,
		);

		$message    = ylc_get_mail_body( 'offline-message', $args );
		$reply_to   = ( $user_copy ) ? $from : $form_data['email'];
		$from_name  = ( $user_copy ) ? '' : $form_data['name'];
		$from_email = ( $user_copy ) ? $from : $form_data['email'];

		return ylc_send_email_message( $from_email, $to, $subject, $message, $reply_to, $from_name );

	}

}