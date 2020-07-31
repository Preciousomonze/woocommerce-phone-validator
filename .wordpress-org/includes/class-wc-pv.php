<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly.

final class WC_PV{

    /**
     * The single instance of the class.
     *
     * @var WC_PV
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main instance
     *
     * @return class object
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Class constructor
     */
    public function __construct() {
        if ( WC_PV_Dependencies::is_woocommerce_active() ) {
            // Define the constants.
            $this->define_constants();

            // Include relevant files.
            $this->includes();

            // Always load translation files.
	    	add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
            
            do_action( 'wc_pv_init' );
        }
        else {
            add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
        }
    }

    /**
     * Constants define
     */
    private function define_constants() {
        $this->define( 'WC_PV_ABSPATH', dirname( WC_PV_PLUGIN_FILE ) . '/' );
        $this->define( 'WC_PV_PLUGIN_FILE', plugin_basename( WC_PV_PLUGIN_FILE ) );
        $this->define( 'WC_PV_ASSETS_PATH', plugins_url( 'assets/', __FILE__ ) );

        if( trim( strtolower( WC_PV_ENVIRONMENT ) ) == 'production' )
            $this->define( 'WC_PV_MIN_SUFFIX', '.min' );
        else
            $this->define( 'WC_PV_MIN_SUFFIX', '' );

    }

    /**
     * 
     * @param string $name
     * @param mixed $value
     */
    private function define( $name, $value ) {
        if ( !defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
     * Check request
     * @param string $type
     * @return bool
     */
    private function is_request($type) {
        switch ($type) {
            case 'admin' :
                return is_admin();
            case 'ajax' :
                return defined('DOING_AJAX');
            case 'cron' :
                return defined('DOING_CRON');
            case 'frontend' :
                return ( !is_admin() || defined( 'DOING_AJAX' ) ) && !defined( 'DOING_CRON' );
        }
    }

    /**
     * load plugin files
     */
    public function includes() {
        //if ($this->is_request('admin')) {}
        if ($this->is_request('frontend')) {
            add_action( 'woocommerce_init', function(){
                include_once( WC_PV_ABSPATH . 'public/class-woocommerce-checkout.php' );
                include_once( WC_PV_ABSPATH . 'public/class-woocommerce-account.php' );
            }, 20 );
        }
        //if ($this->is_request('ajax')) {}
    }

    /**
     * Plugin url
     * @return string path
     */
    public function plugin_url() {
        return untrailingslashit( plugins_url( '/', WC_PV_PLUGIN_FILE ) );
    }

	/**
	 * Load Localisation files.
	 *
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
        echo '<div class="error"><p>';
        _e('<strong>Woocommerce Phone Validator</strong> plugin requires <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a> plugin to be active!', 'woo-phone-validator' );
        echo '</p></div>';

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

        if( is_page( $id ) )
            return true;

        return false;
    }

    /**
     * Checks if we're currently on the myaccount pages
     * 
     * For some reason, the default woocommerce is_account_page() doesnt seem to work, runs too early
     * 
     * @return bool
     */
    public function is_account_page(){
        $id = get_option( 'woocommerce_myaccount_page_id', false );

        if( is_page( $id ) )
            return true;

        return false;
    }

    /**
     * Validation error list
     * 
     * Must follow the phone validation error sequence
     * 
     * @param string $translation_type (optional) this determines some stuff, invalid for now, so not needed 
     * @return array
     */
    public function get_validation_errors( $translation_type = '__' ){
        // "Invalid number", "Invalid country code", "Phone number too short", "Phone number too long", "Invalid number"
        $errors = array(
            __( 'Invalid number', 'woo-phone-validator' ),
            __( 'Invalid country code', 'woo-phone-validator' ),
            __( 'Phone number too short', 'woo-phone-validator' ),
            __( 'Phone number too long', 'woo-phone-validator' ),
            __( 'Invalid number', 'woo-phone-validator' ),
        );
        return $errors;
    }

    /**
     * Separate dial code
     * 
     * @param  bool $value (optional) default is false
     * @return bool
     */
    public function separate_dial_code( $value = false ){
        /**
         * Filter boolean value to separate dial code
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
        if( true === wc_pv()->use_wc_store_default_country() ){
            $default = apply_filters( 'woocommerce_get_base_location', get_option( 'woocommerce_default_country' ) );

            // Remove sub-states.
            if ( strstr( $default, ':' ) ) {
                list( $country, $state ) = explode( ':', $default );
                $default = $country;
            }
        }
        return apply_filters( 'wc_pv_set_default_country', $default );
    }

    /**
     * Gets allowed countries
     *
     * @since 1.2.0
     * 
     * @return array
     */
    public function get_allowed_countries() {
        return apply_filters( 'wc_pv_allowed_countries', array_keys( WC()->countries->get_allowed_countries() ) );
	}

    /**
     * Gets Preferred countries
     *
     * @since 1.3.0
     * 
     * @return array
     */
    public function get_preferred_countries() {
        return apply_filters( 'wc_pv_preferred_countries', array() );
    }

    /**
     * Get logged in user billing phone
     * 
     * @return string
     */
    public function get_current_user_phone() {
       return get_user_meta( get_current_user_id(), 'billing_phone', true );
    }

}
