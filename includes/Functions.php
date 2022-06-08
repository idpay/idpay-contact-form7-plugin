<?php
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

function create_callback_response($db,$order_id,$trans_id,$track_id,$status,$message)
{    $tableName = 'cf7_callbacks';
    $row = $db->get_row( $db->prepare( "SELECT * FROM " . $db->prefix . $tableName ." WHERE id='%s'", $order_id ) );
    if ( $row == NULL ) {
        $row = [
            'id' => $order_id,
            'response' => json_encode([
                'trans_id'=>$trans_id,
                'track_id'=>$track_id,
                'status'=>$status
            ]),
            'message' => json_encode($message),
            'created_at' => time(),
        ];
        $db->insert( $db->prefix . $tableName, $row, array('%d','%s','%s','%s') );
    }
    else {
        $db->update( $db->prefix . $tableName,
            array(
                'response' => json_encode([
                    'trans_id'=>$trans_id,
                    'track_id'=>$track_id,
                    'status'=>$status
                ]),
                'message' => json_encode($message),
                'created_at' => time(),
            ),
            array( 'id' => $order_id ),
            array('%s','%s','%s'),
            array( '%d' )
        );
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

