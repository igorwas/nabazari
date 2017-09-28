<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Operator custom Fields
 *
 * @author     Alberto Ruggiero
 * @since      1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

$options = array(
    'default'  => __( 'Default Avatar', 'yith-live-chat' ),
    'image'    => __( 'Uploaded Image', 'yith-live-chat' ),
    'gravatar' => __( 'Gravatar', 'yith-live-chat' ),
)

?>

    <h3>
        <?php _e( 'YITH Live Chat', 'yith-live-chat' ); ?>
    </h3>
    <span class="description">
    <?php _e( 'Remember to refresh the chat console page after updating the operator name or avatar', 'yith-live-chat' ); ?>
</span>
    <table class="form-table">
        <tr class="ylc-op-nickname">
            <th>
                <label for="ylc_operator_nickname">
                    <?php _e( 'Operator Nickname', 'yith-live-chat' ); ?>
                </label>
            </th>
            <td>
                <input type="text" name="ylc_operator_nickname" id="ylc_operator_nickname" value="<?php echo esc_attr( get_the_author_meta( 'ylc_operator_nickname', $user->ID ) ); ?>" class="regular-text" />
                <br>

                <p class="description">
                    <?php _e( 'If not specified, the system will use the default user nickname.', 'yith-live-chat' ); ?>
                </p>
            </td>
        </tr>
        <tr class="ylc-op-avatar">
            <th>
                <label for="ylc_operator_avatar_type">
                    <?php _e( 'Operator Avatar', 'yith-live-chat' ); ?>
                </label>
            </th>
            <td>
                <div class="avatar">
                    <div class="preview">
                        <?php

                        $file = get_image( get_the_author_meta( 'ylc_operator_avatar_type', $user->ID ), $user );

                        ?>
                        <img src="<?php echo $file; ?>" />
                    </div>
                    <select name="ylc_operator_avatar_type" id="ylc_operator_avatar_type">
                        <?php foreach ( $options as $val => $opt ) : ?>
                            <option value="<?php echo $val ?>"<?php selected( get_the_author_meta( 'ylc_operator_avatar_type', $user->ID ), $val ); ?>>
                                <?php echo $opt; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="upload">
                        <input type="text" name="ylc_operator_avatar" id="ylc_operator_avatar" value="<?php echo esc_attr( get_the_author_meta( 'ylc_operator_avatar', $user->ID ) ); ?>" />
                        <input type="button" value="<?php _e( 'Upload', 'yith-live-chat' ) ?>" id="ylc_operator_avatar_button" class="button" />
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <script type="text/javascript">

        ( function ($) {

            $('#ylc_operator_avatar_type').change(function () {

                var option = $('option:selected', this).val(),
                    img = $('.avatar .preview img'),
                    uploader = $('.avatar .upload');

                switch (option) {
                    case 'image':
                        uploader.show();
                        img.attr('src', '<?php echo get_image( 'image' , $user ); ?>');
                        break;

                    case 'gravatar':
                        uploader.hide();
                        img.attr('src', '<?php echo get_image( 'gravatar' , $user ); ?>');
                        break;

                    default:
                        uploader.hide();
                        img.attr('src', '<?php echo get_image( '' , $user ); ?>');

                }

            }).change();

        }(window.jQuery || window.Zepto));

    </script>

<?php

function get_image( $type, $user ) {

    switch ( $type ) {

        case 'image':
            $file = esc_attr( get_the_author_meta( 'ylc_operator_avatar', $user->ID ) );
            if ( !preg_match( '/(jpg|jpeg|png|gif|ico)$/', $file ) ) {
                $file = YLC_ASSETS_URL . '/images/sleep.png';
            }
            break;

        case 'gravatar':
            $email_hash = md5( $user->user_email );
            $file       = 'https://www.gravatar.com/avatar/' . $email_hash . '.jpg?s=60&d=' . YLC_ASSETS_URL . '/images/default-avatar-admin.png';
            break;

        default:

            $op_avatar = YITH_Live_Chat()->options['operator-avatar'];

            if ( $op_avatar != '' ) {

                $file = $op_avatar;

            }
            else {

                $file = YLC_ASSETS_URL . '/images/default-avatar-admin.png';

            }

    }

    return $file;

}