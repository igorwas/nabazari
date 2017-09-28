<?php
?>
<h2 class="nav-tab-wrapper ari-wp-tabs" id="<?php echo $this->id; ?>">
    <?php
        foreach ( $this->options->items as $item ):
            $title = is_callable( $item->title ) ? call_user_func( $item->title ) : $item->title;
    ?>
    <a href="#" class="nav-tab<?php if ( $item->active ): ?> nav-tab-active<?php endif; ?>"><?php echo $title; ?></a>
    <?php
        endforeach;
    ?>
</h2>
<?php
    foreach ( $this->options->items as $item ):
?>
<div class="nav-container"<?php if ( ! $item->active ): ?> style="display:none;"<?php endif; ?>>
    <?php
        echo is_callable( $item->content ) ? call_user_func( $item->content ) : $item->content;
    ?>
</div>
<?php
    endforeach;
?>
