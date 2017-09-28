<?php
/**
 * WooCommerce More Sorting - Settings
 *
 * @version 3.1.0
 * @since   2.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Settings_More_Sorting' ) ) :

class Alg_WC_Settings_More_Sorting extends WC_Settings_Page {

	/**
	 * Constructor.
	 *
	 * @version 3.0.0
	 */
	function __construct() {
		$this->id    = 'alg_more_sorting';
		$this->label = __( 'More Sorting', 'woocommerce-more-sorting' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 2.1.0
	 */
	function get_settings() {
		global $current_section;
		return apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() );
	}

	/**
	 * maybe_reset_settings.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 * @todo    (maybe) reset `alg_wc_more_sorting_custom_meta_sorting_total_number` to 100 (max)
	 */
	function maybe_reset_settings() {
		global $current_section;
		if ( 'yes' === get_option( $this->id . '_' . $current_section . '_reset', 'no' ) ) {
			foreach ( $this->get_settings() as $value ) {
				if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
					delete_option( $value['id'] );
					$autoload = isset( $value['autoload'] ) ? ( bool ) $value['autoload'] : true;
					add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
				}
			}
		}
	}

	/**
	 * Save settings.
	 *
	 * @version 3.1.0
	 * @since   3.1.0
	 */
	function save() {
		parent::save();
		$this->maybe_reset_settings();
	}
}

endif;

return new Alg_WC_Settings_More_Sorting();
