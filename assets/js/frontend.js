/**
 * Frontend Script
 * 
 * @author Precious Omonzejele (CodeXplorer) 
 */

var $ = jQuery;
// $(document).ready(function(){
// set phone number properly for intl
// here, the index maps to the error code returned from getValidationError
var wcPvPhoneErrorMap = wcPvJson.validationErrors;
// start
if ($( '.wc-pv-intl input' ).length == 0) {// add class, some checkout plugin has overriden my baby
    $( '#billing_phone_field' ).addClass( 'wc-pv-phone wc-pv-intl' );
}
// Set default country.
var wcPvDefCountry = ( wcPvJson.defaultCountry == '' ? $( `${wcPvJson.parentPage} #billing_country` ).val() : wcPvJson.defaultCountry );

let separateDialCode = ( wcPvJson.separateDialCode == 1 ? true : false );
let onlyCountries    = wcPvJson.onlyCountries.map( value => { return value.toUpperCase(); } );
// let preferredCountries = wcPvJson.preferredCountries.map( value => { return value.toUpperCase(); } );

var wcPvPhoneIntl = $( '.wc-pv-intl input' ).intlTelInput(
    {
        initialCountry: ( ( wcPvDefCountry == '' || wcPvDefCountry == undefined ) ? 'NG' : wcPvDefCountry ),
        onlyCountries: onlyCountries,
        separateDialCode: separateDialCode,
        preferredCountries: wcPvJson.preferredCountries,
        utilsScript: wcPvJson.utilsScript,
        //autoHideDialCode: true,
        //nationalMode: false,
        /* geoIpLookup: function(callback) {
		$.get('https://ipinfo.io', function() {}, "jsonp").always(function(resp) {
		const countryCode = (resp && resp.country) ? resp.country : '';//asking for payment shaa,smh
		callback(countryCode);
		});
		},//to pick user country */
    }
);

/*if (wcPvJson.userPhone !== undefined ) {
	wcPvPhoneIntl.intlTelInput("setNumber").val(wcPvJson.userPhone);
}*/

// Some Globals.
var wcPvphoneErrMsg = '';

/**
 * Validates the phone number
 *
 * @param intlTelInput input
 * @return string or bool
 */
function wcPvValidatePhone( input ) {
    const phone = input;
    let result  = false;
    if ( phone.intlTelInput( 'isValidNumber' ) == true ) {
        result = phone.intlTelInput( 'getNumber' );
    } else {
        let errorCode   = phone.intlTelInput( 'getValidationError' );
        wcPvphoneErrMsg = `${wcPvJson.phoneErrorTitle + (wcPvPhoneErrorMap[errorCode] == undefined ? wcPvJson.phoneUnknownErrorMsg : wcPvPhoneErrorMap[errorCode])}`;
    }
    return result;
}

// Incase of country change
$( `${wcPvJson.parentPage} #billing_country` ).change(
    function() {
        let value = $( this ).val();

        // Make sure you only set if its in the selected countries
        if ( onlyCountries.includes( value ) ) {
            wcPvPhoneIntl.intlTelInput( 'setCountry', value );
        }
    }
);

// Adjust design if true.
if ( separateDialCode === true ) {
    $( '.wc-pv-intl' ).addClass( 'wc-pv-separate-dial-code' );
}

/**
 *  Js validation process
 *
 * @param {object} parentEl the parent element
 */
function wcPvValidateProcess( parentEl ) {
    let phoneNumber = wcPvValidatePhone( wcPvPhoneIntl );
    if ( $( '.wc-pv-intl input' ).length == 0) { // Doesnt exist, no need.
        return;
    }

    // Remove errors first, so its not stagnant, special thanks to Sylvain :)
    $( '#wc-ls-phone-valid-field-err-msg' ).remove();

    if ( phoneNumber != false ) { // Phone is valid.
        $( `${wcPvJson.parentPage} input#billing_phone` ).val( phoneNumber ); // Set the real value so it submits it along.

        if ( $( '#wc-ls-phone-valid-field' ).length == 0 ) { // Append.
            parentEl.append( ` <input id = "wc-ls-phone-valid-field" value = "${phoneNumber}" type = "hidden" name = "${wcPvJson.phoneValidatorName}" > ` );
        }
    } else {
        parentEl.append( ` <input id = "wc-ls-phone-valid-field-err-msg" value = "${wcPvphoneErrMsg}" type = "hidden" name = "${wcPvJson.phoneValidatorErrName}" > ` );
        $( '#wc-ls-phone-valid-field' ).remove();
    }
}

// For woocommerce checkout.
if ( wcPvJson.currentPage == 'checkout' ) {
    let wcPvCheckoutForm = $( `${wcPvJson.parentPage}` );
    wcPvCheckoutForm.on(
        'checkout_place_order',
        function(){
            wcPvValidateProcess( wcPvCheckoutForm );
        }
    );
} else if ( wcPvJson.currentPage == 'account') { // For account page.
    let wcPvAccForm = $( `${wcPvJson.parentPage} form` );
    $( `${wcPvJson.parentPage}` ).submit(
        function(){
            wcPvValidateProcess( wcPvAccForm );
        }
    );
}

// });
