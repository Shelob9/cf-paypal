<?php
/**
 * PayPal Express Processor Functions
 *
 * @package   Caldera_Forms_PayPal_Express
 * @author    David Cramer <david@digilab.co.za>
 * @license   GPL-2.0+
 * @link
 * @copyright 2014 David Cramer <david@digilab.co.za>
 */




/**
 * Load the plugin text domain for translation.
 *
 * @since 1.0.0
 */
function cf_paypal_express_load_plugin_textdomain(){
	load_plugin_textdomain( 'cf-paypal-express', FALSE, CF_PAYPAL_EXPRESS_PATH . 'languages');
}



/**
 * Filteres the redirect url and substitutes with PayPal auth if needed.
 *
 * @since 1.0.0
 * @param array		$url			current redirect url
 * @param array		$form			array of the complete form config structure
 * @param array		$config			config array of processor instance
 * @param string	$processid		unique ID if the processor instance
 *
 * @return array	array of altered transient data
 */
function cf_paypal_express_redirect_topaypal($url, $form, $config, $processid){
	global $transdata;
	if(empty($transdata['paypal_express']['checkout']) && !empty($transdata['paypal_express']['request']) && !empty($transdata['paypal_express']['url'])){
		return $transdata['paypal_express']['url'];
	}
	return $url;
}


/**
 * Registers the PayPal Processor
 *
 * @since 1.0.0
 * @param array		$processors		array of current regestered processors
 *
 * @return array	array of regestered processors
 */
function cf_paypal_express_register_processor($processors){

	$processors['paypal_express'] = array(
		"name"				=>	__('PayPal Express', 'cf-paypal-express'),
		"description"		=>	__("Process a payment via PayPal", 'cf-paypal-express'),
		"icon"				=>	CF_PAYPAL_EXPRESS_URL . "icon.png",
		"single"			=>	true,
		"pre_processor"		=>	'cf_paypal_express_setup_payment',
		"processor"			=>	'cf_paypal_express_process_payment',
		"template"			=>	CF_PAYPAL_EXPRESS_PATH . "includes/config.php",
		"magic_tags"		=>	array(
			'ack',
			'transaction_id',
			'currency_code',
			'email'	=>	array(
				'email',
				'text'
			),
			'firstname',
			'lastname',
			'payer_id',
			'payer_status',
			'country_code',
			'name',
			'street',
			'city',
			'state',
			'zip',
			'country_code',
			'country_name',
			'address_status',
			'payment_status',
			'pending_reason',
			'reason_code',
			'checkout_status',
		)
	);
	return $processors;

}


/**
 * Sets up the PayPal tokens in the form submission transient for redirection
 *
 * @since 1.0.0
 * @param array		$transdata		array of the current submission transient
 * @param array		$form			array of the complete form config structure
 * @param array		$referrer		array structure of the referring URL
 * @param string	$processid		unique ID if the processor instance
 *
 * @return array	array of altered transient data
 */
function cf_paypal_express_set_transient($transdata, $form, $referrer, $processid){

	if(!empty($transdata['paypal_express']['checkout'])){

		return $transdata;

	}elseif( isset( $transdata['paypal_express'] ) && $transdata['type'] === 'success' ){

		// setup return urls
		$returnurl = $referrer['scheme'] . '://' . $referrer['host'] . $referrer['path'];
		
		$queryvars = array(
			'cf_tp' => $processid
		);
		if(!empty($referrer['query'])){
			$queryvars = array_merge($referrer['query'], $queryvars);
		}

		// if a request has not happend yet- 
		if(!isset($transdata['paypal_express']['request'])){
			// get settings
			$settings = $transdata['paypal_express']['config'];

			// SETUP token request.
			$request = array(

				// API
				'USER' 								=>	$settings['username'],	//=api_username
				'PWD' 								=>	$settings['password'],	//=api_password
				'SIGNATURE' 						=>	$settings['signature'],	//=api_signature
				'METHOD' 							=>	'SetExpressCheckout',
				'VERSION' 							=>	'116',	//98.0
				'PAYMENTREQUEST_0_PAYMENTACTION' 	=>	'SALE',
				'PAYMENTREQUEST_0_CURRENCYCODE'		=>	$settings['currency'],
				'PAYMENTREQUEST_0_ITEMAMT'			=>	0,
				'L_PAYMENTREQUEST_0_NAME0'			=>	Caldera_Forms::do_magic_tags( $settings['name'] ),
				'L_PAYMENTREQUEST_0_DESC0'			=>	Caldera_Forms::do_magic_tags( $settings['desc'] ),
				'L_PAYMENTREQUEST_0_QTY0'			=>	( !empty( $settings['qty'] ) ? (int) Caldera_Forms::get_field_data( $settings['qty'], $form ) : 1 ),
				'L_PAYMENTREQUEST_0_AMT0'			=>	Caldera_Forms::get_field_data( $settings['price'], $form ),
				'RETURNURL'							=>	$returnurl . '?'.http_build_query( $queryvars ),
				'CANCELURL'							=>	$returnurl . '?'.http_build_query( array_merge($queryvars, array('pp_cancel' => 'true') ) ),

			);

			// set total
			if( $request['L_PAYMENTREQUEST_0_QTY0'] > 1){
				$request['PAYMENTREQUEST_0_AMT'] = $request['PAYMENTREQUEST_0_ITEMAMT']	=	$request['L_PAYMENTREQUEST_0_AMT0'];
				$request['L_PAYMENTREQUEST_0_AMT0']		=	$request['L_PAYMENTREQUEST_0_AMT0'] / $request['L_PAYMENTREQUEST_0_QTY0'];
			}else{
				$request['PAYMENTREQUEST_0_AMT'] = $request['PAYMENTREQUEST_0_ITEMAMT']	= $request['L_PAYMENTREQUEST_0_AMT0'];
			}
			

			// setup checkout type
			//$request['SOLUTIONTYPE'] = 'Sole';
			//$request['LANDINGPAGE'] = 'Billing';

			if(!empty($settings['sandbox'])){
				$url = 'https://api-3t.sandbox.paypal.com/nvp?';
				$link = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=';
			}else{
				$url = 'https://api-3t.paypal.com/nvp?';
				$link = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=';
			}

			// save request URL for later
			$transdata['paypal_express']['request_url'] = $url;
			// save primary request for later
			$transdata['paypal_express']['request'] = $request;

			/**
			 * Filter request body before POSTing to Paypal
			 *
			 * @since 1.1.4
			 *
			 * @param array     $request        Request data
			 * @param array		$transdata		array of the current submission transient
			 * @param array		$form			array of the complete form config structure
			 * @param array		$referrer		array structure of the referring URL
			 * @param string	$processid		unique ID if the processor instance
			 */
			$request = apply_filters( 'cf_paypal_request', $request, $transdata, $form, $referrer, $processid );

			$result = wp_remote_post( $url , array( 'timeout' => 120, 'httpversion' => '1.1', 'body' => $request) );

			// check for error
			if( is_wp_error( $result ) ){
				$transdata['note'] 		= $result->get_error_message();
				$transdata['type'] 		= 'error';
			}else{

				parse_str($result['body'],$data);
				
				// check for ack
				if( isset( $data[ 'ACK' ] ) && strtolower( $data['ACK'] ) === 'success' && isset($data['TOKEN'])){
					// yup
					$transdata['paypal_express']['url'] = $link . $data['TOKEN'];

				}else{
					// push real error
					$transdata['note'] 		= $data['L_LONGMESSAGE0'];
					$transdata['type'] 		= 'error';
				}

			}
		}
	
	}

	return $transdata;
}

/**
 * Processes the actual payment and returns the payment result
 *
 * @since 1.0.0
 * @param array		$config			Config array of the processor
 * @param array		$form			array of the complete form config structure
 *
 * @return array	array of the transaction result
 */
function cf_paypal_express_process_payment($config, $form){
	global $transdata;

	//dump($transdata['paypal_express']);

	if(!empty($transdata['paypal_express']['result'])){
		return $transdata['paypal_express']['result'];
	}

	if( !empty( $transdata['paypal_express']['checkout'] ) && !empty( $transdata['paypal_express']['checkout']['TOKEN'] ) && !empty( $transdata['paypal_express']['checkout']['PAYERID'] ) ){
		
		// complete payment
		$request = $transdata['paypal_express']['request'];
		$request['METHOD'] = 'DoExpressCheckoutPayment';
		$request['TOKEN'] = $transdata['paypal_express']['checkout']['TOKEN'];
		$request['PayerID'] = $transdata['paypal_express']['checkout']['PAYERID'];
		
		// do request
		$result = wp_remote_post( $transdata['paypal_express']['request_url'] , array('timeout' => 120, 'httpversion' => '1.1', 'body' => $request) );
		
		// check for error
		if( is_wp_error( $result ) ){
		
			$transdata['note'] 		= $result->get_error_message();
			$transdata['error']		= true;
		
		}else{

			//payment data
			parse_str($result['body'], $payment);

			$data = $transdata['paypal_express']['checkout'];

			$returns = array(
				'ack'					=>	$data['ACK'],
				'transaction_id'		=>	$payment['PAYMENTINFO_0_TRANSACTIONID'],
				'currency code'			=>	$data['CURRENCYCODE'],
				'payment_status'		=>	$payment['PAYMENTINFO_0_PAYMENTSTATUS'],
				'pending_reason'		=>	$payment['PAYMENTINFO_0_PENDINGREASON'],
				'reason_code'			=>	$payment['PAYMENTINFO_0_REASONCODE'],
				'email'					=>	$data['EMAIL'],
				'firstname'				=>	$data['FIRSTNAME'],
				'lastname'				=>	$data['LASTNAME'],
				'payer_id'				=>	$data['PAYERID'],
				'payer_status'			=>	$data['PAYERSTATUS'],
				'country_code'			=>	$data['COUNTRYCODE'],
				'name'					=>	$data['SHIPTONAME'],
				'street'				=>	$data['SHIPTOSTREET'],
				'city'					=>	$data['SHIPTOCITY'],
				'state'					=>	$data['SHIPTOSTATE'],
				'zip'					=>	$data['SHIPTOZIP'],
				'country_code'			=>	$data['SHIPTOCOUNTRYCODE'],
				'country_name'			=>	$data['SHIPTOCOUNTRYNAME'],
				'address_status'		=>	$data['ADDRESSSTATUS'],
				'checkout_status'		=>	$data['CHECKOUTSTATUS'],
			);

			$transdata['paypal_express']['result'] = $returns;

			return $returns;

		}	
		
	}
}


/**
 * Requests and redirects to PayPal tokens for auth
 *
 * @since 1.0.0
 * @param array		$config			Config array of the processor
 * @param array		$form			array of the complete form config structure
 *
 * @return array	result array and redirect status
 */
function cf_paypal_express_setup_payment($config, $form){
	global $transdata;

	if(!empty($_GET['pp_cancel'])){
		
		if(!empty($transdata['paypal_express'])){
			unset($transdata['paypal_express']);
		}
		
		$return = array(
			'type'	=> 'error',
			'note'	=> 'Transaction has been canceled'
		);
		
		return $return;

	}else{


		// set up checkout if values are set
		if( !empty($_GET['cf_tp']) && !empty($_GET['token']) && !empty($_GET['PayerID']) && empty($transdata['paypal_express']['checkout']) ){
			
			// do an auth
			$request = $transdata['paypal_express']['request'];
			$request['METHOD'] = 'GetExpressCheckoutDetails';
			$request['TOKEN'] = $_GET['token'];
			$request['PayerID'] = $_GET['PayerID'];

			$result = wp_remote_post( $transdata['paypal_express']['request_url'] , array('timeout' => 120, 'httpversion' => '1.1', 'body' => $request) );
			// check for error
			if( is_wp_error( $result ) ){
				$transdata['note'] 		= $result->get_error_message();
				$transdata['type'] 		= 'error';
			}else{

				parse_str($result['body'],$data);					
				$transdata['paypal_express']['checkout'] = $data;

			}

		}

		// check if payment is new 
		if(empty($transdata['paypal_express']['checkout'])){

			// only if a new payment is started.
			$transdata['expire'] = 1200; // set expire to 20 min
			if( isset( $config['paypal_accout_id'] ) ){
				$accounts = get_option( '_cf_account_connections' );
				if( empty( $accounts['paypal'][$config['paypal_accout_id']]['api_username'] ) ){
					$pubnote = __('Internal Error: Sorry, Please try again later.', 'cf-paypal-express');
					if( current_user_can( 'activate_plugins' ) ){
						$pubnote = __('PayPal is setup with Account Connection but the account is missing or has been removed.', 'cf-paypal-express');
					}
					$return = array(
						'type'	=> 'error',
						'note'	=> $pubnote
					);
					return $return;
				}				
				$settings = $config;
				$settings['username'] = $accounts['paypal'][$config['paypal_accout_id']]['api_username'];
				$settings['password'] = $accounts['paypal'][$config['paypal_accout_id']]['api_password'];
				$settings['signature'] = $accounts['paypal'][$config['paypal_accout_id']]['api_secret'];
				$transdata['paypal_express']['config'] = $settings;
			}else{
				$transdata['paypal_express']['config'] = $config;
			}
			$return = array(
				'type'	=> 'success'
			);
			return $return;
		}
	}

}

