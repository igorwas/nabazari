<?php

defined( 'ABSPATH' ) or exit;

$_GET['tab'] = 'stock';
$page_id = isset( $_GET['page_id'] ) && $_GET['page_id'] > 0 ?  $_GET['page_id'] : '';

if ( current_user_can( 'view_woocommerce_reports' ) ) : ?>

<div id="yith-wcmf-reports">

    <h1><?php echo __('Stock', 'woocommerce'); ?></h1>

    <div class="buttons">
        <a href="?<?php echo $page_id > 0 ? 'page_id=' . $page_id . '&amp;': ''; ?>reports=stock-report&amp;report=low_in_stock" class="button <?php echo $_GET['report'] == 'low_in_stock' ? 'current' : ''; ?>"><?php echo __('Low stock', 'woocommerce'); ?></a>
        <a href="?<?php echo $page_id > 0 ? 'page_id=' . $page_id . '&amp;': ''; ?>reports=stock-report&amp;report=out_of_stock" class="button <?php echo $_GET['report'] == 'out_of_stock' ? 'current' : ''; ?>"><?php echo __('Out of stock', 'woocommerce'); ?></a>
        <a href="?<?php echo $page_id > 0 ? 'page_id=' . $page_id . '&amp;': ''; ?>reports=stock-report&amp;report=most_stocked" class="button
        <?php echo $_GET['report'] == 'most_stocked' ? 'current' : ''; ?>"><?php echo __('High-stocked', 'woocommerce'); ?></a>
    </div>

    <?php

    require_once ABSPATH .'/wp-content/plugins/woocommerce/includes/admin/class-wc-admin-reports.php';
    foreach ( glob( ABSPATH .'/wp-content/plugins/woocommerce/includes/admin/reports/*.php' ) as $filename ) { require_once $filename; }
    WC_Admin_Reports::output();

    ?>

</div>

<?php else : ?>

<p><?php echo __( 'Only users with "Shop Reports" capabilities can view this page.', 'yith-frontend-manager-for-woocommerce'); ?></p>

<?php endif; ?>

<?php do_action( 'yith_wcfm_reports' );