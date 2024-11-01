<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

GFForms::include_payment_addon_framework();
add_action( 'wp', array( 'Waf_VGC_GFPaymentAddOn', 'maybe_thankyou_page' ), 5 );
class Waf_VGC_GFPaymentAddOn extends GFPaymentAddOn {

	protected $_version = GF_VGC_VERSION;
	protected $_min_gravityforms_version = '2.5.5.3';
	protected $_slug = 'gravityformsverygoodcollection';
	protected $_path = 'gravityformsverygoodcollection/very-good-collection.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Very Good Collection Add-On';
	protected $_short_title = 'VGC';
	protected $_requires_credit_card = false;
	protected $_supports_callbacks = true;

	// Members plugin integration
	protected $_capabilities = array(
		'gravityforms_verygoodcollection',
		'gravityforms_verygoodcollection_uninstall',
		'gravityforms_verygoodcollection_plugin_page'
	);

	// Permissions
	protected $_capabilities_settings_page = 'gravityforms_verygoodcollection';
	protected $_capabilities_form_settings = 'gravityforms_verygoodcollection';
	protected $_capabilities_uninstall = 'gravityforms_verygoodcollection_uninstall';
	protected $_capabilities_plugin_page = 'gravityforms_verygoodcollection_plugin_page';

	// Automatic upgrade enabled
	protected $_enable_rg_autoupgrade = true;

	/**
	 * @var array $_args_for_deprecated_hooks Will hold a few arrays which are needed by some deprecated hooks, keeping them out of the $authorization array so that potentially sensitive data won't be exposed in logging statements.
	 */
	private $_args_for_deprecated_hooks = array();

	private static $_instance = null;

	public static function get_instance(){
		if ( self::$_instance == null ) {
			self::$_instance = new Waf_VGC_GFPaymentAddOn();
		}

		return self::$_instance;
	}

	//----- SETTINGS PAGES ----------//

	public function plugin_settings_fields() {

		$description = '<p style="text-align: left;">' . esc_html__( 'Very Good Collection is a payment gateway for merchants.', 'gravityformsvgc' ) . '</p>';
		$description .= '<p>' . esc_html__('Our Supported Currencies: ' , 'gravityformsvgc') . '<b>'. $this->get_supported_currencies(true) .'</b></p>';

		return array(
			array(
				'title'       => esc_html__( 'Very Good Collection Account Information', 'gravityformsvgc' ),
				'description' => $description,
				'fields'      => array(
                    array(
	                    'name'        => 'prefix',
	                    'label'       => esc_html__( 'Prefix', 'gravityformsvgc' ),
	                    'description' => esc_html__( 'Please enter a prefix for your invoice numbers. If you use your Very Good Collection account for multiple stores ensure this prefix is unique as Very Good Collection will not allow orders with the same invoice number.', 'gravityformsvgc' ),
	                    'type'        => 'text',
	                    'class'       => 'medium',
	                    'required'    => true
                    ),
                    array(
	                    'name'        => 'merchantKey',
	                    'label'       => esc_html__( 'Merchant Key', 'gravityformsvgc' ),
	                    'description' => __( 'Required: Enter your Merchant Key here. You can get your Public Key from <a href="https://verygoodcollection.com/user/merchant">here</a>', 'gravityformsvgc' ),
	                    'type'        => 'text',
	                    'class'       => 'medium',
	                    'required'    => true
                    ),
                    array(
	                    'name'        => 'publicKey',
	                    'label'       => esc_html__( 'Public Key', 'gravityformsvgc' ),
	                    'description' => __( 'Required: Enter your Public Key here. You can get your Public Key from <a href="https://verygoodcollection.com/user/api">here</a>', 'gravityformsvgc' ),
	                    'type'        => 'text',
	                    'class'       => 'medium',
	                    'required'    => true
                    ),
                    array(
	                    'name'        => 'secretKey',
	                    'label'       => esc_html__( 'Secret Key', 'gravityformsvgc' ),
	                    'description' => __( 'Required: Enter your Secret Key here. You can get your Secret Key from <a href="https://verygoodcollection.com/user/api">here</a>', 'gravityformsvgc' ),
	                    'type'        => 'text',
	                    'class'       => 'medium',
	                    'required'    => true
                    ),
				),
			),
		);
	}

	// Override feed settings fields to remove subscription type
	public function feed_settings_fields() {

		return array(

			array(
				'description' => '',
				'fields'      => array(
					array(
						'name'     => 'feedName',
						'label'    => esc_html__( 'Name', 'gravityforms' ),
						'type'     => 'text',
						'class'    => 'medium',
						'required' => true,
						'tooltip'  => '<h6>' . esc_html__( 'Name', 'gravityforms' ) . '</h6>' . esc_html__( 'Enter a feed name to uniquely identify this setup.', 'gravityforms' )
					),
					array(
						'name'     => 'transactionType',
						'label'    => esc_html__( 'Transaction Type', 'gravityforms' ),
						'type'     => 'select',
						'onchange' => "jQuery(this).parents('form').submit();",
						'choices'  => array(
							array(
								'label' => esc_html__( 'Select a transaction type', 'gravityforms' ),
								'value' => ''
							),
							array(
								'label' => esc_html__( 'Products and Services', 'gravityforms' ),
								'value' => 'product'
							),
						),
						'tooltip'  => '<h6>' . esc_html__( 'Transaction Type', 'gravityforms' ) . '</h6>' . esc_html__( 'Select a transaction type.', 'gravityforms' )
					),
				)
			),
			array(
				'title'      => esc_html__( 'Subscription Settings', 'gravityforms' ),
				'dependency' => array(
					'field'  => 'transactionType',
					'values' => array( 'subscription' )
				),
				'fields'     => array(
					array(
						'name'     => 'recurringAmount',
						'label'    => esc_html__( 'Recurring Amount', 'gravityforms' ),
						'type'     => 'select',
						'choices'  => $this->recurring_amount_choices(),
						'required' => true,
						'tooltip'  => '<h6>' . esc_html__( 'Recurring Amount', 'gravityforms' ) . '</h6>' . esc_html__( "Select which field determines the recurring payment amount, or select 'Form Total' to use the total of all pricing fields as the recurring amount.", 'gravityforms' )
					),
					array(
						'name'    => 'billingCycle',
						'label'   => esc_html__( 'Billing Cycle', 'gravityforms' ),
						'type'    => 'billing_cycle',
						'tooltip' => '<h6>' . esc_html__( 'Billing Cycle', 'gravityforms' ) . '</h6>' . esc_html__( 'Select your billing cycle.  This determines how often the recurring payment should occur.', 'gravityforms' )
					),
					array(
						'name'    => 'recurringTimes',
						'label'   => esc_html__( 'Recurring Times', 'gravityforms' ),
						'type'    => 'select',
						'choices' => array(
							             array(
								             'label' => esc_html__( 'infinite', 'gravityforms' ),
								             'value' => '0'
							             )
						             ) + $this->get_numeric_choices( 1, 100 ),
						'tooltip' => '<h6>' . esc_html__( 'Recurring Times', 'gravityforms' ) . '</h6>' . esc_html__( 'Select how many times the recurring payment should be made.  The default is to bill the customer until the subscription is canceled.', 'gravityforms' )
					),
					array(
						'name'   => 'setupFee',
						'label'  => esc_html__( 'Setup Fee', 'gravityforms' ),
						'type'   => 'setup_fee',
						'hidden' => $this->get_setting( 'trial_enabled' ),
					),
					array(
						'name'    => 'trial',
						'label'   => esc_html__( 'Trial', 'gravityforms' ),
						'type'    => 'trial',
						'hidden'  => $this->get_setting( 'setupFee_enabled' ),
						'tooltip' => '<h6>' . esc_html__( 'Trial Period', 'gravityforms' ) . '</h6>' . esc_html__( 'Enable a trial period.  The user\'s recurring payment will not begin until after this trial period.', 'gravityforms' )
					),
				)
			),
			array(
				'title'      => esc_html__( 'Products &amp; Services Settings', 'gravityforms' ),
				'dependency' => array(
					'field'  => 'transactionType',
					'values' => array( 'product', 'donation' )
				),
				'fields'     => array(
					array(
						'name'          => 'paymentAmount',
						'label'         => esc_html__( 'Payment Amount', 'gravityforms' ),
						'type'          => 'select',
						'choices'       => $this->product_amount_choices(),
						'required'      => true,
						'default_value' => 'form_total',
						'tooltip'       => '<h6>' . esc_html__( 'Payment Amount', 'gravityforms' ) . '</h6>' . esc_html__( "Select which field determines the payment amount, or select 'Form Total' to use the total of all pricing fields as the payment amount.", 'gravityforms' )
					),
				)
			),
			array(
				'title'      => esc_html__( 'Other Settings', 'gravityforms' ),
				'dependency' => array(
					'field'  => 'transactionType',
					'values' => array( 'subscription', 'product', 'donation' )
				),
				'fields'     => $this->other_settings_fields()
			),

		);
	}

	// Get currently supported currencies from very good collection endpoint
	public function get_supported_currencies($string = false) {
		$currency_request = wp_remote_get("https://verygoodcollection.com/api/currency-supported2");
		$currency_array = array();
		if ( ! is_wp_error( $currency_request ) && 200 == wp_remote_retrieve_response_code( $currency_request ) ){
			$currencies = json_decode(wp_remote_retrieve_body($currency_request));
			if($currencies->currency_code && $currencies->currency_name){
				foreach ($currencies->currency_code as $index => $item){
					if($string === true){
						$currency_array[] = $currencies->currency_name[$index];
					}else{
						$currency_array[$currencies->currency_code[$index]] = $currencies->currency_name[$index];
					}
				}
			}
		}
		if($string === true){
			return implode(", ", $currency_array);
		}
		return $currency_array;
	}


	// # TRANSACTIONS --------------------------------------------------------------------------------------------------

	/**
	 * Initialize authorizing the transaction for the Product & Services type feed or return authorization error.
	 *
	 * @since  1.0
	 *
	 * @param array $feed            The Feed object currently being processed.
	 * @param array $submission_data The customer and transaction data.
	 * @param array $form            The Form object currently being processed.
	 * @param array $entry           The Entry object currently being processed.
	 *
	 * @return array
	 */
	public function authorize( $feed, $submission_data, $form, $entry ) {
		// Get billing info field map.
		$billing_map = $this->get_field_map_fields( $feed, 'billingInformation' );
		// Get the plugin settings.
		$settings = $this->is_plugin_settings( $this->get_slug() ) ? $this->get_current_settings() : $this->get_plugin_settings();
		// Prepare vgc args
		$merchant_key = $settings['merchantKey'];
		$public_key = $settings['publicKey'];
		$secret_key = $settings['secretKey'];
		$prefix = $settings['prefix'];
		$tx_ref = $prefix . '_' . $entry['id'];
		$currency = GFCommon::get_currency();
		$currency_array = $this->get_supported_currencies();
		$currency_code = array_search($currency, $currency_array);
		$amount = $submission_data['payment_amount'];
		$first_name = $this->get_field_value( $form, $entry, $billing_map['firstname'] );
		$last_name = $this->get_field_value( $form, $entry, $billing_map['lastname'] );
		$email = $this->get_field_value( $form, $entry, $billing_map['email'] );

		$error_count = 0;
		$error_message = "";

		if (empty($merchant_key) || empty($public_key) || empty($secret_key) || empty($prefix)) {
			$error_message .= "<p style='text-align: left;'>The payment setting of this website is not correct, please contact Administrator</p>";
			$error_count++;
		}

		if (empty($tx_ref)) {
			$error_message .= "<p style='text-align: left;'>It seems that something is wrong with your order. Please try again</p>";
			$error_count++;
		}

		if (empty($amount) || !is_numeric($amount)) {
			$error_message .= "<p style='text-align: left;'>It seems that you have submitted an invalid price for this order. Please try again</p>";
			$error_count++;
		}

		if (empty($email) || !is_email($email)) {
			$error_message .= "<p style='text-align: left;'>Your email is empty or not valid. Please check and try again</p>";
			$error_count++;
		}

		if (!$first_name) {
			$error_message .= "<p style='text-align: left;'>Your first name is empty or not valid. Please check and try again</p>";
			$error_count++;
		}

		if (!$last_name) {
			$error_message .= "<p style='text-align: left;'>Your last name is empty or not valid. Please check and try again</p>";
			$error_count++;
		}

		if (empty($currency_code) || !is_numeric($currency_code)) {
			$error_message .= "<p style='text-align: left;'>The currency code is not valid. Please check and try again</p>";
			$error_count++;
		}

		if ($error_count > 0) {
			add_filter( 'gform_validation_message', function($message, $form) use ( $error_message ) {
				return "<div class='validation_error'>". $error_message . "</div>";
			}, 10, 2 );
			return $this->authorization_error("Error");
		} else {
			return array(
				'is_authorized' => true,
			);
		}
	}

	/**
	 * @param $entry
	 * @param $action
	 *
	 * @return bool
     * Mark the payment as pending
	 */
	public function complete_authorization( &$entry, $action ) {
		$this->add_pending_payment( $entry, $action );
		return true;
	}

	/**
	 * Capture the Gravity Forms Very Good Collection charge which was authorized during validation.
	 *
	 * @since  1.0
	 *
	 * @param array $auth            Contains the result of the authorize() function.
	 * @param array $feed            The Feed object currently being processed.
	 * @param array $submission_data The customer and transaction data.
	 * @param array $form            The Form object currently being processed.
	 * @param array $entry           The Entry object currently being processed.
	 *
	 * @return array
	 */
	public function capture( $auth, $feed, $submission_data, $form, $entry ) {
		// Get billing info field map.
		$billing_map = $this->get_field_map_fields( $feed, 'billingInformation' );
		// Get the plugin settings.
        $settings = $this->is_plugin_settings( $this->get_slug() ) ? $this->get_current_settings() : $this->get_plugin_settings();
		// Prepare vgc args
		$merchant_key = $settings['merchantKey'];
		$public_key = $settings['publicKey'];
		$secret_key = $settings['secretKey'];
		$tx_ref = $settings['prefix'] . '_' . $entry['id'];
		$currency = GFCommon::get_currency();
        $currency_array = $this->get_supported_currencies();
        $currency_code = array_search($currency, $currency_array);
		$amount = $submission_data['payment_amount'];
		$first_name = $this->get_field_value( $form, $entry, $billing_map['firstname'] );
		$last_name = $this->get_field_value( $form, $entry, $billing_map['lastname'] );
		$email = $this->get_field_value( $form, $entry, $billing_map['email'] );
		$title = "Payment For Items on " . get_bloginfo('name');
		$callback_url = $this->get_webhook_url($tx_ref, $secret_key, $entry['id']);
		?>
		<!DOCTYPE html>
			<html>
			<head>
				<title>Very Good Collection Secure Verification</title>
				<style>
					.waf-loading-text {
						position: fixed;
						top: 0;
						left: 0;
						right: 0;
						bottom: 0;
						background: #fff
							url('data:image/gif;base64,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')
							center fixed
							no-repeat;
						z-index: 998;
					}
				</style>
				<script language="Javascript">
					window.onload = function(){
						document.forms['waf_vgc_payment_form'].submit();
					}
				</script>
			</head>
			<body>
			<div>
			</div>
			<h3 class="waf-loading-text"></h3>
			<form id="waf_vgc_payment_form" name="waf_vgc_payment_form" method="POST" action="https://verygoodcollection.com/ext_transfer" >
				<input type="hidden" name="merchant_key" value="<?php esc_attr_e($merchant_key); ?>" />
				<input type="hidden" name="public_key" value="<?php esc_attr_e($public_key);  ?>" />
				<input type="hidden" name="callback_url" value="<?php echo esc_url($callback_url);  ?>" />
				<input type="hidden" name="return_url" value="<?php echo esc_url($callback_url);  ?>" />
				<input type="hidden" name="tx_ref" value="<?php esc_attr_e($tx_ref);  ?>" />
				<input type="hidden" name="amount" value="<?php esc_attr_e($amount);  ?>" />
				<input type="hidden" name="email" value="<?php esc_attr_e($email); ?>" />
				<input type="hidden" name="first_name" value="<?php esc_attr_e($first_name); ?>" />
				<input type="hidden" name="last_name" value="<?php esc_attr_e($last_name); ?>" />
				<input type="hidden" name="title" value="<?php esc_attr_e($title); ?>" />
				<input type="hidden" name="description" value="<?php esc_attr_e($title); ?>" />
				<input type="hidden" name="quantity" value="1" />
				<input type="hidden" name="currency" value="<?php esc_attr_e($currency_code); ?>" />
				<input type="submit" value="submit" style="display: none"/>
			</form>
			</body>
			</html>
		<?php
	}
	/**
	 * Append the phone field to the default billing_info_fields added by the framework.
	 *
	 * @return array
	 */
	public function billing_info_fields() {

		$fields = array(
			array( 'name' => 'firstname', 'label' => esc_html__( 'First Name', 'gravityformsvgc' ),  'required' => false ),
			array( 'name' => 'lastname', 'label' => esc_html__( 'Last Name', 'gravityformsvgc' ), 	 'required' => false ),
			array( 'name' => 'email',    'label' => esc_html__( 'Email', 'gravityformsvgc' ),        'required' => false ),
			array( 'name' => 'address',  'label' => esc_html__( 'Address', 'gravityformsvgc' ),      'required' => false ),
			array( 'name' => 'address2', 'label' => esc_html__( 'Address 2', 'gravityformsvgc' ),    'required' => false ),
			array( 'name' => 'city',     'label' => esc_html__( 'City', 'gravityformsvgc' ),         'required' => false ),
			array( 'name' => 'state',    'label' => esc_html__( 'State', 'gravityformsvgc' ),        'required' => false ),
			array( 'name' => 'zip',      'label' => esc_html__( 'Zip', 'gravityformsvgc' ),          'required' => false ),
			array( 'name' => 'country',  'label' => esc_html__( 'Country', 'gravityformsvgc' ),      'required' => false ),
			array( 'name' => 'phone',    'label' => esc_html__( 'Phone Number', 'gravityformsvgc' ), 'required' => false ),
		);

		return $fields;

	}

	/**
	 * Generate the url Very Good Collection webhooks should be sent to.
	 *
	 * @since  1.0
	 *
	 * @param int $feed_id The feed id.
	 *
	 * @return string The webhook URL.
	 */
	public function get_webhook_url( $tx_ref, $secret_key, $entry_id ) {

		$url = home_url( '/', 'http' ) . '?callback=' . $this->_slug;
		if ( ! rgblank( $tx_ref ) ) {
			$url .= '&tx_ref=' . $tx_ref;
		}
		if ( ! rgblank( $secret_key ) ) {
			$url .= '&secret_key=' . $secret_key;
		}
		if ( ! rgblank( $entry_id ) ) {
			$url .= '&entry_id=' . $entry_id;
		}
		return $url;
	}

	public function callback() {
	    $tx_ref = sanitize_text_field($_GET['tx_ref']);
		$secret_key = sanitize_text_field($_GET['secret_key']);
		$entry_id = intval(sanitize_text_field($_GET['entry_id']));
		$action['entry_id'] = $entry_id;

		$vgc_request = wp_remote_get("https://verygoodcollection.com/api/verify-payment/{$tx_ref}/{$secret_key}");
		
		if (!is_wp_error($vgc_request) && 200 == wp_remote_retrieve_response_code($vgc_request)) {
			$vgc_payment = json_decode(wp_remote_retrieve_body($vgc_request));
			$status = $vgc_payment->status;
			$amount_paid = $vgc_payment->data->amount;
			$reference_id = $vgc_payment->data->reference;
			$action['amount'] = $amount_paid;
			if ($status === 'success') {
				$action['type'] = 'complete_payment';
				$action['transaction_id'] = $reference_id;		
			} elseif ($status === 'cancelled') {
				$action['type'] = 'fail_payment';
				$action['transaction_id'] = $reference_id;
			} else {
				$action['type'] = 'fail_payment';
				$action['transaction_id'] = $reference_id;
			}
		}
		return $action;
	}

	public function post_callback( $callback_action, $result ) {
		if ($result === true) {
			$entry_id = $callback_action['entry_id'];
			$entry = GFAPI::get_entry( $entry_id );
			if ($entry['source_url'] && $entry['form_id']) {
				$form_id = $entry['form_id'];
				$source_url = esc_url($entry['source_url']);
				if ($callback_action['type'] === 'complete_payment') {
					$waf_vgc_gf_return = base64_encode('completed|' . $form_id . "|" . $entry_id);
				} else {
					$waf_vgc_gf_return = base64_encode('failed|' . $form_id . "|" . $entry_id);
				}
				$redirect_to = add_query_arg('waf_vgc_gf_return', $waf_vgc_gf_return, $source_url);
				wp_redirect($redirect_to);
				exit();
			}
		}
	}

	public static function maybe_thankyou_page() {
		$instance = self::get_instance();
		if ( ! $instance->is_gravityforms_supported() ) {
			return;
		}
		if ( $str = rgget( 'waf_vgc_gf_return' ) ) {
			$str = base64_decode( $str );
			list($type, $form_id, $lead_id ) = explode( '|', $str );
			$form = GFAPI::get_form( $form_id );
			$lead = GFAPI::get_entry( $lead_id );
			if ( ! class_exists( 'GFFormDisplay' ) ) {
				require_once( GFCommon::get_base_path() . '/form_display.php' );
			}
			$source_url = esc_url($lead['source_url']);
			if($source_url){
			    $retry_link = " <a href='{$source_url}'>Retry</a>";
			}else{
			    $retry_link = "";
			}
			if ($type === "failed") {
				$confirmation            = '<div class="validation_error gform_validation_error" role="alert"><span style="color: red;">The payment has been failed or cancelled by Very Good Collection</span>' . $retry_link . '</div>';
			} else {
				$confirmation = GFFormDisplay::handle_confirmation( $form, $lead, false );
				if ( is_array( $confirmation ) && isset( $confirmation['redirect'] ) ) {
					header( "Location: {$confirmation['redirect']}" );
					exit;
				}
			}
			GFFormDisplay::$submission[ $form_id ] = array( 'is_confirmation'      => true,
                                                        'confirmation_message' => $confirmation,
                                                        'form'                 => $form,
                                                        'lead'                 => $lead
            );
		}
	}
}