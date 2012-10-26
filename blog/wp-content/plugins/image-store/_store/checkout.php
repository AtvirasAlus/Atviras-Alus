<?php

/**
 * Checkout information page
 *
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2012
 * @since 1.0.2
 */
// Stop direct access of the file
if (!defined('ABSPATH'))
	die();

$this->subtitutions = array();

//pre populate fields
$fields = array('last_name', 'first_name', 'user_email', 'ims_address', 'ims_city', 'ims_state', 'ims_zip', 'ims_phone');
if (current_user_can('customer') && empty($_POST['enoticecheckout'])) {
	$userdata = wp_get_current_user();
	foreach (array('ims_address', 'ims_city', 'ims_state', 'ims_zip', 'ims_phone') as $field) {
		if (!isset($userdata->$field))
			$userdata->$field = false;
	}
}else {
	foreach ($fields as $key) {
		$userdata->$key = '';
		if (isset($_POST[$key]))
			$userdata->$key = $_POST[$key];
	}
}

$output .= '<form method="post" action="#" class="shipping-info">';

$output .= '<fieldset>';
$output .= '<legend>' . __("Shipping Information", 'ims') . '</legend>';

$output .= '<div class="ims-p user-info">';
$output .= '<label for="first_name">' . __('First Name', 'ims') . ( $this->opts['required_first_name'] ? ' <span class="req">*</span>' : '' ) . ' </label>';
$output .= '<input type="text" name="first_name" id="first_name" value="' . esc_attr($userdata->first_name) . '" class="ims-input" />';
$output .= '<span class="ims-break"></span>';
$output .= '<label for="last_name">' . __('Last Name', 'ims') . ( $this->opts['required_last_name'] ? ' <span class="req">*</span>' : '' ) . ' </label>';
$output .= '<input type="text" name="last_name" id="last_name" value="' . esc_attr($userdata->last_name) . '" class="ims-input"/>';
$output .= '</div><!--.user-info-->';

$output .= '<div class="ims-p email-info">';
$output .= '<label for="user_email">' . __('Email', 'ims') . ( $this->opts['required_user_email'] ? ' <span class="req">*</span>' : '' ) . ' </label>';
$output .= '<input type="text" name="user_email" id="user_email" value="' . esc_attr($userdata->user_email) . '" class="ims-input" />';
$output .= '</div><!--.email-info-->';

$output .= '<div class="ims-p adress-info">';
$output .= '<label for="ims_address">' . __('Address', 'ims') . ( $this->opts['required_ims_address'] ? ' <span class="req">*</span>' : '' ) . ' </label>';
$output .= '<input type="text" name="ims_address" id="ims_address" value="' . esc_attr($userdata->ims_address) . '" class="ims-input" />';
$output .= '<span class="ims-break"></span>';

$output .= '<label for="ims_city">' . __('City', 'ims') . ( $this->opts['required_ims_city'] ? ' <span class="req">*</span>' : '' ) . ' </label>';
$output .= '<input type="text" name="ims_city" id="ims_city" value="' . esc_attr($userdata->ims_city) . '" class="ims-input" />';
$output .= '<span class="ims-break"></span>';

$output .= '<label for="ims_state">' . __('State', 'ims') . ( $this->opts['required_ims_state'] ? ' <span class="req">*</span>' : '' ) . ' </label>';
$output .= '<input type="text" name="ims_state" id="ims_state" value="' . esc_attr($userdata->ims_state) . '" class="ims-input" />';
$output .= '<span class="ims-break"></span>';

$output .= '<label for="ims_zip">' . __('Zip', 'ims') . ( $this->opts['required_ims_zip'] ? ' <span class="req">*</span>' : '' ) . ' </label>';
$output .= '<input type="text" name="ims_zip" id="ims_zip" value="' . esc_attr($userdata->ims_zip) . '" class="ims-input" />';
$output .= '<span class="ims-break"></span>';

$output .= '<label for="ims_phone">' . __('Phone', 'ims') . ( $this->opts['required_ims_phone'] ? ' <span class="req">*</span>' : '' ) . ' </label>';
$output .= '<input type="text" name="ims_phone" id="ims_phone" value="' . esc_attr($userdata->ims_phone) . '" class="ims-input" />';
$output .= '</div>';

$output .= apply_filters('ims_checkout_user_fields', '', $this->cart, $this->opts);

$output .= '<div class="ims-p">';
$output .= '<label for="ims_instructions">' . __('Additional Instructions', 'ims') . ' </label>';
$output .= '<textarea name="instructions" id="ims_instructions" class="ims-instructions">'
		. ( isset($this->cart['instructions']) ? esc_textarea($this->cart['instructions']) : esc_textarea($this->cart['instructions']) ) . '</textarea>';

$output .= '</div>';
$output .= '<div class="ims-p"><small><span class="req">*</span>' . __("Required fields", 'ims') . '</small></div>';
$output .= '</fieldset><!--.shipping-info-->';

$output .= '<fieldset class="order-info">';
$output .= '<legend>' . __("Order Information", 'ims') . '</legend>';
$output .= '<div class="ims-p order-info">';
$output .= '<span class="ims-items"><strong>' . __("Total items: ", 'ims') . '</strong>' . $this->cart['items'] . '</span>';
$output .= '<span class="ims-total"><strong>' . __("Order Total: ", 'ims') . '</strong>' . $this->format_price($this->cart['total']) . '</span>';

$output .= '</div>';

$output .= apply_filters('ims_checkout_order_fields', '', $this->cart, $this->opts);

$output .= '</fieldset><!--.order-info-->';

if (isset($this->opts['shippingmessage']))
	$output .='<div class="shipping-message">' .
			make_clickable(wpautop(stripslashes(preg_replace($this->opts['tags'], $this->subtitutions, $this->opts['shippingmessage'])))) . '</div>';

$output .= '<div class="ims-p submit-buttons">';
$output .= '<input name="cancelcheckout" type="submit" value="' . esc_attr__('Cancel', 'ims') . '" class="secundary" /> ';
$output .= '<input name="enoticecheckout" type="submit" value="' . esc_attr__('Submit Order', 'ims') . '" class="primary" />';

$output .= apply_filters('ims_checkout_actions', '', $this->cart, $this->opts);

$output .= '</div><!--.submit-buttons-->';

$output .='<input type="hidden" name="_wpnonce" id="_wpnonce" value="' . wp_create_nonce("ims_submit_order") . '" />';

$output .= apply_filters('ims_checkout_hidden_fields', '', $this->cart, $this->opts);

$output .= '</form>';