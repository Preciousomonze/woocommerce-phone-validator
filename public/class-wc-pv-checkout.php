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
		add_action( 'woocommerce_after_checkout_validation', array( __CLASS__, 'checkout_validate' ), 10, 2 );
	}

	/**
	 * For phone validation.
	 *
	 * @param array $fileds | the external data
	 * @hook woocommerce_after_checkout_validation
	 */
	public static function checkout_validate( $fields, $error ) {
		global $wc_pv_woo_option_meta;
		
		/**
		 * Filters the disable checkout billing validation.
		 *
		 * @param int    $fields
		 * @param string $error
		 */
		$disabled_billing_validation = apply_filters( 'wc_pv_disable_checkout_billing_validation', get_option( $wc_pv_woo_option_meta['disable_checkout_billing_validation'], false ),  $fields, $error );

		/**
		 * Filters the disable checkout shipping validation.
		 *
		 * @param int    $fields
		 * @param string $error
		 */
		$disabled_shipping_validation = apply_filters( 'wc_pv_disable_checkout_shipping_validation', get_option( $wc_pv_woo_option_meta['disable_checkout_shipping_validation'], false ),  $fields, $error );

		if ( ! $disabled_billing_validation ) {
			WC_PV_Engine::billing_phone_validation( 'checkout', $fields, $error );
		}

		if ( ! $disabled_shipping_validation ) {
			WC_PV_Engine::shipping_phone_validation( 'checkout', $fields, $error );
		}
	}
}

WC_PV_Checkout::instance();
