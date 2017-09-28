<form id="YLC_popup_form" action="">
    <label for="YLC_msg_name">
        <?php _e( 'Your Name', 'yith-live-chat' ) ?>
    </label>:
    <div class="form-line">
        <input type="text" name="name" id="YLC_msg_name" placeholder="<?php _e( 'Please enter your name', 'yith-live-chat' ) ?>">
        <i class="chat-ico fa fa-user"></i>
    </div>
    <label for="YLC_msg_email">
        <?php _e( 'Your Email', 'yith-live-chat' ) ?>
    </label>:
    <div class="form-line">
        <input type="email" name="email" id="YLC_msg_email" placeholder="<?php _e( 'Please enter your email', 'yith-live-chat' ) ?>">
        <i class="chat-ico fa fa-envelope-o"></i>
    </div>
    <label for="YLC_msg_message">
        <?php _e( 'Your Message', 'yith-live-chat' ) ?>
    </label>:
    <div class="form-line">
        <textarea id="YLC_msg_message" name="message" placeholder="<?php _e( 'Write your question', 'yith-live-chat' ) ?>" class="chat-field"></textarea>
    </div>
    <div class="chat-send">
        <div id="YLC_offline_ntf" class="chat-ntf"></div>
        <a href="javascript:void(0)" id="YLC_send_btn" class="chat-form-btn">
            <?php _e( 'Send', 'yith-live-chat' ) ?>
        </a>
    </div>
</form>