<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( "manage_options" ) ) {
	wp_die( __( "You do not have sufficient permissions to access this page.", 'idpay-contact-form-7' ) );
}

if ( isset( $_POST['update'] ) ) {
	$options['api_key']         = sanitize_text_field( $_POST['api_key'] );
	$options['return-page-id']  = ( intval( $_POST['return-page-id'] ) );
	$options['return']          = get_page_link( intval( $_POST['return-page-id'] ) );
	$options['success_message'] = sanitize_text_field( $_POST['success_message'] );
	$options['failed_message']  = sanitize_text_field( $_POST['failed_message'] );
    $options['sandbox']         = !empty( $_POST['sandbox'] ) ? 1 : 0;
	$options['currency']        = $_POST['currency'];

	update_option( "idpay_cf7_options", $options );

	echo "<br><div class='updated'><p><strong>". __( "Settings Updated.", 'idpay-contact-form-7' ) ."</strong></p></div>";
}

$options         = get_option( 'idpay_cf7_options' );
$success_message = ( ! empty( $options['success_message'] ) ? $options['success_message'] : __( 'Your payment has been successfully completed. Tracking code: {track_id}', 'idpay-contact-form-7' ) );
$failed_message  = ( ! empty( $options['failed_message'] ) ? $options['failed_message'] : __( 'Your payment has failed. Please try again or contact the site administrator in case of a problem.', 'idpay-contact-form-7' ) );
$return_page_id  = ( ! empty( $options['return-page-id'] ) ? $options['return-page-id'] : 0 );
$api_key         = ( ! empty( $options['api_key'] ) ? $options['api_key'] : "" );
$currency        = $options['currency'];
$checked         = $options['sandbox'] == 1 ? 'checked' : '';
?>

<h2><?php _e( 'IDPay Gateway Settings for the forms created by Contact Form 7', 'idpay-contact-form-7' ) ?></h2>
<form method="post" enctype="multipart/form-data" style="width: 100%;">
    <table id="idpay_main_setting_table">
        <tr>
            <td style="width: 130px;">
                <b><?php _e( 'API KEY', 'idpay-contact-form-7' ) ?></b>
            </td>
            <td>
                <input type="input" name="api_key" size="36" value="<?php echo $api_key ?>">
                <br>
                <?php
                _e( 'You can create an API Key by going to your <a href="https://idpay.ir/dashboard/web-services">web service</a>.', 'idpay-contact-form-7' );
                ?>
            </td>
        </tr>
        <tr>
            <td>
                <b><?php _e( 'Sandbox', 'idpay-contact-form-7' ) ?></b>
            </td>
            <td>
                <input type="checkbox" name="sandbox"
                       value="1" <?php echo $checked ?> >
                <br>
                <?php
                _e( 'If you check this option, the gateway will work in test (sandbox) mode.', 'idpay-contact-form-7' );
                ?>
            </td>
        </tr>
        <tr>
            <td>
                <b><?php _e( 'Return page from transaction', 'idpay-contact-form-7' ) ?></b>
            </td>
            <td>
                <?php
                echo wp_dropdown_pages( [
                    'depth'                 => 0,
                    'child_of'              => 0,
                    'selected'              => $return_page_id,
                    'echo'                  => 0,
                    'name'                  => 'return-page-id',
                    'id'                    => NULL,
                    'class'                 => NULL,
                    'show_option_none'      => NULL,
                    'show_option_no_change' => NULL,
                    'option_none_value'     => NULL,
                ] )
                ?>
                <br>
                <?php
                _e( 'Put short code [idpay_cf7_result] into the selected page. If you do not do this, your transaction will not be verified.', 'idpay-contact-form-7' );
                ?>
            </td>
        </tr>
        <tr>
            <td>
                <b><?php _e( 'Currency:', 'idpay-contact-form-7' ) ?></b>
            </td>
            <td>
                <select name="currency">
                    <option value="rial" <?php selected( $options['currency'], 'rial' ); ?>><?php _e('Rial', 'idpay-contact-form-7') ?></option>
                    <option value="toman" <?php selected( $options['currency'], 'toman' ); ?>><?php _e('Toman', 'idpay-contact-form-7') ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <b><?php _e( 'Successful transaction message:', 'idpay-contact-form-7' ) ?></b>
            </td>
            <td>
                <textarea name="success_message" rows="4" cols="50" dir="auto"><?php esc_html_e( $success_message, 'idpay-contact-form-7' ) ?></textarea>
                <br>
                <?php
                esc_html_e( 'Enter the message you want to display to the customer after a successful payment. You can also choose these placeholders {track_id}, {order_id} for showing the order id and the tracking id respectively.', 'idpay-contact-form-7' );
                ?>
            </td>
        </tr>
        <tr>
            <td>
                <b><?php _e( 'Unsuccessful transaction message:', 'idpay-contact-form-7' ) ?></b>
            </td>
            <td>
                <textarea name="failed_message" rows="4" cols="50" dir="auto"><?php esc_html_e( $failed_message, 'idpay-contact-form-7' ) ?></textarea>
                <br>
                <?php
                esc_html_e( 'Enter the message you want to display to the customer after a failure occurred in a payment. You can also choose these placeholders {track_id}, {order_id} for showing the order id and the tracking id respectively.', 'idpay-contact-form-7' );
                ?>
        </tr>
        <tr>
            <td>
                <input type="submit" name="btn2"
                       class="button-primary"
                       value="<?php _e( 'Save', 'idpay-contact-form-7' ) ?>">
            </td>
        </tr>
    </table>
    <input type="hidden" name="update">
</form>
