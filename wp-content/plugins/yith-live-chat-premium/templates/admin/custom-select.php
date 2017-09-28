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
 * Text Plugin Admin View
 *
 * @author     Alberto Ruggiero
 * @since      1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

$id         = $this->_panel->get_id_field( $option['id'] );
$name       = $this->_panel->get_name_field( $option['id'] );
$selections = ( !is_array( $db_value ) ) ? array() : $db_value;

$args = array(
    'sort_column' => 'menu_order',
    'post_type'   => 'page',
    'post_status' => 'publish',
);

$pages = get_pages( $args );

?>
<div id="<?php echo $id ?>-container" <?php if ( isset( $option['deps'] ) ): ?>data-field="<?php echo $id ?>" data-dep="<?php echo $this->_panel->get_id_field( $option['deps']['ids'] ) ?>" data-value="<?php echo $option['deps']['values'] ?>" <?php endif ?> class="yit_options rm_option rm_input rm_text">
    <div class="option">

        <select multiple="multiple" id="<?php echo $id ?>" name="<?php echo $name; ?>[]" style="width:350px" data-placeholder="<?php esc_attr_e( 'Choose pages&hellip;', 'yith-live-chat' ); ?>" title="<?php esc_attr_e( 'Pages', 'yith-live-chat' ) ?>" class="ylc-select">
            <option value="0" <?php echo selected( in_array( 0, $selections ), true, false ); ?> ><?php _e( 'Home Page', 'yith-live-chat' ) ?></option>
            <?php
            if ( !empty( $pages ) ) {
                foreach ( $pages as $page ) {
                    echo '<option value="' . esc_attr( $page->ID ) . '" ' . selected( in_array( $page->ID, $selections ), true, false ) . '>' . $page->post_title . '</option>';
                }
            }
            ?>
        </select>
        <br />
        <a class="select_all button" href="#"><?php _e( 'Select all', 'yith-live-chat' ); ?></a>
        <a class="select_none button" href="#"><?php _e( 'Select none', 'yith-live-chat' ); ?></a>

    </div>
    <span class="description"><?php echo ( $option['desc'] ) ? $option['desc'] : ''; ?></span>

    <div class="clear"></div>
</div>

