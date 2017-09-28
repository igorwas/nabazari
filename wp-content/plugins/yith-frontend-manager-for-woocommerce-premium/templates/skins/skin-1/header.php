<?php
/*
YITH FRONTEND DASHBOARD SKIN DEFAULT HEADER
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="yith_wcfm-header" class="left-logo">
    <div class="yith_wcfm-container">
        <div class="yith_wcfm-header-content">
            <div id="yith_wcfm-nav-toggle">
                <a href="#" aria-expanded="false">
                    <span class="screen-reader-text">Frontend Dashboard Menu</span>
                </a>
            </div>
			<?php
			$blog_title = get_bloginfo( 'name' );
			$blog_link  = get_bloginfo( 'url' );
			?>
            <div class="yith_wcfm-site-name">
                <a href="<?php echo $blog_link; ?>"><?php echo $blog_title; ?></a>
            </div>
        </div>

    </div>
</div>