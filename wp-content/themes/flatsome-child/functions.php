<?php
// Add custom Theme Functions here


function woocommerce_template_loop_product_title() {
	$length    = 29; //Length including the trunc_ext char count
	$trunc_ext = '...';
	$title     = get_the_title();
	echo '<p class="name product-title"><a href="' . get_the_permalink() . '" title="' . $title . '">' . mb_strimwidth( $title, 0, $length, $trunc_ext ) . '</a></p>';
}

function shipping_init() {
	// create a new taxonomy
	register_taxonomy(
		'shipping',
		'product',
		array(
			'label' => __( 'Shipping' ),
			'rewrite' => array( 'slug' => 'shipping' ),
			'hierarchical' => true
		)
	);
}
add_action( 'init', 'shipping_init' );

function payment_init() {
	// create a new taxonomy
	register_taxonomy(
		'payment',
		'product',
		array(
			'label' => __( 'Payment' ),
			'rewrite' => array( 'slug' => 'payment' ),
			'hierarchical' => true
		)
	);
}
add_action( 'init', 'payment_init' );

function list_terms_custom_taxonomy( $atts ) {
    extract( shortcode_atts( array(
        'custom_taxonomy' => '',
    ), $atts ) );

    if($custom_taxonomy == 'shipping') {
        $title = "Доставка";
    } else {
        $title = "Оплата";
    }

    $args = array(
    'taxonomy' => $custom_taxonomy,
    'orderby' => 'name',
    	          'show_count' => 0,
            	  'pad_counts' => 0,
    	          'hierarchical' => 1,
            	  'title_li' => '',
            	  'capabilities' => array(
                      'assign_terms' => 'edit_posts',
                      'edit_terms' => 'publish_posts'
                     )
    );

    echo '<aside id="woocommerce_product_categories-3" class="widget woocommerce widget_product_categories"><h3 class="widget-title shop-sidebar">'.$title.'</h3><div class="is-divider small"></div><ul class="product-categories">';
    echo wp_list_categories($args);
    echo '</ul></aside>';
}


add_shortcode( 'ct_terms', 'list_terms_custom_taxonomy' );

add_filter('widget_text', 'do_shortcode');