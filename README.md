# Phone Validator for WooCommerce

**Author:** __Preciousomonze__

**Contributors:** __Helgatheviking 🤾🏻‍♀️__


**Donate link:** <a href="https://rave.flutterwave.com/pay/preciousomonze" target="_blank">__Drop something for your boy 🤓 🥳__</a>

**Tags:** woocommerce, preciousomonze plugin, phone validator, intlTelInput, woocommerce phone validator

**Requires at least:** 4.9

**Tested up to:** 5.2

**Stable tag:** 1.2.0

**License:** GPLv3 or later

**License URI:** http://www.gnu.org/licenses/gpl-3.0.html

## Description

Phone Validator for WooCommerce is a plugin which helps in validating international telephone numbers for woocommerce. It adds a flag dropdown to the Billing phone number field which allows your site users to choose their country base and validate a phone number accordingly on the _checkout_ page and _Billing Account edit_ page of WooCommerce. It forces users to enter a valid phone number before being able to checkout or update their Billing details.

#### This plugin is based on https://intl-tel-input.com/ developed by [Jack O'Connor](https://github.com/jackocnr/).

### Suggestions / Feature Request

If you have suggestions or a new feature request, feel free to get in touch with me via twitter. follow me on Twitter! **[@preciousomonze](https://twitter.com/preciousomonze)**


## Installation


### Automatic Installation
* 	Login to your WordPress Admin area
* 	Go to "Plugins > Add New" from the left hand menu
* 	In the search box type __Phone Validator for WooCommerce__
*	From the search result you will see __Phone Validator for WooCommerce__ click on __Install Now__ to install the plugin.
*	After installation, activate the plugin, that's all 🤗.


### Manual Installation
1. 	Download the plugin zip file in the repo or click [__here__](https://github.com/Preciousomonze/woocommerce-phone-validator/releases/)
2. 	Login to your WordPress Admin. Click on "Plugins > Add New" from the left hand menu.
3.  Click on the "Upload" option, then click "Choose File" to select the zip file from your computer. Once selected, press "OK" and press the "Install Now" button.
4.  Activate the plugin. 🤧 that's all.


## Frequently Asked Questions

### What Do I Need To Use The Plugin

1.	You need to have WooCommerce plugin installed and activated on your WordPress site.

## Changelog

### 1.0.0 - 12/07/2019
*   First release 🤗 🥳
### 1.0.1 - 15/07/2019
* Fix: Proper sanitizing
### 1.1.0  - 04/08/2019
* Fix: Made error message a little bit more descriptive
* Fix: Removed appended element when not needed
* Added: Enables validation on Billing Address Page
### 1.1.1 -  08/08/2019
* Fix: Js remove element
* Fix: Compatibility with checkout manager when the checkout fields are altered
* Fix: Validation loop hole
### 1.2.0 - 25/07/2020
* New: Translation support for error messages, ikr! finally 😂 (I am sincerely sorry).
* New: Enable country code after flag(Operation Separate Dial Code) using filter 'wc_pv_separate_dial_code', set to true ☺️
* New: Ability to set default country using filter 'wc_pv_set_default_country'
* New: Phone field now uses same list of allowed countries in your WooCommerce settings by default ( Thanks to Helgatheviking 🤾🏻‍♀️)
* New: Enable altering list of allowed countries using filter hooks
* Update: Tested up to WordPress 5.4 🥳🥳
* Update: Tested up to WooCommerce 4.3 🥳🥳
* Fix: Error Messages not re-rendering due to logic error(Thanks to Sylvain): https://wordpress.org/support/topic/error-messages-are-not-refreshed-in-case-of-successive-errors-on-checkout-page/

## Screenshots ##

### 1. Woocommerce billing phone field includes a flag
###
![Screenshot 1](https://github.com/Preciousomonze/woocommerce-phone-validator/blob/master/assets/images/screenshot-1.PNG)

### 2. Error shown to user when an invalid phone number is submitted on checkout
###
![Screenshot 2](https://github.com/Preciousomonze/woocommerce-phone-validator/blob/master/assets/images/screenshot-2.PNG)

### 3. Error show to user when an invalid phone number is submitted on the billing address edit page
###
![Screenshot 3](https://github.com/Preciousomonze/woocommerce-phone-validator/blob/master/assets/images/screenshot-3.png)
