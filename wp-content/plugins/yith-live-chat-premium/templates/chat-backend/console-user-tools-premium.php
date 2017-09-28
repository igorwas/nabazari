<div class="sidebar-info timer">
    <strong>
        <?php _e( 'Elapsed time', 'yith-live-chat' ) ?>
    </strong>
    <span id="YLC_timer">
    </span>
</div>
<div class="sidebar-info macro">
    <select class="macro-select" style="width:100%;">
        <option value=""></option>
        <?php echo apply_filters( 'ylc_macro_options', '' ) ?>
    </select>
</div>