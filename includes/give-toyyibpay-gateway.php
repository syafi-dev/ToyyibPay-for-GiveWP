<?php

if (!defined('ABSPATH')) {
  exit;
}

use Give\Helpers\Form\Utils as FormUtils;

class Give_ToyyibPay_Gateway {
  static private $instance;

  const QUERY_VAR           = 'toyyibpay_givewp_return';
  const LISTENER_PASSPHRASE = 'toyyibpay_givewp_listener_passphrase';

  private function __construct() 
  {
// 	  add_filter('give_payment_gateways', 'add_toyyibpay_gateway');
	  
	  $gateway_to_check = 'toyyibpay';
	  if (function_exists('give_is_gateway_active') && give_is_gateway_active($gateway_to_check)) 
	  {
		  add_action( 'give_fields_donation_form_after_personal_info', function( $group ) {
				$group->append(
					give_field( 'text', 'contactNumber' )
						->showInReceipt()
						->minLength(2)
						->label( __( 'Phone Number' ))
						->maxLength(30)
						->placeholder('Phone Number') 
						->storeAsDonorMeta()
						->required() // Could instead be marked as readOnly() (optional)
						->helpText( __( 'This is a field used to add your Phone number') ) 
				);			
			});		  
		  
		  

		}
    add_action('init', array($this, 'return_listener'));
    add_action('give_gateway_toyyibpay', array($this, 'process_payment'));
    add_action('give_toyyibpay_cc_form', array($this, 'give_toyyibpay_cc_form'));
    add_filter('give_enabled_payment_gateways', array($this, 'give_filter_toyyibpay_gateway'), 10, 2);
    $this->give_get_toyyibpay_keys();
  }

  static function get_instance() {
    if (null === static::$instance) {
      static::$instance = new static();
    }

    return static::$instance;
  }

  public function give_filter_toyyibpay_gateway($gateway_list, $form_id) {
    if ((false === strpos($_SERVER['REQUEST_URI'], '/wp-admin/post-new.php?post_type=give_forms')) && $form_id )
	{
		 if (isset($gateway_list['toyyibpay'])) 
		 {
			// Set ToyyibPay as the first item in the list
			$toyyibpay_gateway = $gateway_list['toyyibpay'];
			unset($gateway_list['toyyibpay']);
			$gateway_list = array('toyyibpay' => $toyyibpay_gateway) + $gateway_list;
		}
//       unset($gateway_list['toyyibpay']);
    }
    return $gateway_list;
  }

  private function create_payment($purchase_data) {

    $form_id  = intval($purchase_data['post_data']['give-form-id']);
    $price_id = isset($purchase_data['post_data']['give-price-id']) ? $purchase_data['post_data']['give-price-id'] : '';

    // Collect payment data.
    $insert_payment_data = array(
      'price'           => $purchase_data['price'],
      'give_form_title' => $purchase_data['post_data']['give-form-title'],
      'give_form_id'    => $form_id,
      'give_price_id'   => $price_id,
      'date'            => $purchase_data['date'],
      'user_email'      => $purchase_data['user_email'],
      'purchase_key'    => $purchase_data['purchase_key'],
      'currency'        => give_get_currency($form_id, $purchase_data),
      'user_info'       => $purchase_data['user_info'],
      'status'          => 'pending',
      'gateway'         => 'toyyibpay',
    );

    /**
     * Filter the payment params.
     *
     * @since 3.0.2
     *
     * @param array $insert_payment_data
     */
    $insert_payment_data = apply_filters('give_create_payment', $insert_payment_data);

    // Record the pending payment.
    return give_insert_payment($insert_payment_data);
  }

  public function give_get_toyyibpay_keys() {

		if ( give_is_test_mode() ) {
			// Sandbox.
			$this->userSecretKey  = trim( give_get_option( 'toyyibpay_userSecretKeyDev' ) );
			$this->categoryCode   = trim( give_get_option( 'toyyibpay_categorycodeDev' ) );
		} else {
			// LIVE.
			$this->userSecretKey  = trim( give_get_option( 'toyyibpay_userSecretKey' ) );
			$this->categoryCode   = trim( give_get_option( 'toyyibpay_categorycode' ) );
		}
	}

  private function get_toyyibpay($purchase_data) {
    return array(
      'api_key'           => $this->userSecretKey,
      'category_code'     => $this->categoryCode,
      'description'       => preg_replace("/[^a-zA-Z0-9 ]/", "", give_get_option('toyyibpay_description', true)),
      'phone'             => give_get_option('toyyibpay_contact', true),
      'paymentChannel'    => give_get_option('toyyibpay_paymentchannel', true),
      'paymentCharge'     => give_get_option('toyyibpay_paymentcharge', true),
    );
  }

  public static function get_listener_url($form_id) {
    $passphrase = get_option(self::LISTENER_PASSPHRASE, false);
    if (!$passphrase) {
      $passphrase = md5(site_url() . time());
      update_option(self::LISTENER_PASSPHRASE, $passphrase);
    }

    $arg = array(
      self::QUERY_VAR => $passphrase,
      'form_id'       => $form_id,
    );
    return add_query_arg($arg, site_url('/'));
  }

  public function process_payment($purchase_data) {

    // Validate nonce.
    give_validate_nonce($purchase_data['gateway_nonce'], 'give-gateway');

    $payment_id = $this->create_payment($purchase_data);


    $toyyibpay_key = $this->get_toyyibpay($purchase_data);


    // Check payment.
    if (empty($payment_id) || empty($toyyibpay_key['api_key']) || $toyyibpay_key['api_key'] == 1 || $toyyibpay_key['api_key'] == '1' || empty($toyyibpay_key['category_code']) || $toyyibpay_key['category_code'] == 1 || $toyyibpay_key['category_code'] == '1' ) {
      // Record the error.
      give_record_gateway_error(__('Payment Error', 'give-toyyibpay'), sprintf( /* translators: %s: payment data */
        __('Payment Error. Payment data: %s', 'give-toyyibpay'), json_encode($purchase_data)), $payment_id);
      // Problems? Send back.
      give_send_back_to_checkout();
    }

    $form_id     = intval($purchase_data['post_data']['give-form-id']);

    $name = $purchase_data['user_info']['first_name'] . ' ' . $purchase_data['user_info']['last_name'];
	 
  $toyyibpay = new ToyyibPayGiveAPI($toyyibpay_key['api_key']);

  if (empty($purchase_data['post_data']['give-form-title']) || $purchase_data['post_data']['give-form-title'] == 1 || $purchase_data['post_data']['give-form-title'] == '1') {
		$purchase_data['post_data']['give-form-title'] =  'No Name';
	}
	if (empty($toyyibpay_key['description']) || $toyyibpay_key['description'] == 1 || $toyyibpay_key['description'] == '1') {
    $toyyibpay_key['description'] = 'No description';
    
  if ($toyyibpay_key['paymentChannel'] == 'A') {
    $paymentChannel = '0';
  } elseif ($toyyibpay_key['paymentChannel'] == 'B') {
    $paymentChannel = '1';
  } elseif ($toyyibpay_key['paymentChannel'] == 'C') {
    $paymentChannel = '2';
  }

  if ($toyyibpay_key['paymentCharge'] == 'A') {
    $paymentCharge = '';
  } elseif ($toyyibpay_key['paymentCharge'] == 'B') {
    $paymentCharge = '0';
  } elseif ($toyyibpay_key['paymentCharge'] == 'C') {
    $paymentCharge = '1';
  } elseif ($toyyibpay_key['paymentCharge'] == 'D') {
    $paymentCharge = '2';
  }

  }
  if (empty($toyyibpay_key['extraEmail']) || $toyyibpay_key['extraEmail'] == 1 || $toyyibpay_key['extraEmail'] == '1') {
		$toyyibpay_key['extraEmail'] = '';
  }
	
	$billName ='Form ID #'.$purchase_data['post_data']['give-form-id'];
  $billDescription =$purchase_data['post_data']['give-form-title'] . ' ' . get_bloginfo('name');
	$billPhone= sanitize_text_field( $purchase_data['post_data']['contactNumber'] );
	$parameter = array(
		'userSecretKey'           => trim($toyyibpay_key['api_key']),
		'categoryCode'            => trim($toyyibpay_key['category_code']),
		'billName'                => substr(preg_replace("/[^a-zA-Z0-9 ]/", "", $billName),0,30),
		'billDescription'         => substr(preg_replace("/[^a-zA-Z0-9 ]/", "", $billDescription),0,100),
		'billPriceSetting'        => 1,
		'billPayorInfo'           => 1, 
		'billAmount'              => strval($purchase_data['price'] * 100), 
		'billReturnUrl'           => self::get_listener_url($form_id),
		'billCallbackUrl'         => self::get_listener_url($form_id),
		'billExternalReferenceNo' => $payment_id,
		'billTo'                  => trim($name),
		'billEmail'               => trim($purchase_data['user_email']),
		'billPhone'               => trim($billPhone),
		'billSplitPayment'        => 0,
		'billSplitPaymentArgs'    =>'',
		'billPaymentChannel'		  => $paymentChannel,
		'billDisplayMerchant'		  => 1,
		'billContentEmail'			  => trim($toyyibpay_key['extraEmail']),
    	'billChargeToCustomer'	=> $paymentCharge,
		'billASPCode'				      => 'toyyibPay-V1-GIVEWP-V1.6.0'
  );
  
  
  $createBill = $toyyibpay->createBill($parameter);

    if ($createBill['header'] !== 200) {
      // Record the error.
      give_record_gateway_error(__('Payment Error', 'give-toyyibpay'), sprintf( /* translators: %s: payment data */
        __('Bill creation failed. Error message: %s', 'give-toyyibpay'), json_encode($createBill['body'])), $payment_id);
      // Problems? Send back.
      give_send_back_to_checkout();
    }

    give_update_meta($form_id, 'toyyibpay_id', $createBill['body']['BillCode']);
    give_update_meta($form_id, 'toyyibpay_payment_id', $payment_id);

    give_insert_payment_note($payment_id, "Transaction attempt made with Bill Code: {$createBill['body']['BillCode']}");
    wp_redirect($createBill['body']['BillURL']);
    exit;
  }

  public function give_toyyibpay_cc_form($form_id) 
  {
	  $bc = give_get_meta($form_id, 'toyyibpay_collect_billing', true);
        if (empty($bc)) {
            $bc = give_get_option('toyyibpay_collect_billing');
        }

        if (give_is_setting_enabled($bc)) {
            give_default_cc_address_fields($form_id);
        }

        if (FormUtils::isLegacyForm($form_id)) {
            return false;
        }

        printf(
        '
        <fieldset class="no-fields">
            <div style="display: flex; justify-content: center; margin-top: 20px;">
                <img src="https://wplab.mova.my/givewp1/wp-content/plugins/toyyibPay-For-GiveWP/includes/assets/toyyibpay logo.svg" width=100>
            </div>
            <p style="text-align: center;"><b>%1$s</b></p>
            <p style="text-align: center;">
                <b>%2$s</b> %3$s
            </p>
        </fieldset>
        ',
            esc_html__('', 'toyyibpaygivewp'),
            esc_html__('', 'toyyibpaygivewp'),
            esc_html__('You will be redirected to toyyibPay to select your preferred payment method. After completing the payment process, you will be brought back to this page to view your receipt.', 'toyyibpaygivewp')
        );

        return true;
    
  }

  private function publish_payment($payment_id, $data) {
    if ('publish' !== get_post_status($payment_id)) {
      give_update_payment_status($payment_id, 'publish');
      if ($data['type'] === 'redirect') {
        give_insert_payment_note($payment_id, "Transaction successfull with Bill Code: {$data['billcode']} | Invoice No: {$data['transaction_id']}");
      } else {
        give_insert_payment_note($payment_id, "Transaction successfull with Bill Code: {$data['billcode']} | Invoice No: {$data['transaction_id']}");
      }
    }
  }

  public function return_listener() {
    if (!isset($_GET[self::QUERY_VAR])) {
      return;
    }

    $passphrase = get_option(self::LISTENER_PASSPHRASE, false);
    if (!$passphrase) {
      return;
    }

    if ($_GET[self::QUERY_VAR] != $passphrase) {
      return;
    }

    if (!isset($_GET['form_id'])) {
      exit;
    }
    $form_id = preg_replace('/\D/', '', $_GET['form_id']);

    $custom_donation = give_get_meta($form_id, 'toyyibpay_customize_toyyibpay_donations', true, 'global');
    $status          = give_is_setting_enabled($custom_donation, 'enabled');
	
    if ($status) {
		$api_key = trim(give_get_meta($form_id, 'toyyibpay_api_key', true));
    } else {
		$api_key = trim(give_get_option('toyyibpay_api_key'));
    }
	
	$toyyibpay = new ToyyibPayGiveAPI($api_key);

    try {
	  $data = $toyyibpay->getTransactionData();
    } catch (Exception $e) {
      // status_header(403);
      // exit('Failed X VerificationCode Validation');
    }

    if ($data['billcode'] !== give_get_meta($form_id, 'toyyibpay_id', true)) {
      exit('No ToyyibPay ID found');
    }

    $payment_id = give_get_meta($form_id, 'toyyibpay_payment_id', true);

    if ($data['paid']) {
      $this->publish_payment($payment_id, $data);
    }

    if ($data['type'] === 'redirect') {
      if ($data['paid']) {
        //give_send_to_success_page();
        $return = add_query_arg(array(
          'payment-confirmation' => 'toyyibpay',
          'payment-id'           => $payment_id,
        ), get_permalink(give_get_option('success_page')));
      } else {
        $return = give_get_failed_transaction_uri('?payment-id=' . $payment_id);
      }

      wp_redirect($return);
    }
    exit;
  }

}
Give_ToyyibPay_Gateway::get_instance();