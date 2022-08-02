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
	 * Construcdur :)
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_css' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_js' ) );

		// Woocommerce things.
		add_filter( 'woocommerce_billing_fields', array( __CLASS__, 'add_billing_fields' ), 20, 1 );
	}

	/**
	 * Enqueues all necessary scripts.
	 */
	public static function enqueue_js() {
		$intl_tel_js_file = '/assets/intl-tel-build/js/intlTelInput-jquery.min.js';
		$frontend_js_file = '/assets/js/frontend' . WC_PV_MIN_SUFFIX . '.js';

		wp_register_script( 'wc_pv_intl-phones-lib', wc_pv()->plugin_url() . $intl_tel_js_file, array( 'jquery' ), WC_PV_Helper::get_asset_version_number( $intl_tel_js_file ), true );
		$script_dep = array( 'wc_pv_intl-phones-lib' );

		if ( is_checkout() ) { // For checkout, to load properly.
			$script_dep[] = 'wc-checkout';
		}

		wp_register_script( 'wc_pv_js-script', wc_pv()->plugin_url() . $frontend_js_file, $script_dep, WC_PV_Helper::get_asset_version_number( $frontend_js_file ), true );

		// Localise script.
		global $wc_pv_woo_custom_field_meta;
		$wc_pv_json = array(
			'isRTL'                 => ( is_rtl() ? 'yes' : 'no' ),
			'nonceField'			=> wp_nonce_field( $wc_pv_woo_custom_field_meta['validation_nonce_action'], $wc_pv_woo_custom_field_meta['validation_nonce_field'] ),
			'nonceFieldName'		=> $wc_pv_woo_custom_field_meta['validation_nonce_action'],
			'phoneValidatorName'    => $wc_pv_woo_custom_field_meta['billing_hidden_phone_field'],
			'phoneValidatorErrName' => $wc_pv_woo_custom_field_meta['billing_hidden_phone_err_field'],
			'phoneErrorTitle'       => _x( '<strong>Phone validation error:</strong> ', 'starting error phrase', 'woo-phone-validator' ),
			'phoneUnknownErrorMsg'  => _x( 'Internal error 🥶', 'Incase error is unknown', 'woo-phone-validator' ),
			'separateDialCode'      => WC_PV_Helper::separate_dial_code(),
			'validationErrors'      => WC_PV_Helper::get_validation_errors(),
			'defaultCountry'        => WC_PV_Helper::get_default_country(),
			'onlyCountries'         => WC_PV_Helper::get_allowed_countries(),
			'preferredCountries'    => WC_PV_Helper::get_preferred_countries(),
			'excludeCountries'		=> WC_PV_Helper::get_excluded_countries(),
			'nationalMode'			=> WC_PV_Helper::national_mode(),
			'autoHideDialCode'		=> WC_PV_Helper::auto_hide_dial_code(),
			'autoPlaceholder'		=> WC_PV_Helper::get_auto_placeholder(),
			'customContainer'		=> WC_PV_Helper::custom_container(),
			'allowDropdown'			=> WC_PV_Helper::allow_dropdown(),
			'utilsScript'           => wc_pv()->plugin_url() . '/assets/intl-tel-build/js/utils.js',
		);

		// Get phone value for international lib use.
		$phone = WC_PV_Helper::get_current_user_phone();

		if ( ! empty( $phone ) ) {
			$wc_pv_json['userPhone'] = $phone;
		}
		// Change parent class according to pages.
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
	public static function enqueue_css() {
		$intl_tel_css_file = '/assets/intl-tel-build/css/intlTelInput.min.css';
		$frontend_css_file = '/assets/css/frontend' . WC_PV_MIN_SUFFIX . '.css';

		wp_enqueue_style( 'wc_pv_intl-phones-lib-css', wc_pv()->plugin_url() . $intl_tel_css_file, array(), WC_PV_Helper::get_asset_version_number( $intl_tel_css_file ) );
		wp_enqueue_style( 'wc_pv_css-style', wc_pv()->plugin_url() . $frontend_css_file, ['wc_pv_intl-phones-lib-css'], WC_PV_Helper::get_asset_version_number( $frontend_css_file ) );
	}

	/**
	 * Adds extra fields to woocommerce billing form.
	 * 
	 * @param array $fields
	 * @return array
	 */
	public function add_billing_fields( $fields ) {

		$extras = apply_filters( 'wc_pv_add_to_main_class', '', $fields );

		$fields['billing_phone']['class'][0] .= ' wc-pv-phone wc-pv-intl ' . $extras;
		return $fields;
	}

	
	/**
	 * For backend validation of billing phone.
	 *
	 * @param array    $fields
 	 * @param WP_Error $errors Validation errors.
	 * @return bool
	 */
	public static function billing_phone_validation( $fields, $errors ) {
		return WC_PV_Helper::phone_validation( 'billing', $fields, $errors );
	}

	/**
	 * For backend validation of shipping phone.
	 *
	 * @param array    $fields
 	 * @param WP_Error $errors Validation errors.
	 * @return bool
	 */
	public static function shipping_phone_validation( $fields, $errors ) {
		return WC_PV_Helper::phone_validation( 'shipping', $fields, $errors );
	}


}

WC_PV_Engine::init();
