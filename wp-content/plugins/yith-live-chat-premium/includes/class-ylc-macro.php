<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'YLC_Macro' ) ) {

	/**
	 * Macro class
	 *
	 * @class   YLC_Macro
	 * @package Yithemes
	 * @since   1.1.3
	 * @author  Your Inspiration Themes
	 *
	 */
	class YLC_Macro {

		/**
		 * Single instance of the class
		 *
		 * @var \YLC_Macro
		 * @since 1.1.3
		 */
		protected static $instance;

		/**
		 * @var $post_type string post type name
		 */
		protected $post_type = 'ylc-macro';

		/**
		 * Returns single instance of the class
		 *
		 * @return \YLC_Macro
		 * @since 1.1.3
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self( $_REQUEST );

			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since   1.1.3
		 * @return  mixed
		 * @author  Alberto Ruggiero
		 */
		public function __construct() {

			add_action( 'admin_init', array( $this, 'add_capabilities' ) );
			add_filter( 'yith_wcmv_review_discounts_caps', array( $this, 'get_capabilities' ) );

			add_action( 'init', array( $this, 'add_ylc_macro_post_type' ) );

			if ( is_admin() ) {

				add_filter( "manage_{$this->post_type}_posts_columns", array( $this, 'set_custom_columns' ) );
				add_action( "manage_{$this->post_type}_posts_custom_column", array( $this, 'render_custom_columns' ), 10, 2 );
				add_filter( 'ylc_macro_options', array( $this, 'get_macros' ) );

				add_filter( 'tiny_mce_before_init', array( $this, 'customize_tinymce' ) );
				add_filter( 'quicktags_settings', array( $this, 'customize_quicktags' ) );

			}

		}

		/**
		 * Add ylc-macro post type
		 *
		 * @since   1.1.3
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function add_ylc_macro_post_type() {

			$labels = array(
				'name'               => _x( 'Chat Macros', 'Post Type General Name', 'yith-live-chat' ),
				'singular_name'      => _x( 'Chat Macro', 'Post Type Singular Name', 'yith-live-chat' ),
				'add_new_item'       => __( 'Add New Chat Macro', 'yith-live-chat' ),
				'add_new'            => __( 'Add Chat Macro', 'yith-live-chat' ),
				'new_item'           => __( 'New Chat Macro', 'yith-live-chat' ),
				'edit_item'          => __( 'Edit Chat Macro', 'yith-live-chat' ),
				'view_item'          => __( 'View Chat Macro', 'yith-live-chat' ),
				'search_items'       => __( 'Search Chat Macro', 'yith-live-chat' ),
				'not_found'          => __( 'Not found', 'yith-live-chat' ),
				'not_found_in_trash' => __( 'Not found in Trash', 'yith-live-chat' ),
			);

			$args = array(
				'labels'              => $labels,
				'supports'            => array( 'title', 'editor' ),
				'hierarchical'        => false,
				'public'              => false,
				'show_ui'             => true,
				'menu_position'       => 10,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'has_archive'         => true,
				'exclude_from_search' => true,
				'menu_icon'           => 'dashicons-awards',
				'capability_type'     => 'ylc-macro',
//				'capabilities'        => $this->get_capabilities(),
				'map_meta_cap'        => true,
				'rewrite'             => false,
				'publicly_queryable'  => false,
				'query_var'           => false,
			);

			register_post_type( $this->post_type, $args );

		}

		/**
		 * Add management capabilities to Admin and Shop Manager
		 *
		 * @since   1.1.3
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function add_capabilities() {

			$caps = $this->get_capabilities();

			// gets the admin and shop_mamager roles
			$admin       = get_role( 'administrator' );
			$vendor_role = null;

			if ( function_exists( 'YITH_Vendors' ) ) {
				$vendor_role = get_role( YITH_Vendors()->get_role_name() );
			}

			foreach ( $caps as $key => $cap ) {
				$admin->add_cap( $cap );
				if ( $vendor_role ) {
					$vendor_role->add_cap( $cap );
				}
			}

		}

		/**
		 * Get capabilities for custom post type
		 *
		 * @since   1.1.3
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function get_capabilities() {

			$capability_type = 'ylc-macro';

			return array(
				'edit_post'              => "edit_{$capability_type}",
				'read_post'              => "read_{$capability_type}",
				'delete_post'            => "delete_{$capability_type}",
				'edit_posts'             => "edit_{$capability_type}s",
				'edit_others_posts'      => "edit_others_{$capability_type}s",
				'publish_posts'          => "publish_{$capability_type}s",
				'read_private_posts'     => "read_private_{$capability_type}s",
				'read'                   => "read",
				'delete_posts'           => "delete_{$capability_type}s",
				'delete_private_posts'   => "delete_private_{$capability_type}s",
				'delete_published_posts' => "delete_published_{$capability_type}s",
				'delete_others_posts'    => "delete_others_{$capability_type}s",
				'edit_private_posts'     => "edit_private_{$capability_type}s",
				'edit_published_posts'   => "edit_published_{$capability_type}s",
				'create_posts'           => "edit_{$capability_type}s",
				'manage_posts'           => "manage_{$capability_type}s",
			);

		}

		/**
		 * Set custom columns
		 *
		 * @since   1.1.3
		 *
		 * @param   $columns
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function set_custom_columns( $columns ) {

			$columns = array(
				'cb'          => '<input type="checkbox" />',
				'title'       => __( 'Title', 'yith-live-chat' ),
				'description' => __( 'Description', 'yith-live-chat' ),
			);

			return $columns;

		}

		/**
		 * Render custom columns
		 *
		 * @since   1.1.3
		 *
		 * @param   $column
		 * @param   $post_id
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function render_custom_columns( $column, $post_id ) {

			if ( $column == 'description' ) {

				echo get_post_field( 'post_content', $post_id );

			}

		}

		/**
		 * Get macros
		 *
		 * @since   1.1.3
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function get_macros() {

			$opts = array();

			$args = array(
				'post_type'      => $this->post_type,
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
			);

			$query = new WP_Query( $args );

			if ( $query->have_posts() ) {

				while ( $query->have_posts() ) {

					$query->the_post();

					$opts[] = '<option value="' . esc_attr( $query->post->post_content ) . '">' . $query->post->post_title . '</option>';

				}

			}

			wp_reset_query();
			wp_reset_postdata();

			if ( ! empty( $opts ) ) {
				return implode( '', $opts );
			} else {
				return '';
			}


		}

		/**
		 * Customize TinyMCE
		 *
		 * @since   1.1.3
		 *
		 * @param   $in
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function customize_tinymce( $in ) {

			$screen = get_current_screen();

			if ( $screen && $screen->id == $this->post_type ) {

				//$in['toolbar1'] = 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,wp_fullscreen,wp_adv ';
				//$in['toolbar1'] = 'bold,italic,strikethrough,wp_fullscreen ';
				$in['toolbar1'] = 'wp_fullscreen';
				$in['toolbar2'] = '';

			}

			return $in;

		}

		/**
		 * Customize Quicktags
		 *
		 * @since   1.1.3
		 *
		 * @param   $qtInit
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function customize_quicktags( $qtInit ) {

			$screen = get_current_screen();

			if ( $screen && $screen->id == $this->post_type ) {

				//$qtInit['buttons'] = 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close,dfw';
				//$qtInit['buttons'] = 'strong,em,del,close';
				$qtInit['buttons'] = 'dfw';

			}

			return $qtInit;

		}

	}

	/**
	 * Unique access to instance of YLC_Macro class
	 *
	 * @return \YLC_Macro
	 */
	function YLC_Macro() {

		return YLC_Macro::get_instance();

	}

	new YLC_Macro();

}

