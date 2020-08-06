<?php
/**
 * For handling the fields on edit address page
 */
class WC_PV_Account {

	/**
	 * Construcdur :)
	 */
	public function __construct() {
		// inherits style and js from the checkout class :)
		add_action( 'woocommerce_after_save_address_validation', array( $this, 'account_page_validate' ), 10, 2 );
	}

	/**
	 * For extra custom validation
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
