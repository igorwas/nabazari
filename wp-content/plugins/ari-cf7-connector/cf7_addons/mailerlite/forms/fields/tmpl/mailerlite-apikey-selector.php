<?php
use Ari_Cf7_Connector_Plugins\Mailerlite\Helpers\Helper as Helper;
use Ari_Cf7_Connector_Plugins\Mailerlite\Helpers\Settings as Settings;

$id = $this->get_id();
$container_id = $id . '_container';
$apikey = $this->value;
$is_apikey_defined = ! empty( $apikey );
$popup_id = $id . '_popup';
$predefined_apikey_list = Settings::instance()->get_option( 'apikey_list' );
$has_predefined_apikey = is_array( $predefined_apikey_list ) && count( $predefined_apikey_list ) > 0;
$new_apikey_id = $id . '_new_apikey';
$apikey_select_id = $id . '_select_apikey';
$resolved_api_key = Helper::resolve_apikey( $apikey );
?>
<div class="<?php if ( $is_apikey_defined ): ?> ari-cf7c-mailerlite-apikey-defined<?php endif; ?>" id="<?php echo $container_id; ?>">
    <div class="ari-cf7c-mailerlite-apikey-new-panel">
        <a href="#<?php echo $popup_id; ?>" class="button button-primary cf7-conn-mailerlite-select-apikey"><?php _e( 'Click here to define MailerLite API key', 'contact-form-7-connector' ); ?></a>
    </div>
    <div class="ari-cf7c-mailerlite-apikey-current-panel">
        <div>
            <input type="text" autocomplete="off" class="large-text ari-cf7c-mailerlite-apikey-current ari-cf7c-select-all" value="<?php echo esc_attr( $resolved_api_key ); ?>" readonly data-mailerlite-apikey-info />
        </div>
        <div class="ari-cf7c-block-small">
            <a href="#<?php echo $popup_id; ?>" class="button ari-cf7c-button-max cf7-conn-mailerlite-select-apikey"><?php _e( 'Change API key', 'contact-form-7-connector' ); ?></a>
        </div>
    </div>
    <input type="hidden" name="<?php echo $this->get_name(); ?>" id="<?php echo $id; ?>" value="<?php echo esc_attr( $apikey ); ?>" />
</div>
<div id="<?php echo $popup_id; ?>" class="ari-cf7c-popup mfp-hide ari-cf7c-mailerlite-apikey-popup" data-mailerlite-apikey-id="<?php echo $id; ?>">
    <div class="ari-cf7c-popup-title">
        <h2><?php _e( 'Specify MailerLite API key', 'contact-form-7-connector' ); ?></h2>
    </div>
    <hr />
    <div class="ari-cf7c-popup-body">
        <?php
        if ( $has_predefined_apikey ):
            ?>
            <div class="ari-cf7c-mailerlite-keylist-container" data-mailerlite-key-config="predefined" style="display:none">
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label for="<?php echo $apikey_select_id; ?>"><?php _e( 'API key', 'contact-form-7-connector' ); ?></label>
                        </th>
                        <td>
                            <div>
                                <select id="<?php echo $apikey_select_id; ?>" autocomplete="off" data-apikey-selector>
                                    <option value=""><?php esc_html_e( '- Select MailerLite API key -', 'contact-form-7-connector' ); ?></option>
                                    <?php
                                    foreach ( $predefined_apikey_list as $apikey_item ):
                                        ?>
                                        <option data-apikey="<?php echo esc_attr( $apikey_item->apikey ); ?>" value="<?php echo esc_attr( '{{' . $apikey_item->id . '}}' ); ?>"><?php echo esc_html( $apikey_item->apikey ); ?></option>
                                    <?php
                                    endforeach;
                                    ?>
                                </select>
                            </div>
                            <div class="ari-cf7c-block">
                                <a href="#" class="button button-primary ari-cf7c-button-max" data-mailerlite-key-config-switch="new"><?php _e( 'Enter a new API key', 'contact-form-7-connector' ); ?></a>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        <?php
        endif;
        ?>

        <div class="ari-cf7c-mailerlite-newkey-container" data-mailerlite-key-config="new" style="display:none">
            <div>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th class="row">
                            <label for="<?php echo $new_apikey_id; ?>"><?php _e( 'API key', 'contact-form-7-connector' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="<?php echo $new_apikey_id; ?>" placeholder="<?php esc_attr_e( 'Enter an API key', 'contact-form-7-connector' ); ?>" autocomplete="off" class="large-text" data-apikey-new />
                            <br />
                            <div class="ari-text-right">
                                <a href="https://app.mailerlite.com/subscribe/api" target="_blank"><?php _e( 'Where get API key?', 'contact-form-7-connector' ); ?></a>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="ari-clear">
                <?php
                if ( $has_predefined_apikey ):
                    ?>
                    <div class="ari-left">
                        <a href="#" class="button" data-mailerlite-key-config-switch="predefined"><?php echo _e( '<< Key list', 'contact-form-7-connector' ); ?></a>
                    </div>
                <?php
                endif;
                ?>
                <div class="ari-text-right">
                    <a href="#" class="button" data-apikey-new-validate><?php echo _e( 'Validate', 'contact-form-7-connector' ); ?></a>
                    <a href="#" class="button button-primary" data-apikey-new-apply><?php echo _e( 'Use this key', 'contact-form-7-connector' ); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>