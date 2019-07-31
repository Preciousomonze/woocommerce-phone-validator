/* script */
var $ = jQuery;
//$(document).ready(function(){
//set phone number properly for intl
// here, the index maps to the error code returned from getValidationError 
var wcPvPhoneErrorMap = [ "Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];
//start
var wcPvPhoneIntl = $('.wc-pv-intl input').intlTelInput({
    initialCountry: $(`${wcPvJson.parentPage} #billing_country`).val(),
    /*geoIpLookup: function(callback) {
    $.get('https://ipinfo.io', function() {}, "jsonp").always(function(resp) {
    const countryCode = (resp && resp.country) ? resp.country : "";//asking for payment shaa,smh
    callback(countryCode);
    });
},//to pick user country*/
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
        wcPvphoneErrMsg = `Phone validation error: ${wcPvPhoneErrorMap[errorCode]}`;
    }
    return result;
}
//incase of country change
$(`${wcPvJson.parentPage} #billing_country`).change(function(){
    wcPvPhoneIntl.intlTelInput("setCountry",$(this).val());
});
/**
 *  Js validation process
 * @param {object} parentEl the parent element 
 */
function wcPvValidateProcess(parentEl){
    let phoneNumber = wcPvValidatePhone(wcPvPhoneIntl);
    if(phoneNumber != false){//phone is valid
        $(`${wcPvJson.parentPage} input#billing_phone`).val(phoneNumber);//set the real value so it submits it along
        if($('#wc-ls-phone-valid-field').length == 0){//append
            parentEl.append(`<input id="wc-ls-phone-valid-field" value="${phoneNumber}" type="hidden" name="${wcPvJson.phoneValidatorName}">`);
        }
    }
    else{
        if($('#wc-ls-phone-valid-field-err-msg').length == 0){//append
        parentEl.append(`<input id="wc-ls-phone-valid-field-err-msg" value="${wcPvphoneErrMsg}" type="hidden" name="${wcPvJson.phoneValidatorErrName}">`);
        }
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
    let wcPvAccForm = $(`${wcPvJson.parentPage}`);

    $(`${wcPvJson.parentPage} input#billing_phone`).change(function(){
        wcPvValidateProcess(wcPvAccForm);
    });
}

//});