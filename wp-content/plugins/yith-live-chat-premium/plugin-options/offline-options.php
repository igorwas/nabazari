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

$defaults = YITH_Live_Chat()->defaults;

return array(
    'offline' => array(

        /* =================== HOME =================== */
        'home'     => array(
            array(
                'name' => __( 'YITH Live Chat: Offline Messages Settings', 'yith-live-chat' ),
                'type' => 'title'
            ),
            array(
                'type' => 'close'
            )
        ),
        /* =================== END SKIN =================== */

        /* =================== MESSAGES =================== */
        'settings' => array(
            array(
                'name' => __( 'Hide chat popup when operators are offline', 'yith-live-chat' ),
                'desc' => '',
                'id'   => 'hide-chat-offline',
                'type' => 'on-off',
                'std'  => $defaults['hide-chat-offline'],
            ),

            array(
                'name'              => __( 'Sender\'s E-mail', 'yith-live-chat' ),
                'desc'              => __( 'If not expressed, the system will use the default WordPress admin email.', 'yith-live-chat' ),
                'id'                => 'offline-mail-sender',
                'type'              => 'custom-email',
                'std'               => $defaults['offline-mail-sender'],
                'custom_attributes' => array(
                    'style' => 'width: 100%'
                )
            ),
            array(
                'name'              => __( 'Recipients\' E-mail', 'yith-live-chat' ),
                'desc'              => __( 'If not expressed, the system will use the default WordPress admin email. Separate email addresses with comma ","', 'yith-live-chat' ),
                'id'                => 'offline-mail-addresses',
                'type'              => 'textarea',
                'std'               => $defaults['offline-mail-addresses'],
                'custom_attributes' => array(
                    'style' => 'width: 100%',
                    'class' => 'textareas'
                )
            ),
            array(
                'name' => __( 'Send Copy Of Message To Visitor', 'yith-live-chat' ),
                'desc' => '',
                'id'   => 'offline-send-visitor',
                'type' => 'on-off',
                'std'  => $defaults['offline-send-visitor'],
            ),
            array(
                'name'              => __( 'Message Body', 'yith-live-chat' ),
                'desc'              => __( '(HTML tags are not allowed)', 'yith-live-chat' ),
                'id'                => 'offline-message-body',
                'type'              => 'textarea',
                'std'               => $defaults['offline-message-body'],
                'deps'              => array(
                    'ids'    => 'offline-send-visitor',
                    'values' => 'yes'
                ),
                'custom_attributes' => array(
                    'required' => 'required',
                    'class'    => 'textareas'
                )
            ),
            array(
                'name' => __( 'Show the offline message form even when all the operators are busy', 'yith-live-chat' ),
                'desc' => '',
                'id'   => 'offline-busy',
                'type' => 'on-off',
                'std'  => $defaults['offline-busy'],
            ),
        ),
    )
);