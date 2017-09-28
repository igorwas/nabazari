<?php
?>
<h2><?php _e( 'MailChimp Integration', 'contact-form-7-connector' ); ?></h2>
<div class="ari-cf7c-panel-note">
    <?php printf( __( 'Select or enter a MailChimp API key then navigate to <a href="%s">Email</a> parameter and select a form element which contains subscriber\'s email. Select list(s) in "Subscribe to → List" drop-down. Other parameters are optional.', 'contact-form-7-connector' ), '#ari_cf7connector_mailchimp_settings_email' ); ?>
    <br /><br />
    <?php printf( __( 'If some form elements are not shown, <a href="#" onclick="%s">save</a> form settings to update element list.', 'contact-form-7-connector' ), 'var btnSaveList = document.getElementsByName(\'wpcf7-save\');if (btnSaveList.length > 0) btnSaveList[0].click();return false;' ); ?>
</div>
<hr />
<?php echo $this->form->render( 'mailchimp' ); ?>
<hr />
<div class="ari-cf7c-panel-note">
    <?php printf( __( 'Mailchimp integration is provided by <a href="%s" target="_blank">Contact Form 7 Connector</a> plugin.', 'contact-form-7-connector' ), admin_url( 'admin.php?page=ari-cf7connector' ) ); ?>
</div>