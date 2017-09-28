<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct access forbidden.' );
}

/**
 * @class      YITH_WCFM_For_Vendor
 * @package    Yithemes
 * @since      Version 1.7
 * @author     Your Inspiration Themes
 *
 */
if ( ! class_exists( 'YITH_Frontend_Manager_For_Vendor' ) ) {

    /**
     * YITH_Frontend_Manager_For_Vendor Class
     */
    class YITH_Frontend_Manager_For_Vendor {

        /**
         * Main instance
         */
        private static $_instance = null;

        /**
         * check if current user is a valid vendor
         */
        private $current_user_is_vendor = null;

        /**
         * check if current user is a valid vendor
         */
        public $vendor = null;

        /**
         * Construct
         */
        public function __construct(){

            $this->vendor = yith_get_vendor( 'current', 'user' );
            $this->current_user_is_vendor = $this->vendor->is_valid() && $this->vendor->has_limited_access();

            if( ! is_admin() ) {
                //Instance of vendors admin classes on front
                $admin_multivendor_classes = YITH_Vendors()->require['admin'];

                foreach ($admin_multivendor_classes as $class) {
                    require_once(YITH_WPV_PATH . $class);
                }

                $main_admin_class = 'YITH_Vendors_Admin';

                if (class_exists($main_admin_class . '_Premium')) {
                    $main_admin_class = $main_admin_class . '_Premium';
                }

                YITH_Vendors()->admin = new $main_admin_class();

                if ('yes' == get_option('yith_wpv_vendors_option_shipping_management')) {

                    $shipping_classes = array(
                        'YITH_Vendor_Shipping_Admin' => 'includes/shipping/class.yith-wcmv-shipping-admin.php',
                        'YITH_Vendor_Shipping_Frontend' => 'includes/shipping/class.yith-wcmv-shipping-frontend.php',
                        'YITH_Vendor_Shipping' => 'includes/modules/module.yith-vendor-shipping.php'
                    );

                    foreach ($shipping_classes as $class => $filename) {
                        if (! class_exists($class) && file_exists( YITH_WPV_PATH . $filename ) ) {
                            require_once( YITH_WPV_PATH . $filename );
                        }
                    }
                }
            }

            if( $this->current_user_is_vendor ){
                $is_vendor_owner = get_current_user_id() == $this->vendor->get_owner();
                // Vendors admin limitation
                if( ! $is_vendor_owner ){
                    // Allow commissions section only for vendor store owner
                    add_filter( 'yith_wcfm_print_commissions_section', '__return_false' );
                    add_filter( 'yith_wcfm_remove_commissions_menu_item', '__return_true' );
                }
                //Allow vendors to manage WooCommerce on front
                add_filter( 'yith_wcfm_access_capability', array( $this, 'allow_vendor_to_manage_store_on_front' ) );

                //Vendor can't manage taxonomy and we need to remove the add new tags and add new category button
                add_filter( 'yith_wcfm_show_add_new_product_taxonomy_term', '__return_false' );

                if( ! is_admin() && function_exists( 'YITH_Vendor_Shipping' ) ){

                    YITH_Vendor_Shipping()->admin = new YITH_Vendor_Shipping_Admin();

                    /* === Shipping Modules === */
                    add_action( 'wp_enqueue_scripts', array( YITH_Vendor_Shipping()->admin, 'enqueue_scripts' ), 20 );
                    add_filter( 'yith_wcmv_is_shipping_tab', 'YITH_Frontend_Manager_For_Vendor::is_shipping_tab' );
                }

                /* === Manage Section === */
                add_filter( 'yith_wcfm_get_section_enabled_id_from_object', array( $this, 'get_section_enabled_id_from_object' ) );

                /* === Products === */
                add_action( 'init', array( $this, 'products_management' ), 20 );
                add_filter( 'yith_wcfm_premium_products_subsections', array( $this, 'prevent_vendor_edit_product_taxonomies' ), 10, 4 );

                /* === Coupons === */
                add_action( 'init', array( $this, 'coupons_management' ), 20 );

                /* === Orders === */
                add_action( 'init', array( $this, 'orders_management' ), 20 );
                add_filter( 'yith_wcfm_get_subsections_in_print_navigation', array( $this, 'orders_subsections' ), 10, 2 ) ;

                /* === Vendor Panel: Search Customer ===  */
                add_action( 'wc_ajax_json_search_customers', 'YITH_Vendors_Admin::json_search_admins', 5);

                /* === Vendors Reports === */
                add_filter( 'yith_wcfm_reports_subsections', array( $this, 'add_vendor_commissions_reports_subsections' ), 10, 2 );
                add_filter( 'yith_wcfm_orders_reports_type', array( $this, 'orders_reports_type' ) );

                /* === Dashboard === */
                add_filter( 'yith_wcfm_outofstock_count_transient', '__return_false' );
                add_filter( 'yith_wcfm_low_stock_count_transient', '__return_false' );
                add_filter( 'yith_wcfm_save_stock_transient', '__return_false' );
            }

            else { //WebSite Admin

                /* === Reports === */
                add_filter( 'yith_wcfm_reports_subsections', array( $this, 'add_vendor_reports_admin_subsections' ), 15, 2 );

                /* === Orders === */
                add_action( 'yith_wcfm_order_cols_suborder', array( YITH_Vendors()->orders, 'render_shop_order_columns' ), 10, 2 );

                /* === Create and Save Section Option for Vendor === */
                add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'admin_settings_sanitize_option' ), 10, 3 );
                add_action( 'woocommerce_admin_field_yith-wcfm-double-checkbox', 'yith_wcfm_double_checkbox', 10, 1 );
                add_filter( 'yith_wcfm_section_option_type', 'YITH_Frontend_Manager_For_Vendor::section_option_type', 10, 2 );
                add_filter( 'yith_wcfm_section_option_title', 'YITH_Frontend_Manager_For_Vendor::section_option_title', 10, 2 );

                /* === Products === */
                add_action( 'yith_wcfm_product_save', array( YITH_Vendors()->admin, 'save_product_commission_meta' ), 10, 2 );
                add_filter( 'yith_wcmv_single_product_commission_value_object', array( $this, 'single_product_commission_value_object' ) );
            }

            /* === Register Style === */
            add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );

            /* === Both Actions for Admin and Vendors === */
            add_filter( 'yith_wcfm_print_shortcode_template_args', array( $this, 'orders_template_args' ) );

            /* === Reports === */
            add_action( 'yith_wcmv_after_setup', 'YITH_Frontend_Manager::regenerate_transient_rewrite_rule_transient' );

            $YITH_Vendors_Admin = YITH_Vendors()->admin;

            if( empty( $YITH_Vendors_Admin ) ){
                $YITH_Vendors_Admin_Class = $this->include_admin_class();
                $YITH_Vendors_Admin = new $YITH_Vendors_Admin_Class();
            }

            /* === Products === */
            add_action( 'yith_wcfm_show_product_metaboxes', array( $this, 'show_vendor_admin_metaboxes' ), 20, 1 );
            add_action( 'yith_wcfm_product_save', array( $YITH_Vendors_Admin, 'add_vendor_taxonomy_to_product' ), 10, 2 );

            if( ! is_admin() && ! is_ajax() ){
	            /* === Reports === */
	            add_filter( 'woocommerce_reports_get_order_report_data_args', array( $this, 'filter_dashboard_values' ) );

	            remove_action( 'admin_menu', array( $YITH_Vendors_Admin, 'vendor_settings' ) );

                add_filter( 'yith_wcmv_edit_order_uri', array( $this, 'edit_order_uri' ), 10, 2 );
                add_filter( 'wp_count_posts', 'YITH_Vendors_Admin::vendor_count_posts', 10, 3 );

                /* === Orders === */
                add_filter( 'yith_wcmv_commissions_attribute_label_is_edit_order_page', array( $this, 'is_edit_order_page' ) );
                add_filter( 'yith_wcmv_commissions_attribute_label_order_object', array( $this, 'single_order_page_object' ) );

                /* === URI Management === */
                add_filter( 'yith_wcmv_commissions_list_table_commission_url', 'yith_wcfm_commission_url', 10, 2 );
                add_filter( 'yith_wcmv_commissions_list_table_product_url', 'yith_wcfm_product_url', 10, 3 );
                add_filter( 'yith_wcmv_commissions_list_table_order_url', 'yith_wcfm_order_url', 10, 3 );
            }
        }

        /**
         * is edit order page
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return bool
         */
        public function is_edit_order_page( $check ){
            $obj = YITH_Frontend_Manager()->gui->get_current_section_obj();
            if( ! empty( $obj ) && $obj->is_current( 'product_order' ) ){
                $check = true;
            }
            return $check;
        }

        /**
         * is edit order page
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return bool
         */
        public function single_order_page_object( $order ){
            if( ! empty( $_GET['id'] ) ){
                $try_get_order = wc_get_order( $_GET['id'] );
                if( $try_get_order instanceof WC_Order ){
                    $order = $try_get_order;
                }
            }

            return $order;
        }


        /**
         * Main plugin Instance
         *
         * @static
         * @return YITH_Frontend_Manager_For_Vendor Main instance
         *
         * @since  1.7
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Show vendor metabox on vedit product
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return void
         */
        public function show_vendor_admin_metaboxes( $_post = null ){
            global $post;
            $old_post = $post;

            if( ! empty( $_post ) ){
                $post = $_post;
            }

            ob_start(); ?>

            <p class="form-field">
                <label>
                    <?php echo YITH_Vendors()->get_vendors_taxonomy_label( 'singular_name' ); ?>
                </label>
                <?php
                $YITH_Vendors_Admin = ! empty( YITH_Vendors()->admin ) ? YITH_Vendors()->admin : new YITH_Vendors_Admin();
                $YITH_Vendors_Admin->single_taxonomy_meta_box( YITH_Vendors()->get_taxonomy_name(), __return_empty_string() );
                ?>
            </p>

            <?php
            echo ob_get_clean();

            $post = $old_post;

        }

        /**
         * Filter dashboard value
         *
         * @param $args
         * @return mixed
         */
        public function filter_dashboard_values( $args ){

            $current_section = YITH_Frontend_Manager()->gui->get_current_section_obj()->get_id();

            if( 'dashboard' == $current_section ){
                if( $this->vendor->is_valid() && $this->vendor->has_limited_access() ) {
                    $args['where'] = array(
                        array(
                            'key' => 'posts.ID',
                            'operator' => 'in',
                            'value' => $this->vendor->get_orders('all')
                        )
                    );
                }

                elseif( $this->vendor->is_super_user() ){

                    $args['where'] = array(
                        array(
                            'key'      => 'posts.post_parent',
                            'operator' => '=',
                            'value'    => 0
                        )
                    );
                }
            }

            return $args;
        }

        /**
         * @param $type
         * @return string
         */
        public static function section_option_type( $type, $section_obj ){
            $type = ! empty( $section_obj ) && 'vendor-panel' != $section_obj->id ? 'yith-wcfm-double-checkbox' : 'checkbox';
            return $type;
        }

        /**
         * @param $type
         * @return string
         */
        public static function section_option_title( $title, $section_obj ){
            if( ! empty( $section_obj ) && 'vendor-panel' == $section_obj->id ){
                $title = sprintf( '%s (%s)', $title, __( 'only available for vendors', 'yith-frontend-manager-for-woocommerce' ) );
            }
            return $title;
        }

        /**
         * Register style and script
         */
        public function register_scripts(){
            wp_register_style( 'jquery-chosen', YITH_WCFM_URL . 'plugin-fw/assets/css/chosen/chosen.css', array(), YITH_WPV_VERSION );
            wp_register_style( 'yith-wc-product-vendors-admin', YITH_WPV_ASSETS_URL . 'css/admin.css', array( 'jquery-chosen' ), YITH_WPV_VERSION );
        }

        /**
         * Allow vendor to manager store on front
         *
         * @static
         * @return string Vendor role
         *
         * @since  1.0
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function allow_vendor_to_manage_store_on_front( $cap ){
            return YITH_Vendors()->get_role_name();
        }

        /**
         * filter the section enabled id for vendor
         *
         * @param $option_id
         * @return string  Vendor section enabled id
         */
        public function get_section_enabled_id_from_object( $option_id ){
            return $option_id . '_vendor';
        }


        /* === START PRODUCTS === */

        /**
         * add products management action
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return void
         */
        public function products_management(){
            add_filter( 'yith_wcfm_products_list_query_args', array( $this, 'filter_product_list' ) );
            add_filter( 'clean_url', array( $this, 'change_blank_state_url' ), 10, 3 );
            add_filter( 'yith_wcfm_products_list_cols', array( $this, 'remove_vendor_col_in_product_list' ) );
            add_filter( 'yith_wcfm_print_product_section', array( $this, 'check_for_vendor_product' ), 10, 4 );
            add_filter( 'manage_product_posts_columns', array( $this, 'render_product_columns' ), 15 );
            add_filter( 'yith_wcfm_allowed_product_status', array( $this, 'allowed_product_status' ) );
        }

        /**
         * Add vendor tax query arg
         *
         * @return array query args
         *
         * @since  1.0
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function filter_product_list( $query_args ){
            $vendor_query_args = $this->vendor->get_query_products_args();
            if( ! empty( $vendor_query_args['tax_query'] ) ){
                $query_args['tax_query'] = ! empty( $query_args['tax_query'] ) ? array_merge( $query_args['tax_query'], $vendor_query_args['tax_query'] ) : $vendor_query_args['tax_query'];
            }
            return $query_args;
        }

        /**
         * Add vendor tax query arg
         *
         * @return string product url
         *
         * @since  1.0
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function change_blank_state_url( $good_protocol_url, $original_url, $_context ){
            $product_blank_state_url = admin_url( 'post-new.php?post_type=product&tutorial=true' );
            if( $original_url == $product_blank_state_url && is_admin() && ! YITH_Frontend_Manager()->is_admin ){
                $sections = YITH_Frontend_Manager()->gui->get_sections();
                $products_section_obj = ! empty( $sections['products'] ) ? $sections['products'] : null;
                if( ! is_null( $products_section_obj ) ){
                    $good_protocol_url = $products_section_obj->get_url('product');
                }
            }
            return $good_protocol_url;
        }

        /**
         * Remove vendor taxonomies col in product list table
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return array cols
         */
        public function remove_vendor_col_in_product_list( $cols ){
            if( $this->vendor->is_valid() && isset( $cols['taxonomy-yith_shop_vendor'] ) ){
                unset( $cols['taxonomy-yith_shop_vendor'] );
            }
            return $cols;
        }

        /**
         * Remove taxonomies management for vendors
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return array product sebsections
         */
        public function prevent_vendor_edit_product_taxonomies( $subsections, $free_subsections, $premium_subsections, $obj ){
            return $free_subsections;
        }

        /**
         * Check if the current product are assign to vendor or not
         * If not an error message shown
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return bool $check = true if vendor can edit this product, not otherwise
         */
        public function check_for_vendor_product( $check, $subsection, $section, $atts ){
            if( ! empty( $_GET['product_id'] ) && $this->vendor->is_valid() ){
                $product_id = $_GET['product_id'];
                $check = has_term( $this->vendor->id, YITH_Vendors()->get_taxonomy_name(), $product_id );
            }

            else {
                if( method_exists( YITH_Vendors()->admin, 'vendor_can_add_products' ) ){
                    $section = $atts['section_obj'];

                    if( array_key_exists( 'product', $section->get_current_subsection() ) ){
                        $check = YITH_Vendors()->admin->vendor_can_add_products( $this->vendor, 'product' );
                        if( ! $check ){
                            add_filter( "yith_wcfm_restricted_products_section_args", array( $this, 'product_amount_limit_message' ) );
                        }
                    }
                }
            }

            return $check;
        }

        public function product_amount_limit_message( $args ){
            $products_limit = get_option( 'yith_wpv_vendors_product_limit', 25 );
            $args['alert_message'] = sprintf( __( 'You are not allowed to create more than %1$s products', 'yith-woocommerce-product-vendors' ), $products_limit );
            return $args;
        }

        /**
         * Check for featured management
         *
         * Allowed or Disabled for vendor
         *
         * @since  1.3
         *
         * @param $columns The product column name
         *
         * @return array
         * @author andrea Grilo <andrea.grillo@yithemes.com>
         */
        public function render_product_columns( $columns ) {
            if ( $this->vendor->is_valid() && $this->vendor->has_limited_access() && 'no' == $this->vendor->featured_products ) {
                unset( $columns['featured'] );
            }

            return $columns;
        }

        /**
         * Get select for product status
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return array product allowed status
         */
        public function allowed_product_status( $product_status ){
            $is_edit_product = ! empty( $_GET['product_id'] );
            $is_add_product  = empty( $_GET['product_id'] );
            $back_to_review = 'yes' == get_option( 'yith_wpv_vendors_option_pending_post_status', 'no' );

            if( ( $is_edit_product && $back_to_review ) || ( $is_add_product && 'no' == $this->vendor->skip_review ) ){
                $not_allowed = array( 'publish', 'draft' );
                foreach( $not_allowed as $remove ){
                    if( isset( $product_status[ $remove ] ) ){
                        unset( $product_status[ $remove ] );
                    }
                }
            }

            return $product_status;
        }

        /**
         * Get default product for single commission
         *
         * @since 1.0
         * @return $post object
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function single_product_commission_value_object( $_product ){
            if( ! empty( $_GET['product_id'] ) ){
                $_product = wc_get_product( $_GET['product_id'] );
            }
            return $_product;
        }

        /* === END PRODUCTS === */

        /* === START COUPONS === */

        /**
         * add coupons management action
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return void
         */
        public function coupons_management(){
            add_filter( 'yith_wcfm_query_coupons_args', array( $this, 'filter_coupons_list' ) );
            add_filter( 'yith_wcfm_print_coupons_section', array( $this, 'check_for_vendor_coupon' ), 10, 4 );
            add_filter( 'yith_wcfm_coupons_args', array( $this, 'vendor_coupons_type' ), 99 );
            add_filter( 'yith_wcfm_print_section_path', array( $this, 'add_edit_coupon_template' ), 10, 3 );
        }

        /**
         * Only show current vendor's coupon
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         *
         * @param  array $request Current request
         *
         * @return array Modified request
         * @since  1.0
         */
        public function filter_coupons_list( $args ) {
            if ( $this->vendor->is_valid() ) {
                $args[ 'author__in' ] = $this->vendor->admins;
            }

            return $args;
        }

        /**
         *
         */
        public function add_edit_coupon_template( $section, $subsection, $section_id ){
            if( 'coupons' == $section_id && 'coupon' == $subsection ){
                $section = 'multi-vendor';
            }

            return $section;
        }

        /**
         * Check if the current coupon are assign to vendor or not
         * If not an error message shown
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return bool $check = true if vendor can edit this product, not otherwise
         */
        public function check_for_vendor_coupon( $check, $subsection, $section, $atts ){
            if( ! empty( $_GET['coupons'] ) && 'coupon' == $_GET['coupons'] && ! empty( $_GET['code'] ) && $this->vendor->is_valid() ){
                global $wpdb;
                $coupon_code = $_GET['code'];
                $sql = $wpdb->prepare( "SELECT post_author FROM $wpdb->posts WHERE post_title = %s AND post_type = 'shop_coupon' LIMIT 1;", $coupon_code );
                $post_author = $wpdb->get_var( $sql );
                $check = in_array( $post_author, $this->vendor->get_admins() );
            }

            return $check;
        }

        /**
         * Only show vendor coupon type
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         *
         * @param  array $atts template args
         *
         * @return array $atts template args
         * @since  1.0
         */
        public function vendor_coupons_type( $atts ){
            if( ! empty( $atts['coupon_types'] ) ){
                $to_disabled = array( 'percent', 'fixed_cart' );
                foreach ( $to_disabled as $disabled ){
                    if( isset( $atts['coupon_types'][$disabled] ) ){
                        unset( $atts['coupon_types'][$disabled] );
                    }
                }
            }

            return $atts;
        }

        /* === END COUPONS === */

        /* === START ORDERS === */

        /**
         * add products management action
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return void
         */
        public function orders_management(){
            add_filter( 'yith_wcfm_print_orders_section', array( $this, 'check_for_vendor_order' ), 10, 4 );
            add_filter( 'yith_wcmv_is_vendor_order_details_page', array( $this, 'is_vendor_order_details_page' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'allow_vendor_to_manage_refunds' ), 20 );
            add_filter( 'yith_wcmv_print_orders_list_shortcode', array( $this, 'vendors_orders_list' ), 99, 2 );
        }

        /**
         * check if vendor can manage refunds or not
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return void
         */
        public function allow_vendor_to_manage_refunds(){
            if( $this->current_user_is_vendor && YITH_Vendors()->orders->is_vendor_order_details_page() ){
                $refund_management = 'yes' == get_option ( 'yith_wpv_vendors_option_order_refund_synchronization', 'no' );
                if( ! $refund_management ){
                    $js = "jQuery( 'button.refund-items' ).remove();";
                    wp_add_inline_script( 'yith-frontend-manager-order-js', $js );
                }
            }
        }

        /**
         * Remove add order cap for vendors
         *
         * @since 1.0
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @return Orders section for vendors
         */
        public function orders_subsections( $subsections, $section ){
            if( YITH_Frontend_Manager()->gui->get_section( 'product_orders' ) == $section ){
                unset( $subsections[ 'product_order' ] );
            }

            return $subsections;
        }

        /**
         * Check if the current page is an order tails page
         * if yes add script to remove the payments and customer infotmation
         * for vendors, if options is enabled
         *
         * @param $check
         * @since 1.0
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @return bool yes if it0s an order details page on front, false otherwise
         */
        public function is_vendor_order_details_page( $check ){
            if( YITH_Frontend_Manager()->gui ){
                $current_section = YITH_Frontend_Manager()->gui->get_current_section_obj();
                if( $current_section instanceof YITH_Frontend_Manager_Section && $current_section->is_current() && ! empty( $_GET['id'] ) ){
                    $check = true;
                }
            }

            return $check;
        }

        /**
         * Check if the current order are assign to vendor or not
         * If not an error message shown
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return bool $check = true if vendor can edit this product, not otherwise
         */
        public function check_for_vendor_order( $check, $subsection, $section, $atts ){
            if( ! empty( $_GET['product_orders'] ) && 'product_order' == $_GET['product_orders'] && ! empty( $_GET['id'] ) && $this->vendor->is_valid() ){
                $check = in_array( $_GET['id'], $this->vendor->get_orders() );
            }

            return $check;
        }

        /**
         * Suborders cols in orders list
         */
        public function show_suborders_in_list( $column, $order ){
            $suborder_ids = YITH_Orders::get_suborder ( $order->id );
            if ( $suborder_ids ) {
                foreach ( $suborder_ids as $suborder_id ) {
                    $suborder  = wc_get_order ( $suborder_id );
                    $vendor    = yith_get_vendor ( $suborder->post->post_author, 'user' );
                    $order_uri = esc_url ( 'post.php?post=' . absint ( $suborder_id ) . '&action=edit' );

                    printf ( '<mark class="%s tips" data-tip="%s">%s</mark> <strong><a href="%s">#%s</a></strong> <small class="yith-wcmv-suborder-owner">(%s %s)</small>',
                        sanitize_title ( $suborder->get_status () ),
                        wc_get_order_status_name ( $suborder->get_status () ),
                        wc_get_order_status_name ( $suborder->get_status () ),
                        $order_uri,
                        $suborder_id,
                        _x ( 'in', 'Order table details', 'yith-woocommerce-product-vendors' ),
                        $vendor->name
                    );

                    do_action ( 'yith_wcmv_after_suborder_details', $suborder );
                }
            } else {
                echo '<span class="na">&ndash;</span>';
            }
        }

        /**
         * Orders template args
         *
         * @param $args
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return array template args
         */
        public function orders_template_args( $args ){
            if( ! $this->current_user_is_vendor ){
                /* Add suborder col */
                $new_cols = array( 'suborder' => __( 'Suborders', 'yith-frontend-manager-for-woocommerce' ) );

                $cols = $args['columns'];
                $orders_col_pos = array_search( 'order', array_keys( $cols ) );
                $first = array_slice( $cols, 0, $orders_col_pos+1, true );
                $last = array_slice( $cols, $orders_col_pos + 1, count( $cols ), true );
                $cols = array_merge( $first, $new_cols, $last );

                $args['columns'] = $cols;

                /* Filter orders list */
                $args['query_args']['post_parent'] = 0;
            }

            else {
                $args['query_args']['post_status']  = array_keys( wc_get_order_statuses() );
                $args['query_args']['post__in']     = $this->vendor->get_orders( 'all' );
                $args['query_args']['fields']       = 'ids';
            }

            return $args;
        }

        /**
         * Retreive the vendor orders list
         *
         * @param $orders
         * @param $args
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return $orders order object array
         */
        public function vendors_orders_list( $orders, $args ){
            if( $this->current_user_is_vendor ){
                $order_ids = get_posts( $args );
                if( ! empty( $order_ids ) ){
                    $orders = array();
                    foreach( $order_ids as $order_id ){
                        $orders[] = wc_get_order( $order_id );
                    }
                }
            }
            return $orders;
        }

        /**
         * Hack edit suborder uri
         *
         * @param $uri
         * @param $order_id
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @return mixed
         */
        public function edit_order_uri( $uri, $order_id ){
            if( ! empty( YITH_Frontend_Manager()->gui ) ){
                $sections = YITH_Frontend_Manager()->gui->get_sections();
                $orders_section = ! empty( $sections['product_orders'] ) ? $sections['product_orders'] : null;

                if( $orders_section ){
                    $uri = $orders_section::get_edit_order_permalink( $order_id );
                }
            }

            return $uri;
        }

        /* === END ORDERS === */

        /* === START REPORTS === */

        /**
         * hide coupon usage report for vendors
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return array  report subsections
         */
        public function orders_reports_type( $types ){
            if( isset( $types['coupon_usage'] ) ){
                unset( $types['coupon_usage'] );
            }
            return $types;
        }

        /**
         * add reports management action
         *
         * @author Corrado Porzio <corrado.porzio@yithemes.com>
         * @since 1.0
         * @return mixed commissions report subsections
         */
        public function add_vendor_commissions_reports_subsections( $subsections, $obj ) {
            $new_subsection = array(
                'commissions-report' => array(
                    'slug' => $obj->get_option( 'slug', $obj->id . '_commissions-report', 'commissions-report' ),
                    'name' => __( 'Commissions', 'yith-frontend-manager-for-woocommerce' )
                ),
            );

            if( $this->current_user_is_vendor && ! is_admin() ){
                unset( $subsections['customers-report'] );
            }

            return array_merge( $subsections, $new_subsection );
        }

        /**
         * Add vendor reports for admin
         *
         * @param $subsections
         * @param $obj
         *
         * @return array subsections
         */
        public function add_vendor_reports_admin_subsections( $subsections, $obj ) {
            $new_subsection = array(
                'vendors' => array(
                    'slug' => $obj->get_option( 'vendors', $obj->id . '_vendors', 'vendors' ),
                    'name' => YITH_Vendors()->get_vendors_taxonomy_label( 'menu_name' )
                ),
            );

            if( ! $this->current_user_is_vendor && ! is_admin() ){
                unset( $subsections['commissions'] );
            }

            return array_merge( $subsections, $new_subsection );
        }

        /* === END REPORTS === */

        /**
         * Sanitize value for double checkbox option type
         *
         *
         * @author Andrea Grillo <andrea.grillo@yitheme.com>
         * @since 1.0
         *
         * @param $value
         * @param $option
         * @param $raw_value
         * @return string
         */
        public function admin_settings_sanitize_option( $value, $option, $raw_value ){
            if( 'yith-wcfm-double-checkbox' == $option['type'] ){
                $value = is_null( $raw_value ) ? 'no' : 'yes';
                $vendor_section_option = $option['id'] . '_vendor';
                $vendor_raw_value    = isset( $_POST[ $vendor_section_option ] ) ? wp_unslash( $_POST[ $vendor_section_option ] ) : null;
                $vendor_value = is_null( $vendor_raw_value ) ? 'no' : 'yes';
                update_option( $vendor_section_option, $vendor_value );
            }

            return $value;
        }

        /**
         * include admin class
         *
         * @author Andrea Grillo <andrea.grillo@yitheme.com>
         * @since 1.0
         * @return string classname
         */
        public function include_admin_class(){
            $classname = ' YITH_Vendors_Admin';

            if( ! class_exists( 'YITH_Vendors_Admin' ) ){
                $admin_class = YITH_WPV_PATH . 'includes/class.yith-vendors-admin.php';
                if( file_exists( $admin_class ) ){
                    require_once( $admin_class );
                }

                $admin_premium_class = YITH_WPV_PATH . 'includes/class.yith-vendors-admin-premium.php';
                if( file_exists( $admin_premium_class ) ){
                    require_once( $admin_premium_class );
                    $classname = 'YITH_Vendors_Admin_Premium';
                }
            }

            return $classname;
        }

        /**
         * Check if the current vendor show shipping tabs
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return void
         */
        public static function is_shipping_tab( $is_shipping_tab ){
            if( ! is_admin() && ! is_ajax() ){
                $is_shipping_tab = ! empty( $_GET['page'] ) && YITH_Vendors()->admin->vendor_panel_page == $_GET['page'] && ! empty( $_GET['tab'] ) && 'vendor-shipping' == $_GET['tab'];
            }

            return $is_shipping_tab;
        }
    }
}

/**
 * Main instance of plugin
 *
 * @return /YITH_Frontend_Manager_For_Vendor
 * @since  1.9
 * @author Andrea Grillo <andrea.grillo@yithemes.com>
 */
if ( ! function_exists( 'YITH_Frontend_Manager_For_Vendor' ) ) {
    function YITH_Frontend_Manager_For_Vendor() {
        return YITH_Frontend_Manager_For_Vendor::instance();
    }
}