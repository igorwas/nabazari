<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'YITH_WC_Email_New_Order' ) ) :

/**
 * New Order Email
 *
 * An email sent to the admin when a new order is received/paid for.
 *
 * @class 		YITH_WC_Email_New_Order
 * @version		2.0.0
 * @package		WooCommerce/Classes/Emails
 * @author 		WooThemes
 * @extends 	WC_Email
 *
 * @property YITH_Commission $object
 */
class YITH_WC_Email_New_Order extends WC_Email {

    /**
     * Order number
     */
    public $order_number = '';

    /**
     * Construct
     */
    function __construct() {
        $this->id 				= 'new_order_to_vendor';
		$this->title 			= __( 'New order (to vendor)', 'yith-woocommerce-product-vendors' );
		$this->description		= __( 'New order emails are sent to vendor(s) when an order is received.', 'yith-woocommerce-product-vendors' );

		$this->heading 			= __( 'New customer order', 'woocommerce' );
		$this->subject      	= __( '[{site_title}] New customer order ({order_number}) - {order_date}', 'woocommerce' );

		$this->template_html 	= 'emails/vendor-new-order.php';
		$this->template_plain 	= 'emails/plain/vendor-new-order.php';

		$this->recipient 		= YITH_Vendors()->get_vendors_taxonomy_label( 'singular_name' );

		// Triggers for this email
		add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'trigger' ) );
        add_action( 'woocommerce_order_status_pending_to_completed_notification', array( $this, 'trigger' ) );
        add_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $this, 'trigger' ) );
        add_action( 'woocommerce_order_status_failed_to_processing_notification', array( $this, 'trigger' ) );
        add_action( 'woocommerce_order_status_failed_to_completed_notification', array( $this, 'trigger' ) );
        add_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $this, 'trigger' ) );

	    //Force to send email if no Syn option enabled
	    add_action( 'yith_wcmv_new_order_email', array( $this, 'trigger' ) );

        // Call parent constructor
		parent::__construct();

        $this->vendor       = null;
	}

    /**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 */
	function trigger( $order_id ) {

		if ( ! $this->is_enabled() || empty( $order_id ) || ! wp_get_post_parent_id( $order_id ) ) {
            return false;
        }

        $this->object = wc_get_order( $order_id );

        $this->vendor = yith_get_vendor( get_post_field( 'post_author', yit_get_prop( $this->object, 'id' ) ), 'user' );

        if( ! $this->vendor->is_valid() ){
            return false;
        }

        $this->order_number = yith_wcmv_get_email_order_number( $this->object, 'yes' == $this->get_option('show_parent_order_id', 'no') );

        $this->find['order-date']   = '{order_date}';
        $this->find['order-number'] = '{order_number}';

        $this->replace['order-date']   = date_i18n( wc_date_format(), strtotime( yit_get_prop( $this->object, 'order_date' ) ) );
		$this->replace['order-number'] = $this->object->get_order_number();

        $vendor_email = $this->vendor->store_email;

        if( empty( $vendor_email ) ){
            $vendor_owner = get_user_by( 'id', absint( $this->vendor->get_owner() ) );
            $vendor_email = $vendor_owner instanceof WP_User ? $vendor_owner->user_email : false;
        }

        // Send Email to Vendor
        $this->send( $vendor_email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

    /**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_html() {
		ob_start();
		yith_wcpv_get_template( $this->template_html, array(
			'order' 		=> $this->object,
            'order_number'  => $this->order_number,
            'vendor'        => $this->vendor,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => false,
            'yith_wc_email' => $this
		), '' );
		return ob_get_clean();
	}

	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain() {
		ob_start();
		yith_wcpv_get_template( $this->template_plain, array(
			'order' 		=> $this->object,
            'order_number'  => $this->order_number,
            'vendor'        => $this->vendor,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => false,
            'yith_wc_email' => $this
		), '' );
		return ob_get_clean();
	}

	/**
	 * Initialise Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
				'type' 			=> 'checkbox',
				'label' 		=> __( 'Enable this email notification', 'woocommerce' ),
				'default' 		=> 'yes'
			),
			'subject' => array(
				'title' 		=> __( 'Subject', 'woocommerce' ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'woocommerce' ), $this->subject ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'heading' => array(
				'title' 		=> __( 'Email Heading', 'woocommerce' ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'woocommerce' ), $this->heading ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'email_type' => array(
				'title' 		=> __( 'Email type', 'woocommerce' ),
				'type' 			=> 'select',
				'description' 	=> __( 'Choose email format.', 'woocommerce' ),
				'default' 		=> 'html',
				'class'			=> 'email_type wc-enhanced-select',
				'options'		=> $this->get_email_type_options()
			),
            'show_parent_order_id' => array(
                'title'         => __( 'Order id', 'woocommerce' ),
                'type'          => 'checkbox',
                'label'         => __( 'Show parent order id instead of vendor suborder id', 'woocommerce' ),
                'default'       => 'no'
            ),
		);
	}
}

endif;

return new YITH_WC_Email_New_Order();
