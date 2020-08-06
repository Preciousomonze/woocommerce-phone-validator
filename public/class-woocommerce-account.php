<?php
/**
 * For handling the fields on edit address page
 *
 * @class   WC_PV_Account
 * @package Woo Phone Validator/Classes
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * For handling the fields on edit address page
 */
class WC_PV_Account {

	/**
	 * The single instance of the class.
	 *
	 * @var WC_PV_Account
	 *
	 * @since 2.0.0
	 */
	protected static $instance = null;

	/**
	 * Main class instance. Ensures only one instance of class is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_PV_Account
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
	 * Construcdur :)
	 */
	public function __construct() {
		add_action( 'woocommerce_after_save_address_validation', array( $this, 'account_page_validate' ), 10, 2 );
	}

	/**
	 * For Phone validation
	 *
	 * @param int    $user_id User ID being saved.
	 * @param string $load_address Type of address e.g. billing or shipping.
	 * @hook woocommerce_after_save_address_validation
	 */
	public function account_page_validate( $user_id, $load_address ) {
		wc_pv()->billing_phone_validation();
	}
}
new WC_PV_Account();
