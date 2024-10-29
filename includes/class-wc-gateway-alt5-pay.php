<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Gateway_Alt5_Pay class.
 *
 * @since 2.0.0
 * @extends WC_Payment_Gateway
 */

class WC_Gateway_Alt5_Pay extends WC_Payment_Gateway {
	

	/** @var  string save debug information */
	var $debug;

	/** @var  string save order */
	var $order;

	

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register plugin information
		$this->id         = 'alt5pay';
		
		$this->method_title       = __( 'ALT 5 Pay', 'woocommerce-gateway-alt5pay-checkout' );
		$this->method_description = __( alt5pay_getDescription(), 'woocommerce-gateway-alt5pay-checkout' );
		$this->icon = alt5pay_getPaymentIcon();




		$this->has_fields = true;

		// Create plugin fields and settings

	
		$this->init_settings();
$this->init_form_fields();
$this->alt5pay_checkout_plugin_setup();


		// Get setting values
		foreach ( $this->settings as $key => $val ) $this->$key = $val;

		// Load plugin checkout icon
			// Add hooks
		add_action( 'admin_notices',                                            array( $this, 'alt5_pay_commerce_ssl_check' ) );
			add_action( 'woocommerce_receipt_alt5_pay',                              array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_update_options_payment_gateways',              array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

 		// Payment listener/API hook
 	
	}

	/**
	 * Check if SSL is enabled and notify the user.
	 */
	function alt5_pay_commerce_ssl_check() {
		if ( get_option( 'woocommerce_force_ssl_checkout' ) == 'no' && $this->enabled == 'yes' ) {
			$admin_url = admin_url( 'admin.php?page=wc-settings&tab=checkout' );
			echo '<div class="error"><p>' . esc_html(sprintf( __('ALT 5 Pay is enabled and the <a href="%s">force SSL option</a> is disabled; your checkout is not secure! Please enable SSL and ensure your server has a valid SSL certificate.', 'wc-gateway-alt5-pay' ), $admin_url )) . '</p></div>';
		}
	}

	/**
	 * Initialize Gateway Settings Form Fields.
	 */
	function init_form_fields() {

		$this->form_fields = array(
			'enabled'     => array(
				'title'       => __( 'Enable/Disable', 'wc-gateway-alt5-pay' ),
				'label'       => __( 'Enable ALT 5 Pay', 'wc-gateway-alt5-pay' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
			),
			'description' => array(
				'title'       => __( 'Description', 'wc-gateway-alt5-pay' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'wc-gateway-alt5-pay' ),
				'default'     => 'The most secure way to pay in crypto.'
			), 'payment_icon' => array(
				'title' => __('Payment Icon ', 'woocommerce'),
				'type' => 'select',
				'description' => __('Select payment icon for checkout <div class="payment_method_alt5pay"><img src="'. plugin_dir_url( __FILE__ ) . 'images/paywithcoins1.svg" /></div> <div class="payment_method_alt5pay"><img src="'. plugin_dir_url( __FILE__ ) . 'images/paywithcoins2.svg" /><div>', 'woocommerce'),
				'options' => array(
					'1' => 'Pay with ALT 5 Pay',
					'2' => 'Pay with Crypto',
				),
				'default' => '1',
			),
	
				'mode'    => array(
				'title'       => __( 'Endpoint', 'wc-gateway-alt5-pay' ),
				'type'        => 'select',
				'description' => __( 'Select Sandbox for testing the plugin, Production when you are ready to go live', 'wc-gateway-alt5-pay' ),
				'default'     => 'sandbox',
				'options'     => array(
					'sandbox' => 'Sandbox',
					'live' => 'Live'
				)),'public_key' => array(
				'title'       => __( 'Live Public Key', 'wc-gateway-alt5-pay' ),
				'type'        => 'text',
				'description' => __( 'Key obtained from <a href="https://dashboard.alt5pay.com/" target="_blank"><b>ALT 5 Pay</b></a>, on <b>Settings</b> - <b>API Keys<b>.', 'wc-gateway-alt5-pay' ),
				'default'     => ''
			),
			'secret_key'    => array(
				'title'       => __( 'Live Secret Key', 'wc-gateway-alt5-pay' ),
				'type'        => 'text',
				'description' => __( 'Key obtained from <a href="https://dashboard.alt5pay.com/" target="_blank"><b>ALT 5 Pay</b></a>, on <b>Settings</b> - <b>API Keys<b>.', 'wc-gateway-alt5-pay' ),
				'default'     => ''
			),
			'merchant_id'    => array(
				'title'       => __( 'Live Merchant Id', 'wc-gateway-alt5-pay' ),
				'type'        => 'text',
				'description' => __( 'Merchant id in <a href="https://dashboard.alt5pay.com/" target="_blank"><b>ALT 5 Pay</b></a>, on <b>Settings</b> - <b>Account Information</b>.', 'wc-gateway-alt5-pay' ),
				'default'     => ''
			),
			'sandbox_public_key' => array(
				'title'       => __( 'Sandbox Public Key', 'wc-gateway-alt5-pay' ),
				'type'        => 'text',
				'description' => __( 'Key obtained from <a href="https://sandbox.digitalpaydev.com/" target="_blank"><b>DigitalPayDev</b></a>, on <b>Settings</b> - <b>API Keys<b>.', 'wc-gateway-alt5-pay' ),
				'default'     => ''
			),
			'sandbox_secret_key'    => array(
				'title'       => __( 'Sandbox Secret Key', 'wc-gateway-alt5-pay' ),
				'type'        => 'text',
				'description' => __( 'Key obtained from <a href="https://sandbox.digitalpaydev.com/" target="_blank"><b>DigitalPayDev</b></a>, on <b>Settings</b> - <b>API Keys<b>.', 'wc-gateway-alt5-pay' ),
				'default'     => ''
			),
			'sandbox_merchant_id'    => array(
				'title'       => __( 'Sandbox Merchant Id', 'wc-gateway-alt5-pay' ),
				'type'        => 'text',
				'description' => __( 'Merchant id in <a href="https://sandbox.digitalpaydev.com/" target="_blank"><b>DigitalPayDev</b></a>, on <b>Settings</b> - <b>Account Information</b>.', 'wc-gateway-alt5-pay' ),
				'default'     => ''
		
			),	'prefix'    => array(
				'title'       => __( 'Order Prefix', 'wc-gateway-alt5-pay' ),
				'type'        => 'text',
				'description' => __( 'Enter a prefix to your order numbers, by default the prefix is WC-', 'wc-gateway-alt5-pay' ),
				'default'     => ''
		 
			),
			'debug'    => array(
				'title'       => __( 'Debug', 'wc-gateway-alt5-pay' ),
				'type'        => 'checkbox',
				'label'       => __( 'Write information to a debug log.', 'wc-gateway-alt5-pay' ),
				'description' => __( 'The log will be available via WooCommerce > System Status on the Logs tab with a name starting with \'Alt5Pay\'', 'wc-gateway-alt5-pay' ),
				'default'     => 'no'
			),

		);
	}


	/**
	 * UI - Admin Panel Options
	 */
	function admin_options() { ?>
		<h3><?php _e( 'ALT 5 Pay','wc-gateway-alt5-pay' ); ?></h3>
		<p><?php _e( 'The ALT 5 Pay Gateway is simple and powerful.  The plugin works by adding crypto currencies fields on the checkout page, and then sending the details to ALT 5 Pay for verification.', 'wc-gateway-alt5-pay' ); ?></p>
		<table class="form-table">
			<?php $this->generate_settings_html(); ?>
		</table>
		<?php
	}

	/**
	 * UI - Payment page fields for ALT 5 Pay.
	 */
	function payment_fields() {
		if ( $this->description ) { ?>
		<?php echo '<p>'.esc_html($this->description).'</p>'; ?>
		<?php } 
	
		?>
	


		<?php
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int @order_id
	 * @return array
	 */


	 
	 function alt5pay_checkout_requirements()
	 {
		 global $wp_version;
		 global $woocommerce;
		 $errors = array();
	 
		 // WooCommerce required
		 if (true === empty($woocommerce)) {
			 $errors[] = 'The WooCommerce plugin for WordPress needs to be installed and activated. Please contact your web server administrator for assistance.';
		 } elseif (true === version_compare($woocommerce->version, '2.2', '<')) {
			 $errors[] = 'Your WooCommerce version is too old. The Alt5Pay payment plugin requires WooCommerce 2.2 or higher to function. Your version is ' . $woocommerce->version . '. Please contact your web server administrator for assistance.';
		 } elseif (get_woocommerce_currency()!='USD' && get_woocommerce_currency()!='CAD' && get_woocommerce_currency()!='EUR' && get_woocommerce_currency()!='GBP') {
			$errors[] = 'Alt5Pay Checkout only supports Woocommerece Currencies USD,CAD,EUR and GBP  ';
		}
		 if (empty($errors)):
			 return false;
		 else:
			 return implode("<br>\n", $errors);
		 endif;
	 }

function alt5pay_checkout_plugin_setup()
{

  $failed = $this->alt5pay_checkout_requirements();
  $plugins_url = admin_url('plugins.php');

 if ($failed === false) {

		global $wpdb;
        $table_name = '_alt5pay_transactions';

        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name(
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `order_id` varchar(255) NOT NULL,
        `transaction_id` varchar(255) NOT NULL,
        `transaction_status` varchar(50) NOT NULL DEFAULT 'new',
		`payment_url` varchar(255) NOT NULL DEFAULT '',
		`payment_amount` float NOT NULL DEFAULT '0',
		`invoice_amount` float NOT NULL DEFAULT '0',
		`currency` varchar(10) NOT NULL DEFAULT '',
		`ref_id` varchar(255) NOT NULL,
        `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
        ) $charset_collate;";

require_once ABSPATH . 'wp-admin/includes/upgrade.php';
dbDelta($sql);


 } else {

        // Requirements not met, return an error message
    wp_die($failed . '<br><a href="' . $plugins_url . '">Return to plugins screen</a>');

 }

}



	 function process_payment($order_id)
	 {
		 #this is the one that is called intially when someone checks out
		 global $woocommerce;
		 $order = new WC_Order($order_id);
		 // Return thankyou redirect
		 return array(
			 'result' => 'success',
			 'redirect' => $this->get_return_url($order),
		 );
	 }

/**
	 * Receipt page.
	 *
	 * Display text and a button to direct the user to Alt5pays Pay.
	 *
	 * @since 1.0.0
	 */
	public function receipt_page( $order_id ) {

		echo '<p>'.esc_html(__( 'Thank you for your order.', 'alt5-pay' )) . '</p>';
	}

	
	/**
	 * Check payment details for valid format
	 *
	 * @return bool
	 */


	
	/**
	 * Get post data if set
	 *
	 * @param string $name
	 * @return string|null
	 */
	protected function get_post( $name ) {
		if ( isset( $_POST[ $name ] ) ) {
			return sanitize_text_field($_POST[ $name ]);
		}
		return null;
	}


	/**
	 * Log system processes.
	 * @since 1.0.0
	 */
	public function log( $message ) {
		if ( 'yes' === $this->get_option( 'testmode' )) {
			if ( empty( $this->logger ) ) {
				$this->logger = new WC_Logger();
			}
			$this->logger->add( 'alt5_pay', $message );
		}
	}


	
}





function alt5pay_getPaymentIcon()
{



	$alt5pay_checkout_options = get_option('woocommerce_alt5pay_settings');
	$paymenticon  = $alt5pay_checkout_options['payment_icon'];

    $icon = null;
    if(	$paymenticon  == 1){
		$icon = plugin_dir_url( __FILE__ ) . 'images/paywithcoins1.svg';
	}else{
		$icon = plugin_dir_url( __FILE__ ) . 'images/paywithcoins2.svg';
	}
    return $icon;
   
}


function alt5pay_getDescription()
{


	$alt5pay_checkout_options = get_option('woocommerce_alt5pay_settings');
	$description  = $alt5pay_checkout_options['description'];

 
    return $description;
   
}