<?php
/**
 * For handling the checkout fields
 */
Class WC_PV_Checkout{

    /**
     * Construcdur :)
     */
    public function __construct(){
        //if(wc_pv()->is_account_page() || wc_pv()->is_checkout()){
            //henqueue
            add_action( 'wp_enqueue_scripts', array($this,'enqueue_css' ));
            add_action( 'wp_enqueue_scripts', array($this,'enqueue_js' ));
            //woocommerce things
            add_filter('woocommerce_billing_fields', array($this,'add_billing_fields'),20,1);
            add_action('woocommerce_after_checkout_validation', array($this,'checkout_validate'));
        //}
    }
    
    /**
     * enqueues all necessary scripts
     */
    public function enqueue_js(){
        //p_enqueue_script('NameMySccript','path/to/MyScript','dependencies_MyScript', 'VersionMyScript', 'InfooterTrueorFalse');
        wp_register_script('wc_pv_intl-phones-lib',wc_pv()->plugin_url().'/assets/vendor/js/intlTelInput-jquery.min.js',array('jquery'),WC_PV_PLUGIN_VERSION,true);
        $script_dep = array('wc_pv_intl-phones-lib');
        if(is_checkout())//for checkout, to load properly
            $script_dep[] = 'wc-checkout';
        wp_register_script('wc_pv_js-script',wc_pv()->plugin_url().'/assets/js/frontend'.WC_PV_MIN_SUFFIX.'.js',$script_dep,WC_PV_PLUGIN_VERSION,true);
        //localise script,
        global $wc_pv_woo_custom_field_meta;
        $wcjson = array(
            'phoneValidatorName'=>$wc_pv_woo_custom_field_meta['billing_hidden_phone_field'],
            'phoneValidatorErrName' => $wc_pv_woo_custom_field_meta['billing_hidden_phone_err_field']
        );
        //get phone value for international lib use
        $phone = get_user_meta(get_current_user_id(),'billing_phone',true);
        if(!empty($phone)){
            $wcjson['userPhone'] = $phone;
        }
        //change parent class according to pages
        $wcjson['parentPage'] = '.woocommerce-checkout';
        $wcjson['currentPage'] = 'checkout';
        if(is_account_page()){
            $wcjson['parentPage'] = '.woocommerce-MyAccount-content';
            $wcjson['currentPage'] = 'account';
        }
        $wcjson['utilsScript'] = wc_pv()->plugin_url().'/assets/vendor/js/utils.js';
        wp_localize_script( 'wc_pv_js-script', 'wcPvJson', $wcjson );
		//
		wp_enqueue_script('wc_pv_intl-phones-lib');
        wp_enqueue_script('wc_pv_js-script');
    }
    /**
     * enqueues all necessary css
     */
    public function enqueue_css(){
        wp_enqueue_style( 'wc_pv_intl-phones-lib-css',wc_pv()->plugin_url().'/assets/vendor/css/intlTelInput.min.css');
        wp_enqueue_style( 'wc_pv_css-style',wc_pv()->plugin_url().'/assets/css/frontend'.WC_PV_MIN_SUFFIX.'.css',array(),WC_PV_PLUGIN_VERSION);
    }
    
    /**
     * Adds extra fields to woocommerce billing form
     */
    public function add_billing_fields($fields){
        $fields['billing_phone']['class'][0] .= ' wc-pv-phone wc-pv-intl';
        return $fields;
    }

    /**
     * For extra custom validation
     * 
     * @param array $data | the external data
     * @hook woocommerce_after_checkout_validation
     */
    public function checkout_validate($data){
        global $wc_pv_woo_custom_field_meta;
        $phone_name = $wc_pv_woo_custom_field_meta['billing_hidden_phone_field'];
        $phone_err_name = $wc_pv_woo_custom_field_meta['billing_hidden_phone_err_field'];
        $phone_valid_field = strtolower( sanitize_text_field($_POST[$phone_name]) );
        $phone_valid_err_field = trim( sanitize_text_field( $_POST[$phone_err_name] ) );
        $bil_email = sanitize_email($_POST['billing_email']);
        $bil_phone = sanitize_text_field($_POST['billing_phone']);
		       
       if( !empty($bil_email) && !empty($bil_phone) && (!empty($phone_valid_err_field)) && (empty($phone_valid_field) || !is_numeric($phone_valid_field) ) ){//there was an error, this way we know its coming directly from normal woocommerce, so no conflict :)
        $ph = explode(':',$phone_valid_err_field);
        $ph[0] = '<strong>'.$ph[0].'</strong>';
        $phone_err_msg = implode(':',$ph);
        $out =  __($phone_err_msg, WC_PV_TEXT_DOMAIN );
			wc_add_notice( $out, 'error');
        }
    }
}
new WC_PV_Checkout();