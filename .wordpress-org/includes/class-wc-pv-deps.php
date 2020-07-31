<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class WC_PV_Dependencies {
	private static $active_plugins;

	public static function init() {
		self::$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}
	}

	/**
	 * Check if woocommerce exist
	 *
	 * @return Boolean
	 */
	public static function woocommerce_active_check() {
		$wc_boot_file = 'woocommerce/woocommerce.php';

		if ( ! self::$active_plugins ) {
			self::init();
		}

		return ( in_array( $wc_boot_file, self::$active_plugins, true ) || array_key_exists( $wc_boot_file, self::$active_plugins ) );
	}

	/**
	 * Check if woocommerce is active
	 *
	 * @return Boolean
	 */
	public static function is_woocommerce_active() {
		return self::woocommerce_active_check();
	}
}
