<?php
/**
 * Handling checkout validation
 *
 * @class   WC_PV_Checkout
 * @package Woo Phone Validator/Classes
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * For handling the checkout validation
 */
class WC_PV_Checkout {

	/**
	 * The single instance of the class.
	 *
	 * @var WC_PV_Checkout
	 *
	 * @since 2.0.0
	 */
	protected static $instance = null;

	/**
	 * Main class instance. Ensures only one instance of class is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_PV_Checkout
	 * @since  2.0.0
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 2.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning this object is forbidden. 🤡', 'woo-phone-validator' ), '2.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden. 🤡', 'woo-phone-validator' ), '2.0.0' );
	}

	/**
	 * Construcdur :).
	 */
	public function __construct() {
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'checkout_validate' ), 10, 2 );
	}

	/**
	 * For phone validation.
	 *
	 * @param array $fileds | the external data
	 * @hook woocommerce_after_checkout_validation
	 */
	public function checkout_validate( $fields, $error ) {
		/**
		 * Filters the disable checkout billing/shipping validation.
		 *
		 * @param int    $user_id
		 * @param string $load_address
		 */
		$disabled_validation = apply_filters( 'wc_pv_disable_checkout_' . $load_address . '_validation', get_option( $wc_pv_woo_option_meta['disable_checkout_' . $load_address . '_validation'], false ),  $user_id, $load_address );

		if ( ! $disabled_validation ) {
			WC_PV_Helper::{ $load_address . "_phone_validation" }( $fields, $error );
		}

		WC_PV_Helper::billing_phone_validation();
	}
}

WC_PV_Checkout::instance();
