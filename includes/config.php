<?php
/**
 * PayPal Processor setup template.
 *
 * @package   Caldera_Forms_PayPal_Express
 * @author    David Cramer <david@digilab.co.za>
 * @license   GPL-2.0+
 * @link
 * @copyright 2014 David Cramer <david@digilab.co.za>
 */

if( ! is_ssl() ){
?>
	<div class="error" style="border-left-color: #FF0;">
		<p>
			<?php esc_html_e( 'Your site is not using secure HTTPS. SSL/HTTPS is not required to use PayPal for Caldera Forms, but it is recommended.', 'cf-paypal' ); ?>
		</p>
	</div>
<?php
}// ! is_ssl()
// Check to see if using the CF Account Connections addon to manage accounts
if( class_exists( 'Account_Connections' ) ){
	echo '<div class="caldera-config-group">';
	echo '<label>' . __('PayPal Account', 'cf-paypal-express') . '</label>';
	echo '<div class="caldera-config-field">';
	$accounts = get_option( '_cf_account_connections' );
	?>
		<select class="block-input field-config required" name="{{_name}}[paypal_accout_id]">
		<option value=""></option>
		<?php
		if( !empty( $accounts['paypal'] ) ){
			foreach( (array) $accounts['paypal'] as $index_id=>$paypal ){
				echo '<option value="'.$index_id.'" {{#is paypal_accout_id value="'.$index_id.'"}}selected="selected"{{/is}}>'.$paypal['account_name'].'</option>';
			}
		}
		?>
		</select>
	<?php
	echo '<p style="margin: 3px 0px;"><a href="' . admin_url( 'admin.php?page=account_connections' ) . '" target="_blank">' . __('Setup accounts in Account Connections', 'cf-paypal-express') . '</a></p>';

	echo '</div></div>';
}else{

// Fallback if not using the CF Account Connections addon
?>
<div class="caldera-config-group">
	<label><?php _e('API Username', 'cf-paypal-express'); ?></label>
	<div class="caldera-config-field">		
		<input type="text" id="{{_id}}_username" class="block-input required field-config" name="{{_name}}[username]" value="{{username}}" required>
	</div>
</div>
<div class="caldera-config-group">
	<label><?php _e('API Password', 'cf-paypal-express'); ?></label>
	<div class="caldera-config-field">		
		<input type="text" id="{{_id}}_password" class="block-input required field-config" name="{{_name}}[password]" value="{{password}}" required>
	</div>
</div>
<div class="caldera-config-group">
	<label><?php _e('API Signature', 'cf-paypal-express'); ?></label>
	<div class="caldera-config-field">		
		<input type="text" id="{{_id}}_signature" class="block-input required field-config" name="{{_name}}[signature]" value="{{signature}}" required>
	</div>
</div>


	<div class="caldera-config-group">
		<p class="description">
			<?php esc_html_e( 'This plugin uses PayPal Express, which is also known as the PayPal NVP/SOAP API.', 'cf-paypal' ); ?>
		</p>
		<p class="description">
			<a href="https://developer.paypal.com/docs/classic/api/apiCredentials/#creating-an-api-signature" target="_blank"><?php esc_html_e( 'Learn about getting API credentials here', 'cf-paypal' ); ?></a>
		</p>
		<p class="description">
			<?php esc_html_e( 'Your main API credentials can not be used in test mode.', 'cf-paypal' ); ?>
		</p>
		<p class="description">
			<a href="https://developer.paypal.com/docs/classic/lifecycle/sb_create-accounts/" target="_blank"><?php esc_html_e( 'Learn About Sandbox Credentials Here', 'cf-paypal' ); ?></a>
		</p>
	</div>

<?php } ?>

<div class="caldera-config-group">
	<label for="{{_id}}_sandbox"><?php _e('Sandbox Mode', 'cf-paypal-express'); ?></label>
	<div class="caldera-config-field">
		<input id="{{_id}}_sandbox" type="checkbox" class="field-config" name="{{_name}}[sandbox]" value="1" {{#if sandbox}}checked="checked"{{/if}}>
	</div>
</div>

<div class="caldera-config-group">
	<label><?php _e('Price', 'cf-paypal-express'); ?></label>
	<div class="caldera-config-field">
		{{{_field slug="price" type="calculation,text,hidden" exclude="system"}}}
	</div>
</div>
<div class="caldera-config-group">
	<label><?php _e('Currency', 'cf-paypal-express'); ?></label>
	<div class="caldera-config-field">
		<select id="{{_id}}_currency" class="required field-config" name="{{_name}}[currency]">
			<option value="USD" {{#is currency value="USD"}}selected="selected"{{/is}}>USD</option>
			<option value="AUD" {{#is currency value="AUD"}}selected="selected"{{/is}}>AUD</option>
			<option value="BRL" {{#is currency value="BRL"}}selected="selected"{{/is}}>BRL</option>
			<option value="GBP" {{#is currency value="GBP"}}selected="selected"{{/is}}>GBP</option>
			<option value="CAD" {{#is currency value="CAD"}}selected="selected"{{/is}}>CAD</option>
			<option value="CZK" {{#is currency value="CZK"}}selected="selected"{{/is}}>CZK</option>
			<option value="DKK" {{#is currency value="DKK"}}selected="selected"{{/is}}>DKK</option>
			<option value="EUR" {{#is currency value="EUR"}}selected="selected"{{/is}}>EUR</option>
			<option value="HKD" {{#is currency value="HKD"}}selected="selected"{{/is}}>HKD</option>
			<option value="HUF" {{#is currency value="HUF"}}selected="selected"{{/is}}>HUF</option>
			<option value="ILS" {{#is currency value="ILS"}}selected="selected"{{/is}}>ILS</option>
			<option value="JPY" {{#is currency value="JPY"}}selected="selected"{{/is}}>JPY</option>
			<option value="MXN" {{#is currency value="MXN"}}selected="selected"{{/is}}>MXN</option>
			<option value="TWD" {{#is currency value="TWD"}}selected="selected"{{/is}}>TWD</option>
			<option value="NZD" {{#is currency value="NZD"}}selected="selected"{{/is}}>NZD</option>
			<option value="NOK" {{#is currency value="NOK"}}selected="selected"{{/is}}>NOK</option>
			<option value="PHP" {{#is currency value="PHP"}}selected="selected"{{/is}}>PHP</option>
			<option value="PLN" {{#is currency value="PLN"}}selected="selected"{{/is}}>PLN</option>
			<option value="RUB" {{#is currency value="RUB"}}selected="selected"{{/is}}>RUB</option>
			<option value="SGD" {{#is currency value="SGD"}}selected="selected"{{/is}}>SGD</option>
			<option value="SEK" {{#is currency value="SEK"}}selected="selected"{{/is}}>SEK</option>
			<option value="CHF" {{#is currency value="CHF"}}selected="selected"{{/is}}>CHF</option>
			<option value="THB" {{#is currency value="THB"}}selected="selected"{{/is}}>THB</option>
			<option value="MYR" {{#is currency value="MYR"}}selected="selected"{{/is}}>MYR</option>
			<option value="PHP" {{#is currency value="PHP"}}selected="selected"{{/is}}>PHP</option>
		</select>
	</div>
</div>
<div class="caldera-config-group">
	<label><?php _e('Item Name', 'cf-paypal-express'); ?></label>
	<div class="caldera-config-field">		
		<input type="text" id="{{_id}}_name" class="block-input field-config" name="{{_name}}[name]" value="{{name}}">
	</div>
</div>
<div class="caldera-config-group">
	<label><?php _e('Item Description', 'cf-paypal-express'); ?></label>
	<div class="caldera-config-field">		
		<input type="text" id="{{_id}}_desc" class="block-input field-config" name="{{_name}}[desc]" value="{{desc}}">
	</div>
</div>

<div class="caldera-config-group">
	<label><?php _e('Quantity Field', 'cf-paypal-express'); ?></label>
	<div class="caldera-config-field">
		{{{_field slug="qty" exclude="system"}}}
	</div>
</div>


