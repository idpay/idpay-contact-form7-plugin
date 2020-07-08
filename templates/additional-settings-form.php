<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<form>
    <div>
        <input name='idpay_enable' id='idpay_active' value='1' type='checkbox' <?php echo $checked ?>>
        <label for='idpay_active'><?php _e( 'Enable Payment through IDPay gateway', 'idpay-contact-form-7' ) ?></label>
    </div>
    <div>
        <input name='idpay_default_enable' id='idpay_default_active' onclick="active_idpay_amount()" value='1' type='checkbox' <?php echo $amount > 0 ? 'CHECKED' : ''; ?> >
        <label for='idpay_default_active'><?php _e( 'Predefined amount', 'idpay-contact-form-7' ) ?></label>
    </div>
    <table id="idpay_amount_table" style="transition:height .3s ease;overflow:hidden;display:block;height:<?php echo $amount > 0 ? '40px' : '0'; ?>;">
        <tr>
            <td><?php _e( 'Predefined amount', 'idpay-contact-form-7' ) ?></td>
            <td><input id="idpay_amount" type='text' name='idpay_amount' value='<?php echo $amount ?>'></td>
            <td><?php _e( $currency == 'rial' ? 'Rial' : 'Toman', 'idpay-contact-form-7' ) ?></td>
        </tr>
    </table>
    <script>
        function active_idpay_amount(){
            var checkBox = document.getElementById("idpay_default_active");
            var table    = document.getElementById("idpay_amount_table");
            var text     = document.getElementById("idpay_amount");
            if(checkBox.checked != true){
                table.style.height = '0'
                text.value = ''
            }else{
                table.style.height = '40px'
            }
        }
    </script>

    <div>
        <p>
			<?php _e( 'You can choose fields below in your form. If the predefined amount is not empty, field <code>idpay_amount</code> will be ignored. On the other hand, if you want your customer to enter an arbitrary amount, choose <code>idpay_amount</code> in your form and clear the predefined amount.', 'idpay-contact-form-7' ) ?>
        </p>
        <p>
			<?php _e( "Also check your wp-config.php file and look for this line of code: <code>define('WPCF7_LOAD_JS', false)</code>. If there is not such a line, please put it into your wp-config.file.", 'idpay-contact-form-7' ) ?>
        </p>
        <p>
			<?php _e( "You can add your currency as a suffix for your input by using : <code>suffix</code> in your tag. Also all of the contact-form-7 <code>text</code> tag's options are available too.", 'idpay-contact-form-7' ) ?>
        </p>
    </div>

    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'Field', 'idpay-contact-form-7' ) ?></th>
                <th><?php _e( 'Description', 'idpay-contact-form-7' ) ?></th>
                <th><?php _e( 'Example', 'idpay-contact-form-7' ) ?></th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>idpay_amount</td>
                <td><?php _e( 'An arbitrary amount', 'idpay-contact-form-7' ) ?></td>
                <td>
                    <code>[payment idpay_amount]</code>
                    <code>[payment idpay_amount suffix]</code>
                </td>
            </tr>
            <tr>
                <td>idpay_description</td>
                <td><?php _e( 'Payment description', 'idpay-contact-form-7' ) ?></td>
                <td><code>[text idpay_description]</code></td>
            </tr>
            <tr>
                <td>idpay_phone</td>
                <td><?php _e( 'Phone number field', 'idpay-contact-form-7' ) ?></td>
                <td><code>[text idpay_phone]</code></td>
            </tr>
            <tr>
                <td>your-email</td>
                <td><?php _e( 'Email field', 'idpay-contact-form-7' ) ?></td>
                <td><code>[email your-email]</code></td>
            </tr>
            <tr>
                <td>your-name</td>
                <td><?php _e( 'User\'s name field', 'idpay-contact-form-7' ) ?></td>
                <td><code>[text your-name]</code></td>
            </tr>
        </tbody>
    </table>
    <input type='hidden' name='post' value='<?php echo $post_id ?>'>
</form>
