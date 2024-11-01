=== WooCommerce Eway Gateway ===
Contributors: eway, woocommerce, automattic, woothemes, royho, akeda, mattyza, bor0, dwainm, laurendavissmith001, mikejolley, kloon, jeffstieler
Tags: credit card, eway, payment request, gateway, woocommerce, automattic
Requires at least: 6.0
Tested up to: 6.2
Stable tag: 3.7.2
Requires PHP: 7.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

This is the official WooCommerce extension to take credit card and subscription payments directly on your store with Eway.

== Description ==

The Eway extension for WooCommerce allows you to take credit card payments directly on your store without redirecting your customers to a third party site to make payment. Supports **WooCommerce Subscriptions, WooCommerce Refunds API**, as well as **token payments**, which allows customers to save credit cards for future purchases. Everything happens on your site without the customer ever leaving.

The Eway payment gateway for WooCommerce makes use of Eway’s brand new Rapid 3.1 API, it supports **3D Secure** and is **fully PCI compliant** as per Eway’s specifications and adds support for processing **subscription payments** as well as **token payments** allowing customers to save credit cards for future purchases.

By using Eway’s Rapid 3.1 API there is a single endpoint for processing payment, meaning you only need this one extension to take payment through any of Eway’s processing countries, Eway Australia, Eway New Zealand, Eway Singapore, Eway Malaysia, and Eway Hong Kong. Eway uses complex DNS technology to ensure your payment is routed to the correct country.

= Key Features =

* Ability to host promotional flash sales in real-time
* Generate discount coupons for your customers to help with special promotions
* Product reviews from your customers
* Automatic up-sells and cross-sells
* Intuitive order management suite

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Install and activate WooCommerce if you haven't already done so
1. For help setting up and configuring, please refer to our [user guide](https://docs.woocommerce.com/document/eway)

== Frequently Asked Questions ==

= Does this require an Eway merchant account? =

Yes! An Eway merchant account, customer API key and customer API password are required for this gateway to function.

= Does this require an SSL certificate? =

An SSL certificate is recommended for additional safety and security for your customers.

= Where do I find my Eway API Key? =

Eway has updated the API setup instructions. Please go to this link for the latest information: https://go.eway.io/s/article/How-do-I-setup-my-Live-Eway-API-Key-and-Password.

= Eway Credit Card option not showing at checkout =

When in live mode, you need to have SSL enabled and your store must be using AUD, NZD, SGD, HKD or MYR as the store currency. You must also have valid API keys for the mode you are using (Sandbox credentials for Sandbox mode; and live credentials for live mode).

= Where can I find a list of error codes and their meanings? =

A list of error codes can be found inside the Eway Rapid 3.1 Documentation. [Download the Eway Rapid 3.1 Documentation](https://eway.io/api-v3/#response-amp-error-codes)

= I am getting a V6018 error code at checkout =

When using Eway, the store currency must match the Eway location you are using. For example, if you’re using Eway Australia you need to have your store currency set to AUD.

= Is 3D Secure supported? =

Yes, it is, as of version 3.0 of the plugin.

= Failed to process your transaction, error code: SOAP-ERROR: Parsing WSDL =

If you get an error that says:

`Failed to process your transaction, error code: SOAP-ERROR: Parsing WSDL: Couldn't load from 'https://api.sandbox.ewaypayments.com/soap.asmx?WSDL'; : failed to load external entity "https://api.sandbox.ewaypayments.com/soap.asmx?WSDL"`

Check that you’re using the correct API key and that the correct password has been entered. If you’re using sandbox mode, be sure to use the API key and password from your Eway Partner Account sandbox account.

= Where can I find documentation? =

For help setting up and configuring, please refer to our [user guide](https://docs.woocommerce.com/document/eway)

= Where can I get support or talk to other users? =

If you get stuck, you can ask for help in the Plugin Forum.

== Changelog ==

= 3.7.2 - 2024-01-18 =
* Add - New configuration option: Connection Method
* Add - New connection method "Responsive shared page" utilizes checkout page hosted by Eway and allows to use Apple Pay and Google Pay

= 3.7.1 - 2023-09-28 =
Add - Enroll saved Eway customer token for Eway 3DS validation.
Add - Implement admin setting to control whether 3-D Secure is enabled.
Dev - Bump WooCommerce "tested up to" version 7.9.
Dev - Bump WooCommerce minimum supported version from 6.8 to 7.7.
Dev - Bump PHP minimum supported version from 7.2 to 7.3.
Dev - Bump WordPress minimum supported version from 5.8 to 6.1.
Dev - Add Playwright end-to-end tests.

= 3.7.0 - 2023-07-31 =
* Add - Implement Eway Secure Fields (SAQ - A).
* Add - Credit card field validation error on the client side.
* Fix - Prevent invalid token customer ID error on Eway subscription renewal.
* Dev - Added new GitHub Workflow to run Quality Insights Toolkit tests.

= 3.6.3 - 2023-07-20 =
* Fix - Include build directory.

= 3.6.2 - 2023-07-04 =
* Tweak - Bump WC tested up to version to 7.6
* Tweak - Bump WP tested up to version to 6.2
* Tweak - Bump minimum WP version to 5.8

= 3.6.1 - 2023-04-04 =
* Dev – Bump PHP minimum supported version from 7.0 to 7.2.
* Dev – Bump WooCommerce minimum supported version from 6.0 to 6.8.
* Dev – Bump WooCommerce “tested up to” version 7.4.
* Fix - Handle failed order correctly with the following successful transaction

= 3.6.0 - 2023-03-13 =
* Fix - Prevent "missing invalid token ID" error when renewing subscriptions.

= 3.5.2 - 2023-01-09 =
* Fix – Trigger 3D Secure check for subscription payments.
* Update – Bump our Node support to v16.
* Update – Bump our NPM support to v8.

= 3.5.0 - 2022-10-31 =
* Add - Declare support for High-performance Order Systems ("HPOS").

= 3.4.5 - 2022-10-04 =
* Add - Support for High-performance Order Storage ("HPOS") (formerly known as Custom Order Tables, "COT").

= 3.4.4 - 2022-08-15 =
* Tweak - Bump minimum WP version to 5.6
* Tweak - Bump minimum PHP version to 7.0
* Tweak - Bump minimum WC version to 6.0
* Tweak - Bump WC tested up to version to 6.7
* Fix - Prevent code duplication via refactoring

= 3.4.3 - 2022-07-05 =
* Fix - Saved payment information for newly created customers during checkout flow
* Update - Bump WP and WC tested up to versions

= 3.4.2 - 2022-05-04 =
* Update - Bump tested up to
* Fix - Fixed PHP notice when checking API credentials

= 3.4.1 - 2022-01-19 =
* Fix - Fatal error when PHP version is older than 7.3.
* Update - Require WC 3.8, PHP 5.6.

= 3.4.0 - 2022-01-18 =
* New - Add credit card icons on checkout page.
* Update - Remove Laser from allowable card types.
* Fix - Show error notices immediately on checkout page.
* Fix - Show error when store uses unsupported currency.
* New - Add support for WC Payment Token API.
* Update - Require WC 3.0, WCS 2.0.

= 3.3.0 - 2021-08-17 =
* Update - Actualize Eway brand name in documentation and source code.
* Update - Bumped WordPress and WooCommerce tested up to versions.

= 3.2.2 - 2021-06-01 =
* Fix - Fatal error when updating payment for all subscriptions.

= 3.2.1 - 2021-05-17 =
* Fix - Fatal error when PHP version is older than 7.3.

= 3.2.0 - 2021-05-13 =
* New - Add support for Cart and Checkout blocks.
* Fix - Do not show Eway as a payment option if API key or password are not set up.
* Update - Bump WP and WC tested up to versions.

= 3.1.25 - 2020-11-19 =
* Fix - Allow all-zero CVNs to be used during checkout.
* Tweak - PHP 8 compatibility.
* Tweak - jQuery 3.5 Compatibility.
* Fix - Use https-protocol for RedirectURL if possible.
* Tweak - Limit the character lengths of values in the checkout to conform with the Eway API.
* Fix - Log debug output into log file instead of displaying during checkout.

= 3.1.24 - 2020-04-06 =
* Fix    - Deprecated notice when viewing a subscription.
* Tweak  - WC 4.0 compatibility.
* Tweak  - WP 5.4 compatibility.

= 3.1.23 - 2019-10-29 =
* Fix    - Use order currency instead of store currency.
* Tweak  - WC 3.8 compatibility.
* Tweak  - WP 5.3 compatibility.

= 3.1.22 - 2019-08-09 =
* Tweak  - WC 3.7 compatibility.

= 3.1.21 - 2019-07-02 =
* Tweak  - Add JCB to card types.

= 3.1.20 - 2019-04-16 =
* Tweak  - WC 3.6 compatibility.

= 3.1.19 - 2018-11-19 =
* Update - WP 5.0 compatibility.

= 3.1.18 - 2018-10-17 =
* Update - Add settings link
* Update - WC 3.5 compatibility.

= 3.1.17 - 2018-08-21 =
* Fix    - Store Host IP is captured/Depicted as customer IP address on Eway site.

= 3.1.16 - 2018-05-22 =
* Update - Privacy policy notification.
* Update - Export/erasure hooks added.
* Update - WC 3.4 compatibility.

= 3.1.15 - 2018-05-02 =
* Update - WP tested up to version.
* Fix - coding standards.
* Fix - nonce usage, input sanitization, output escaping.
