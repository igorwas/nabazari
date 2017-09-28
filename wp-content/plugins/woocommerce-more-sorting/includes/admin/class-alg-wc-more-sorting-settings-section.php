<?php
/**
 * More Sorting for WooCommerce - Section Settings
 *
 * @version 3.1.0
 * @since   3.1.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_More_Sorting_Settings_Section' ) ) :

class Alg_WC_More_Sorting_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function __construct() {
		$this->additional_desc_tip = sprintf( __( 'You will need <a target="_blank" href="%s">More Sorting Options for WooCommerce Pro</a> plugin to change this value.', 'woocommerce-more-sorting' ), 'https://wpcodefactory.com/item/more-sorting-options-for-woocommerce-wordpress-plugin/' );
		add_filter( 'woocommerce_get_sections_alg_more_sorting',                   array( $this, 'settings_section' ) );
		add_filter( 'woocommerce_get_settings_alg_more_sorting' . '_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
	}

	/**
	 * settings_section.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function settings_section( $sections ) {
		$sections[ $this->id ] = $this->desc;
		return $sections;
	}

	/**
	 * get_settings.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function get_settings() {
		return array_merge( $this->get_section_settings(), array(
			array(
				'title'     => __( 'Reset Section Settings', 'woocommerce-more-sorting' ),
				'type'      => 'title',
				'id'        => 'alg_more_sorting' . '_' . $this->id . '_reset_options',
			),
			array(
				'title'     => __( 'Reset Settings', 'woocommerce-more-sorting' ),
				'desc'      => '<strong>' . __( 'Reset', 'woocommerce-more-sorting' ) . '</strong>',
				'id'        => 'alg_more_sorting' . '_' . $this->id . '_reset',
				'default'   => 'no',
				'type'      => 'checkbox',
			),
			array(
				'type'      => 'sectionend',
				'id'        => 'alg_more_sorting' . '_' . $this->id . '_reset_options',
			),
		) );
	}

}

endif;
