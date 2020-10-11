<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

/**
 * Shows a configured message when a payment is successful.
 * This message can be configured at the Wordpress dashboard.
 * Also note that the message will be shown
 * if the short code has been inserted in a page.
 *
 * @see \IDPay\CF7\Admin\Menu::admin_table()
 *
 * @param $message
 * @param $track_id
 * @param $order_id
 *
 * @return string
 */
function filled_message( $message, $track_id, $order_id ) {
    return str_replace( [ "{track_id}", "{order_id}" ], [
        $track_id,
        $order_id,
    ], $message );
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
function call_gateway_endpoint( $url, $args ) {
    $number_of_connection_tries = 4;
    while ( $number_of_connection_tries ) {
        $response = wp_safe_remote_post( $url, $args );
        if ( is_wp_error( $response ) ) {
            $number_of_connection_tries --;
            continue;
        } else {
            break;
        }
    }
    return $response;
}

$status    = !empty($_POST['status'])  ? $_POST['status']   : (!empty($_GET['status'])  ? $_GET['status']   : NULL);
$track_id  = !empty($_POST['track_id'])? $_POST['track_id'] : (!empty($_GET['track_id'])? $_GET['track_id'] : NULL);
$id        = !empty($_POST['id'])      ? $_POST['id']       : (!empty($_GET['id'])      ? $_GET['id']       : NULL);
$order_id  = !empty($_POST['order_id'])? $_POST['order_id'] : (!empty($_GET['order_id'])? $_GET['order_id'] : NULL);
$params    = !empty($_POST['id']) ? $_POST : $_GET;

global $wpdb;
$value   = array();
$options = get_option( 'idpay_cf7_options' );
foreach ( $options as $k => $v ) {
    $value[ $k ] = $v;
}

if ( ! empty( $id ) && ! empty( $order_id ) ) {

    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "cf7_transactions WHERE trans_id='%s'", $id ) );
    if ( $row !== NULL ) {
        if ( $row->status == 'completed' ) {
            wp_redirect( add_query_arg( ['status' => 'success', 'message' => filled_message( $value['success_message'], $row->track_id, $row->order_id )], $value['return'] ) );
            exit();
        }
    }

    if ( $status != 10 ) {
        $wpdb->update( $wpdb->prefix . 'cf7_transactions',
            array(
                'status'   => 'failed',
                'track_id' => $track_id,
                'log'  => 'data => <pre>'. print_r($params, true) . '</pre>'
            ),
            array( 'trans_id' => $id ),
            array(
                '%s',
                '%s',
                '%s',
            ),
            array( '%d' )
        );
        wp_redirect( add_query_arg( ['status' => 'failed', 'message' => filled_message( $value['failed_message'], $track_id, $order_id )], $value['return'] ) );
        exit();
    }

    $api_key = $value['api_key'];
    $sandbox = $value['sandbox'] == 1 ? 'true' : 'false';

    $data = array(
        'id'       => $id,
        'order_id' => $order_id,
    );
    $headers = array(
        'Content-Type' => 'application/json',
        'X-API-KEY'    => $api_key,
        'X-SANDBOX'    => $sandbox,
    );
    $args    = array(
        'body'    => json_encode( $data ),
        'headers' => $headers,
        'timeout' => 15,
    );

    $response = call_gateway_endpoint( 'https://api.idpay.ir/v1.1/payment/verify', $args );

    if ( is_wp_error( $response ) ) {
        wp_redirect( add_query_arg( ['status' => 'failed', 'message' => $response->get_error_message()], $value['return'] ) );
        exit();
    }

    $http_status = wp_remote_retrieve_response_code( $response );
    $result      = wp_remote_retrieve_body( $response );
    $result      = json_decode( $result );

    if ( $http_status != 200 ) {
        $message = sprintf( __( 'An error occurred while verifying a transaction. error status: %s, error code: %s, error message: %s', 'idpay-contact-form-7' ), $http_status, $result->error_code, $result->error_message );
        $wpdb->update( $wpdb->prefix . 'cf7_transactions',
            array(
                'status' => 'failed',
                'log'  => $message . '\n data => <pre>'. print_r($params, true) . '</pre>',
            ),
            array( 'trans_id' => $id ),
            array(
                '%s',
                '%s',
            ),
            array( '%d' )
        );

        wp_redirect( add_query_arg( ['status' => 'failed', 'message' => $message], $value['return'] ) );
        exit();
    }

    $verify_status   = empty( $result->status ) ? NULL : $result->status;
    $verify_track_id = empty( $result->track_id ) ? NULL : $result->track_id;
    $verify_id       = empty( $result->id ) ? NULL : $result->id;
    $verify_order_id = empty( $result->order_id ) ? NULL : $result->order_id;
    $verify_amount   = empty( $result->amount ) ? NULL : $result->amount;

    if ( empty( $verify_status ) || empty( $verify_track_id ) || $verify_status < 100 ) {
        $wpdb->update( $wpdb->prefix . 'cf7_transactions',
            array(
                'status'   => 'failed',
                'track_id' => $verify_track_id,
                'log'  => 'verify result => <pre>'. print_r($result, true) . '</pre>',
            ),
            array( 'trans_id' => $verify_id ),
            array(
                '%s',
                '%s',
                '%s',
            ),
            array( '%d' )
        );

        wp_redirect( add_query_arg( ['status' => 'failed', 'message' => filled_message( $value['failed_message'], $verify_track_id, $verify_order_id )], $value['return'] ) );
        exit();
    } else {
        $wpdb->update( $wpdb->prefix . 'cf7_transactions',
            array(
                'status'   => 'completed',
                'track_id' => $verify_track_id,
                'log'  => 'result => <pre>'. print_r($result, true) . '</pre>',
            ),
            array( 'trans_id' => $verify_id ),
            array(
                '%s',
                '%s',
                '%s',
            ),
            array( '%d' )
        );

        wp_redirect( add_query_arg( ['status' => 'success', 'message' => filled_message( $value['success_message'], $verify_track_id, $verify_order_id )], $value['return'] ) );
        exit();
    }
} else {
    wp_redirect( add_query_arg( ['status' => 'failed', 'message' => __( 'Transaction not found', 'idpay-contact-form-7' )], $value['return'] ) );
    exit();
}
