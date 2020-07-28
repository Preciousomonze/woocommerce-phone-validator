=== Phone Validator for WooCommerce ===
Contributors: preciousomonze,helgatheviking
Tags: woocommerce,phone number validator,checkout phone validator,account phone validator,woocommerce validation
Requires at least: 4.9
Tested up to: 5.4
Requires PHP: 7.0
License: GNU General Public License v3.0
License URI: https://github.com/Preciousomonze/woocommerce-phone-validator/blob/master/LICENSE
Donate link: https://rave.flutterwave.com/pay/preciousomonze
Stable tag: 1.2.0

Phone Validator for WooCommerce Helps in validating international telephone numbers on woocommerce billing address.

== Description ==
Phone Validator for WooCommerce is a plugin which helps in validating international telephone numbers for woocommerce. It adds a flag dropdown to the Billing phone number field which allows your site users to choose their country base and validate a phone number accordingly on the _checkout_ page and _Billing Account edit_ page of WooCommerce. It forces users to enter a valid phone number before being able to checkout or update their Billing details.

== Suggestions / Feature Request ==

If you have suggestions or a new feature request, feel free to get in touch with me via twitter. follow me on Twitter! **[@preciousomonze](https://twitter.com/preciousomonze)**


== Installation ==

= Automatic Installation =
* 	Login to your WordPress Admin area
* 	Go to "Plugins > Add New" from the left hand menu
* 	In the search box type __Woocommerce Phone Validator__
*	From the search result you will see __Woocommerce Phone Validator__ click on __Install Now__ to install the plugin.
*	After installation, activate the plugin, that's all.


= Manual Installation =
1. 	Download the plugin zip file in the repository or click [__here__](https://github.com/Preciousomonze/woocommerce-phone-validator/releases/)
2. 	Login to your WordPress Admin. Click on "Plugins > Add New" from the left hand menu.
3.  Click on the "Upload" option, then click "Choose File" to select the zip file from your computer. Once selected, press "OK" and press the "Install Now" button.
4.  Activate the plugin. that's all.


== Frequently Asked Questions ==

= What Do I Need To Use The Plugin =

1.	You need to have WooCommerce plugin installed and activated on your WordPress site.

= Can i contribute to the codes of this plugin? =
Yes you can, check out the [github repo here](https://github.com/Preciousomonze/woocommerce-phone-validator/)

== Changelog ==

= 1.0.0 =
* lauhcing first version.
= 1.0.1 =
* Fix: Proper sanitizing
= 1.1.0 =
* Added: Enables validation on Billing Address Page
= 1.1.1 =
* Fix: Compatibility with checkout manager when the checkout fields are altered
= 1.2.0 =
* New: Translation support for error messages, ikr! finally 😂 (I am sincerely sorry).
* New: Enable country code after flag(Operation Separate Dial Code) using filter 'wc_pv_separate_dial_code', set to true ☺️
* New: Ability to set default country using filter 'wc_pv_set_default_country'
* New: Phone field now uses same list of allowed countries in your WooCommerce settings by default ( Thanks to Helgatheviking 🤾🏻‍♀️)
* New: Enable altering list of allowed countries using filter hooks
* Update: Tested up to WordPress 5.4 🥳🥳
* Update: Tested up to WooCommerce 4.3 🥳🥳


== Upgrade notice ==
Translation support.  IKR! finally 😂 (I am sincerely sorry).
New filter hooks to help customize some things, view Changelog for more info.

== Screenshots ==

1. Woocommerce billing phone field includes a flag

2. Error shown to user when an invalid phone number is submitted on checkout

3. Error show to user when an invalid phone number is submitted on the billing address edit page