<?php
use Ari_Cf7_Connector\Helpers\Helper as Helper;

$action_url = Helper::build_url(
    array(
        'noheader' => '1',
    )
);

$form = $data['form'];
?>
<?php if ( ! ARI_WP_LEGACY ) settings_errors(); ?>
<div class="ari-cf7c-settings">
    <div class="metabox-holder has-right-sidebar">
        <div class="inner-sidebar">
            <div class="postbox">
                <h3><?php _e( 'Helpful links', 'contact-form-7-connector' ); ?></h3>
                <div class="inside">
                    <ul>
                        <li>
                            <a href="http://www.ari-soft.com/Contact-Form-7-Connector/" target="_blank"><?php _e( 'Support', 'contact-form-7-connector' ); ?></a>
                        </li>
                        <li>
                            <a href="https://wordpress.org/support/plugin/ari-cf7-connector/reviews/" target="_blank"><?php _e( 'Write a review and give a rating', 'contact-form-7-connector' ); ?></a>
                        </li>
                        <li>
                            <a href="https://twitter.com/ARISoft" target="_blank"><?php _e( 'Follow us on Twitter', 'contact-form-7-connector' ); ?></a>
                        </li>
                        <li>
                            <a href="http://contact-form-7-connector.ari-soft.com/#pricing" class="cf7c-important-link" target="_blank"><?php _e( 'Upgrade to PRO', 'contact-form-7-connector' ); ?></a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="postbox">
                <h3><?php _e( 'Other plugins', 'contact-form-7-connector' ); ?></h3>
                <div class="inside">
                    <ul>
                        <li>
                            <a href="https://wordpress.org/plugins/cf7-editor-button/" target="_blank"><?php _e( '<b>CF7 Editor Button</b> adds editor button to embed shortcodes', 'contact-form-7-connector' ); ?></a>
                        </li>
                        <li>
                            <a href="https://wordpress.org/plugins/ari-fancy-lightbox/" target="_blank"><?php _e( '<b>ARI Fancy Lightbox</b> is the best lightbox plugin', 'contact-form-7-connector' ); ?></a>
                        </li>
                        <li>
                            <a href="https://wordpress.org/plugins/ari-stream-quiz/" target="_blank"><?php _e( '<b>ARI Stream Quiz</b> is viral quiz builder', 'contact-form-7-connector' ); ?></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div id="post-body">
            <div id="post-body-content">
                <div class="postbox">
                    <div class="inside ari-theme">
                        <form action="<?php echo esc_url( $action_url ); ?>" method="POST">
                            <?php
                                $this->tabs->render();
                            ?>

                            <button type="submit" class="button button-primary"><?php _e( 'Save Changes', 'contact-form-7-connector' ); ?></button>

                            <input type="hidden" id="ctrl_action" name="action" value="save" />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>