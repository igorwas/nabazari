<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! function_exists( 'yith_wcfm_get_template' ) ) {
    /**
     * Get Plugin Template
     *
     * It's possible to overwrite the template from theme.
     * Put your custom template in woocommerce/product-vendors folder
     *
     * @param        $filename
     * @param array  $args
     * @param string $section
     *
     * @use   wc_get_template()
     * @since 1.0
     * @return void
     * @author Andrea Grillo <andrea.grillo@yithemes.com>
     */
    function yith_wcfm_get_template( $filename, $args = array(), $section = '' ) {

        $ext           = strpos( $filename, '.php' ) === false ? '.php' : '';
        $template_name = $section . '/' . $filename . $ext;
        $template_path = WC()->template_path();
        $default_path  = YITH_WCFM_TEMPLATE_PATH;

        if ( defined( 'YITH_WCFM_PREMIUM' ) ) {
            $premium_template = str_replace( '.php', '-premium.php', $template_name );
            $located_premium  = wc_locate_template( $premium_template, $template_path, $default_path );
            $template_name    = file_exists( $located_premium ) ? $premium_template : $template_name;
        }

        wc_get_template( $template_name, $args, $template_path, $default_path );
    }
}

if ( ! function_exists( 'yith_wcfm_setup' ) ) {
    /*
     * Create Frontend Manager page
     * Fire at register_activation_hook
     *
     * @return void
     * @since  1.0
     * @author Andrea Grillo <andrea.grillo@yithemes.com>
     */
    function yith_wcfm_setup() {
        if( ! get_option('yith_wcfm_db_version', false ) ) {
            $main_schortcode_name = yith_wcfm_get_main_shortcode_name();
            /* wc_create_page( $slug, $option, $page_title, $page_content, $post_parent ) */
            $page_id = wc_create_page( 'frontend-manager', 'yith_wcfm_main_page_id', __('Frontend Manager', 'yith-frontend-manager-for-woocommerce'), "[{$main_schortcode_name}]", 0 );
            update_option( 'yith_wcfm_default_main_page_id', $page_id );
            update_option( 'yith_wcfm_db_version', YITH_WCFM_DB_VERSION );
        }

        set_site_transient( yith_wcfm_get_rewrite_rules_transient(), true );
    }
}

if( ! function_exists( 'yith_wcfm_get_rewrite_rules_transient' ) ){
    /**
     * Get the transient name for refresh rewrite rules
     *
     * @author Andrea Grillo <andrea.grillo@yithemes.com>
     * @since 1.0
     * @return string transient name
     */
    function yith_wcfm_get_rewrite_rules_transient(){
        return 'yith_wcfm_refresh_rewrite_rules';
    }
}

if( ! function_exists( 'yith_wcfm_get_main_shortcode_name' ) ){
    /*
     * Get Main shortcode name
     *
     * @return string shortcode name
     * @since  1.0
     * @author Andrea Grillo <andrea.grillo@yithemes.com>
     */
    function yith_wcfm_get_main_shortcode_name(){
        return 'yith_woocommerce_frontend_manager';
    }
}

if( ! function_exists( 'yith_wcfm_get_section_enabled_id_from_object' ) ){
	/**
	 * Get the section_id or option_id for enabled section
	 *
	 * @param $type section_id or option_id
	 * @param $obj the object from class that you need to check if are enabled or not
	 *
	 * @return string the id
	 *
	 * @since  1.0
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
    function yith_wcfm_get_section_enabled_id_from_object( $type, $obj ){
        $class_name = is_object( $obj ) ? get_class( $obj ) : $obj;
        $class_name = str_ireplace( '_premium' , '',  $class_name );
        $section_id = strtolower( $class_name );

        if( 'option_id' == $type ){
            $section_id = "yith_wcfm_enable_{$section_id}";
        }

        return apply_filters( 'yith_wcfm_get_section_enabled_id_from_object', $section_id );
    }
}

if( ! function_exists( 'yith_wcfm_is_section_enabled' ) ){
	/**
	 * Check if a section are enabled or not from backend admin panel
	 *
	 * @param $obj YITH_Frontend_Manager_Section section
	 * @return bool true if enabled, false otherwise
	 *
	 * @since  1.0
	 * @author Andrea Grillo <andrea.grillo@yithemes.com>
	 */
	function yith_wcfm_is_section_enabled( $obj ){
		$is_enabled_option_id = yith_wcfm_get_section_enabled_id_from_object( 'option_id', $obj );
		$is_enabled = 'yes' == get_option( $is_enabled_option_id, 'yes' );
		return apply_filters( 'yith_wcfm_is_section_enabled', $is_enabled, $is_enabled_option_id, $obj->slug );
	}
}

/**
 * LOAD CUSTOM PAGE TEMPLATE WHEN IN DASHBOARD
 *
 * @param $page_template
 *
 * @return string
 */
if( ! function_exists( 'yith_wcfm_look_for_shortcode' ) ) {
	function yith_wcfm_look_for_shortcode( $page_template ) {
		if ( wc_post_content_has_shortcode( 'yith_woocommerce_frontend_manager' ) && is_user_logged_in() ) {
			$skin = get_option( 'yith_wcfm_skin', 'default' );
			if ( $skin != 'none' ) {
				$template_path = WC()->template_path();
				$skin_path     = YITH_WCFM_TEMPLATE_PATH . 'skins/' . $skin . '/';
				$page_template = wc_locate_template( 'main.php', $template_path, $skin_path );
			}
		}

		return $page_template;
	}
}

add_action( 'page_template', 'yith_wcfm_look_for_shortcode' );

/**
 * Enqueue skin specific style
 */
if( ! function_exists( 'yith_wcfm_include_skin_style_and_functions' ) ) {
	function yith_wcfm_include_skin_style_and_functions() {
		if ( wc_post_content_has_shortcode( 'yith_woocommerce_frontend_manager' ) ) {
			$skin = get_option( 'yith_wcfm_skin', 'default' );
			if ( $skin != 'none' ) {
				$skin_url = YITH_WCFM_TEMPLATE_URL . 'skins/' . $skin . '/';
				wp_enqueue_style( 'yith_wcfd-skin-style', $skin_url . 'assets/css/style.css', array(), YITH_WCFM_VERSION );

				$skin_path = YITH_WCFM_TEMPLATE_PATH . 'skins/' . $skin . '/';
				require_once( $skin_path . 'skin-functions.php' );
			}
		}
	}
}

add_action( 'wp_enqueue_scripts', 'yith_wcfm_include_skin_style_and_functions' );

/**
 * Frontend Manager Skins Widgets
 */
if( ! function_exists( 'yith_wcfm_skin_widgets_init' ) ) {
	function yith_wcfm_skin_widgets_init() {
		register_sidebar( array(
			'name'          => __( 'Frontend Manager - Header sidebar', 'yith-frontend-manager-for-woocommerce' ),
			'id'            => 'yith_wcfm_header_sidebar',
			'description'   => __( 'Widgets in this area will be shown in the header of your Frontend page.',
                'yith-frontend-manager-for-woocommerce' ),
			'before_widget' => '<div id="%1$s" class="widget yith_wcfm-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="yith_wcfm-widget-title">',
			'after_title'   => '</h2>',
		) );

		register_sidebar( array(
			'name'          => __( 'Frontend Manager - Footer sidebar', 'yith-frontend-manager-for-woocommerce' ),
			'id'            => 'yith_wcfm_footer_sidebar',
			'description'   => __( 'Widgets in this area will be shown in the footer of your Frontend page.',
                'yith-frontend-manager-for-woocommerce' ),
			'before_widget' => '<div id="%1$s" class="widget yith_wcfm-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="yith_wcfm-widget-title">',
			'after_title'   => '</h2>',
		) );
	}
}

add_action( 'widgets_init', 'yith_wcfm_skin_widgets_init' );

/**
 * Double checkboxes panel options
 */
if( ! function_exists( 'yith_wcfm_double_checkbox' ) ){
    function yith_wcfm_double_checkbox( $value ){
        $temp_value = $value;
        $temp_value['id'] = $temp_value['id'] . '_vendor';

        if ( ! isset( $value['type'] ) ) {
                return;
        }
        if ( ! isset( $value['id'] ) ) {
            $value['id'] = '';
        }
        if ( ! isset( $value['title'] ) ) {
            $value['title'] = isset( $value['name'] ) ? $value['name'] : '';
        }
        if ( ! isset( $value['class'] ) ) {
            $value['class'] = '';
        }
        if ( ! isset( $value['css'] ) ) {
            $value['css'] = '';
        }
        if ( ! isset( $value['default'] ) ) {
            $value['default'] = '';
        }
        if ( ! isset( $value['desc'] ) ) {
            $value['desc'] = '';
        }
        if ( ! isset( $value['desc_tip'] ) ) {
            $value['desc_tip'] = false;
        }
        if ( ! isset( $value['placeholder'] ) ) {
            $value['placeholder'] = '';
        }

        // Custom attribute handling
        $custom_attributes = array();

        if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
            foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
                $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
            }
        }

        // Description handling
        $field_description = WC_Admin_Settings::get_field_description( $value );
        extract( $field_description );

        $option_value           = WC_Admin_Settings::get_option( $value['id'], $value['default'] );
        $option_value_vendor    = WC_Admin_Settings::get_option( $value['id'] . '_vendor', $value['default'] );

        $visbility_class = array();

        if ( ! isset( $value['hide_if_checked'] ) ) {
            $value['hide_if_checked'] = false;
        }
        if ( ! isset( $value['show_if_checked'] ) ) {
            $value['show_if_checked'] = false;
        }
        if ( 'yes' == $value['hide_if_checked'] || 'yes' == $value['show_if_checked'] ) {
            $visbility_class[] = 'hidden_option';
        }
        if ( 'option' == $value['hide_if_checked'] ) {
            $visbility_class[] = 'hide_options_if_checked';
        }
        if ( 'option' == $value['show_if_checked'] ) {
            $visbility_class[] = 'show_options_if_checked';
        }

        ?>
        <thead>
            <tr>
                <th class="<?php echo $value['type']; ?>"></th>
                <th class="<?php echo $value['type']; ?>">
                    <label for="<?php echo esc_attr( $value['id'] ); ?>">
                        <?php _e( 'Shop Manager', 'yith-frontend-manager-for-woocommerce' ); ?>
                    </label>
                </th>
                <th class="<?php echo $value['type']; ?>">
                    <label for="<?php echo esc_attr( $value['id'] . '_vendor' ); ?>">
                        <?php _e( 'Vendor', 'yith-frontend-manager-for-woocommerce' );; ?>
                    </label>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr valign="top" class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
                <th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?></th>
                <td class="forminp forminp-checkbox <?php echo $value['type']; ?>">
                    <fieldset>
                        <?php if ( ! empty( $value['title'] ) ) :?>
                            <legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ) ?></span></legend>
                        <?php endif;?>
                            <input
                                name="<?php echo esc_attr( $value['id'] ); ?>"
                                id="<?php echo esc_attr( $value['id'] ); ?>"
                                type="checkbox"
                                class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
                                value="1"
                                <?php checked( $option_value, 'yes'); ?>
                                <?php echo implode( ' ', $custom_attributes ); ?>
                            /> <?php echo $description ?>
                        <?php echo $tooltip_html; ?>
                    </fieldset>
                </td>
                <td class="forminp forminp-checkbox <?php echo $value['type']; ?>">
                    <fieldset>
                        <?php if ( ! empty( $value['title'] ) ) :?>
                            <legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ) ?></span></legend>
                        <?php endif;?>
                        <input
                                name="<?php echo esc_attr( $value['id'] . '_vendor' ); ?>"
                                id="<?php echo esc_attr( $value['id'] . '_vendor' ); ?>"
                                type="checkbox"
                                class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] . '_vendor' : '' ); ?>"
                                value="1"
                            <?php checked( $option_value_vendor, 'yes'); ?>
                            <?php echo implode( ' ', $custom_attributes ); ?>
                        /> <?php echo $description ?>
                        <?php echo $tooltip_html; ?>
                    </fieldset>
                </td>
            </tr>
        </tbody>
    <?php

    }
}

if( ! function_exists( 'yith_wcfm_get_main_page_url' ) ){
    /**
     * Get the frontend manager main page url
     *
     * @return string main page url
     *
     * @since  1.0
     * @author Andrea Grillo <andrea.grillo@yithemes.com>
     */
    function yith_wcfm_get_main_page_url(){
        $default_main_page_id = get_option( 'yith_wcfm_default_main_page_id' );
        $main_page_id         = get_option( 'yith_wcfm_main_page_id' );

        return ! empty( $main_page_id ) ? get_page_link( $main_page_id ) : get_page_link( $default_main_page_id );
    }
}

if( ! function_exists( 'yith_wcfm_get_section_url' ) ){
    /**
     * Get the current endpoint section url
     *
     * @return string current endpoint section url
     *
     * @since  1.0
     * @author Andrea Grillo <andrea.grillo@yithemes.com>
     */
    function yith_wcfm_get_section_url( $section = 'current', $subsection = '' ){
        $url = '';

        $gui = YITH_Frontend_Manager()->gui;

        if( $gui ){
            $section_obj = 'current' == $section ? $gui->get_current_section_obj() : $gui->get_section( $section );

            if( is_object( $section_obj ) && is_callable( array( $section_obj, 'get_url' ) ) ){
                $url = $section_obj->get_url( $subsection );
            }
        }

        return ! empty( $url ) ? $url : get_permalink();
    }
}

if( ! function_exists( 'yith_wcfm_months_dropdown' ) ){

    /**
     * Display a monthly dropdown for filtering items
     *
     * @since 3.1.0
     * @access protected
     *
     * @global wpdb      $wpdb
     * @global WP_Locale $wp_locale
     *
     * @param string $post_type
     */
    function yith_wcfm_months_dropdown( $post_type ) {
        global $wpdb, $wp_locale;

        /**
         * Filters whether to remove the 'Months' drop-down from the post list table.
         *
         * @since 4.2.0
         *
         * @param bool   $disable   Whether to disable the drop-down. Default false.
         * @param string $post_type The post type.
         */
        if ( apply_filters( 'disable_months_dropdown', false, $post_type ) ) {
            return;
        }

        $extra_checks = "AND post_status != 'auto-draft'";
        if ( ! isset( $_GET['post_status'] ) || 'trash' !== $_GET['post_status'] ) {
            $extra_checks .= " AND post_status != 'trash'";
        } elseif ( isset( $_GET['post_status'] ) ) {
            $extra_checks = $wpdb->prepare( ' AND post_status = %s', $_GET['post_status'] );
        }

        $months = $wpdb->get_results( $wpdb->prepare( "
			SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
			FROM $wpdb->posts
			WHERE post_type = %s
			$extra_checks
			ORDER BY post_date DESC
		", $post_type ) );

        /**
         * Filters the 'Months' drop-down results.
         *
         * @since 3.7.0
         *
         * @param object $months    The months drop-down query results.
         * @param string $post_type The post type.
         */
        $months = apply_filters( 'months_dropdown_results', $months, $post_type );

        $month_count = count( $months );

        if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
            return;

        $m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;
        ?>
        <label for="filter-by-date" class="screen-reader-text"><?php _e( 'Filter by date' ); ?></label>
        <select name="m" id="filter-by-date">
            <option<?php selected( $m, 0 ); ?> value="0"><?php _e( 'All dates' ); ?></option>
            <?php
            foreach ( $months as $arc_row ) {
                if ( 0 == $arc_row->year )
                    continue;

                $month = zeroise( $arc_row->month, 2 );
                $year = $arc_row->year;

                printf( "<option %s value='%s'>%s</option>\n",
                    selected( $m, $year . $month, false ),
                    esc_attr( $arc_row->year . $month ),
                    /* translators: 1: month name, 2: 4-digit year */
                    sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
                );
            }
            ?>
        </select>
        <?php
    }
}

if( ! function_exists( 'yith_wcfm_add_confirm_script_on_delete' ) ){
    /**
     * Add a confirmation box if I can try to delete an item
     *
     * @return void
     *
     * @since  1.0
     * @author Andrea Grillo <andrea.grillo@yithemes.com>
     */
    function yith_wcfm_add_confirm_script_on_delete(){
        $message = apply_filters( 'yith_wcfm_delete_script_message', __( "You are about to delete this item. Click OK to delete the item or CANCEL to stop.", 'yith-frontend-manager-for-woocommerce' ) );
        $js = "$('body').on( 'click', 'a.yith-wcfm-delete', function(e){
             if( ! confirm( '{$message}' ) ){
                e.preventDefault();
             }   
        });";
        wc_enqueue_js( $js );
    }
}

if( ! function_exists( 'yith_wcfm_add_inline_action' ) ){
    /**
     * Add inline action
     *
     * @return void
     *
     * @since  1.0
     * @author Andrea Grillo <andrea.grillo@yithemes.com>
     */
    function yith_wcfm_add_inline_action( $atts ){
        yith_wcfm_get_template( 'inline-actions', $atts, 'sections' );
    }
}

if( ! function_exists( 'yith_wcfm_commission_url' ) ){
    /**
     * Get commission url for frontend manager
     *
     * @author Andrea Grillo <andrea.grillo@yithemes.com>
     * @since 1.0
     * @return string url
     */
    function yith_wcfm_commission_url ( $url, $rec ) {
        $url = add_query_arg( array( 'id' => $rec->id, ), yith_wcfm_get_section_url( 'commissions' ) );
        return $url;
    }
}

if( ! function_exists( 'yith_wcfm_order_url' ) ) {
    /**
     * Get order url for frontend manager
     *
     * @author Andrea Grillo <andrea.grillo@yithemes.com>
     * @since 1.0
     * @return string url
     */
    function yith_wcfm_order_url ( $url, $rec, $order ) {
        $url = add_query_arg( array( 'id' => yit_get_prop( $order, 'id') ), yith_wcfm_get_section_url( 'product_orders', 'product_order' ) );
        return $url;
    }
}

if( ! function_exists( 'yith_wcfm_product_url' ) ) {
    /**
     * Get product url for frontend manager
     *
     * @author Andrea Grillo <andrea.grillo@yithemes.com>
     * @since 1.0
     * @return string url
     */
    function yith_wcfm_product_url ( $url, $product, $rec ) {
        $url = add_query_arg( array( 'product_id' => yit_get_base_product_id( $product ) ), yith_wcfm_get_section_url( 'products', 'product' ) );
        return $url;
    }
}
