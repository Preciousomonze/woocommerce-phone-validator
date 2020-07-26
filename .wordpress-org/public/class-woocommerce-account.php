<?php
/**
 * For handling the fields on edit address page
 */
Class WC_PV_Account{

    /**
     * Construcdur :)
     */
    public function __construct(){
        //inherits style and js from the checkout class :)
        add_action('woocommerce_after_save_address_validation', array($this,'account_page_validate'),10,2);
    }

    /**
     * For extra custom validation
     * 
     * @param int         $user_id User ID being saved.
     * @param string      $load_address Type of address e.g. billing or shipping.
     * @hook woocommerce_after_save_address_validation
     */
    public function account_page_validate($user_id,$load_address){
        global $wc_pv_woo_custom_field_meta;

        $phone_name = $wc_pv_woo_custom_field_meta['billing_hidden_phone_field'];
        $phone_err_name = $wc_pv_woo_custom_field_meta['billing_hidden_phone_err_field'];
        $phone_valid_field = isset( $_POST[$phone_name] ) ? strtolower( sanitize_text_field( $_POST[$phone_name] ) ) : '';
        $phone_valid_err_field = isset( $_POST[$phone_err_name] ) ? trim( sanitize_text_field( $_POST[$phone_err_name] ) ) : '';
        $bil_email = isset( $_POST['billing_email'] ) ? sanitize_email($_POST['billing_email']) : '';
        $bil_phone = isset( $_POST['billing_phone'] ) ? sanitize_text_field( $_POST['billing_phone'] ) : '';

        if( !empty( $bil_email ) && !empty( $bil_phone ) && ( empty( $phone_valid_field ) || !is_numeric( $phone_valid_field ) ) ){//there was an error, this way we know its coming directly from normal woocommerce, so no conflict :)
            if( !is_numeric( str_replace( ' ', '', $bil_phone ) ) ) // WC will handle this, so no need to report errors
            return;

            $ph = explode( ':', $phone_valid_err_field);
            $ph[0] = '<strong>'.$ph[0].'</strong>';
            $phone_err_msg = implode(':',$ph);
            $out =  __($phone_err_msg, 'woo-phone-validator' );
            wc_add_notice( $out, 'error' );
        }
    }
}
new WC_PV_Account();
