<?php
/**
 * @file Conatins Result class.
 */

namespace IDPay\CF7\Payment;

use IDPay\CF7\ServiceInterface;

/**
 * Class Result
 *
 * Handles reacting on definition of a short code.
 *
 * The short code should be inserted into a page so that a
 * coming transaction can be verified.
 *
 * @package IDPay\CF7\Payment
 */
class Result implements ServiceInterface
{

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        add_shortcode('idpay_cf7_result', array($this, 'handler'));
    }

    /**
     * Reacts on definition of short code 'idpay_cf7_result', whenever it is
     * defined.
     *
     * @param $atts
     *
     * @return string
     */
    public function handler($atts)
    {
        if (!empty($_GET['idpay_cf7_order_id'])) {
            require_once(dirname(__DIR__) . '/Functions.php');
            return fetch_callback_response($_GET['idpay_cf7_order_id']);
        }
        return '<b>' . _e('Transaction not found', 'idpay-contact-form-7') . '</b>';
    }
}
