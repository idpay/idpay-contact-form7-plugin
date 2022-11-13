<?php
/*
Plugin Name: IDPay for Contact Form 7
Description: Integrates IDPay Payment Gateway with Contact Form 7
Author: IDPay
Author URI: https://idpay.ir/
Version: 2.3.2
Text Domain: idpay-contact-form-7
Domain Path: languages
*/

require_once 'vendor/autoload.php';

use IDPay\CF7\Init;
use IDPay\CF7\Plugin;

define( 'CF7_IDPAY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );


/**
 * Load plugin textdomain.
 */
function idpay_contact_form_7_load_textdomain() {
    load_plugin_textdomain( 'idpay-contact-form-7', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'idpay_contact_form_7_load_textdomain' );

include_once(ABSPATH . 'wp-admin/includes/plugin.php');
if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
    Init::call_services();
}

function cf7_idpay_activate() {
    Plugin::activate();
}

function cf7_idpay_deactivate() {
    Plugin::deactivate();
}

add_action( 'plugins_loaded', 'cf7_idpay_update_db', 10, 0 );
function cf7_idpay_update_db() {
    $version = get_option( 'idpay_cf7_version', '1.0' );
    if ( version_compare( $version, '2.1.1' ) < 0 ) {
        Plugin::update();
    }

    if(isset($_GET['cf7_idpay'])){
        if($_GET['cf7_idpay'] == 'callback') {
            require_once( dirname(__FILE__) . '/includes/Callback.php' );
        }
    }
}

$plugin = new IDPay\CF7\Plugin();
register_activation_hook( __FILE__, 'cf7_idpay_activate' );
register_deactivation_hook( __FILE__, 'cf7_idpay_deactivate' );
