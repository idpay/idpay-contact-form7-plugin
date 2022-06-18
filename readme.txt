=== IDPay for Contact Form 7 ===
Contributors: JMDMahdi, imikiani, meysamrazmi, vispa, Mohammad-Malek
Tags: IDPay, contact form 7, form, payment, contact form
Stable tag: 2.2.0
Tested up to: 6.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

IDPay payment gateway for Contact Form 7

== Description ==

After installing and enabling this plugin, you can create a custom form in which a customer can enter her arbitrary amount an pay through IDPay gateway. Or you can configure that form so that a predefined amount is payable.

For doing a transaction through IDPay gateway, you must have an API Key. You can obtain the API Key by going to your [dashboard](https://idpay.ir/dashboard/web-services) in your IDPay [account](https://idpay.ir/user).

== Installation ==

After creating a web service on https://idpay.ir and getting an API Key, follow this instruction:

1. Go to Contact.
2. Click on IDPay Configuration.
3. Enter your API Key.
4. After configuring the gateway, create a new contact form and add some field you want.
5. Then go to "IDPay payment" tab and Enable Payment through IDPay gateway for that form.
6. If you would like your customer pay a fixed amount, Select the "Predefined amount" checkbox and enter that amount in to opened text field. Also we provide a custom field so that a customer can enter their arbitrary amount in the field. This field is: [payment idpay_amount].

If you need to use this plugin in Test mode, Select the "Sandbox" checkbox.

Also there is a complete documentation [here](https://blog.idpay.ir/helps/103) which helps you to install the plugin step by step.

Thank you so much for using IDPay Payment Gateway.

== Changelog ==

= 2.2.0, June 13, 2022 =
	* Improve sanitizing
	* Change redirecting behavior after payment

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
* First release.
