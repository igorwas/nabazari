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
 * Upload Plugin Admin View
 *
 * @author     Alberto Ruggiero
 * @since      1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

$id   = $this->_panel->get_id_field( $option['id'] );
$name = $this->_panel->get_name_field( $option['id'] );

?>
<div id="<?php echo $id ?>-container">
    <div id="<?php echo $id ?>-container" class="yit_options rm_option rm_input rm_text rm_upload" <?php if (isset( $option['deps'] )): ?>data-field="<?php echo $id ?>" data-dep="<?php echo $this->_panel->get_id_field( $option['deps']['ids'] ) ?>" data-value="<?php echo $option['deps']['values'] ?>" <?php endif ?>>
        <div class="option">
            <input type="text" name="<?php echo $name ?>" id="<?php echo $id ?>" value="<?php echo $db_value == '1' ? '' : esc_attr( $db_value ) ?>" class="custom_upload_img_url" />
            <input type="button" value="<?php _e( 'Upload', 'yith-plugin-fw' ) ?>" id="<?php echo $id ?>-button" class="upload_button button" />
        </div>
        <div class="clear"></div>
        <span class="description"><?php echo $option['desc'] ?></span>
    </div>
    <div class="custom_upload_img_preview" style="margin-top:10px;">

        <?php
        if ( $db_value != '' ) {

            $file = $db_value;

        }
        else {

            $file = YLC_ASSETS_URL . '/images/default-avatar-admin.png';

        } ?>

        <img src="<?php echo $file; ?>" style="width:60px" />

    </div>
</div>
<script type="text/javascript">
    ( function ($) {

        $('.plugin-option .custom_upload_img_url').change(function () {
            var url = $(this).val();
            var re = new RegExp("(http|ftp|https)://[a-zA-Z0-9@?^=%&amp;:/~+#-_.]*.(gif|jpg|jpeg|png|ico)");

            var preview = $('.custom_upload_img_preview img');
            if (url != '') {
                if (re.test(url)) {
                    preview.attr('src', url)

                } else {
                    preview.attr('src', '<?php echo YLC_ASSETS_URL . '/images/default-avatar-admin.png' ?>');
                }
            } else {
                preview.attr('src', '<?php echo YLC_ASSETS_URL . '/images/default-avatar-admin.png' ?>');
            }

        }).change();

    }(window.jQuery || window.Zepto));
</script>

