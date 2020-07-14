<?php

/**
 * @file Contains Admin AdditionalSettingsForm.
 */

namespace IDPay\CF7\Admin;

use IDPay\CF7\ServiceInterface;

/**
 * Class AdditionalSettingsForm
 * Defines a tab beside other tabs in all contact forms in edit mode.
 *
 * @package IDPay\CF7\Admin
 */
class AdditionalSettingsForm implements ServiceInterface {

    /**
     * {@inheritdoc}
     */
    public function register() {
        add_filter( 'wpcf7_editor_panels', array(
            $this,
            'editor_panels',
        ) );
        add_action( 'wpcf7_save_contact_form', array(
            $this,
            'save',
        ), 10, 1 );
        add_action( 'wpcf7_init',
            array(
                $this,
                'idpay_payment_tag',
            ) );
        add_filter( 'wpcf7_validate_payment', array(
            $this,
            'idpay_payment_tag_validation',
        ) , 10, 2);
        add_filter( 'wpcf7_validate_payment*', array(
            $this,
            'idpay_payment_tag_validation',
        ) , 10, 2);
    }

    /**
     * Renders a tab beside other tabs for a contact form in edit mode.
     *
     * @param $cf7
     *   the contact form 7 instance which is passed through the hook
     *   'editor_panels'.
     */
    public function render( $cf7 ) {
        $post_id = sanitize_text_field( $_GET['post'] );
        $enable  = get_post_meta( $post_id, "_idpay_cf7_enable", TRUE );
        $amount  = get_post_meta( $post_id, "_idpay_cf7_amount", TRUE );
        $checked = $enable == "1" ? "CHECKED" : "";
        $options = get_option( 'idpay_cf7_options' );
        $currency = $options['currency'];

        require_once( CF7_IDPAY_PLUGIN_PATH . 'templates/additional-settings-form.php' );
    }

    /**
     * Saves additional settings in the contact form.
     * Hooks into an event when a contact form is going to be saved.
     *
     * @param $cf7
     *   The contact form must be saved.
     */
    public function save( $cf7 ) {
        $post_id = sanitize_text_field( $_POST['post'] );

        //update IDPay options
        if ( ! empty( $_POST['idpay_enable'] ) ) {
            update_post_meta( $post_id, "_idpay_cf7_enable", "1" );
        } else {
            update_post_meta( $post_id, "_idpay_cf7_enable", 0 );
        }
        $amount = sanitize_text_field( $_POST['idpay_amount'] );
        update_post_meta( $post_id, "_idpay_cf7_amount", $amount );

        //update IDPay tags in form text
        $properties = $cf7->get_properties();
        $post_content = $properties['form'];

        //remove default cf7 tag names with name on idpay_amount
        $post_content = preg_replace( '/(\[(text|hidden|acceptance|checkbox|checkbox|radio|count|date|file|number|number|range|quiz|captchac|recaptcha|response|select|textarea))(\* *|  *)(idpay_amount){1}(?!\-)(?!\_)(?![A-Za-z_0-9])/', '$0_'. rand(0, 10), $post_content );

        //handle all conflict possibilities for end user
        $match = [];
        preg_match_all( '/(idpay_amount){1}(| .*)(]){1}/', $post_content, $match );

        if( !empty($match) && !empty($match[0]) ){
            //there should be only one shortcode
            $occurrence = 0;
            foreach( $match[0] as $str ){
                $parts = explode( $str, $post_content );

                //keep the first one and remove the rest
                if( $occurrence == 0 ){
                    //change the shortcodes used in the form if the default amount is set
                    if ( $amount !== "" ){
                        $pos = strpos( $str, 'currency' );
                        if( $pos === false ){
                            $post_content = implode( 'idpay_amount readonly default:post_meta "'. $amount .'"]', $parts );
                        }else{
                            $post_content = implode( 'idpay_amount currency:off readonly default:post_meta "'. $amount .'"]', $parts );
                        }
                    }
                }
                else{
                    $first = '';
                    if( sizeof( $parts ) > 2 ){
                        $first = $parts[0] . $str;
                        array_shift( $parts );
                    }
                    $post_content = $first . implode( ']', $parts );
                }
                $occurrence++;
            }
        }
        $properties['form'] = $post_content;
        $cf7->set_properties( $properties );
    }

    /**
     * Hooks into an event when Contact Form 7 wants to draw all tabs for
     * a contact form. We want to add the ability of using IDPay payment gateway
     * in that contact form. Therefore it use the render() method
     * to draw a new tab beside other tabs in a contact form's edit mode.
     *
     * @param $panels
     *
     * @return array
     */
    public function editor_panels( $panels ) {
        $new_page = array(
            'IDPayPanel' => array(
                'title'    => __( 'IDPay payment', 'idpay-contact-form-7' ),
                'callback' => array( $this, 'render' ),
            ),
        );
        $panels = array_merge( $panels, $new_page );

        return $panels;
    }

    /**
     * Submits new tang name to use in contact form 7
     */
    public function idpay_payment_tag() {
        wpcf7_add_form_tag( array( 'payment', 'payment*' ),
            array( $this, 'idpay_payment_tag_handler' ),
            array( 'name-attr' => true )
        );
    }

    /**
     * Renders a Html form tag in contact form.
     *
     * @param $tag
     *
     * @return String
     */
    public function idpay_payment_tag_handler( $tag ) {
        if ( empty( $tag->name ) ) {
            return '';
        }

        $class = wpcf7_form_controls_class( $tag->type, 'wpcf7-text' );
        $class .= ' wpcf7-validates-as-payment';

        $validation_error = wpcf7_get_validation_error( $tag->name );
        if ( $validation_error ) {
            $class .= ' wpcf7-not-valid';
        }

        $atts = array();
        $atts['size'] 		= $tag->get_size_option( '40' );
        $atts['class'] 		= $tag->get_class_option( $class );
        $atts['id'] 		= $tag->get_id_option();
        $atts['tabindex'] 	= $tag->get_option( 'tabindex', 'signed_int', true );
        $atts['maxlength'] 	= $tag->get_maxlength_option();
        $atts['minlength'] 	= $tag->get_minlength_option();
        $atts['type'] 		= 'number';
        $atts['aria-invalid'] = $validation_error ? 'true' : 'false';

        if ( $atts['maxlength'] and $atts['minlength']
            and $atts['maxlength'] < $atts['minlength'] ) {
            unset( $atts['maxlength'], $atts['minlength'] );
        }

        if ( $tag->has_option( 'readonly' ) ) {
            $atts['readonly'] = 'readonly';
        }

        if ( $tag->is_required() ) {
            $atts['aria-required'] = 'true';
        }

        $value = (string) reset( $tag->values );

        if ( $tag->has_option( 'placeholder' )
            or $tag->has_option( 'watermark' ) ) {
            $atts['placeholder'] = $value;
            $value = '';
        }

        $value = $tag->get_default_option( $value );
        $value = wpcf7_get_hangover( $tag->name, $value );

        $atts['value'] = $value;
        $atts['name'] = $tag->name;

        $atts = wpcf7_format_atts( $atts );

        $idpay_logo = sprintf(
            '<span class="idpay-logo" style="font-size: 12px;padding: 5px 0;"><img src="%1$s" style="display: inline-block;vertical-align: middle;width: 70px;">%2$s</span>',
            plugins_url( '../../assets/logo.svg', __FILE__ ), __( 'Pay with IDPay', 'idpay-contact-form-7' )
        );

        $input = sprintf( '<input %1$s style="max-width: calc(100%% - 60px);"/>', $atts );

        $suffix = $tag->get_option( 'currency' );
        if(!isset($suffix[0]) || 'off' != $suffix[0]){
            //shows the currency for default
            $options = get_option( 'idpay_cf7_options' );
            $suffix  = '<span class="currency idpay-currency" style="position: absolute;top: calc(50% - 12px);left: 5px;">'. __( $options['currency'] == 'rial' ? 'Rial' : 'تومان', 'idpay-contact-form-7' ) .'</span>';
            $input   = '<span class="idpay-input-holder" style="position: relative;display: block;">'. $input . $suffix .'</span>';
        }

        $html = sprintf(
            '<span class="wpcf7-form-control-wrap %1$s">%2$s %3$s %4$s</span>',
            sanitize_html_class( $tag->name ), $input, $validation_error, $idpay_logo
        );

        if( !empty( $_GET['idpay_error'] ) ){
            echo '<div class="alert alert-error idpay-error">'. $_GET['idpay_error'] .'</div>';
            echo '<style>
                .idpay-error{
                    color: #F44336;
                    font-size: 13px;
                    border-right: 2px solid #F44336;
                    padding: 5px 15px;
                }
            </style>';
        }

        return $html;
    }

    /**
     * Validates tag properties
     *
     * @param $result
     * 	validations from other tags
     *
     * @param $tag
     *
     * @return $result
     */
    public function idpay_payment_tag_validation( $result, $tag )
    {
        $name = $tag->name;

        $value = isset($_POST[$name])
            ? trim(wp_unslash(strtr((string)$_POST[$name], "\n", " ")))
            : '';

        if ('' === $value) {
            $result->invalidate($tag, wpcf7_get_message('invalid_required'));
        }
        else {
            $options = get_option( 'idpay_cf7_options' );
            $amount = $options['currency'] == 'rial' ? intval($value) : intval($value) * 10;
            if ( 500000000 < $amount ) {
                $result->invalidate( $tag, sprintf(
                    __( 'amount should be less than %d %s', 'idpay-contact-form-7' ),
                    50000000 * $options['currency'] == 'rial' ? 10 : 1 ,
                    __( $options['currency'] == 'rial' ? 'Rial' : 'Toman', 'idpay-contact-form-7' )
                ) );
            } elseif ( $amount < 1000 ) {
                $result->invalidate( $tag, sprintf(
                    __( 'amount should be greater than %d %s', 'idpay-contact-form-7' ),
                    100 * ( $options['currency'] == 'rial' ? 10 : 1 ),
                    __( $options['currency'] == 'rial' ? 'Rial' : 'Toman', 'idpay-contact-form-7' )
                ) );
            }
        }

        return $result;
    }
}
