<?php
/**
 * Shows a configured message when a payment is successful.
 * This message can be configured at the Wordpress dashboard.
 * Also note that the message will be shown
 * if the short code has been inserted in a page.
 *
 * @param $message
 * @param $track_id
 * @param $order_id
 *
 * @return string
 * @see \IDPay\CF7\Admin\Menu::admin_table()
 *
 */
function filled_message($message, $track_id, $order_id)
{
    return str_replace(["{track_id}", "{order_id}"], [
        $track_id,
        $order_id,
    ], $message);
}

/**
 * @param $db
 * @param $order_id
 * @param $trans_id
 * @param $track_id
 * @param $status
 * @param $message
 * @return void
 */
function create_callback_response($order_id, $status, $message)
{
    global $wpdb;
    $tableName = 'cf7_callbacks';
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . $tableName . " WHERE id='%s'", $order_id));
    if ($row == NULL) {
        $row = [
            'id' => $order_id,
            'response' => json_encode(['status' => $status]),
            'message' => $message,
            'created_at' => time(),
        ];
        $wpdb->insert($wpdb->prefix . $tableName, $row, array('%s', '%s', '%s', '%s'));
    } else {
        $wpdb->update($wpdb->prefix . $tableName,
            array(
                'response' => json_encode(['status' => $status]),
                'message' => $message,
                'created_at' => time(),
            ),
            array('id' => $order_id),
            array('%s', '%s', '%s'),
            array('%s')
        );
    }
}

/**
 * @param $db
 * @param $order_id
 * @return string
 */
function fetch_callback_response($order_id)
{
    global $wpdb;
    $tableName = 'cf7_callbacks';
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . $tableName . " WHERE id='%s'", $order_id));
    if ($row == NULL) {
        return '<b>' . _e('Transaction not found', 'idpay-contact-form-7') . '</b>';
    } else {
        $response = json_decode($row->response);
        $color = $response->status == 'failed' ? '#f44336' : '#8BC34A';
        return '<b style="color:' . $color . ';text-align:center;display: block;">' . $row->message . '</b>';
    }
}

/**
 * Calls the gateway endpoints.
 *
 * Tries to get response from the gateway for 4 times.
 *
 * @param $url
 * @param $args
 *
 * @return array|\WP_Error
 */
function call_gateway_endpoint($url, $args)
{
    $number_of_connection_tries = 4;
    while ($number_of_connection_tries) {
        $response = wp_safe_remote_post($url, $args);
        if (is_wp_error($response)) {
            $number_of_connection_tries--;
            continue;
        } else {
            break;
        }
    }
    return $response;
}

