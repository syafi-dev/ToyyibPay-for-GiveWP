<?php

/**
 * Class Give_ToyyibPay_Settings
 *
 * @since 1.2
 */

 // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_ToyyibPay_Settings' ) ) {

  /**
	 * Class Give_Braintree_Admin_Settings
	 *
	 * @since 1.0.0
	 */
  class Give_ToyyibPay_Settings {
  

	/**
	 * Class Give_ToyyibPay_Admin_Settings
	 *
	 * @since 1.0.0
	 */
    public function __construct() {
    
      add_filter( 'give_get_sections_gateways', array( $this, 'register_sections' ) );
      add_action( 'give_get_settings_gateways', array( $this, 'register_settings' ) );
    }


 		/**
		 * Register Admin Settings.
		 *
		 * @param array $settings List of admin settings.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return array
		 */

    function register_settings( $settings ) {

      switch ( give_get_current_setting_section() ) {
    
        case 'toyyibpay':

          $settings = array(
            array(
              'id'   => 'give_title_toyyibpay',
              'type' => 'title',
            ),
            array(
              'id'   => 'give_title_toyyibpay2',
              'name'    => __('toyyibPay Production Mode Settings', 'give-toyyibpay'),
              'desc' => __( 'Please disable Test Donation Method to use this payment gateway (Recommended to only enabled this payment gateway at a time).', 'give-toyyibpay' ),
              'type'     => 'radio',
            ),
            array(
              'id'   => 'toyyibpay_userSecretKey',
              'name' => __( 'Secret Key', 'give-toyyibpay' ),
              'desc' => __( 'Enter your Secret key found in your account dashboard.', 'give-toyyibpay' ),
              'type' => 'text',
              'size' => 'regular',
            ),
            array(
              'id'   => 'toyyibpay_categorycode',
              'name' => __( 'Category code', 'give-toyyibpay' ),
              'desc' => __( 'Enter your category code. Create new if you have none (in Category menu).', 'give-toyyibpay' ),
              'type' => 'text',
            ),
//             array(
//               'id'   => 'toyyibpay_contact',
//               'name' => __( 'Default phone number', 'give-toyyibpay' ),
//               'desc' => __( 'Since GiveWP has no phone number input field, this phone number will be used for toyyibPay.', 'give-toyyibpay' ),
//               'type' => 'text',
//             ),
//             array(
//                 'id'          => 'toyyibpay_description',
//                 'name'        => __('Bill Description', 'give-toyyibpay'),
//                 'desc'        => __('Enter description to be included in the bill (100 characters only).', 'give-toyyibpay'),
//                 'type'        => 'textarea',
//             ),
            array(
              'id'   => 'toyyibpay_paymentchannel',
              'name' => __( 'Payment Channel', 'give-toyyibpay' ),
              'desc' => __( 'Choose to allow transactions made via Online Banking (FPX) and/or Credit/Debit Card.', 'give-toyyibpay' ),
              'type' => 'select',
              'options' => array(
                      'A' => 'Online Banking (FPX) only',
                      'B' => 'Credit/Debit Card only',
                      'C' => 'Online Banking (FPX) and Credit/Debit Card'
              ),
            ),
            array(
              'id'   => 'toyyibpay_paymentcharge',
              'name' => __( 'Payment Charge', 'give-toyyibpay' ),
              'desc' => __( 'Choose payer for transaction charges.', 'give-toyyibpay' ),
              'type' => 'select',
              'options' => array(
                'A' => 'Charge inculded in Donation amount',
                'B' => 'Online Banking transactions only charge to Donor',
//                 'C' => 'Charge the credit card charges to the customer',
//                 'D' => 'Charge both FPX and credit card charges to the customer'
              ),
            ),
//             array(
//               'id'          => 'toyyibpay_extraemail',
//               'name'        => __('Extra e-mail to donator (optional)', 'give-toyyibpay'),
//               'desc'        => __('Use this if you want to sent extra separate e-mail (Optional).', 'give-toyyibpay'),
//               'type'        => 'textarea',
//               'default'     => '',
//             ),
            array(
              'id'      => 'give_title_toyyibpay2',
              'name'    => __('toyyibPay Development Mode Settings', 'give-toyyibpay'),
              'desc'    => __('This is optional part for testing purpose only. To use development mode, register a new account here : <a href="https://dev.toyyibpay.com">https://dev.toyyibpay.com</a> and enable <b>Test Mode</b>.', 'give-toyyibpay'),
              'type'    => 'radio',
            ),
              array(
                'id'   => 'toyyibpay_userSecretKeyDev',
                'name' => __( 'Dev Secret Key<br>(Optional)', 'give-toyyibpay' ),
                'desc' => __( 'Enter your Secret key found in your dev account dashboard.', 'give-toyyibpay' ),
                'type' => 'text',
                'size' => 'regular',
              ),
              array(
                'id'   => 'toyyibpay_categorycodeDev',
                'name' => __( 'Dev Category code<br>(Optional)', 'give-toyyibpay' ),
                'desc' => __( 'Enter your category code. Create new if you have none (in dev Category menu).', 'give-toyyibpay' ),
                'type' => 'text',
              ),
            array(
              'id'   => 'give_title_toyyibpay',
              'type' => 'sectionend',
            ),
          );
    
          break;
    
      } // End switch().
    
      return $settings;
    }




    /**
		 * Register Section for Gateway Settings.
		 *
		 * @param array $sections List of sections.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return mixed
		 */
    
  		public function register_sections( $sections ) {
      
        $sections['toyyibpay'] = __( 'toyyibPay', 'give-toyyibpay' );
      
  			return $sections;
      }
      
  }
}
new Give_ToyyibPay_Settings;