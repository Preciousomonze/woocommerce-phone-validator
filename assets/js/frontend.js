/* script */
var $ = jQuery;
//$(document).ready(function(){
//set phone number properly for intl
// here, the index maps to the error code returned from getValidationError 
var wcPvPhoneErrorMap = wcPvJson.validationErrors;
//start
if($('.wc-pv-intl input').length == 0){//add class, some checkout plugin has overriden my baby
    $('#billing_phone_field').addClass('wc-pv-phone wc-pv-intl');
}
// Set default country
let wcPvDefCountry = ( wcPvJson.defaultCountry == '' ? $( `${wcPvJson.parentPage} #billing_country` ).val() : wcPvJson.defaultCountry );
let separateDialCode = ( wcPvJson.separateDialCode == 1 ? true : false );
var wcPvPhoneIntl = $('.wc-pv-intl input').intlTelInput({
    initialCountry: ( wcPvDefCountry == '' ? 'ng' : wcPvDefCountry ),
    /*geoIpLookup: function(callback) {
    $.get('https://ipinfo.io', function() {}, "jsonp").always(function(resp) {
    const countryCode = (resp && resp.country) ? resp.country : "";//asking for payment shaa,smh
    callback(countryCode);
    });
    },//to pick user country*/
    separateDialCode: separateDialCode, 
    utilsScript: wcPvJson.utilsScript
  });

/*if(wcPvJson.userPhone !== undefined ){
    wcPvPhoneIntl.intlTelInput("setNumber").val(wcPvJson.userPhone);
}*/

//some globals
var wcPvphoneErrMsg = "";

/**
 * Validates the phone number
 * 
 * @param intlTelInput input
 * @returns string or bool
 */
function wcPvValidatePhone(input){
    const phone = input;
    let result = false;
    if(phone.intlTelInput("isValidNumber") == true){
        result = phone.intlTelInput("getNumber");
    }
    else{
        let errorCode = phone.intlTelInput("getValidationError");
        wcPvphoneErrMsg = ` ${wcPvJson.phoneErrorTitle + (wcPvPhoneErrorMap[errorCode] == undefined ? wcPvJson.phoneUnknownErrorMsg : wcPvPhoneErrorMap[errorCode])}`;
    }
    return result;
}
//incase of country change
$(`${wcPvJson.parentPage} #billing_country`).change(function(){
    wcPvPhoneIntl.intlTelInput("setCountry",$(this).val());
});
// Adjust design if true
if( separateDialCode == true ){
    $('.wc-pv-intl').addClass( 'wc-pv-separate-dial-code' );
}
/**
 *  Js validation process
 * @param {object} parentEl the parent element 
 */
function wcPvValidateProcess(parentEl){
    let phoneNumber = wcPvValidatePhone(wcPvPhoneIntl);
    if($('.wc-pv-intl input').length == 0)//doesnt exist, no need
        return;
    
    // Remove errors first, so its not stagnant, special thanks to Sylvain :)
    $('#wc-ls-phone-valid-field-err-msg').remove();

    if(phoneNumber != false){//phone is valid
        $(`${wcPvJson.parentPage} input#billing_phone`).val(phoneNumber);//set the real value so it submits it along
        if($('#wc-ls-phone-valid-field').length == 0){//append
            parentEl.append(`<input id="wc-ls-phone-valid-field" value="${phoneNumber}" type="hidden" name="${wcPvJson.phoneValidatorName}">`);
        }
    //    $('#wc-ls-phone-valid-field-err-msg').remove();
    }
    else{
        if($('#wc-ls-phone-valid-field-err-msg').length == 0){//append
        parentEl.append(`<input id="wc-ls-phone-valid-field-err-msg" value="${wcPvphoneErrMsg}" type="hidden" name="${wcPvJson.phoneValidatorErrName}">`);
        }
        $('#wc-ls-phone-valid-field').remove();
    }
}
//for woocommerce checkout
if(wcPvJson.currentPage == "checkout"){
    let wcPvCheckoutForm = $(`${wcPvJson.parentPage}`);
    wcPvCheckoutForm.on('checkout_place_order',function(){
        wcPvValidateProcess(wcPvCheckoutForm);
    });
}
else if(wcPvJson.currentPage == "account"){//for account page
    let wcPvAccForm = $(`${wcPvJson.parentPage} form`);
    $(`${wcPvJson.parentPage}`).submit(function(){
        wcPvValidateProcess(wcPvAccForm);
    });
}

//});