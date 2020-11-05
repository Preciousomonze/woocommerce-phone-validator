<?php
/**
 * The Main Plugin class.
 *
 * @class   WC_PV
 * @package Woo Phone Validator/Classes
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * The main heart of the plugin :)
 */
final class WC_PV {

	/**
	 * The single instance of the class.
	 *
	 * @var WC_PV
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * Array of deprecated hook handlers.
	 *
	 * @var array of WC_PV_Deprecated_Hooks
	 * @since 2.0.0
	 */
	public $deprecated_hook_handlers = array();

	/**
	 * Main instance
	 *
	 * @return class object
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
	 * Class constructor
	 */
	public function __construct() {
		if ( ! WC_PV_Dependencies::is_woocommerce_active() ) {
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
			return;
		}
		// Define the constants.
		$this->define_constants();

		// Include relevant files.
		$this->includes();

		// Always load translation files.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Prepare handling of deprecated action and filters for future use :).
		$this->deprecated_hook_handlers['actions'] = new WC_PV_Deprecated_Action_Hooks();
		$this->deprecated_hook_handlers['filters'] = new WC_PV_Deprecated_Filter_Hooks();

		do_action( 'wc_pv_loaded' );
	}

	/**
	 * Constants define
	 */
	private function define_constants() {
		$this->define( 'WC_PV_ABSPATH', dirname( WC_PV_PLUGIN_FILE ) . '/' );
		$this->define( 'WC_PV_PLUGIN_FILE', plugin_basename( WC_PV_PLUGIN_FILE ) );
		$this->define( 'WC_PV_ASSETS_PATH', plugins_url( 'assets/', __FILE__ ) );

		if ( trim( strtolower( WC_PV_ENVIRONMENT ) ) === 'production' ) {
			$this->define( 'WC_PV_MIN_SUFFIX', '.min' );
		} else {
			$this->define( 'WC_PV_MIN_SUFFIX', '' );
		}

	}

	/**
	 * Define constants
	 *
	 * @param string $name
	 * @param mixed  $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Check request
	 *
	 * @param string $type
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Load plugin files
	 */
	public function includes() {
		// Load for global.		
		include_once WC_PV_ABSPATH . 'includes/class-wc-pv-engine.php';
		include_once WC_PV_ABSPATH . 'includes/class-wc-pv-helper.php';

		if ( $this->is_request( 'frontend' ) ) {
			include_once WC_PV_ABSPATH . 'public/class-wc-pv-checkout.php';
			include_once WC_PV_ABSPATH . 'public/class-wc-pv-account.php';
		}

		// Support deprecated filter hooks and actions.
		include_once WC_PV_ABSPATH . 'includes/compatibility/class-wc-pv-deprecated-action-hooks.php';
		include_once WC_PV_ABSPATH . 'includes/compatibility/class-wc-pv-deprecated-filter-hooks.php';
	}

	/**
	 * Plugin url
	 *
	 * @return string path
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', WC_PV_PLUGIN_FILE ) );
	}

	/**
	 * Load Localisation files.
	 *
	 * @since  1.2.0
	 */
	public function load_plugin_textdomain() {
		// Traditional WordPress plugin locale filter.
		// $locale = apply_filters( 'plugin_locale', get_locale(), 'woo-phone-validator' );

		// load_textdomain( 'woo-phone-validator', WP_LANG_DIR . '/woo-phone-validator/woo-phone-validator-' . $locale . '.mo' );
		load_plugin_textdomain( 'woo-phone-validator', false, plugin_basename( dirname( WC_PV_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Display admin notice
	 */
	public function admin_notices() {
		$note = __( '<strong>Phone Validator for WooCommerce</strong> plugin requires <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a> plugin to be active!', 'woo-phone-validator' );
		// phpcs:ignore
		printf( '<div class="error"><p>%s</p></div>', $note );
	}

}
