=== Title & Descriptions ===

title : IDPay for WP Contact Form 7
Tags: IDPay, contact form 7, form, payment, contact form
Stable tag: 2.3.2
Tested up to: 6.1
Contributors: MimDeveloper.Tv (Mohammad-Malek), imikiani, meysamrazmi, vispa
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

After installing and enabling this plugin, your customers can pay through IDPay gateway.
For doing a transaction through IDPay gateway, you must have an API Key. You can obtain the API Key by going to your [dashboard](https://idpay.ir/dashboard/web-services) in your IDPay [account](https://idpay.ir/user).

== Installation ==

0. After creating a Web Service on https://idpay.ir and getting an API Key, follow this instruction:
1. Go to Contact.
2. Click on IDPay Configuration.
3. Enter your API Key.
4. After configuring the gateway, create a new contact form and add some field you want.
5. Then go to "IDPay payment" tab and Enable Payment through IDPay gateway for that form.
6. If you would like your customer pay a fixed amount, Select the "Predefined amount" checkbox and enter that amount in to opened text field. Also we provide a custom field so that a customer can enter their arbitrary amount in the field. This field is: [payment idpay_amount].

* If you need to use this plugin in Test mode, Select the "Sandbox" checkbox.

== Changelog ==

== 2.3.2, Nov 13, 2022 ==
* Tested Up With Wordpress 6.1 And CF7 Plugin 5.5.6

= 2.3.1, June 18, 2022 =
* First Official Release
* Tested Up With Wordpress 6.0 And CF7 Plugin 5.5.6
* Check Double Spending Correct
* Check Does Not Xss Attack Correct
* Improve Sanitizing
* Change Redirecting Behavior After Payment

= 2.1.4, April 4, 2020 =
* delete unnecessary line of code which could throw error in some installation.

= 2.1.3, October 11, 2020 =
* check GET parameters if POST was empty in relation with IDPay webservices new update.

= 2.1.2, August 5, 2020 =
* change the callback url for permissions problems.

= 2.1.1, July 14, 2020 =
* Change database update function
* unify line indents

= 2.1.0, July 11, 2020 =
* Add log to transactions table.
* Change the callback url in creating transaction so there should be no pending transaction if the user didn't use the [idpay_cf7_result] shortcode anywhere.
* Change the cf7 tag name from text to payment.
* Add currency setting.
* Improve errors handling

= 2.0.1, May 13, 2019 =
* Use wp_safe_remote_post() method instead of curl.
* Try several times to connect to the gateway.

= 2.0, February 18, 2019 =
* Webservice api version 1.1.

= 1.1, December 28, 2018 =
* Translatable strings.
* redesign the plugin.

= 1.0, November 12, 2018 =
* Develope release.
