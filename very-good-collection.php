<?php
/*
Plugin Name: Very Good Collection for Gravity Forms
Plugin URI: http://verygoodcollection.com/
Description: Integrates Gravity Forms with Very Good Collection Payments, enabling end users to purchase goods and services through Gravity Forms.
Version: 1.0
Author: Very Good Collection    
License: GPL-2.0+
Text Domain: gravityformsvgc
*/

if (!defined( 'ABSPATH' )) {
	die();
}

define( 'GF_VGC_VERSION', '1.0' );

add_action( 'gform_loaded', array( 'GF_VGC_Bootstrap', 'load' ), 5 );

class GF_VGC_Bootstrap {

	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_payment_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gf-vgc.php' );

		GFAddOn::register( 'Waf_VGC_GFPaymentAddOn' );
	}
}