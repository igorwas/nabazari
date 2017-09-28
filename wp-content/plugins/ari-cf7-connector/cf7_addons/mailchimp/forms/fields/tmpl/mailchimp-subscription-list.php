<?php
$list_field_message = __( 'List field', 'contact-form-7-connector' );
$form_tag_message = __( 'Form tag', 'contact-form-7-connector' );
?>
<p><?php _e( 'Users will be subscribed to the selected list(s)', 'contact-form-7-connector' ); ?></p>
<br />
<div id="{$id}" class="ari-cloner-container ari-cloner-form-element ari-cf7-mailchimp-subscription-list" data-cloner-control-key="subscriptions" data-cloner-id="subscriptions" data-cloner-opt-items="1">
    <div class="ari-cloner-template ari-cf7c-mailchimp-subscription-item">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="ddlMailchimpConfirmField" class="ari-form-tooltip" title="<?php esc_attr_e( 'If a form element is selected then users will be added to MailChimp lists only if populate the selected form element. Leave value of the parameter is empty if confirmation of subscription does not require.', 'contact-form-7-connector' ); ?>"><?php _e( 'Confirm field', 'contact-form-7-connector' ); ?></label>
                </th>
                <td>
                    <select id="ddlMailchimpConfirmField" data-cloner-type="control" data-cloner-control-key="confirm_field" data-placeholder="<?php esc_attr_e( '- Select form field -', 'contact-form-7-connector' ); ?>" data-allow-clear="true" data-width="90%">
                        <option></option>
                        {{tags_options}}
                    </select>
                </td>
            </tr>
            </tbody>
            <tbody class="ari-cf7c-mailchimp-list-container ari-cloner-container" data-cloner-control-key="lists" data-cloner-id="lists" data-cloner-opt-items="1">
            <tr>
                <td colspan="2">
                    <div class="cf7-item-action-panel">
                        <a href="#" class="button ari-cloner-add-item"><?php _e( 'Add list', 'contact-form-7-connector' ); ?></a>
                    </div>
                    <div class="ari-cloner-template ari-cf7c-mailchimp-list-item">
                        <div>
                            <div class="ari-text-right ari-cf7c-mailchimp-list-item-actions cf7-item-action-panel">
                                <a href="#" class="button ari-cloner-remove-item"><span class="ari-cf7c-icon ari-cf7c-icon-small ari-cf7c-icon--action ari-cf7c-icon-bin"></span> <?php _e( 'Delete list', 'contact-form-7-connector' ); ?></a>
                            </div>

                            <table>
                                <tr>
                                    <th scope="row">
                                        <label for="ddlMailchimpList" class="ari-form-tooltip" title="<?php esc_attr_e( 'Users will be added the selected MailChimp list(s)', 'contact-form-7-connector' ); ?>"><?php _e( 'List *', 'contact-form-7-connector' ); ?></label>
                                    </th>
                                    <td class="ari-cf7c-mailchimp-list-container">
                                        <select id="ddlMailchimpList" class="ari-cf7c-mailchimp-list" data-cloner-type="control" data-cloner-control-key="list_id" data-placeholder="<?php _e( 'Select a list', 'contact-form-7-connector' ); ?>" data-allow-clear="true" data-width="90%">
                                        </select>
                                        <a class="ari-cf7c-mailchimp-lists-reload ari-cf7c-icon--action ari-cf7c-icon ari-cf7c-icon-reload ari-form-tooltip" title="<?php esc_attr_e( 'Reload lists', 'contact-form-7-connector' ); ?>"></a>
                                        <input type="hidden" data-cloner-type="control" data-cloner-control-key="list_meta" />
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="chkMailchimpCustomFields" class="ari-form-tooltip" title="<?php esc_attr_e( 'Enable the parameter if want to populate MailChimp list fields with values of form elements.', 'contact-form-7-connector' ); ?>"><?php _e( 'Custom fields', 'contact-form-7-connector' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" class="ari-cf7c-mailchimp-customfields-switcher" id="chkMailchimpCustomFields" data-cloner-type="control" data-cloner-control-key="use_custom_fields" />
                                    </td>
                                </tr>
                                <tr class="ari-cf7c-mailchimp-customfields-container">
                                    <td colspan="2">
                                        <div class="ari-cloner-container" data-cloner-id="custom_fields" data-cloner-control-key="custom_fields" data-cloner-opt-items="1">
                                            <div class="right-align">
                                                <a href="#" class="button ari-cloner-add-item"><?php _e( 'Add field', 'contact-form-7-connector' ); ?></a>
                                                <a href="#" class="button ari-cf7c-mailchimp-fields-reload"><?php _e( 'Reload fields', 'contact-form-7-connector' ); ?></a>
                                            </div>
                                            <div class="ari-grid-row ari-grid-hidden-s">
                                                <div class="ari-grid-col-6">
                                                    <strong><?php echo $list_field_message; ?></strong>
                                                </div>
                                                <div class="ari-grid-col-6">
                                                    <strong><?php echo $form_tag_message; ?></strong>
                                                </div>
                                            </div>
                                            <div class="ari-cloner-template ari-cf7c-mailchimp-customfields-template ari-grid-row">
                                                <div class="ari-cf7c-mailchimp-listfields-container ari-grid-col-6">
                                                    <div class="ari-grid-show-s">
                                                        <label for="ddlMailchimpFieldId"><?php echo $list_field_message; ?></label>
                                                    </div>
                                                    <div>
                                                        <select id="ddlMailchimpFieldId" data-cloner-type="control" data-cloner-control-key="mailchimp_field_id" data-placeholder="<?php esc_attr_e( '- Select a field -', 'contact-form-7-connector' ); ?>" data-allow-clear="true" data-width="90%">
                                                        </select>
                                                        <input type="hidden" data-cloner-type="control" data-cloner-control-key="mailchimp_field_meta" />
                                                    </div>
                                                </div>
                                                <div class="ari-grid-col-6">
                                                    <div class="ari-grid-show-s">
                                                        <label for="ddlMailchimpFormTag"><?php echo $form_tag_message; ?></label>
                                                    </div>
                                                    <div>
                                                        <select id="ddlMailchimpFormTag" data-cloner-type="control" data-cloner-control-key="form_field" data-placeholder="<?php esc_attr_e( '- Select form field -', 'contact-form-7-connector' ); ?>" data-allow-clear="true" data-width="90%">
                                                            <option></option>
                                                            {{tags_options}}
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="ari-fly-action-panel">
                                                    <a class="ari-cloner-remove-item"><span class="ari-cf7c-icon ari-cf7c-icon--action ari-cf7c-icon-bin"></span></a>
                                                </div>
                                                <hr class="ari-grid-show-s ari-clear" />
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <hr class="ari-cf7c-mailchimp-list-separator" />
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <hr class="ari-cf7c-mailchimp-subscription-separator" />
    </div>
</div>