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


return array(
    'autoplay' => array(

        /* =================== HOME =================== */
        'home'     => array(
            array(
                'name' => __( 'YITH Live Chat: Autoplay Settings', 'yith-live-chat' ),
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
                'name' => __( 'Chat Autoplay', 'yith-live-chat' ),
                'desc' => '',
                'id'   => 'autoplay-enabled',
                'type' => 'on-off',
                'std'  => 'no',
            ),
            array(
                'name'              => __( 'Autoplay Delay', 'yith-live-chat' ),
                'desc'              => __( 'Seconds that have to pass before chat popup opens automatically. Default', 'yith-live-chat' ) . ': 10',
                'id'                => 'autoplay-delay',
                'type'              => 'custom-number',
                'std'               => 10,
                'deps'              => array(
                    'ids'    => 'autoplay-enabled',
                    'values' => 'yes'
                ),
                'custom_attributes' => array(
                    'min'      => 0,
                    'max'      => 20,
                    'required' => 'required'
                )
            ),
            array(
                'name'              => __( 'Automatic Operator\'s Name', 'yith-live-chat' ),
                'desc'              => __( 'Enter here the name of the fictitious operator that greets a new user with an automatic welcome message.', 'yith-live-chat' ),
                'id'                => 'autoplay-operator',
                'type'              => 'text',
                'std'               => __( 'Operator', 'yith-live-chat' ),
                'deps'              => array(
                    'ids'    => 'autoplay-enabled',
                    'values' => 'yes'
                ),
                'custom_attributes' => array(
                    'required' => 'required'
                )
            ),
            array(
                'name'              => __( 'Automatic Welcome Message', 'yith-live-chat' ),
                'desc'              => __( 'Welcome message that is automatically sent to new users when automatic response is enabled', 'yith-live-chat' ),
                'id'                => 'autoplay-welcome',
                'type'              => 'textarea',
                'std'               => __( 'Hi! Welcome in our website! How can I help you?', 'yith-live-chat' ),
                'deps'              => array(
                    'ids'    => 'autoplay-enabled',
                    'values' => 'yes'
                ),
                'custom_attributes' => array(
                    'required' => 'required',
                    'class'    => 'textareas'
                )
            ),
        ),
    )
);