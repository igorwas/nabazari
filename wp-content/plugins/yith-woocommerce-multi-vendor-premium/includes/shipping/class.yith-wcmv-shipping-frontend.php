<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'YITH_WPV_VERSION' ) ) {
    exit( 'Direct access forbidden.' );
}

/**
 *
 *
 * @class      YITH_Vendor_Shipping_Frontend
 * @package    Yithemes
 * @since      Version 1.9.17
 * @author     Your Inspiration Themes
 *
 */

if ( ! class_exists( 'YITH_Vendor_Shipping_Frontend' ) ) {

    /**
     * Class YITH_Vendors_Shipping_Frontend
     *
     * @author Andrea Frascaspata <andrea.frascaspata@yithemes.com>
     */
    class YITH_Vendor_Shipping_Frontend  {

        private $vendor_cart_elements = array();

        /**
         * Constructor
         *
         * @author Andrea Frascaspata <andrea.frascaspata@yithemes.com>
         */
        public function __construct() {

            add_filter( 'woocommerce_cart_shipping_packages' , array( $this , 'woocommerce_cart_shipping_packages' ) );

            add_filter( 'woocommerce_shipping_packages', array( $this, 'woocommerce_shipping_packages' ) );

            add_filter( 'woocommerce_shipping_package_name' , array( $this , 'woocommerce_shipping_package_name' ) , 10 , 3 );

            add_filter( 'woocommerce_package_rates' , array( $this , 'woocommerce_package_rates' ) , 10 , 2 );

            //Register shipping fee commissions
            add_action( 'yith_wcmv_checkout_order_processed', array( $this, 'register_commissions' ), 15, 1 );
        }

        /**
         * Register the commission linked to order
         *
         * @param $order_id int The order ID
         * @param $posted   array The value request
         *
         * @since 1.0
         */
        public function register_commissions( $order_id ) {
            // Only process commissions once
            $processed = get_post_meta( $order_id, '_shipping_commissions_processed', true );
            if ( $processed && $processed == 'yes' ) {
                return;
            }

            $order = wc_get_order( $order_id );
            $shipping_methods = $order->get_shipping_methods();
            $vendor_owner = get_post_field( 'post_author', $order_id );
            $vendor = yith_get_vendor( $vendor_owner, 'user' );

            if( ! empty( $shipping_methods ) ){
                foreach( $shipping_methods as $shipping_id => $shipping ){
                    /** @var WC_Order_Item_Shipping $shipping */
                    $args = array(
                        'line_item_id'  => $shipping_id,
                        'order_id'      => $order_id,
                        'user_id'       => $vendor_owner,
                        'vendor_id'     => $vendor->id,
                        'amount'        => $shipping->get_total('edit'),
                        'last_edit'     => current_time( 'mysql' ),
                        'last_edit_gmt' => current_time( 'mysql', 1 ),
                        'rate'          => 1,
                        'type'          => 'shipping'
                    );

                    $commission_id = YITH_Commission()->add( $args );
                }
            }

            // Mark shipping fee as processed
            update_post_meta( $order_id, '_shipping_commissions_processed', 'yes' );
        }

        /**
         * @param $packages
         * @return array
         */
        public function woocommerce_shipping_packages( $packages ){
            foreach( $packages as $key => $package ){
                if( empty( $package['contents'] ) ){
                    unset( $packages[ $key ] );
                }
            }

            return $packages;
        }

        /**
         * @param $packages
         * @return array
         */
        public function woocommerce_cart_shipping_packages( $packages ) {

            $wc_cart = WC()->cart;

            $this->vendor_cart_elements = array();

            $vendors = $this->get_vendors_in_cart( $wc_cart->get_cart() );

            if( count( $vendors ) > 0 ) {
                $destination_country = strtoupper( WC()->customer->get_shipping_country() );
                $destination_continent = strtoupper( wc_clean( WC()->countries->get_continent_code_for_country( $destination_country ) ) );
                $destination_state = strtoupper( wc_clean(  WC()->customer->get_shipping_state() ) );
                $destination_postcode = strtoupper( wc_clean( WC()->customer->get_shipping_postcode() ) );

                foreach ( $vendors as $vendor ) {
                    if( YITH_Vendor_Shipping::is_single_vendor_shipping_enabled( $vendor ) ) {
                        $zone_key = $this->get_matched_zone( $vendor , $destination_country , $destination_continent , $destination_state , $destination_postcode );

                        if( $zone_key != '' ) {
                            $packages[] = $package = $this->get_package( $wc_cart , $vendor , $zone_key );
                        }
                    }
                }

                // Remove vendor products from WooCommerce shipping packages
                foreach ( $packages as &$package ){
                    if( ! isset(  $package['yith-vendor'] ) ) {

                        if( count( $this->vendor_cart_elements ) > 0 ) {
                            // remove elements
                            foreach ( $this->vendor_cart_elements as $product_vendor_cart_key ) {
                                unset( $package['contents'][$product_vendor_cart_key] );
                            }

                            // recalculate contents_cost
                            foreach ( $package['contents'] as $item ) {
                                if ( $item['data']->needs_shipping() ) {
                                    if ( isset( $item['line_total'] ) ) {
                                        $package['contents_cost'] += $item['line_total'];
                                    }
                                }
                            }

                        }
                    }
                }
            }

            return $packages;

        }

        /**
         * @param $wc_cart
         * @param $vendor
         * @param $zone_key
         * @return array
         */
        private function get_package( $wc_cart , $vendor , $zone_key ) {

            $package = array();

            $cart_elements = $wc_cart->get_cart();

            $package['contents']                 = $this->get_vendors_cart_contens( $vendor , $cart_elements) ;		// Items in the package
            $package['contents_cost']            = 0;					// Cost of items in the package, set below
            $package['applied_coupons']          = array();
            $package['user']['ID']               = get_current_user_id();
            $package['destination']['country']   = WC()->customer->get_shipping_country();
            $package['destination']['state']     = WC()->customer->get_shipping_state();
            $package['destination']['postcode']  = WC()->customer->get_shipping_postcode();
            $package['destination']['city']      = WC()->customer->get_shipping_city();
            $package['destination']['address']   = WC()->customer->get_shipping_address();
            $package['destination']['address_2'] = WC()->customer->get_shipping_address_2();
            $package['yith-vendor'] = $vendor;
            $package['yith-vendor-shipping-zone-key'] = $zone_key;

            foreach ( $package['contents'] as $item ) {
                if ( $item['data']->needs_shipping() ) {
                    if ( isset( $item['line_total'] ) ) {
                        $package['contents_cost'] += $item['line_total'];
                    }
                }
            }

            return $package;

        }

        /**
         * @param $cart_contents
         * @return array
         */
        private function get_vendors_in_cart( $cart_contents ) {

            $vendors = array();

            foreach( $cart_contents as $cart_item ) {

                if( isset( $cart_item['data'] ) ) {

                    $vendor = yith_get_vendor( yit_get_base_product_id( $cart_item['data'] ), 'product' );

                    if ($vendor->is_valid() && YITH_Vendor_Shipping::is_single_vendor_shipping_enabled( $vendor ) && ( ! in_array( $vendor , $vendors )  ) ) {

                        $vendors[] = $vendor;

                    }
                }

            }

            return $vendors;

        }

        /**
         * @param $vendor
         * @param $cart_contents
         * @return array
         */
        private function get_vendors_cart_contens( $vendor , $cart_contents ) {

            $cart_elements = array();

            foreach( $cart_contents as $key => $cart_item ) {

                if( isset( $cart_item['data'] ) ) {

                    $product = $cart_item['data'];

                    if( ! $product->is_virtual() && ! $product->is_downloadable() ) {

                        $product_id = wp_get_post_parent_id( $product->get_id() ) ? wp_get_post_parent_id( $product->get_id() ) : $product->get_id();

                        $current_vendor = yith_get_vendor( $product_id , 'product' );

                        if ( $current_vendor->id == $vendor->id  && YITH_Vendor_Shipping::is_single_vendor_shipping_enabled( $vendor )  ) {

                            $cart_elements[ $key ] = $cart_item;

                            $this->vendor_cart_elements[] = $key;
                        }

                    }

                }

            }

            return $cart_elements;

        }

        /**
         * @param $vendor
         * @param $destination_country
         * @param $destination_continent
         * @param $destination_state
         * @param $destination_postcode
         * @return bool|int|string
         */
        private function get_matched_zone( $vendor , $destination_country , $destination_continent , $destination_state , $destination_postcode ) {

            $search_destination_continent = 'continent:'.$destination_continent;
            $search_destination_country = 'country:'.$destination_country;
            $search_destination_state = 'state:'.$destination_country.':'.$destination_state;

            $zone_data = maybe_unserialize( $vendor->zone_data );

            if( is_array( $zone_data ) ) {

                foreach ( $zone_data as $key => $zone ) {

                    if( isset( $zone['zone_regions'] ) ) {

                        $zone_regions = $zone['zone_regions'];

                        foreach( $zone_regions as $region ) {

                            $is_macthed = ( $region == $search_destination_continent ) || ( $region == $search_destination_country ) || ( $region == $search_destination_state );

                            if( $is_macthed ) {

                                return $key;

                            }

                        }

                    }

                }

            }

            return '';

        }

        /**
         * @param $title
         * @param $i
         * @param $package
         * @return mixed
         */
        public function woocommerce_shipping_package_name( $title , $i , $package ) {

            if( isset( $package['yith-vendor-shipping-zone-key'] ) ) {

                $title = apply_filters ( 'yith_vendor_package_name' , $package['yith-vendor']->name , $package['yith-vendor'] , $i  );

            }

            return $title;
        }

        /**
         * @param $rates
         * @param $package
         * @return array
         */
        public function woocommerce_package_rates( $rates , $package ) {
            error_log( print_r( $rates, true ) );
            if( isset( $package['yith-vendor-shipping-zone-key'] ) ) {

                $key = $package['yith-vendor-shipping-zone-key'];

                if( $key && isset( $package['yith-vendor'] ) ) {

                    $vendor = $package['yith-vendor'];

                    if( YITH_Vendor_Shipping::is_single_vendor_shipping_enabled( $vendor ) ) {

                        $is_counpon_free_shipping = $this->is_coupon_free_shipping( $vendor ) ;

                        $rates = array();

                        $zone_data = maybe_unserialize( $vendor->zone_data );

                        if( is_array( $zone_data ) && isset( $zone_data[ $key ] ) ) {

                            $zone =  $zone_data[ $key ];

                            if ( isset( $zone['zone_shipping_methods'] ) ) {

                                $zone_shipping_methods = $zone['zone_shipping_methods'];

                                foreach( $zone_shipping_methods as $key => $shipping_method ) {
                                    $this->addShippingRate( $package , $vendor , $key , $is_counpon_free_shipping, $shipping_method , $rates );
                                }
                            }
                        }
                    }
                }
            }
            error_log( print_r( $rates, true ) );
            return $rates;
        }

        /**
         * @param $vendor
         * @param $package
         * @param $is_counpon_free_shipping
         * @param $shipping_method
         * @param $rates
         */
        private function addShippingRate( $package , $vendor , $key , $is_counpon_free_shipping , $shipping_method , &$rates ) {

            $is_free_shipping = $shipping_method['type_id'] == 'free_shipping';
            $total_cost = wc_format_decimal( $shipping_method['method_cost'], wc_get_price_decimals() );

            if( ! $is_free_shipping ) {
                $total_cost += $this->get_extra_cost( $vendor , $package );
            }

            $tax_rate = ( $shipping_method['method_tax_status'] == 'none' ) ? false : '';

            $enable_current_rate = $this->is_enable_rate( $package , $is_free_shipping , $shipping_method , $is_counpon_free_shipping );

            $rate = null;

            if( $enable_current_rate ) {
                // Create rate object
                $rate = new WC_Shipping_Rate( $shipping_method[ 'type_id' ].'_'.$key, $shipping_method['method_title'], $total_cost, '', $shipping_method['type_id']);
                $shipping_object_id = $shipping_method[ 'type_id' ].'_'.$key;
                $rates[ $shipping_object_id ] = $rate;

                if ( ! empty( $rate ) && ! is_array( $tax_rate ) && $tax_rate !== false && $total_cost > 0 ) {
                    //$taxes = $this->get_taxes_per_item( $total_cost );
                    $rates[ $shipping_object_id ]->taxes = WC_Tax::calc_shipping_tax( $total_cost, WC_Tax::get_shipping_tax_rates() );
                }
            }
        }

        /**
         * @param $vendor
         * @param $package
         * @return string
         */
        private function get_extra_cost( $vendor , $package ) {

            $products = $package['contents'];

            $shipping_default_price = wc_format_decimal( $vendor->shipping_default_price, wc_get_price_decimals() );

            //@TODO: Override in single product: shipping_product_additional_price and shipping_product_qty_price

            $shipping_product_additional_price = wc_format_decimal( $vendor->shipping_product_additional_price, wc_get_price_decimals() ) * ( count( $products ) - 1 );

            $shipping_product_qty_price_total_amount = 0;
            $shipping_product_qty_price = wc_format_decimal( $vendor->shipping_product_qty_price, wc_get_price_decimals() );

            foreach ( $products as $product ) {
                $shipping_product_qty_price_total_amount  += ( ( $product['quantity'] - 1 ) * $shipping_product_qty_price );
            }

            return $shipping_default_price + $shipping_product_additional_price + $shipping_product_qty_price_total_amount;

        }


        private function is_enable_rate( $package , $is_free_shipping , $shipping_method , $is_counpon_free_shipping ) {

            $enable_current_rate = true;

            // check free shipping enabled
            if( $is_free_shipping ) {

                $min_amount = wc_format_decimal( $shipping_method['min_amount'], wc_get_price_decimals() );

                $package_cost = $package['contents_cost'];

                $is_total_amount_enough = $package_cost > $min_amount;

                if( $shipping_method['method_requires'] == 'coupon' && ! $is_counpon_free_shipping ) {
                    $enable_current_rate = false;
                }
                else if( $shipping_method['method_requires'] == 'min_amount' && ! $is_total_amount_enough ) {
                    $enable_current_rate = false;
                }
                else if( $shipping_method['method_requires'] == 'either' && ( ! $is_counpon_free_shipping && ! $is_total_amount_enough ) ) {
                    $enable_current_rate = false;
                }
                else if( $shipping_method['method_requires'] == 'both' && ( ! ( $is_counpon_free_shipping && $is_total_amount_enough ) ) ) {
                    $enable_current_rate = false;
                }

            }

            return $enable_current_rate;

        }
        /**
         * @param $costs
         * @return array
         */
        protected function get_taxes_per_item( $costs ) {
            $taxes = array();

            // If we have an array of costs we can look up each items tax class and add tax accordingly
            if ( is_array( $costs ) ) {

                $cart = WC()->cart->get_cart();

                foreach ( $costs as $cost_key => $amount ) {
                    if ( ! isset( $cart[ $cost_key ] ) ) {
                        continue;
                    }

                    $item_taxes = WC_Tax::calc_shipping_tax( $amount, WC_Tax::get_shipping_tax_rates( $cart[ $cost_key ]['data']->get_tax_class() ) );

                    // Sum the item taxes
                    foreach ( array_keys( $taxes + $item_taxes ) as $key ) {
                        $taxes[ $key ] = ( isset( $item_taxes[ $key ] ) ? $item_taxes[ $key ] : 0 ) + ( isset( $taxes[ $key ] ) ? $taxes[ $key ] : 0 );
                    }
                }

                // Add any cost for the order - order costs are in the key 'order'
                if ( isset( $costs['order'] ) ) {
                    $item_taxes = WC_Tax::calc_shipping_tax( $costs['order'], WC_Tax::get_shipping_tax_rates() );

                    // Sum the item taxes
                    foreach ( array_keys( $taxes + $item_taxes ) as $key ) {
                        $taxes[ $key ] = ( isset( $item_taxes[ $key ] ) ? $item_taxes[ $key ] : 0 ) + ( isset( $taxes[ $key ] ) ? $taxes[ $key ] : 0 );
                    }
                }
            }

            return $taxes;
        }

        /**
         * @param $vendor
         * @return bool
         */
        private function is_coupon_free_shipping( $vendor ) {

            foreach ( WC()->cart->applied_coupons as $code ) {

                $coupon = new WC_Coupon( $code );

                if ( $coupon->is_valid() ) {

                    $coupon_author = get_post_field( 'post_author', yit_get_prop( $coupon, 'id' ) );

                    if( in_array( $coupon_author, $vendor->get_admins() ) && $coupon->enable_free_shipping() ) {
                        return true;
                    }
                }

            }

            return false;

        }

  }
}

