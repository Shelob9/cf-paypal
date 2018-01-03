<?php
/**
 *
 * Copyright 2014 -2017 David Cramer and CalderaWP LLC
 *
 * @wordpress-plugin
 * Plugin Name: Caldera Forms PayPal Express
 * Plugin URI: https://calderaforms.com/downloads/caldera-forms-paypal-express-add-on/
 * Description: PayPal Express checkout processor for Caldera Forms.
 * Version: 1.1.6
 * Author:      Caldera Labs
 * Author URI:  https://calderaforms.com
 * Text Domain: cf-paypal-express
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// define constants
define( 'CF_PAYPAL_EXPRESS_PATH',  plugin_dir_path( __FILE__ ) );
define( 'CF_PAYPAL_EXPRESS_URL',  plugin_dir_url( __FILE__ ) );
define( 'CF_PAYPAL_EXPRESS_VER', '1.1.6' );

// Add language text domain
add_action( 'init', 'cf_paypal_express_load_plugin_textdomain' );

// filter to add processor to regestered processors array
add_filter('caldera_forms_get_form_processors', 'cf_paypal_express_register_processor');

// filter to setup processor before form starts processing
add_filter('caldera_forms_submit_return_transient_pre_process', 'cf_paypal_express_set_transient', 10, 4);

// filter to add PayPal auth redirect
add_filter('caldera_forms_submit_return_redirect-paypal_express', 'cf_paypal_express_redirect_topaypal', 10, 4);

// filter to initialize the license system
add_action( 'init', 'cf_paypal_express_init_license' );

// load dependencies
include_once CF_PAYPAL_EXPRESS_PATH . 'vendor/autoload.php';

// pull in the functions file
include CF_PAYPAL_EXPRESS_PATH . 'includes/functions.php';


function cf_paypal_express_init_license() {
	$plugin = array(
		'name'      => 'Caldera Forms PayPal Express Add-on',
		'slug'      => 'caldera-forms-paypal-express-add-on',
		'url'       => 'https://calderawp.com',
		'version'   => CF_PAYPAL_EXPRESS_VER,
		'key_store' => 'cf_paypal_express_license',
		'file'      => __FILE__
	);

	if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		include_once CF_PAYPAL_EXPRESS_PATH . 'vendor/calderawp/dismissible-notice/src/functions.php';
	}

	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	new \calderawp\licensing_helper\licensing( $plugin );

}


