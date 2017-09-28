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

global $wp_roles;

if ( !isset( $wp_roles ) ) {
    $wp_roles = new WP_Roles();
}

$role_names = $wp_roles->get_names();

$roles = array();

foreach ( $role_names as $role => $name ) {

    switch ( $role ) {

        case 'administrator':
        case 'editor':
        case 'author':
        case 'contributor':
        case 'subscriber':
            $roles[$role] = $name;
            break;

        default:

    }

}

$defaults = YITH_Live_Chat()->defaults;

return array(
    'user' => array(

        /* =================== HOME =================== */
        'home'     => array(
            array(
                'name' => __( 'YITH Live Chat: Users Settings', 'yith-live-chat' ),
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
                'name'    => __( 'Operator Default Role', 'yith-live-chat' ),
                'desc'    => __( 'In this way, operators will get the same competences of this role, but this won\'t transform the users with this role in operators.', 'yith-live-chat' ),
                'id'      => 'operator-role',
                'type'    => 'select',
                'std'     => $defaults['operator-role'],
                'options' => $roles
            ),
            array(
                'name' => __( 'Operator Default Avatar', 'yith-live-chat' ),
                'desc' => __( 'Operators will be able to customize their own avatar from their profile page.', 'yith-live-chat' ),
                'id'   => 'operator-avatar',
                'type' => 'custom-upload',
                'std'  => $defaults['operator-avatar'],
            ),
            array(
                'name'              => __( 'Maximum Connected Guests', 'yith-live-chat' ),
                'desc'              => __( 'Default', 'yith-live-chat' ) . ': 2. ' . __( 'If set to 0, there will be no limits', 'yith-live-chat' ),
                'id'                => 'max-chat-users',
                'type'              => 'custom-number',
                'std'               => $defaults['max-chat-users'],
                'custom_attributes' => array(
                    'min'      => 0,
                    'required' => 'required'
                )
            ),
        )
    )
);