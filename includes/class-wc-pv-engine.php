<?php
/**
 * The Engine for setting up validate stuff.
 *
 * @class   WC_PV_Engine
 * @package Woo Phone Validator/Classes
 * @since   2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_PV_Engine class.
 */
class WC_PV_Engine {

	/**
	 * The single instance of the class.
	 *
	 * @var WC_PV_Engine
	 *
	 * @since 2.0.0
	 */
	protected static $instance = null;

	/**
	 * Main class instance. Ensures only one instance of class is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_PV_Engine
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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_css' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_js' ) );

		// Woocommerce things
		add_filter( 'woocommerce_billing_fields', array( $this, 'add_billing_fields' ), 20, 1 );
	}

	/**
	 * Enqueues all necessary scripts
	 */
	public function enqueue_js() {
		$intl_tel_js_file = '/assets/vendor/js/intlTelInput-jquery.min.js';
		$frontend_js_file = '/assets/js/frontend' . WC_PV_MIN_SUFFIX . '.js';

		wp_register_script( 'wc_pv_intl-phones-lib', wc_pv()->plugin_url() . $intl_tel_js_file, array( 'jquery' ), wc_pv()->get_asset_version_number( $intl_tel_js_file ), true );
		$script_dep = array( 'wc_pv_intl-phones-lib' );

		if ( is_checkout() ) { // For checkout, to load properly
			$script_dep[] = 'wc-checkout';
		}

		wp_register_script( 'wc_pv_js-script', wc_pv()->plugin_url() . $frontend_js_file, $script_dep, wc_pv()->get_asset_version_number( $frontend_js_file ), true );

		// Localise script,
		global $wc_pv_woo_custom_field_meta;
		$wc_pv_json = array(
			'isRTL'                 => ( is_rtl() ? 'yes' : 'no' ),
			'nonceField'			=> wp_nonce_field( $wc_pv_woo_custom_field_meta['validation_nonce_action'], $wc_pv_woo_custom_field_meta['validation_nonce_field'] ),
			'nonceFieldName'		=> $wc_pv_woo_custom_field_meta['validation_nonce_action'],
			'phoneValidatorName'    => $wc_pv_woo_custom_field_meta['billing_hidden_phone_field'],
			'phoneValidatorErrName' => $wc_pv_woo_custom_field_meta['billing_hidden_phone_err_field'],
			'phoneErrorTitle'       => _x( '<strong>Phone validation error:</strong> ', 'starting error phrase', 'woo-phone-validator' ),
			'phoneUnknownErrorMsg'  => _x( 'Internal error 🥶', 'Incase error is unknown', 'woo-phone-validator' ),
			'separateDialCode'      => wc_pv()->separate_dial_code(),
			'validationErrors'      => wc_pv()->get_validation_errors(),
			'defaultCountry'        => wc_pv()->get_default_country(),
			'onlyCountries'         => wc_pv()->get_allowed_countries(),
			'preferredCountries'    => wc_pv()->get_preferred_countries(),
			'excludeCountries'		=> wc_pv()->get_excluded_countries(),
			'nationalMode'			=> wc_pv()->national_mode(),
			'autoHideDialCode'		=> wc_pv()->auto_hide_dial_code(),
			'autoPlaceholder'		=> wc_pv()->get_auto_placeholder(),
			'customContainer'		=> wc_pv()->custom_container(),
			'allowDropdown'			=> wc_pv()->allow_dropdown(),
			'utilsScript'           => wc_pv()->plugin_url() . '/assets/vendor/js/utils.js',
		);
		// get phone value for international lib use
		$phone = wc_pv()->get_current_user_phone();

		if ( ! empty( $phone ) ) {
			$wc_pv_json['userPhone'] = $phone;
		}
		// change parent class according to pages
		$wc_pv_json['parentPage']  = '.woocommerce-checkout';
		$wc_pv_json['currentPage'] = 'checkout';

		if ( is_account_page() ) {
			$wc_pv_json['parentPage']  = '.woocommerce-MyAccount-content';
			$wc_pv_json['currentPage'] = 'account';
		}

		wp_localize_script( 'wc_pv_js-script', 'wcPvJson', $wc_pv_json );
		wp_enqueue_script( 'wc_pv_intl-phones-lib' );
		wp_enqueue_script( 'wc_pv_js-script' );
	}

	/**
	 * Enqueues all necessary css.
	 */
	public function enqueue_css() {
		$intl_tel_css_file = '/assets/vendor/css/intlTelInput.min.css';
		$frontend_css_file = '/assets/css/frontend' . WC_PV_MIN_SUFFIX . '.css';

		wp_enqueue_style( 'wc_pv_intl-phones-lib-css', wc_pv()->plugin_url() . $intl_tel_css_file, array(), wc_pv()->get_asset_version_number( $intl_tel_css_file ) );
		wp_enqueue_style( 'wc_pv_css-style', wc_pv()->plugin_url() . $frontend_css_file, array('wc_pv_intl-phones-lib-css'), wc_pv()->get_asset_version_number( $frontend_css_file ) );
	}

	/**
	 * Adds extra fields to woocommerce billing form
	 */
	public function add_billing_fields( $fields ) {

		$extras = apply_filters('wc_pv_add_to_main_class', '', $fields );
		
		$fields['billing_phone']['class'][0] .= ' wc-pv-phone wc-pv-intl ' . $extras;
		return $fields;
	}

}

WC_PV_Engine::instance();
