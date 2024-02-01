<?php

class Give_ToyyibPay_Settings_Metabox {
  static private $instance;

  private function __construct() {

  }

  static function get_instance() {
    if (null === static::$instance) {
      static::$instance = new static();
    }

    return static::$instance;
  }

  /**
   * Setup hooks.
   */
  public function setup_hooks() {
    if (is_admin()) {
      add_action('admin_enqueue_scripts', array($this, 'enqueue_js'));
      add_filter('give_forms_toyyibpay_metabox_fields', array($this, 'give_toyyibpay_add_settings'));
      add_filter('give_metabox_form_data_settings', array($this, 'add_toyyibpay_setting_tab'), 0, 1);
    }
  }

  public function add_toyyibpay_setting_tab($settings) {
    if (give_is_gateway_active('toyyibpay')) {
      $settings['toyyibpay_options'] = apply_filters('give_forms_toyyibpay_options', array(
        'id'        => 'toyyibpay_options',
        'title'     => __('toyyibPay', 'give-toyyibpay'),
        'icon-html' => '<span class="give-icon give-icon-purse"></span>',
        'fields'    => apply_filters('give_forms_toyyibpay_metabox_fields', array()),
      ));
    }

    return $settings;
  }

  public function give_toyyibpay_add_settings($settings) {

    // Bailout: Do not show offline gateways setting in to metabox if its disabled globally.
    if (in_array('toyyibpay', (array) give_get_option('gateways'))) {
      return $settings;
    }

    $is_gateway_active = give_is_gateway_active('toyyibpay');

    //this gateway isn't active
    if (!$is_gateway_active) {
      //return settings and bounce
      return $settings;
    }

    //Fields
    $check_settings = array(
      array(
        'name'    => __('toyyibPay', 'give-toyyibpay'),
        'desc'    => __('Do you want to customize the settings for this form?', 'give-toyyibpay'),
        'id'      => 'toyyibpay_customize_toyyibpay_donations',
        'type'    => 'radio_inline',
        'default' => 'global',
        'options' => apply_filters('give_forms_content_options_select', array(
          'global'   => __('Global Option', 'give-toyyibpay'),
          'enabled'  => __('Customize', 'give-toyyibpay'),
          'disabled' => __('Disable', 'give-toyyibpay'),
        )
        ),
      ),
      array(
        'id'          => 'give_title_toyyibpay2',
        'name'        => __('toyyibPay Production Mode Settings', 'give-toyyibpay'),
        'desc'        => __( 'Please disable Test Donation Method to use this payment gateway (Recommended to only enabled this payment gateway at a time).', 'give-toyyibpay' ),
        'type'        => 'radio_inline',
        'row_classes' => 'give-toyyibpay-key',
      ),
      array(
        'name'        => __('User Secretkey (required)', 'give-toyyibpay'),
        'desc'        => __('Enter your User Secret Key, found in your toyyibPay Account Settings.', 'give-toyyibpay'),
        'id'          => 'toyyibpay_userSecretKey',
        'type'        => 'text',
        'row_classes' => 'give-toyyibpay-key',
      ),
      array(
        'name'        => __('Category Code (required)', 'give-toyyibpay'),
        'desc'        => __('Enter your category code. Create new if you have none (in Category menu).', 'give-toyyibpay'),
        'id'          => 'toyyibpay_categorycode',
        'type'        => 'text',
        'row_classes' => 'give-toyyibpay-key',
      ),
      
//       array(
//         'id'          => 'toyyibpay_description',
//         'name'        => __('Bill Description', 'give-toyyibpay'),
//         'desc'        => __('Enter description to be included in the bill (100 characters only).', 'give-toyyibpay'),
//         'type'        => 'textarea',
//         'row_classes' => 'give-toyyibpay-key',
//       ),
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
        'row_classes' => 'give-toyyibpay-key',
      ),
      array(
        'id'   => 'toyyibpay_paymentcharge',
        'name' => __( 'Payment Charge', 'give-toyyibpay' ),
        'desc' => __( 'Choose payer for transaction charges.', 'give-toyyibpay' ),
        'type' => 'select',
        'options' => array(
          'A' => 'Charge inculded in Donation amount',
          'B' => 'Online Banking transactions only charge to Donor',
//           'C' => 'Charge the credit card charges to the customer',
//           'D' => 'Charge both FPX and credit card charges to the customer'
        ),
        'row_classes' => 'give-toyyibpay-key',
      ),
		array(
			'name' => esc_html__('Billing Fields', 'toyyibpaygivewp'),
			'desc' => esc_html__('This option will enable the billing details section for toyyibpaygivewp  which requires the donor\'s address to complete the donation. These fields are not required by toyyibpaygivewp to process the transaction, but you may have the need to collect the data.', 'toyyibpaygivewp'),
			'id' => 'securepay_collect_billing',
			'type' => 'radio_inline',
			'default' => 'disabled',
			'options' => [
				'enabled' => esc_html__('Enabled', 'toyyibpaygivewp'),
				'disabled' => esc_html__('Disabled', 'toyyibpaygivewp'),
			],
		),
//       array(
//         'id'          => 'toyyibpay_extraemail',
//         'name'        => __('Extra e-mail to donator (optional)', 'give-toyyibpay'),
//         'desc'        => __('Use this if you want to sent extra separate e-mail (Optional).', 'give-toyyibpay'),
//         'type'        => 'textarea',
//         'default'     => '',
//         'row_classes' => 'give-toyyibpay-key',
//       ),
      array(
        'id'      => 'give_title_toyyibpay2',
        'name'    => __('toyyibPay Development Mode Settings', 'give-toyyibpay'),
        'desc'    => __('This is optional part for testing purpose only. To use development mode, register a new account here : <a   href="https://dev.toyyibpay.com">https://dev.toyyibpay.com</a> and enable <b>Test Mode</b>.', 'give-toyyibpay'),
        'type'    => 'radio_inline',
        'row_classes' => 'give-toyyibpay-key',
      ),
        array(
          'id'   => 'toyyibpay_userSecretKeyDev',
          'name' => __( 'Dev Secret Key<br>(Optional)', 'give-toyyibpay' ),
          'desc' => __( 'Enter your Secret key found in your dev account dashboard.', 'give-toyyibpay' ),
          'type' => 'text',
          'row_classes' => 'give-toyyibpay-key',
        ),
        array(
          'id'   => 'toyyibpay_categorycodeDev',
          'name' => __( 'Dev Category code<br>(Optional)', 'give-toyyibpay' ),
          'desc' => __( 'Enter your category code. Create new if you have none (in dev Category menu).', 'give-toyyibpay' ),
          'type' => 'text',
        ),
    );

    return array_merge($settings, $check_settings);
  }

  public function enqueue_js($hook) {
    if ('post.php' === $hook || $hook === 'post-new.php') {
      wp_enqueue_script('give_toyyibpay_each_form', GIVE_TOYYIBPAY_PLUGIN_URL . '/includes/js/meta-box.js');
    }
  }

}
Give_ToyyibPay_Settings_Metabox::get_instance()->setup_hooks();