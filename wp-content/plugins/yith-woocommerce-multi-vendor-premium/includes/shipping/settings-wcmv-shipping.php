<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for flat rate shipping.
 */
$settings = array(

    'enabled' => array(
        'title'         => __( 'Enable/Disable', 'yith-woocommerce-product-vendors' ),
        'type'          => 'checkbox',
        'label'         => __( 'Enable Shipping', 'yith-woocommerce-product-vendors' ),
        'default'       => 'yes'
    ),

	'title' => array(
		'title'       => __( 'Method Title', 'yith-woocommerce-product-vendors' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'yith-woocommerce-product-vendors' ),
		'default'     => __( 'Per Vendor Shipping', 'yith-woocommerce-product-vendors' ),
		'desc_tip'    => true
	),
	'tax_status' => array(
		'title'       => __( 'Tax Status', 'yith-woocommerce-product-vendors' ),
		'type'        => 'select',
		'description' => '',
		'default'     => 'taxable',
		'options'     => array(
			'taxable' => __( 'Taxable', 'yith-woocommerce-product-vendors' ),
			'none'    => __( 'None', 'yith-woocommerce-product-vendors' ),
		),
	),

);

return $settings;
