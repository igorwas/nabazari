<?php if ( $this->options['chat-evaluation'] == 'yes' ): ?>

    <div class="chat-evaluation">
        <?php _e( 'Was this conversation useful? Vote this chat session.', 'yith-live-chat' ); ?>

        <div id="YLC_end_chat_ntf" class="chat-ntf"></div>
        <a href="javascript:void(0)" id="YLC_good_btn" class="good">
            <i class="fa fa-thumbs-up"></i>
            <?php _e( 'Good', 'yith-live-chat' ) ?>
        </a>
        <a href="javascript:void(0)" id="YLC_bad_btn" class="bad">
            <i class="fa fa-thumbs-down"></i>
            <?php _e( 'Bad', 'yith-live-chat' ) ?>
        </a>

        <?php if ( $this->options['transcript-send'] == 'yes' ): ?>

            <div class="chat-checkbox">
                <input type="checkbox" name="request_chat" id="YLC_request_chat">
                <label for="YLC_request_chat">
                    <?php _e( 'Receive the copy of the chat via e-mail', 'yith-live-chat' ) ?>
                </label>
            </div>

        <?php endif; ?>

    </div>

<?php else: ?>

    <?php if ( $this->options['transcript-send'] == 'yes' ): ?>

        <div class="chat-evaluation">
            <div id="YLC_end_chat_ntf" class="chat-ntf"></div>
            <a href="javascript:void(0)" id="YLC_chat_request">
                <?php _e( 'Receive the copy of the chat via e-mail', 'yith-live-chat' ) ?>
            </a>
        </div>

    <?php endif; ?>

<?php endif; ?>

