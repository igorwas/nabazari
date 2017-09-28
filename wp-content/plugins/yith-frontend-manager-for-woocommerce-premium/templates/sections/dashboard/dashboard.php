<?php
/**
 * Frontend Manager Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user;

?>
	<div id="yith-wcfm-dashboard">
		<p>
			<?php
			echo sprintf(
				esc_attr__(
					'Hello %s%s%s (not %2$s? %sSign out%s)', 'yith-frontend-manager-for-woocommerce' ),
				'<strong>', esc_html( $current_user->display_name ),
				'</strong>',
				'<a href="' . esc_url( wc_get_endpoint_url( 'customer-logout', '', wc_get_page_permalink( 'myaccount' ) ) ) . '">',
				'</a>'
			);
			?>
		</p>

		<h1><?php _e( 'SHOP STATS', 'yith-frontend-manager-for-woocommerce' ); ?></h1>

		<ul>
			<li>
				<span class="dashicons dashicons-chart-bar"></span>
				<?php echo __( 'Net sales this month', 'yith-frontend-manager-for-woocommerce' ); ?>
				<strong><?php echo wc_price( $report_data->net_sales ); ?></strong>
			</li>
			<li>
				<span class="dashicons dashicons-plus-alt"></span>
				<?php echo __( 'Awaiting process orders', 'yith-frontend-manager-for-woocommerce' ); ?>
				<strong><?php echo $processing_count; ?></strong>
			</li>
			<li>
				<span class="dashicons dashicons-marker"></span>
				<?php echo __( 'On-hold orders', 'yith-frontend-manager-for-woocommerce' ); ?>
				<strong><?php echo $on_hold_count; ?></strong>
			</li>
			<li>
				<span class="dashicons dashicons-warning"></span>
				<?php echo __( 'Low stock level', 'yith-frontend-manager-for-woocommerce' ); ?>
				<strong><?php echo $lowinstock_count; ?></strong>
			</li>
			<li>
				<span class="dashicons dashicons-dismiss"></span>
				<?php echo __( 'Products out of stock', 'yith-frontend-manager-for-woocommerce' ); ?>
				<strong><?php echo $outofstock_count; ?></strong>
			</li>
		</ul>
	</div>
<?php
/**
 * Frontend Manager Dashboard.
 *
 * @since 1.0.0
 */
do_action( 'yith_wcfm_dashboard' );
