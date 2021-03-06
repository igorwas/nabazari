<?php

defined( 'ABSPATH' ) or exit;

$_GET['tab'] = 'stock';
$page_id = isset( $_GET['page_id'] ) && $_GET['page_id'] > 0 ?  $_GET['page_id'] : '';

if ( current_user_can( 'view_woocommerce_reports' ) ) : ?>

<div id="yith-wcmf-reports">

    <h1><?php echo __('Commissions', 'woocommerce'); ?></h1>

    <div class="wrap woocommerce">

        <?php

        require_once ABSPATH .'/wp-content/plugins/woocommerce/includes/admin/class-wc-admin-reports.php';
        foreach ( glob( ABSPATH .'/wp-content/plugins/woocommerce/includes/admin/reports/*.php' ) as $filename ) { require_once $filename; }
        require_once YITH_WPV_PATH .'/includes/class.yith-reports.php';
        
        require_once YITH_WPV_PATH .'/includes/reports/class.yith-report-sale-commissions.php';
        $report = new YITH_Report_Sale_Commissions();
        $report->output_report();

        ?>

    </div>

</div>

<?php else : ?>

<p><?php echo __( 'Only users with "Shop Reports" capabilities can view this page.', 'yith-frontend-manager-for-woocommerce'); ?></p>

<?php endif; ?>

<?php do_action( 'yith_wcfm_reports' );
