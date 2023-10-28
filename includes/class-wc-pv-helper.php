<?php
/**
 * The Helper Plugin class.
 *
 * @class   WC_PV_Helper
 * @package Woo Phone Validator/Classes
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * The main heart of the plugin :)
 */
class WC_PV_Helper {

	/**
	 * Checks if we're currently on the checkout page
	 *
	 * For some reason, the default woocommerce is_checkout() doesnt seem to work, runs too early
	 *
	 * @return bool
	 */
	public static function is_checkout() {
		if ( is_page( get_option( 'woocommerce_checkout_page_id', false ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if we're currently on the myaccount pages.
	 *
	 * For some reason, the default woocommerce is_account_page() doesnt seem to work, runs too early.
	 *
	 * @return bool
	 */
	public static function is_account_page() {
		if ( is_page( get_option( 'woocommerce_myaccount_page_id', false ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * For backend validation of phone.
	 * 
	 * @param string $area
	 * @param string $type Mostly Billing|Shipping
	 * @param array|null    $fields (optional) arrays are passed here when its used with checkout_validation hook, otherwise, we pass null for other usecase
 	 * @param WP_Error|null $errors Validation errors same as above.
	 * @return bool
	 */
	public static function phone_validation( $area, $type, $fields = null, $errors = null ) {
		global $wc_pv_woo_custom_field_meta;

		// Nonce.
		$nonce_action = $wc_pv_woo_custom_field_meta['validation_nonce_action'];
		$nonce_field  = $wc_pv_woo_custom_field_meta['validation_nonce_field'];

		// Custom fields.
		$phone_name            = $wc_pv_woo_custom_field_meta[ "{$type}_hidden_phone_field" ];
		$phone_err_name        = $wc_pv_woo_custom_field_meta[ "{$type}_hidden_phone_err_field" ];
		$phone_valid_field     = strtolower( self::sanitize_field( $phone_name, 'text', $nonce_action, $nonce_field ) );
		$phone_valid_err_field = trim( self::sanitize_field( $phone_err_name, 'text', $nonce_action, $nonce_field ) );
		// $bil_email          = wc_pv()->sanitize_field( 'billing_email', 'email', $nonce_action, $nonce_field );
		$phone             = self::sanitize_field( $type . '_phone', 'text', $nonce_action, $nonce_field );

		return self::check_phone_validation( $area, $type, $phone, $phone_valid_field, $phone_valid_err_field );
	}

	/**
	 * Checks for phone validation.
	 * 
	 * @param string $area
	 * @param string $type Mostly Billing|Shipping
	 * @param string $phone
	 * @param string $phone_valid_field
	 * @param string $phone_valid_err_field
	 * @return bool
	 */
	public static function check_phone_validation( $area, $type, $phone, $phone_valid_field, $phone_valid_err_field ) {
		// if ( ! empty( $bil_email ) && ! empty( $bil_phone ) && ( empty( $phone_valid_field ) || ! is_numeric( $phone_valid_field ) ) ) {// from account side.
		// TODO: Email validation needed? https://github.com/Preciousomonze/woocommerce-phone-validator/issues/46
		if ( /* ! empty( $bil_email ) && */ ! empty( $phone ) && ( ! empty( $phone_valid_err_field ) ) && ( empty( $phone_valid_field ) || ! is_numeric( $phone_valid_field ) ) ) {// there was an error, this way we know its coming directly from normal woocommerce, so no conflict :)
			/* Issues #28, says WC Doesn't actually handle thing, soooo.
			if ( ! is_numeric( str_replace( ' ', '', $bil_phone ) ) ) { // WC will handle this, so no need to report errors
				return true;
			}*/

			/**
			 * Filters the validation error for WC.
			 * 
			 * Incase one decides not to display anything or edit based on their logic.
			 * Wanted to make this a way of disabling to, but naa, there will be other means.
			 * 
			 * @since 2.0.0
			 * 
			 * @param string $phone_valid_err_field
			 * @param string $phone
			 * @param string $type billing|shipping
			 */
			$phone_err_msg = apply_filters( 'wc_pv_' . $type . '_validation_error', $phone_valid_err_field, $phone, $type );

			if ( ! empty( $phone_err_msg ) ) {
				wc_add_notice( __( $phone_err_msg, 'woo-phone-validator' ), 'error' );
				return false;
			}
		}

		return true;
	}

	/**
	 * Helps filter fields with wp_nonce.
	 * 
	 * @param string $name Field name
	 * @param string $type text,field(for now)
	 * @param string $nonce_action
	 * @param string $nonce_field
	 * 
	 * @since 2.0.0
	 * @return string
	 */
	public static function sanitize_field( $name, $type, $nonce_action, $nonce_field ) {
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
	 * Get logged in user meta phone phone.
	 * 
	 * @param string $type must be a valid meta name: billing_phone, shipping_phone, etc.
	 *
	 * @return string
	 */
	public static function get_current_user_phone( $type = 'billing_phone' ) {
		return get_user_meta( get_current_user_id(), $type, true );
	}

	/**
	 * Validation error list.
	 *
	 * Must follow the phone validation error sequence
	 *
	 * @param string $translation_type (optional) this determines some stuff, invalid for now, so not needed
	 * @since 1.2.0
	 * @return array
	 */
	public static function get_validation_errors( $translation_type = '__' ) {
		/**
		 * Filters validation error list.
		 * 
		 * Invalid number, Invalid country code, Phone number too short, Phone number too long, Invalid number.
		 * @param array
		 * @since 2.0.0
		 */
		return apply_filters( 'wc_pv_validation_error_list', array(
			__( 'Invalid number', 'woo-phone-validator' ),
			__( 'Invalid country code', 'woo-phone-validator' ),
			__( 'Phone number too short', 'woo-phone-validator' ),
			__( 'Phone number too long', 'woo-phone-validator' ),
			__( 'Invalid number', 'woo-phone-validator' ),	
		) );
	}

	/**
	 * Separate dial code.
	 *
	 * @param  bool $value (optional) default is false
	 * @return bool
	 */
	public static function separate_dial_code( $value = false ) {
		/**
		 * Filters boolean value to separate dial code
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
	public static function use_wc_store_default_country() {
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
	public static function get_default_country() {
		$default = '';
		if ( true === self::use_wc_store_default_country() ) {
			$default = apply_filters( 'woocommerce_get_base_location', get_option( 'woocommerce_default_country' ) );

			// Remove sub-states.
			if ( strstr( $default, ':' ) ) {
				list( $country, $state ) = explode( ':', $default );
				$default                 = $country;
			}
		}

		/**
		 * Filters Default country.
		 * 
		 * @since 2.0
		 * @param string $default
		 */
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
	public static function get_allowed_countries() {
		return apply_filters( 'wc_pv_allowed_countries', array_keys( WC()->countries->get_allowed_countries() ) );
	}

	/**
	 * Gets excluded countries
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function get_excluded_countries() {
		return apply_filters( 'wc_pv_excluded_countries', array() );
	}

	/**
	 * Gets Preferred countries
	 *
	 * @since 1.3.0
	 * @return array
	 */
	public static function get_preferred_countries() {
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
	public static function national_mode() {
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
	public static function auto_hide_dial_code() {
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
	public static function allow_dropdown() {
		return apply_filters( 'wc_pv_allow_dropdown', true );
	}
	
	/**
	 * Additional classes to add to the parent div
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public static function custom_container() {
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
	public static function get_auto_placeholder() {
		return apply_filters( 'wc_pv_set_auto_placeholder', 'polite' );
	}

	/**
	 * Gets Custom Placeholder.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function get_custom_placeholder() {
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
	public static function get_placeholder_number_type() {
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
	public static function get_localized_countries() {
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
	public static function hidden_input() {
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
	public static function format_on_display() {
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
	public static function get_asset_version_number( $file ) {
		$f = WC_PV_ABSPATH . ltrim( $file, '/' );
		return ( trim( strtolower( WC_PV_ENVIRONMENT ) ) === 'production' ? WC_PV_PLUGIN_VERSION : filemtime( $f ) );
	}

}
