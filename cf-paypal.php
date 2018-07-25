<?php
/**
 *
 *
 * @wordpress-plugin
 * Plugin Name: Caldera Forms PayPal
 * Plugin URI: https://github.com/misfist/cf-paypal
 * Description: PayPal checkout processor for Caldera Forms.
 * Version: 1.1.7
 * Author:      Misfit
 * Author URI:  https://github.com/misfist
 * Text Domain: cf-paypal
 * License:     GPL-3.0
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// define constants
define( 'CF_PAYPAL_EXPRESS_PATH',  plugin_dir_path( __FILE__ ) );
define( 'CF_PAYPAL_EXPRESS_URL',  plugin_dir_url( __FILE__ ) );
define( 'CF_PAYPAL_EXPRESS_VER', '1.1.7' );

// Add language text domain
add_action( 'init', 'cf_paypal_express_load_plugin_textdomain' );

// filter to add processor to regestered processors array
add_filter('caldera_forms_get_form_processors', 'cf_paypal_express_register_processor');

// filter to setup processor before form starts processing
add_filter('caldera_forms_submit_return_transient_pre_process', 'cf_paypal_express_set_transient', 10, 4);

// filter to add PayPal auth redirect
add_filter('caldera_forms_submit_return_redirect-paypal_express', 'cf_paypal_express_redirect_topaypal', 10, 4);

// load dependencies
include_once CF_PAYPAL_EXPRESS_PATH . 'vendor/autoload.php';

// pull in the functions file
include CF_PAYPAL_EXPRESS_PATH . 'includes/functions.php';


