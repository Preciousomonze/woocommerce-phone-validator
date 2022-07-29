<?php
/**
 * Phone Validator for WooCommerce
 *
 * @package PluginPackage
 * @author Precious Omonzejele (CodeXplorer 🤾🏽‍♂️🥞🦜🤡)
 *
 * @wordpress-plugin
 * Plugin Name: Phone Validator for WooCommerce
 * Plugin URI: https://github.com/Preciousomonze/woo-phone-validator
 * Description: Phone Validator for WooCommerce Helps in validating international telephone numbers on WooCommerc billing address.
 * Author: Precious Omonzejele (CodeXplorer 🤾🏽‍♂️🥞🦜🤡)
 * Author URI: https://codexplorer.ninja
 * Version: 2.0.0
 * Requires at least: 5.0
 * Tested up to: 5.4
 * WC requires at least: 3.0
 * WC tested up to: 4.3
 *
 * Text Domain: woo-phone-validator
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

// Make sure you update the version values when necessary.
define( 'WC_PV_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WC_PV_PLUGIN_FILE', __FILE__ );
define( 'WC_PV_TEXT_DOMAIN', 'woo-phone-validator' );
define( 'WC_PV_PLUGIN_VERSION', '2.0.0' );

/**
 * Environment, should be either test or production
 * Note: if youre on localhost, even if you change this constant to production, it'll still use test :)
 */
$_wc_pv_env = 'production';

if ( isset( $_SERVER['SERVER_NAME'] ) && strpos( sanitize_text_field( $_SERVER['SERVER_NAME'] ), 'localhost' ) !== false || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ) {
	$_wc_pv_env = 'test';
}

define( 'WC_PV_ENVIRONMENT', $_wc_pv_env );

// For global option meta access :).
 $wc_pv_woo_option_meta = array(
	'disable_checkout_billing_display'   => '',
	'disable_checkout_shipping_display'  => '',
	'disable_checkout_billing_validate'  => '',
	'disable_checkout_shipping_validate' => '',
	'disable_account_billing_display'    => '',
	'disable_account_shipping_display'   => '',
 );

// Custom fields names.
$wc_pv_woo_custom_field_meta = array(
	'billing_hidden_phone_field'     => '_wc_pv_phone_validator',
	'billing_hidden_phone_err_field' => '_wc_pv_phone_validator_err',
	'validation_nonce_action' 		 => 'phone_validate',
	'validation_nonce_field' 		 => 'wc_pv_validate_nonce'
);

/**
 * Initiate Phone Validator Chakra.
 */
function wc_pv_initiate() {
	// Include dependencies file.
	if ( ! class_exists( 'WC_PV_Dependencies' ) ) {
		include_once dirname( __FILE__ ) . '/includes/class-wc-pv-deps.php';
	}

	// Include the main class.
	if ( ! class_exists( 'WC_PV' ) ) {
		include_once dirname( __FILE__ ) . '/includes/class-wc-pv.php';
	}

	if ( ! function_exists( 'wc_pv' ) ) {

		/**
		 * Run instance.
		 */
		function wc_pv() {
			return WC_PV::instance();
		}

		$GLOBALS['wc_pv'] = wc_pv();
	}
}

add_action( 'plugins_loaded', 'wc_pv_initiate' );
