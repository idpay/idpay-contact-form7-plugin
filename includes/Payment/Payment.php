<?php
/**
 * @file Contains Payment class.
 */

namespace IDPay\CF7\Payment;

use IDPay\CF7\ServiceInterface;

/**
 * Class Payment
 *
 * This class defines a method which will be hooked into an event when
 * a contact form is going to be submitted.
 * In that method we want to redirect to IDPay payment gateway if everything is
 * ok.
 *
 * @package IDPay\CF7\Payment
 */
class Payment implements ServiceInterface
{

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        add_action('wpcf7_mail_sent', array($this, 'after_send_mail'));
    }

    /** Hooks into 'wpcf7_mail_sent'.
     *
     * @param $cf7
     *   the contact form's data which is submitted.
     */
    public function after_send_mail($cf7)
    {
        require_once(dirname(__DIR__) . '/Functions.php');
        global $wpdb;
        global $postid;
        $postid = $cf7->id();

        $enable = get_post_meta($postid, "_idpay_cf7_enable", TRUE);
        if ($enable != "1") {
            return;
        }

        $wpcf7 = \WPCF7_ContactForm::get_current();
        $submission = \WPCF7_Submission::get_instance();

        $phone = '';
        $description = '';
        $amount = '';
        $email = '';
        $name = '';

        if ($submission) {
            $data = $submission->get_posted_data();
            $phone = isset($data['idpay_phone']) ? $data['idpay_phone'] : "";
            $description = isset($data['idpay_description']) ? $data['idpay_description'] : "";
            $amount = isset($data['idpay_amount']) ? $data['idpay_amount'] : "";
            $email = isset($data['your-email']) ? $data['your-email'] : "";
            $name = isset($data['your-name']) ? $data['your-name'] : "";
        }

        $predefined_amount = get_post_meta($postid, "_idpay_cf7_amount", TRUE);
        if ($predefined_amount !== "") {
            $amount = $predefined_amount;
        }

        $options = get_option('idpay_cf7_options');
        foreach ($options as $k => $v) {
            $value[$k] = $v;
        }
        $active_gateway = 'IDPay';
        $url_return = get_home_url() . "?cf7_idpay=callback";

        $row = array();
        $row['form_id'] = $postid;
        $row['trans_id'] = '';
        $row['gateway'] = $active_gateway;
        $row['amount'] = $value['currency'] == 'rial' ? $amount : $amount * 10;
        $row['phone'] = $phone;
        $row['description'] = $description;
        $row['email'] = $email;
        $row['created_at'] = time();
        $row['status'] = 'pending';
        $row['log'] = '';
        $row_format = array(
            '%d',
            '%s',
            '%s',
            '%d',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            "%s",
        );

        $api_key = $value['api_key'];
        $sandbox = $value['sandbox'] == 1 ? 'true' : 'false';
        $amount = intval($amount);
        $desc = $description;

        if (empty($api_key)) {
            $order_id = time();
            $status = 'failed';
            $message = __('IDPay should be configured properly', 'idpay-contact-form-7');
            create_callback_response($order_id, $status, $message);
            wp_redirect(add_query_arg(['idpay_cf7_order_id' => $order_id], get_page_link(intval($options['return-page-id']))));
            exit;
        }

        if (empty($amount)) {
            $order_id = time();
            $status = 'failed';
            $message = __('Amount can not be empty', 'idpay-contact-form-7');
            create_callback_response($order_id, $status, $message);
            wp_redirect(add_query_arg(['idpay_cf7_order_id' => $order_id], get_page_link(intval($options['return-page-id']))));
            exit;
        }

        $data = array(
            'order_id' => time(),
            'amount' => $value['currency'] == 'rial' ? $amount : $amount * 10,
            'name' => $name,
            'phone' => $phone,
            'mail' => $email,
            'desc' => $desc,
            'callback' => $url_return,
        );
        $headers = array(
            'Content-Type' => 'application/json',
            'X-API-KEY' => $api_key,
            'X-SANDBOX' => $sandbox,
        );
        $args = array(
            'body' => json_encode($data),
            'headers' => $headers,
            'timeout' => 15,
        );

        $response = call_gateway_endpoint('https://api.idpay.ir/v1.1/payment', $args);
        if (is_wp_error($response)) {
            $error = $response->get_error_message();
            $order_id = $data['order_id'];
            $row['status'] = 'failed';
            $row['log'] = $error;
            $row['order_id'] = $order_id;
            $wpdb->insert($wpdb->prefix . "cf7_transactions", $row, $row_format);
            $status = 'failed';
            $message = $error;
            create_callback_response($order_id, $status, $message);
            wp_redirect(add_query_arg(['idpay_cf7_order_id' => $order_id], get_page_link(intval($options['return-page-id']))));
            exit();
        }

        $http_status = wp_remote_retrieve_response_code($response);
        $result = wp_remote_retrieve_body($response);
        $result = json_decode($result);

        if ($http_status != 201 || empty($result) || empty($result->id) || empty($result->link)) {
            $error = sprintf('Error : %s (error code: %s)', $result->error_message, $result->error_code);
            $row['status'] = 'failed';
            $row['log'] = $error;
            $wpdb->insert($wpdb->prefix . "cf7_transactions", $row, $row_format);
            $order_id = $data['order_id'];
            $status = 'failed';
            $message = $error;
            create_callback_response($order_id, $status, $message);
            wp_redirect(add_query_arg(['idpay_cf7_order_id' => $order_id], get_page_link(intval($options['return-page-id']))));

        } else {
            // save Transaction ID to Order & Payment
            $row['trans_id'] = $result->id;
            $wpdb->insert($wpdb->prefix . "cf7_transactions", $row, $row_format);
            $order_id = $data['order_id'];
            $status = 'Redirected';
            $message = 'Redirect To IPG';
            create_callback_response($order_id, $status, $message);
            wp_redirect($result->link);
        }
        exit();
    }
}
