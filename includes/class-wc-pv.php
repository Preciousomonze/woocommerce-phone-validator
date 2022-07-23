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
		if ( $this->is_request( 'frontend' ) ) {
			include_once WC_PV_ABSPATH . 'includes/class-wc-pv-engine.php';
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


	/**
	 * Checks if we're currently on the checkout page
	 *
	 * For some reason, the default woocommerce is_checkout() doesnt seem to work, runs too early
	 *
	 * @return bool
	 */
	public function is_checkout() {
		$id = get_option( 'woocommerce_checkout_page_id', false );

		if ( is_page( $id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if we're currently on the myaccount pages
	 *
	 * For some reason, the default woocommerce is_account_page() doesnt seem to work, runs too early
	 *
	 * @return bool
	 */
	public function is_account_page() {
		$id = get_option( 'woocommerce_myaccount_page_id', false );

		if ( is_page( $id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * For backend validation of phone
	 *
	 * @return bool
	 */
	public function billing_phone_validation() {
		global $wc_pv_woo_custom_field_meta;
		// Nonce
		$nonce_action = $wc_pv_woo_custom_field_meta['validation_nonce_action'];
		$nonce_field  = $wc_pv_woo_custom_field_meta['validation_nonce_field'];

		// Custom fields
		$phone_name            = $wc_pv_woo_custom_field_meta['billing_hidden_phone_field'];
		$phone_err_name        = $wc_pv_woo_custom_field_meta['billing_hidden_phone_err_field'];
		$phone_valid_field     = strtolower( wc_pv()->sanitize_field( $phone_name, 'text', $nonce_action, $nonce_field ) );
		$phone_valid_err_field = trim( wc_pv()->sanitize_field( $phone_err_name, 'text', $nonce_action, $nonce_field ) );
		$bil_email             = wc_pv()->sanitize_field( 'billing_email', 'email', $nonce_action, $nonce_field );
		$bil_phone             = wc_pv()->sanitize_field( 'billing_phone', 'text', $nonce_action, $nonce_field );


		// if ( ! empty( $bil_email ) && ! empty( $bil_phone ) && ( empty( $phone_valid_field ) || ! is_numeric( $phone_valid_field ) ) ) {// from account side.
		if ( ! empty( $bil_email ) && ! empty( $bil_phone ) && ( ! empty( $phone_valid_err_field ) ) && ( empty( $phone_valid_field ) || ! is_numeric( $phone_valid_field ) ) ) {// there was an error, this way we know its coming directly from normal woocommerce, so no conflict :)
			/* Issues #28, says WC Doesn't actually handle thing, soooo.
			if ( ! is_numeric( str_replace( ' ', '', $bil_phone ) ) ) { // WC will handle this, so no need to report errors
				return true;
			}*/

			$phone_err_msg = __( $phone_valid_err_field, 'woo-phone-validator' );
			wc_add_notice( $phone_err_msg, 'error' );
			return false;
		}

		// Space for custom error handling extension.
		$outside_handling = apply_filters( 'wc_pv_add_validation_error', '', $bil_phone, $bil_email );

		if ( ! empty( $outside_handling ) ) {
			wc_add_notice( $outside_handling, 'error' );
		}

		return true;
	}

	/**
	 * Helps filter fields with wp_nonce
	 * 
	 * @param string $name Field name
	 * @param string $type text,field(for now)
	 * @param string $nonce_action
	 * @param string $nonce_field
	 * 
	 * @since 2.0.0
	 * @return string
	 */
	public function sanitize_field( $name, $type, $nonce_action, $nonce_field ) {
		// phpcs:ignore
		$nonce_field = ( isset( $_POST[$nonce_field] ) ? sanitize_text_field( $_POST[$nonce_field] ) : '' );
		$field_pass  = ( ! isset( $_POST[$name] ) || ! wp_verify_nonce( sanitize_text_field( $nonce_field ), $nonce_action ) ? false : true );

		if ( ! $field_pass ) {
			return '';
		}
		$field = '';
		switch ( strtolower( trim( $type) ) ) {
			case 'email':
				$field = sanitize_email( $_POST[$name] );
				break;
			default:
				$field = sanitize_text_field( $_POST[$name] );
		}
		return $field;
	}

	/**
	 * Get logged in user billing phone
	 *
	 * @return string
	 */
	public function get_current_user_phone() {
		return get_user_meta( get_current_user_id(), 'billing_phone', true );
	}

	/**
	 * Validation error list
	 *
	 * Must follow the phone validation error sequence
	 *
	 * @param string $translation_type (optional) this determines some stuff, invalid for now, so not needed
	 * @since 1.2.0
	 * @return array
	 */
	public function get_validation_errors( $translation_type = '__' ) {
		// Invalid number, Invalid country code, Phone number too short, Phone number too long, Invalid number
		$errors = array(
			__( 'Invalid number', 'woo-phone-validator' ),
			__( 'Invalid country code', 'woo-phone-validator' ),
			__( 'Phone number too short', 'woo-phone-validator' ),
			__( 'Phone number too long', 'woo-phone-validator' ),
			__( 'Invalid number', 'woo-phone-validator' ),
		);
		/**
		 * Validation error list.
		 * 
		 * @since 2.0.0
		 */
		return apply_filters( 'wc_pv_validation_error_list', $errors );
	}

	/**
	 * Separate dial code
	 *
	 * @param  bool $value (optional) default is false
	 * @return bool
	 */
	public function separate_dial_code( $value = false ) {
		/**
		 * Filter boolean value to separate dial code
		 *
		 * @since 1.2.0
		 */
		return apply_filters( 'wc_pv_separate_dial_code', $value );
	}

	/**
	 * Use default WooCommerce store
	 *
	 * @since 1.2.0
	 * @return bool
	 */
	public function use_wc_store_default_country() {
		return apply_filters( 'wc_pv_use_wc_default_store_country', false );
	}

	/**
	 * Gets default country
	 *
	 * If 'wc_pv_use_wc_default_store is set to true, uses store default country
	 *
	 * @since 1.2.0
	 * @return string
	 */
	public function get_default_country() {
		$default = '';
		if ( true === wc_pv()->use_wc_store_default_country() ) {
			$default = apply_filters( 'woocommerce_get_base_location', get_option( 'woocommerce_default_country' ) );

			// Remove sub-states.
			if ( strstr( $default, ':' ) ) {
				list( $country, $state ) = explode( ':', $default );
				$default                 = $country;
			}
		}
		return apply_filters( 'wc_pv_set_default_country', $default );
	}

	/**
	 * Gets allowed countries
	 *
	 * Uses Allowed countries set in WooCommerce store
	 * 
	 * @since 1.2.0
	 * @return array
	 */
	public function get_allowed_countries() {
		return apply_filters( 'wc_pv_allowed_countries', array_keys( WC()->countries->get_allowed_countries() ) );
	}

	/**
	 * Gets excluded countries
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_excluded_countries() {
		return apply_filters( 'wc_pv_excluded_countries', array() );
	}

	/**
	 * Gets Preferred countries
	 *
	 * @since 1.3.0
	 * @return array
	 */
	public function get_preferred_countries() {
		return apply_filters( 'wc_pv_preferred_countries', array() );
	}

	/**
	 * National Mode.
	 *
	 * Allow users to enter national numbers (and not have to think about international dial codes).
	 * Formatting, validation and placeholders still work. 
	 * Then you can use getNumber to extract a full international number - see example. 
	 * This option now defaults to true, and it is recommended that you leave it that way as it provides a
	 * better experience for the user. 
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function national_mode() {
		return apply_filters( 'wc_pv_national_mode', true );
	}

	/**
	 * Auto hide dial code
	 * 
	 * If there is just a dial code in the input: remove it on blur or submit.
	 * This is to prevent just a dial code getting submitted with the form. 
	 * Requires nationalMode to be set to false. 
	 * So if true, sets nationalMode to false
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function auto_hide_dial_code() {
		$value = apply_filters( 'wc_pv_auto_hide_dial_code', false );
		if ( true === $value ) {
			// Set nationalMode to false
			add_filter( 'wc_pv_national_mode', '__return_false' );
		}
		return $value;
	}

	/**
	 * Allow dropdown
	 * 
	 * Whether or not to allow the dropdown. 
	 * If disabled, there is no dropdown arrow, and the selected flag is not clickable. 
	 * Also we display the selected flag on the right instead because it is just a marker of state.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function allow_dropdown() {
		return apply_filters( 'wc_pv_allow_dropdown', true );
	}
	
	/**
	 * Additional classes to add to the parent div
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function custom_container() {
		return apply_filters( 'wc_pv_add_container_classes', '' );
	}

	/**
	 * Set the input's placeholder to an example number for the selected country.
	 *
	 * Set the input's placeholder to an example number for the selected country, and update it if the country changes.
	 * You can specify the number type using the placeholderNumberType option. By default it is set to "polite", 
	 * which means it will only set the placeholder if the input doesn't already have one.
	 * You can also set it to "aggressive", which will replace any existing placeholder, or "off". 
	 * Requires the utilsScript option.
	 * 
	 * @since 2.0.0
	 * @return string
	 */
	public function get_auto_placeholder() {
		return apply_filters( 'wc_pv_set_auto_placeholder', 'polite' );
	}

	/**
	 * Gets Custom Placeholder.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_custom_placeholder() {
		return apply_filters( 'wc_pv_set_placeholder', array() );
	}

	/**
	 * Placeholder number type.
	 * 
	 * Specify one of the keys from the global enum intlTelInputUtils.numberType
	 * FIXED_LINE,MOBILE,FIXED_LINE_OR_MOBILE,TOLL_FREE,PREMIUM_RATE,SHARED_COST,VOIP,PERSONAL_NUMBER,PAGER,UAN,VOICEMAIL,UNKNOWN
	 * Default is MOBILE
	 * 
	 * @since 2.0.0
	 * @return string
	 */
	public function get_placeholder_number_type() {
		$number_types = array(
			'FIXED_LINE',
			'MOBILE',
			'FIXED_LINE_OR_MOBILE',
			'TOLL_FREE',
			'PREMIUM_RATE',
			'SHARED_COST',
			'VOIP',
			'PERSONAL_NUMBER',
			'PAGER',
			'UAN',
			'VOICEMAIL',
			'UNKNOWN'
		);

		$value = strtoupper( apply_filters( 'wc_pv_set_placeholder_number_type', 'mobile' ) );

		// Already same mobile value, retun asap.
		if ( 'MOBILE' === $value ) {
			return $value;
		}

		// Since value is searched for in array, make sure its at least 3 characters
		if ( strlen( $value ) < 3 ) {
			return '';
		}

		$type = preg_grep( '/^{$value}/i', $number_types );

		if ( empty( $type ) ) {
			return '';
		}
		return $type[0];
	}

	/**
	 * Localized Countries.
	 * 
	 * Type: Object Default: {}
	 * Allows to translate the countries by its given iso code e.g.: { 'de': 'Deutschland' }
	 * Somehow i have to convert this to object on the js side.
	 * 
	 * @return string Json
	 */
	public function get_localized_countries() {
		$data = apply_filters( 'wc_pv_set_localized_countries', array() );
		return wp_json_encode( $data );
	}

	/**
	 * Hidden input for Phone number
	 * 
	 * Add a hidden input with the given name. Alternatively, if your input name contains square brackets 
	 * (e.g. name="phone_number[main]") then it will give the hidden input the same name, 
	 * replacing the contents of the brackets with the given name 
	 * (e.g. hiddenInput: "full", then in this case the hidden input would have name="phone_number[full]").
	 * Leaving this here, might not need implementation currently. :) 
	 * If you find this and want to implement it, cool, be my guest. 
	 * 
	 * @return string
	 */
	public function hidden_input() {
		return apply_filters( 'wc_pv_set_hidden_input', '' );
	}

	/**
	 * Format On Display
	 * 
	 * Format the input value (according to the nationalMode option) during initialisation, and on setNumber.
	 * Requires the utilsScript option.
	 * 
	 * @return bool
	 */
	public function format_on_display() {
		return apply_filters( 'wc_pv_format_on_display', true );
	}

	/**
	 * Gets asset version Number.
	 * 
	 * If in debug mode, uses filemtime to avoid 
	 * caching wahala
	 * 
	 * @param string $file The asset file
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_asset_version_number( $file ) {
		$f = WC_PV_ABSPATH . ltrim( $file, '/' );
		return ( trim( strtolower( WC_PV_ENVIRONMENT ) ) === 'production' ? WC_PV_PLUGIN_VERSION : filemtime( $f ) );
	}

}
