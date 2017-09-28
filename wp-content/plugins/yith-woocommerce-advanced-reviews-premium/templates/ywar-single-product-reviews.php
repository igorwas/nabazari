<?php
/**
 * Display single product reviews for YITH WooCommerce Advanced Reviews
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/ywar-single-product-reviews.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see           http://docs.woothemes.com/document/template-structure/
 * @author        Yithemes
 * @package       yit-woocommerce-advanced-reviews/Templates
 * @version       1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $review_stats;
global $product;
$product_id = yit_get_prop($product, 'id');
?>

<div id="ywar_reviews">
	<div id="reviews_summary">
		<h3><?php _e( 'Customers\' review', 'yith-woocommerce-advanced-reviews' ) ?></h3>

		<?php do_action( 'ywar_summary_prepend', $product ) ?>

		<div class="reviews_bar">

			<?php for ( $i = 5; $i >= 1; $i -- ) :
				$perc = ( $review_stats['total'] == '0' ) ? 0 : floor( $review_stats[ $i ] / $review_stats['total'] * 100 );
				?>

				<div class="ywar_review_row">
					<?php do_action( 'ywar_summary_row_prepend', $i, $product_id, $perc ) ?>

					<span class="ywar_stars_value" style="color:<?php echo get_option( 'ywar_summary_rating_label_color' ); ?>">
						<?php printf( _n( '%s star', '%s stars', $i, 'yith-woocommerce-advanced-reviews' ), $i ); ?>
					</span>
					<span class="ywar_num_reviews" style="color:<?php echo get_option( 'ywar_summary_count_color' ); ?>">
						<?php echo $review_stats[ $i ]; ?>
					</span>
					<span class="ywar_rating_bar">
                    <span style="background-color:<?php echo get_option( 'ywar_summary_bar_color' ); ?>"
                          class="ywar_scala_rating">
						<span class="ywar_perc_rating"
						      style="width: <?php echo $perc; ?>%; background-color:<?php echo get_option( 'ywar_summary_percentage_bar_color' ); ?>">
							<?php if ( 'yes' == get_option( 'ywar_summary_percentage_value' ) ) : ?>
								<span style="color:<?php echo get_option( 'ywar_summary_percentage_value_color' ); ?>"
								      class="ywar_perc_value"><?php printf( '%s %%', $perc ); ?></span>
							<?php endif; ?>
						</span>
					</span>
				</span>

					<?php do_action( 'ywar_summary_row_append', $i, $product_id, $perc ) ?>
				</div>
			<?php endfor; ?>
		</div>

		<?php do_action( 'ywar_summary_append' ) ?>

		<?php if ( has_action( 'ywar_reviews_header' ) ) : ?>
			<div id="reviews_header">
				<?php do_action( 'ywar_reviews_header', $review_stats ) ?>
			</div>
		<?php endif; ?>
	</div>

	<?php do_action( 'ywar_after_summary', $product_id ) ?>
	<div id="reviews_dialog"></div>
</div>
