<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Main class
 *
 * @class   YITH_Livechat_Premium
 * @package Yithemes
 * @since   1.0.0
 * @author  Your Inspiration Themes
 */

if ( ! class_exists( 'YITH_Livechat_Premium' ) ) {

	class YITH_Livechat_Premium extends YITH_Livechat {

		/**
		 * @var string Yith Live Chat Offline Messages
		 */
		protected $_offline_messages_page = 'ylc_offline_messages';

		/**
		 * @var string Yith Live Chat Logs
		 */
		protected $_chat_logs_page = 'ylc_chat_logs';

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_Livechat_Premium
		 * @since 1.1.0
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self;

			}

			return self::$instance;

		}

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since   1.0.0
		 * @return  mixed
		 * @author  Alberto Ruggiero
		 */
		public function __construct() {

			parent::__construct();

			$this->includes_premium();

			add_filter( 'ylc_default_avatar', array( $this, 'encoded_default_avatar_url' ), 10, 2 );
			add_filter( 'ylc_vendor', array( $this, 'get_vendor_info' ) );
			add_filter( 'ylc_vendor_only', array( $this, 'set_vendor_only' ) );

			if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {

				add_action( 'wp_footer', array( &$this, 'custom_css' ) );

				add_filter( 'ylc_plugin_opts_premium', array( $this, 'get_frontend_premium_options' ) );
				add_filter( 'ylc_max_guests', array( $this, 'get_max_guests' ) );

				add_filter( 'ylc_busy_form', array( $this, 'set_busy_form' ) );
				add_filter( 'ylc_autoplay_opts', array( $this, 'get_autoplay_opts' ) );
				add_filter( 'ylc_company_avatar', array( $this, 'get_company_avatar' ) );
				add_filter( 'ylc_can_show_chat', array( $this, 'show_chat_button' ), 10, 1 );
				add_filter( 'ylc_frontend_opts', array( $this, 'set_frontend_opts' ), 10 );

			} else {

				add_action( 'yit_panel_custom-email', array( $this, 'custom_email_template' ), 10, 3 );
				add_action( 'yit_panel_custom-colorpicker', array( $this, 'custom_colorpicker_template' ), 10, 3 );
				add_action( 'yit_panel_custom-number', array( $this, 'custom_number_template' ), 10, 3 );
				add_action( 'yit_panel_custom-upload', array( $this, 'custom_upload_template' ), 10, 3 );
				add_action( 'yit_panel_custom-select', array( $this, 'custom_select_template' ), 10, 3 );

				add_action( 'admin_menu', array( $this, 'add_console_submenu' ), 5 );
				add_action( 'init', array( $this, 'update_operators' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'premium_admin_scripts' ), 100 );

				add_filter( 'yith_wcmv_live_chat_caps', array( $this, 'add_vendor_capability' ) );
				add_filter( 'yith_wpv_vendor_menu_items', array( $this, 'activate_vendor' ) );
				add_filter( 'ylc_nickname', array( $this, 'get_nickname' ) );
				add_filter( 'ylc_avatar_type', array( $this, 'get_avatar_type' ) );
				add_filter( 'ylc_avatar_image', array( $this, 'get_avatar_image' ) );

				if ( current_user_can( 'answer_chat' ) ) {
					add_action( 'show_user_profile', array( &$this, 'custom_operator_fields' ), 10 );
					add_action( 'personal_options_update', array( &$this, 'save_custom_operator_fields' ) );
				}

				if ( current_user_can( 'edit_user' ) ) {

					add_action( 'edit_user_profile', array( &$this, 'custom_operator_fields' ), 10 );
					add_action( 'edit_user_profile_update', array( &$this, 'save_custom_operator_fields' ) );

				}

			}

			// register plugin to licence/update system
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

		}

		/**
		 * Include required core files
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function includes_premium() {

			// Back-end includes
			if ( is_admin() ) {
				include_once( 'includes/admin/class-yith-custom-table.php' );
				include_once( 'includes/class-ylc-macro.php' );
				include_once( 'templates/admin/ylc-offline-table.php' );
				include_once( 'templates/admin/ylc-chat-log-table.php' );
			}

			// Include core files
			include_once( 'includes/functions-ylc-ajax-premium.php' );
			include_once( 'includes/functions-ylc-email-premium.php' );
			include_once( 'includes/functions-ylc-commons-premium.php' );

		}

		/**
		 * Get vendor info (if is active YITH WooCommerce Multi Vendor premium)
		 *
		 * @since   1.1.0
		 * @return  array|int
		 * @author  Alberto Ruggiero
		 */
		public function get_vendor_info() {

			$result = array(
				'vendor_id'   => 0,
				'vendor_name' => ''
			);

			$vendor = '';

			if ( defined( 'YITH_WPV_PREMIUM' ) ) {

				if ( ! is_admin() ) {

					if ( YITH_Vendors()->frontend->is_vendor_page() ) {
						$vendor = yith_get_vendor( get_query_var( 'term' ) );
					} else {

						global $post;

						if ( $post && 'product' == $post->post_type ) {
							$_product = is_singular( 'product' ) ? WC()->product_factory->get_product( absint( $post->ID ) ) : __return_null();
							$vendor   = yith_get_vendor( $_product, 'product' );
						}

					}

				} else {

					$vendor = yith_get_vendor( 'current', 'user' );

				}

				if ( $vendor ) {
					$result['vendor_id']   = $vendor->id;
					$result['vendor_name'] = ( $vendor->id != 0 ) ? $vendor->term->name : '';
				}

			}

			return $result;

		}

		/**
		 * Encode default avatar URLs for Gravatar
		 *
		 * @since   1.0.0
		 *
		 * @param   $value
		 * @param   $type
		 *
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function encoded_default_avatar_url( $value, $type ) {

			return urlencode( YLC_ASSETS_URL . '/images/default-avatar-' . $type . '.png' );

		}

		/**
		 * Get premium options defaults
		 *
		 * @since   1.1.0
		 *
		 * @param   $defaults
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function ylc_get_defaults_premium( $defaults ) {

			$premium_defaults = array(
				'offline-mail-sender'          => '',
				'offline-mail-addresses'       => '',
				'offline-send-visitor'         => 'yes',
				'offline-message-body'         => __( 'Thanks for contacting us. We will answer as soon as possible.', 'yith-live-chat' ),
				'offline-busy'                 => 'no',
				'chat-evaluation'              => 'yes',
				'transcript-send'              => 'yes',
				'transcript-mail-sender'       => '',
				'transcript-message-body'      => __( 'Below you can find a copy of the chat conversation you have requested.', 'yith-live-chat' ),
				'transcript-send-admin'        => 'no',
				'transcript-send-admin-emails' => '',
				'header-button-color'          => '#009EDB',
				'chat-button-width'            => 260,
				'chat-button-diameter'         => 60,
				'chat-conversation-width'      => 370,
				'form-width'                   => 260,
				'chat-position'                => 'right-bottom',
				'border-radius'                => 5,
				'chat-animation'               => 'bounceIn',
				'chat-button-type'             => 'classic',
				'custom-css'                   => '',
				'operator-role'                => 'editor',
				'operator-avatar'              => '',
				'max-chat-users'               => 2,
				'only-vendor-chat'             => 'no',
				'hide-chat-offline'            => 'no',
				'showing-pages'                => array(),
				'showing-pages-all'            => 'yes',
				'autoplay-delay'               => 10
			);

			return array_merge( $defaults, $premium_defaults );

		}

		/**
		 * ADMIN FUNCTIONS
		 */

		/**
		 * Add YITH Live Chat to vendor admin panel (if is active YITH WooCommerce Multi Vendor premium)
		 *
		 * @since   1.1.0
		 *
		 * @param   $pages
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function activate_vendor( $pages ) {

			$pages[] = $this->_console_page;
			$pages[] = $this->_offline_messages_page;
			$pages[] = $this->_chat_logs_page;

			return $pages;

		}

		/**
		 * Add chat capability to vendor operators (if is active YITH WooCommerce Multi Vendor premium)
		 *
		 * @since   1.1.0
		 * @return  array|int
		 * @author  Alberto Ruggiero
		 */
		public function add_vendor_capability() {
			return array( 'answer_chat' => true );
		}

		/**
		 * Add submenu under YITH Live Chat console page
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function add_console_submenu() {

			$console_title = __( 'Chat console', 'yith-live-chat' );
			$offline_title = __( 'Offline messages', 'yith-live-chat' );
			$logs_title    = __( 'Chat logs', 'yith-live-chat' );
			$macro_title   = __( 'Chat macros', 'yith-live-chat' );

			if ( current_user_can( 'manage_options' ) ) {

				add_submenu_page( $this->_console_page, $console_title, $console_title, 'manage_options', $this->_console_page, array( $this, 'get_console_template' ) );
				add_submenu_page( $this->_console_page, $offline_title, $offline_title, 'manage_options', $this->_offline_messages_page, array( YLC_Offline_Messages(), 'output' ) );
				add_submenu_page( $this->_console_page, $logs_title, $logs_title, 'manage_options', $this->_chat_logs_page, array( YLC_Chat_Logs(), 'output' ) );
				add_submenu_page( $this->_console_page, $macro_title, $macro_title, 'manage_options', 'edit.php?post_type=ylc-macro' );
			} else {
				if ( current_user_can( 'answer_chat' ) ) {

					if ( ! $this->multivendor_check() ) {
						return;
					}

					add_submenu_page( $this->_console_page, $console_title, $console_title, 'answer_chat', $this->_console_page, array( $this, 'get_console_template' ) );
					add_submenu_page( $this->_console_page, $offline_title, $offline_title, 'answer_chat', $this->_offline_messages_page, array( YLC_Offline_Messages(), 'output' ) );
					add_submenu_page( $this->_console_page, $logs_title, $logs_title, 'answer_chat', $this->_chat_logs_page, array( YLC_Chat_Logs(), 'output' ) );
					add_submenu_page( $this->_console_page, $macro_title, $macro_title, 'answer_chat', 'edit.php?post_type=ylc-macro' );

				}
			}

		}

		/**
		 * Add styles and scripts for options panel
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function premium_admin_scripts() {

			if ( function_exists( 'WC' ) ) {

				wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . $this->is_script_debug_active() . '.js', array( 'jquery' ), WC_VERSION, true );
				wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2' . $this->is_script_debug_active() . '.js', array( 'jquery' ), '3.5.4' );

			} else {

				wp_register_script( 'jquery-tiptip', YLC_ASSETS_URL . '/js/jquery.tipTip' . $this->is_script_debug_active() . '.js', array( 'jquery' ), '1.3.0', true );
				wp_register_script( 'select2', YLC_ASSETS_URL . '/js/select2' . $this->is_script_debug_active() . '.js', array( 'jquery' ), '3.5.4' );

			}

			wp_register_style( 'ylc-tiptip', YLC_ASSETS_URL . '/css/tipTip' . $this->is_script_debug_active() . '.css' );
			wp_register_style( 'select2', YLC_ASSETS_URL . '/css/select2.css' );

			wp_register_script( 'ylc-admin-premium', YLC_ASSETS_URL . '/js/ylc-admin-premium' . $this->is_script_debug_active() . '.js', array( 'jquery', 'wp-color-picker', 'jquery-tiptip', 'select2' ), YLC_VERSION, true );

			$localization = array(
				'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'yith-live-chat' ),
				'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'yith-live-chat' ),
				'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'yith-live-chat' ),
				'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'yith-live-chat' ),
				'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'yith-live-chat' ),
				'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'yith-live-chat' ),
				'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'yith-live-chat' ),
				'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'yith-live-chat' ),
				'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'yith-live-chat' ),
				'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'yith-live-chat' ),
				'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'yith-live-chat' ),
				'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'yith-live-chat' ),
			);


			switch ( ylc_get_current_page() ) {

				case $this->_panel_page:

					wp_enqueue_style( 'select2' );
					wp_enqueue_style( 'wp-color-picker' );

					wp_enqueue_script( 'ylc-admin-premium' );

					wp_localize_script( 'ylc-admin-premium', 'ylc', $localization );

					break;

				case $this->_offline_messages_page:
				case $this->_chat_logs_page:
				case 'profile.php':
				case 'user-edit.php':

					//Load FontAwesome
					$this->load_fontawesome();

					wp_enqueue_style( 'ylc-tiptip' );
					wp_enqueue_style( 'ylc-styles' );

					wp_enqueue_script( 'jquery-tiptip' );
					wp_enqueue_script( 'ylc-admin-premium' );

					wp_localize_script( 'ylc-admin-premium', 'ylc', $localization );

					break;

				case $this->_console_page:

					wp_enqueue_style( 'select2' );
					wp_enqueue_script( 'select2' );

					break;

			}

		}

		/**
		 * Get user nickname
		 *
		 * @since   1.0.0
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function get_nickname() {

			return get_the_author_meta( 'ylc_operator_nickname', $this->user->ID );

		}

		/**
		 * Check if YITH Multi Vendor Premium is active and current vendor has Live Chat enabled
		 *
		 * @since   1.1.4
		 * @return  boolean
		 * @author  Alberto Ruggiero
		 */
		public function multivendor_check() {

			if ( defined( 'YITH_WPV_PREMIUM' ) && YITH_WPV_PREMIUM ) {

				$vendor = yith_get_vendor( 'current', 'user' );

				if ( get_option( 'yith_wpv_vendors_option_live_chat_management' ) != 'yes' && $vendor->is_valid() && $vendor->has_limited_access() ) {
					return false;
				}

			}

			return true;

		}

		/**
		 * Add custom operator fields
		 *
		 * @since   1.0.0
		 *
		 * @param   $user
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function custom_operator_fields( $user ) {

			if ( ! $this->multivendor_check() ) {
				return;
			}

			include( YLC_TEMPLATE_PATH . '/admin/custom-operator-fields.php' );

		}

		/**
		 * Save custom operator fields
		 *
		 * @since   1.0.0
		 *
		 * @param   $user_id
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function save_custom_operator_fields( $user_id ) {

			if ( ! $this->multivendor_check() ) {
				return;
			}

			if ( empty( $_POST['ylc_operator_nickname'] ) ) {

				$op_name = get_the_author_meta( 'nickname', $user_id );

			} else {
				$op_name = $_POST['ylc_operator_nickname'];
			}

			// Update user meta now
			update_user_meta( $user_id, 'ylc_operator_nickname', $op_name );
			update_user_meta( $user_id, 'ylc_operator_avatar_type', $_POST['ylc_operator_avatar_type'] );
			update_user_meta( $user_id, 'ylc_operator_avatar', $_POST['ylc_operator_avatar'] );

		}

		/**
		 * Load Custom Email Template
		 *
		 * @since   1.0.0
		 *
		 * @param   $option
		 * @param   $db_value
		 * @param   $custom_attributes
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function custom_email_template( $option, $db_value, $custom_attributes ) {

			include( YLC_TEMPLATE_PATH . '/admin/custom-email.php' );

		}

		/**
		 * Load Custom Colorpicker Template
		 *
		 * @since   1.0.0
		 *
		 * @param   $option
		 * @param   $db_value
		 * @param   $custom_attributes
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function custom_colorpicker_template( $option, $db_value, $custom_attributes ) {

			include( YLC_TEMPLATE_PATH . '/admin/custom-colorpicker.php' );

		}

		/**
		 * Load Custom Number Template
		 *
		 * @since   1.0.0
		 *
		 * @param   $option
		 * @param   $db_value
		 * @param   $custom_attributes
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function custom_number_template( $option, $db_value, $custom_attributes ) {

			include( YLC_TEMPLATE_PATH . '/admin/custom-number.php' );

		}

		/**
		 * Load Custom Upload Template
		 *
		 * @since   1.0.0
		 *
		 * @param   $option
		 * @param   $db_value
		 * @param   $custom_attributes
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function custom_upload_template( $option, $db_value, $custom_attributes ) {

			include( YLC_TEMPLATE_PATH . '/admin/custom-upload.php' );

		}

		/**
		 * Load Custom Select Template
		 *
		 * @since   1.1.3
		 *
		 * @param   $option
		 * @param   $db_value
		 * @param   $custom_attributes
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function custom_select_template( $option, $db_value, $custom_attributes ) {

			include( YLC_TEMPLATE_PATH . '/admin/custom-select.php' );

		}

		/**
		 * Updates operator roles
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function update_operators() {

			if ( isset( $_GET['settings-updated'] ) && 'true' == $_GET['settings-updated'] ) {

				$role = get_option( 'ylc_op_prev_role' );

				if ( $role != $this->options['operator-role'] ) {

					update_option( 'ylc_op_prev_role', $this->options['operator-role'] );

					$this->ylc_operator_role( $this->options['operator-role'] );

				}

			}

		}

		/**
		 * Get user avatar image
		 *
		 * @since   1.0.0
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function get_avatar_image() {

			$avatar_type = get_the_author_meta( 'ylc_operator_avatar_type', $this->user->ID );

			if ( $avatar_type == 'default' ) {

				$company_avatar = $this->get_company_avatar();

				if ( $company_avatar != '' ) {
					return $company_avatar;

				} else {

					return '';
				}

			} else {

				return get_the_author_meta( 'ylc_operator_avatar', $this->user->ID );

			}

		}

		/**
		 * Get user avatar type
		 *
		 * @since   1.0.0
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function get_avatar_type() {

			$avatar_type = get_the_author_meta( 'ylc_operator_avatar_type', $this->user->ID );

			if ( $avatar_type == 'default' ) {

				$company_avatar = $this->get_company_avatar();

				if ( $company_avatar != '' ) {
					$avatar_type = 'image';
				}

			}

			return $avatar_type;

		}

		/**
		 * Get company avatar
		 *
		 * @since   1.0.0
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function get_company_avatar() {

			$op_avatar = esc_html( $this->options['operator-avatar'] );

			if ( $op_avatar != '' ) {

				return $op_avatar;

			} else {

				return '';

			}

		}

		/**
		 * FRONTEND FUNCTIONS
		 */

		/**
		 * Get Premium Options
		 *
		 * @since   1.0.0
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function get_frontend_premium_options() {

			return array(
				'bg_color'       => $this->options['header-button-color'],
				'x_pos'          => $this->get_chat_position( 'x' ),
				'y_pos'          => $this->get_chat_position( 'y' ),
				'border_radius'  => $this->set_round_corners( $this->options['border-radius'], $this->options['chat-position'] ),
				'popup_width'    => $this->options['chat-conversation-width'],
				'btn_type'       => $this->get_chat_button_type(),
				'btn_width'      => ( $this->get_chat_button_type() == 'round' ? $this->options['chat-button-diameter'] : $this->options['chat-button-width'] ),
				'btn_height'     => ( $this->get_chat_button_type() == 'round' ? $this->options['chat-button-diameter'] : 0 ),
				'form_width'     => $this->options['form-width'],
				'animation_type' => $this->options['chat-animation'],
				'autoplay'       => false,//(int) $this->set_chat_autoplay( $this->options['autoplay-enabled'] ),
				'autoplay_delay' => $this->set_autoplay_delay( $this->options['autoplay-delay'] )
			);

		}

		/**
		 * Set Frontend Options
		 *
		 * @since   1.2.1
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function set_frontend_opts() {

			return array(
				'button_type' => $this->get_chat_button_type(),
				'button_pos'  => $this->get_chat_position( 'y' ),
				'form_width'  => 'width: ' . $this->options['form-width'] . 'px;',
				'chat_width'  => 'width: ' . $this->options['chat-conversation-width'] . 'px;',
			);

		}

		/**
		 * Get chat position
		 *
		 * @since   1.2.0
		 *
		 * @param   $pos
		 *
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function get_chat_position( $pos ) {

			$positions = explode( '-', apply_filters( 'ylc_frontend_position', $this->options['chat-position'] ) );

			return ( $pos == 'x' ? $positions[0] : $positions[1] );

		}

		/**
		 * Get chat button type
		 *
		 * @since   1.2.0
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function get_chat_button_type() {

			return apply_filters( 'ylc_frontend_button_type', $this->options['chat-button-type'] );

		}

		/**
		 * Set border radius
		 *
		 * @since   1.0.0
		 *
		 * @param   $pos
		 * @param   $radius
		 *
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function set_round_corners( $radius, $pos ) {

			if ( $radius != 0 ) {

				$positions = explode( '-', apply_filters( 'ylc_frontend_position', $pos ) );

				if ( $positions[1] == 'bottom' ) {

					return $radius . 'px ' . $radius . 'px 0 0';

				} else {

					return '0 0 ' . $radius . 'px ' . $radius . 'px';

				}

			} else {

				return 0;

			}

		}

		/**
		 * Set chat autoplay
		 *
		 * @since   1.0.0
		 *
		 * @param   $autoplay
		 *
		 * @return  bool
		 * @author  Alberto Ruggiero
		 */
		public function set_chat_autoplay( $autoplay ) {

			if ( $autoplay == 'yes' ) {

				return true;

			} else {

				return false;

			}

		}

		/**
		 * Set chat autoplay delay
		 *
		 * @since   1.0.0
		 *
		 * @param   $delay
		 *
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function set_autoplay_delay( $delay ) {

			return $delay * 1000;

		}

		/**
		 * Get autoplay options
		 *
		 * @since   1.0.0
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function get_autoplay_opts() {

			return array(
				'company_name' => '', //esc_html( $this->options['autoplay-operator'] ),
				'auto_msg'     => '', //$this->options['autoplay-welcome'],
			);

		}

		/**
		 * Add Custom CSS
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function custom_css() {

			$show_chat = apply_filters( 'ylc_can_show_chat', true );

			if ( $show_chat ) {

				$custom_css = $this->options['custom-css'];

				if ( $custom_css != '' ) :

					?>

					<style type="text/css">
						<?php
						echo stripslashes( $custom_css );
						?>
					</style>

					<?php

				endif;

			}
		}

		/**
		 * Get max chat users
		 *
		 * @since   1.0.0
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function get_max_guests() {

			return $this->options['max-chat-users'];

		}

		/**
		 * Set offline form when busy
		 *
		 * @since   1.0.0
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function set_busy_form() {
			$busy_form = $this->options['offline-busy'];

			if ( $busy_form == 'yes' ) {

				return true;

			} else {

				return false;

			}
		}

		/**
		 * Set chat for vendor sections only (if is active YITH WooCommerce Multi Vendor premium)
		 *
		 * @since   1.1.2
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function set_vendor_only() {

			$vendor_only = $this->options['only-vendor-chat'];

			if ( $vendor_only == 'yes' ) {

				return true;

			} else {

				return false;

			}

		}

		/**
		 * Checks if chat can be showed
		 *
		 * @since   1.1.3
		 *
		 * @param   $show
		 *
		 * @return  boolean
		 * @author  Alberto Ruggiero
		 */
		public function show_chat_button( $show ) {

			global $post;

			if ( defined( 'YITH_WPV_PREMIUM' ) && $this->options['only-vendor-chat'] == 'yes' ) {

				$vendor_info = $this->get_vendor_info();

				if ( $vendor_info['vendor_id'] == 0 ) {

					$show = false;

				}

			} else {

				if ( $this->options['showing-pages-all'] == 'yes' ) {

					$show = true;

				} else {

					$post_id = ( is_home() || is_front_page() ) ? 0 : $post->ID;

					if ( in_array( $post_id, $this->options['showing-pages'] ) ) {

						$show = true;

					} else {

						$show = false;

					}

				}


			}

			if ( $this->options['hide-chat-offline'] == 'yes' ) {

				$token      = YITH_Live_Chat()->user_auth();
				$request    = wp_remote_get( 'https://' . $this->options['firebase-appurl'] . '.firebaseio.com/chat_users.json?auth=' . $token );
				$users      = json_decode( wp_remote_retrieve_body( $request ) );
				$online_ops = 0;

				if ( $users ) {

					foreach ( $users as $user ) {

						$valid_op = false;

						if ( isset( $user->user_type ) && $user->user_type == 'operator' ) {

							if ( defined( 'YITH_WPV_PREMIUM' ) && $this->options['only-vendor-chat'] == 'yes' ) {

								$vendor_info = $this->get_vendor_info();

								if ( $vendor_info['vendor_id'] == $user->vendor_id && $user->status == 'online' ) {

									$valid_op = true;

								}

							} else {

								if ( $user->status == 'online' ) {

									$valid_op = true;

								}

							}


							if ( $valid_op ) {

								$online_ops ++;

							}

						}


					}

				}

				if ( $online_ops === 0 ) {

					$show = false;

				}

			}

			return $show;

		}

		/**
		 * YITH FRAMEWORK
		 */

		/**
		 * Register plugins for activation tab
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once 'plugin-fw/licence/lib/yit-licence.php';
				require_once 'plugin-fw/licence/lib/yit-plugin-licence.php';
			}
			YIT_Plugin_Licence()->register( YLC_INIT, YLC_SECRET_KEY, YLC_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @since   2.0.0
		 * @return  void
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once( 'plugin-fw/lib/yit-upgrade.php' );
			}
			YIT_Upgrade()->register( YLC_SLUG, YLC_INIT );
		}

	}

}