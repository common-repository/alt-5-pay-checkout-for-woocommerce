<?php
/**
 * Plugin Name: ALT 5 Pay Checkout for WooCommerce
 * Plugin URI: https://alt5pay.com/woocommerce/
 * Description: Accept crypto currencies directly on your WooCommerce site in a seamless and secure checkout environment with ALT 5 Pay.
 * Version: 1.0.3
 * Author: ALT 5 Pay
 * Author URI: https://alt5pay.com
 * 
 * @package WordPress
 * @author ALT 5 Pay
 * @since 1.0.0
 */



/**
 * ALT 5 Pay WooCommerce Class
 */
class WC_Alt5Pay {

	
	/**
	 * Constructor
	 */
	public function __construct(){
		define( 'WC_Alt5Pay_VERSION', '1.0.3' );
		define( 'WC_Alt5Pay_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' );
		define( 'WC_Alt5Pay_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'WC_Alt5Pay_PLUGIN_DIR', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) . '/' );
		define( 'WC_Alt5Pay_MAIN_FILE', __FILE__ );

		// Actions
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ), 0 );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );


		add_action( 'admin_enqueue_scripts',  array( $this, 'alt5pay_styles' ));
		add_action( 'wp_enqueue_scripts',  array( $this, 'alt5pay_styles' ));

	

	}

	/**
	 * Add links to plugins page for settings and documentation
	 * @param  array $links
	 * @return array
	 */

	 public function alt5pay_styles() {

        wp_enqueue_style( 'alt5paystyle', plugins_url('css/alt5paystyle.css', __FILE__) );
    
}



	public function plugin_action_links( $links ) {
		$subscriptions = ( class_exists( 'WC_Subscriptions_Order' ) ) ? '_subscriptions' : '';
		if ( class_exists( 'WC_Subscriptions_Order' ) && ! function_exists( 'wcs_create_renewal_order' ) ) {
			$subscriptions = '_subscriptions_deprecated';
		}
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_alt5_pay' . $subscriptions ) . '">' . __( 'Settings', 'wc-gateway-alt5-pay' ) . '</a>',
			'<a href="https://alt5pay.com/">' . __( 'Support', 'wc-gateway-alt5-pay' ) . '</a>',
			'<a href="https://alt5pay.com/">' . __( 'Docs', 'wc-gateway-alt5-pay' ) . '</a>'
		);
		return array_merge( $plugin_links, $links );
	}

	/**
	 * Init localisations and files
	 */
	public function init() {

		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			$plugins_url = admin_url('plugins.php');
			deactivate_plugins(plugin_basename(__FILE__));
			wp_die('WooCommerce needs to be installed and activated before ALT 5 Pay Checkout for WooCommerce can be activated.<br><a href="' . $plugins_url . '">Return to plugins screen</a>');

			return;
		}

		
		// Includes
		include_once( 'includes/class-wc-gateway-alt5-pay.php' );

		// Localisation
		load_plugin_textdomain( 'wc-gateway-alt5-pay', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	}

	/**
	 * Register the gateway for use
	 */
	public function register_gateway( $methods ) {

		$methods[] = 'WC_Gateway_Alt5_Pay';

		return $methods;

	}


	/**
	 * Include jQuery and our scripts
	 */
	function add_alt5_pay_scripts() {

		wp_enqueue_style( 'alt5paystyle', WC_Alt5Pay_PLUGIN_DIR . 'css/alt5paystyle.css', false );
		wp_enqueue_script( 'alt5payscript', WC_Alt5Pay_PLUGIN_DIR . 'js/alt5Script.js', array( 'jquery' ), WC_Alt5Pay_VERSION, true );

	}






	/**
	 * Check if the user has any billing records in the Customer Vault
	 *
	 * @param $user_id
	 *
	 * @return bool
	 */
	function user_has_stored_data( $user_id ) {
		return get_user_meta( $user_id, 'customer_vault_ids', true ) != null;
	}


}





function alt5pay_add_error_page() {
    



	$page = get_page_by_path( 'order-cancelled' , OBJECT );

	if ( !isset($page) )
  $my_post = array(
      'post_title'    => wp_strip_all_tags( 'Order Cancelled' ),
      'post_content'  => 'Your order stands cancelled. Please go back to <a href="/shop">Shop page</a> and reorder.',
      'post_status'   => 'publish',
      'post_author'   => "Alt5Pay",
      'post_type'     => 'page',
    );

    // Insert the post into the database
    wp_insert_post( $my_post );

  
}


register_activation_hook(__FILE__, 'alt5pay_add_error_page');
#autoloader
function ALT5_loadclass($class)
{
    if (strpos($class, 'ALT5_') !== false):
        if (!class_exists('lib/' . $class, false)):
            #doesnt exist so include it
            include 'lib/' . $class . '.php';
        endif;
    endif;
}

spl_autoload_register('ALT5_loadclass');


function alt5pay_register_partially_paid_status() {
    register_post_status( 'wc-partial-payment', array(
        'label'                     => 'Partially Paid',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Partially Paid <span class="count">(%s)</span>', 'Partially Paid <span class="count">(%s)</span>' )
    ) );
	$order_status = 'Partially Paid';



}
add_action( 'init', 'alt5pay_register_partially_paid_status' );


add_filter( 'wc_order_statuses', 'alt5pay_custom_order_status');
function alt5pay_custom_order_status( $order_statuses ) {
    $order_statuses['wc-partial-payment'] =  _x( 'Partially Paid', 'Order status', 'woocommerce' );
    return $order_statuses;
}


add_action( 'woocommerce_order_status_partial-payment', 'alt5pay_partial_payment_status_custom_notification',10, 2 );

function alt5pay_partial_payment_status_custom_notification( $order_id, $order ) {

$heading = 'Order is Partially Paid';
$subject = 'Order # {order_number} Partially Paid';

// Get WooCommerce email objects
$mailer = WC()->mailer()->get_emails();

// Use one of the active emails e.g. "Customer_Processing_Order"
// Wont work if you choose an object that is not active
// Assign heading & subject to chosen object
$mailer['WC_Email_Customer_Processing_Order']->heading = $heading;
$mailer['WC_Email_Customer_Processing_Order']->settings['heading'] = $heading;
$mailer['WC_Email_Customer_Processing_Order']->subject = $subject;
$mailer['WC_Email_Customer_Processing_Order']->settings['subject'] = $subject;

// Send the email with custom heading & subject
$mailer['WC_Email_Customer_Processing_Order']->trigger( $order_id );

// To add email content use https://businessbloomer.com/woocommerce-add-extra-content-order-email/
// You have to use the email ID chosen above and also that $order->get_status() == "preorder-paid"

}



add_action( 'woocommerce_email_before_order_table', 'alt5pay_partial_payment_add_content_specific_email', 10, 4 );
  
function alt5pay_partial_payment_add_content_specific_email( $order, $sent_to_admin, $plain_text, $email ) {

	$orderStatus=$order->get_status();
	$transaction_id=$order->transaction_id;



	$transaction_data=alt5pay_checkout_get_order_status($transaction_id);

	foreach($transaction_data as $row){
	
		$invoiceStatus=$row->transaction_status;
		$invoicePaidAmount=number_format($row->payment_amount, 2, '.', '');
	$invoiceAmount=number_format($row->invoice_amount, 2, '.', '');
	$currency=$row->currency;
	$payURL=$row->payment_url;
	}





	
	
	
	$invoiceOustandingAmount=number_format($invoiceAmount-$invoicePaidAmount, 2, '.', '');

	


//die($transaction_data);
	
		$alt5pay_checkout_options = get_option('woocommerce_alt5pay_settings');
	
		

		$preCurrency='$';
		if ($currency=='EUR'){$preCurrency='€';}
		if ($currency=='GBP'){$preCurrency='£';}




   if ( $orderStatus == 'partial-payment' ) {
      echo '<h2 class="email-upsell-title">ACTION REQUIRED</h2><p class="email-upsell-p">Your transaction was only partially paid.</p><p>You have paid <strong>'.esc_html($preCurrency).''.esc_html($invoicePaidAmount).'</strong> out of <strong>'.esc_html($preCurrency).''.esc_html($invoiceAmount).'</strong></p> <p>Your outstanding amount is <strong>'.esc_html($preCurrency).''.esc_html($invoiceOustandingAmount).'</strong> </p><p><a href="'.esc_html($payURL).'" target="_blank">Click here to pay the outstanding balance</a></p>';


   }
}




function alt5pay_checkout_get_transaction_id_alt5pay_orderid_id($order_id)
{
    global $wpdb;
    $table_name = '_alt5pay_transactions';
    $transaction_id = $wpdb->get_var($wpdb->prepare("SELECT transaction_id FROM $table_name WHERE order_id = %s LIMIT 1", $order_id));

    return $transaction_id;
}



function alt5pay_checkout_get_order_id_alt5pay_ref_id($ref_id)
{
    global $wpdb;
    $table_name = '_alt5pay_transactions';
    $order_id = $wpdb->get_var($wpdb->prepare("SELECT order_id FROM $table_name WHERE ref_id = %s LIMIT 1", $ref_id));

    return $order_id;
}



function alt5pay_checkout_get_order_status($transaction_id)
{
    global $wpdb;
    $table_name = '_alt5pay_transactions';
    $transaction_data = $wpdb->get_results("SELECT * FROM $table_name WHERE transaction_id = '$transaction_id' LIMIT 1");
//die("SELECT * FROM $table_name WHERE transaction_id = '$transaction_id' LIMIT 1");
    return $transaction_data;
}




function alt5pay_checkout_update_order_id_status($transaction_id,$order_id,$invoiceStatus,$invoiceAmount,$invoicePaidAmount,$currency)
{
	global $wpdb;
    $table_name = '_alt5pay_transactions';
    if ($order_id != null && $transaction_id != null && $invoiceStatus != null):
        $wpdb->update($table_name, array('transaction_status' => $invoiceStatus, 'invoice_amount' => $invoiceAmount, 'payment_amount' => $invoicePaidAmount, 'currency' => $currency), array("order_id" => $order_id, 'transaction_id' => $transaction_id));
    else:

    endif;
}

function alt5pay_insert_transaction($order_id = null, $ref_id = null, $transaction_id = null, $payment_url = null)
{
    global $wpdb;

    if ($order_id != null && $transaction_id != null):
        global $woocommerce;

    //Retrieve the order
    $order = new WC_Order($order_id);
    $order->set_transaction_id($transaction_id);
    $order->save();
    //Retrieve the transaction ID

        $table_name = '_alt5pay_transactions';
        $wpdb->insert(
            $table_name,
            array(
                'order_id' => $order_id,
				'ref_id' => $ref_id,
                'transaction_id' => $transaction_id,
				'payment_url' => $payment_url,
            )
        );
    else:

    endif;

}





add_action('template_redirect', 'alt5pay_woo_custom_redirect_after_purchase');
function alt5pay_woo_custom_redirect_after_purchase()
{

    global $wp;
	global $woocommerce;
  $alt5pay_checkout_options = get_option('woocommerce_alt5pay_settings');





	$show_alt5pay=true;

   if (is_checkout() && !empty($wp->query_vars['order-received']) && $_GET['paid']!='true') {

	$order_id = $wp->query_vars['order-received'];

	
	try {

		
		$order = new WC_Order($order_id);
		if ($order->get_payment_method() == 'alt5pay' && $show_alt5pay == true):

		//this means if the user is using Alt5Pay AND this is not the redirect



		$items = $woocommerce->cart->get_cart();
	$itemcount=0;
	$_allcartitems='';
	foreach ( WC()->cart->get_cart() as $cart_item ) {

	$product= $cart_item['data'];
				$_cartitems='{"item":"'.$product->get_name().'","cost":"'.$product->get_price().'","quantity":"'.$cart_item['quantity'].'"}';
if($itemcount==0){

	$_allcartitems=$_cartitems;
}else{
	$_allcartitems=$_allcartitems.','.$_cartitems;
}


				$itemcount=$itemcount+1;
			} 


		$new_customer_vault_id = '';
		$order = new WC_Order( $order_id );
		$user = new WP_User( $order->get_user_id() );

		$prefix =  $alt5pay_checkout_options['prefix'];
if($prefix=='' || $prefix=='undefined')($prefix='WC-');
$ref_id=$prefix.$order_id;
			$mode =  $alt5pay_checkout_options['mode'];
			if($mode=='live'){
		$publickey = $alt5pay_checkout_options['public_key'];
		$secretkey = $alt5pay_checkout_options['secret_key'];	
		$merchantid =  $alt5pay_checkout_options['merchant_id'];
			}else{
					$publickey = $alt5pay_checkout_options['sandbox_public_key'];
					$secretkey = $alt5pay_checkout_options['sandbox_secret_key'];
					$merchantid =  $alt5pay_checkout_options['sandbox_merchant_id'];

			}


	

		$currency = $order->get_currency();
		$order_data = $order->get_data();
		$order_billing_first_name = $order_data['billing']['first_name'];
		$order_billing_last_name = $order_data['billing']['last_name'];
		$order_billing_email = $order_data['billing']['email'];
		$order_billing_company = $order_data['billing']['company'];
		$order_billing_phone = $order_data['billing']['phone'];
		$order_billing_address_1 = $order_data['billing']['address_1'];
		$order_billing_city = $order_data['billing']['city'];
		$order_billing_state = $order_data['billing']['state'];
		$order_billing_postcode = $order_data['billing']['postcode'];
		$order_billing_country = $order_data['billing']['country'];


		$date = new DateTime();

		$ipn_url = get_site_url()."/wp-json/alt5pay/ipn/status/";
		$timestamp=$date->getTimestamp();
		$nonce = (int)($timestamp/1000);

		$payload ='{
			"contact": {"email":"'.$order_billing_email.'","firstname":"'.$order_billing_first_name.'","lastname":"'.$order_billing_last_name.'","company":"'.$order_billing_company.'","address":"'.$order_billing_address_1.'","city":"'.$order_billing_city.'","prov_state":"'.$order_billing_state.'","country":"'.$order_billing_country.'","postal_zip":"'.$order_billing_postcode.'","phone":"'.$order_billing_phone.'"},
			"ref_id":"'.$ref_id.'",
			"items":['.$_allcartitems.'],
			"total_amount":"'.$order->get_total().'",
			"currency":"'.$currency.'",
			"due_date":"'.date("Y-m-d").'",
			"sendemail":false,
			"timestamp": '.$timestamp.',
			"nonce": '.$nonce.',
			"url":"'.$ipn_url.'",
			"cancel_url":"'.$order->get_cancel_order_url().'",
			"success_url":"'.$order->get_checkout_order_received_url().'",
			"type":"widget"
			}
			';



			$finalBodyString = 'contact={"email":"'.$order_billing_email.'","firstname":"'.$order_billing_first_name.'","lastname":"'. $order_billing_last_name.'","company":"'.$order_billing_company.'","address":"'.$order_billing_address_1.'","city":"'.$order_billing_city.'","prov_state":"'.$order_billing_state.'","country":"'.$order_billing_country.'","postal_zip":"'.$order_billing_postcode.'","phone":"'.$order_billing_phone.'"}&ref_id='.$ref_id .'&items=['.$_allcartitems.']&total_amount='.$order->get_total().'&currency='.$currency.'&due_date='.date("Y-m-d").'&sendemail=false&timestamp='.$timestamp.'&nonce='.$nonce.'&url='.$ipn_url.'&cancel_url='.$order->get_cancel_order_url().'&success_url='.$order->get_checkout_order_received_url().'&type=widget';

		




		$hmac=hash_hmac('sha512', $finalBodyString, $secretkey);


		
$authHeader = base64_encode($publickey . ':' . $hmac);






		$invoice = new ALT5_Inv();
		//this creates the invoice with all of the config params from the item
		$invoice->ALT5_createInvoice($payload,$authHeader,$mode,$publickey,$merchantid,$finalBodyString);
		$invoiceData = json_decode($invoice->ALT5_getInvoiceData());	

		
		

		if ($invoiceData->status=='error'):
		
			$errorURL = get_home_url().'/order-cancelled';
			$order_status = "wc-cancelled";
			$order = new WC_Order($order_id);
			$items = $order->get_items();
			$order->update_status($order_status, __($invoiceData->status.'.', 'woocommerce'));

			 //clear the cart first so things dont double up
			WC()->cart->empty_cart();
			foreach ($items as $item) {
				//now insert for each quantity
				$item_count = $item->get_quantity();
				for ($i = 0; $i < $item_count; $i++):
					WC()->cart->add_to_cart($item->get_product_id());
				endfor;
			}
			wp_redirect($errorURL);
			die();
		endif; 

		$invoiceID=$invoiceData->data->invoice_id;
		$redirectURL=$invoiceData->data->widget_url;
                alt5pay_insert_transaction($order_id, $ref_id, $invoiceID,$redirectURL);

				$order_status  = $order->get_status(); 
				if($order_status=='pending' && $redirectURL!=''){
				$order->add_order_note( __( 'payment_url: ' , 'wc-gateway-alt5-pay' ).'<a href="'. $redirectURL.'" target="_blank">'. $redirectURL.'</a>');
				}
		

	  wp_redirect($redirectURL);      
  
		endif;
		

	} catch (Exception $e) {
		global $woocommerce;

		$cart_url = $woocommerce->cart->get_cart_url();

		wp_redirect($cart_url);
		exit;
	}

}

}





new WC_Alt5Pay();

function alt5pay_check_ipn_response(){
	$_GET   = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
		$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		$postedData = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postedData = json_decode(file_get_contents('php://input'), true);
            if (!is_array($postedData)) {
				$postedData = array (
					"secret_key" => sanitize_text_field($_POST["secret_key"]),
					"ref_id" => sanitize_text_field($_POST["ref_id"]),
					"price" => sanitize_text_field($_POST["price"]),
					"amount" => sanitize_text_field($_POST["amount"]),
					"total" => sanitize_text_field($_POST["total"]),
					"date_time" => sanitize_text_field($_POST["date_time"]),
					"transaction_id" => sanitize_text_field($_POST["transaction_id"]),
					"coin" => sanitize_text_field($_POST["coin"]),
					"network" => sanitize_text_field($_POST["network"]),
					"currency" => sanitize_text_field($_POST["currency"])
				);
            }
        }  else {
			$postedData = array (
				"secret_key" => sanitize_text_field($_GET["secret_key"]),
				"ref_id" => sanitize_text_field($_GET["ref_id"]),
				"price" => sanitize_text_field($_GET["price"]),
				"amount" => sanitize_text_field($_GET["amount"]),
				"total" => sanitize_text_field($_GET["total"]),
				"date_time" => sanitize_text_field($_GET["date_time"]),
				"transaction_id" => sanitize_text_field($_GET["transaction_id"]),
				"coin" => sanitize_text_field($_GET["coin"]),
				"network" => sanitize_text_field($_GET["network"]),
				"currency" => sanitize_text_field($_GET["currency"])
			);
        } 

		$headers = apache_request_headers();
	 $signature=$headers["Signature"];

	 $alt5pay_checkout_options = get_option('woocommerce_alt5pay_settings');
	 $mode =  $alt5pay_checkout_options['mode'];
	 $secretkey='';
			 if($mode=='live'){
 
		 $secretkey = $alt5pay_checkout_options['secret_key'];	
 
			 }else{
			 
					 $secretkey = $alt5pay_checkout_options['sandbox_secret_key'];
				 
 
			 }

	$sec = new ALT5_Sec();
	//this creates the invoice with all of the config params from the item
	$secCheck=$sec->ALT5_checkSecurity($signature,$secretkey,file_get_contents('php://input'));


if($secCheck){

	alt5pay_handle_ipn_request( stripslashes_deep( $postedData ) );

}else{

}


		// Notify Alt5 Pay that information has been received
		header( 'HTTP/1.0 200 OK' );
		flush();

}





/**
 * Check Alt5 Pay IPN validity.
 *
 * @param array $data
 * @since 1.0.0
 */
function alt5pay_handle_ipn_request( $data ) {



	$alt5_pay_error  = false;
	$alt5_pay_done   = false;
	$debug_email    = get_option( 'debug_email', get_option( 'admin_email' ) );

	$vendor_name    = get_bloginfo( 'name', 'display' );
	$vendor_url     = home_url( '/' );

	if (isset($data["ref_id"]))
	{
		$ref_id      = $data["ref_id"];

		$order_id       = alt5pay_checkout_get_order_id_alt5pay_ref_id($ref_id);
	
		$order          = wc_get_order( $order_id );
		$original_order = $order;


		$transaction_id=alt5pay_checkout_get_transaction_id_alt5pay_orderid_id($order_id);



 $transaction_data=alt5pay_checkout_get_order_status($transaction_id);
 foreach($transaction_data as $row){
$payURL=$row->payment_url;
}

	
		$price = $data["price"];
		$amount = $data["amount"];
		$total = $data["total"];
		$date_time = $data["date_time"];
		$transaction_id = $data["transaction_id"];
		$coin = $data["coin"];
		$network = $data["network"];
		$currency = $data["currency"];


	
			$order->add_order_note( __( 'ref_id: ' , 'wc-gateway-alt5-pay' ) . $ref_id);
			$order->add_order_note( __( 'price: ' , 'wc-gateway-alt5-pay' ) . $price);
			$order->add_order_note( __( 'amount: ' , 'wc-gateway-alt5-pay' ) . $amount);
			$order->add_order_note( __( 'total: ' , 'wc-gateway-alt5-pay' ) . $total);
			$order->add_order_note( __( 'date_time: ' , 'wc-gateway-alt5-pay' ) . $date_time);
			$order->add_order_note( __( 'transaction_hash: ' , 'wc-gateway-alt5-pay' ) . $transaction_id);
			$order->add_order_note( __( 'coin: ' , 'wc-gateway-alt5-pay' ) . $coin);
			$order->add_order_note( __( 'network: ' , 'wc-gateway-alt5-pay' ) . $network);
			$order->add_order_note( __( 'currency: ' , 'wc-gateway-alt5-pay' ) . $currency);
			$order->add_order_note( __( 'payment_url: ' , 'wc-gateway-alt5-pay' ).'<a href="'. $payURL.'" target="_blank">'. $payURL.'</a>');



			if (
				($ref_id != "")
				&&
				($price != "")
				&&
				($amount != "")
				&&
				($total != "")
				&&
				($date_time != "")
				&&
				($transaction_id != "")
				&&
				($coin != "")
				&&
				($network != "")
				&&
				($currency != "")
			)
			{
				// Success
				halt5pay_handle_ipn_payment_complete( $data, $order );
			}
		
	}


}


function alt5pay_get_order_prop( $order, $prop ) {
	switch ( $prop ) {
		case 'order_total':
			$getter = array( $order, 'get_total' );
			break;
		default:
			$getter = array( $order, 'get_' . $prop );
			break;
	}

	return is_callable( $getter ) ? call_user_func( $getter ) : $order->{ $prop };
}



 function halt5pay_handle_ipn_payment_complete( $data, $order ) {

	$order_id = alt5pay_get_order_prop( $order, 'id' );
	$order = new WC_Order($order_id);


	$transaction_id=alt5pay_checkout_get_transaction_id_alt5pay_orderid_id($order_id);
	$invoice = new ALT5_Inv();

		$invoice->ALT5_checkInvoiceStatus($transaction_id);
		$invoiceData = json_decode($invoice->ALT5_getInvoiceData());
		$invoiceStatus=$invoiceData->data->payment_status;
		$invoicePaidAmount=$invoiceData->data->paid_amount;
		$invoiceAmount=$invoiceData->data->total_amount;
		$invoiceCurrency=$invoiceData->data->currency;
		$invoiceOustandingAmount=$invoiceAmount-$invoicePaidAmount;

		alt5pay_checkout_update_order_id_status($transaction_id,$order_id,$invoiceStatus,$invoiceAmount,$invoicePaidAmount,$invoiceCurrency);

	if ($invoiceStatus == 'Paid')
	{
	$order->update_status( 'processing' );
		$order->payment_complete();
	


 $order_status  = $order->get_status(); 
if($order_status!='pending'){

		$email_oc = new WC_Email_Customer_Processing_Order();	
$email_oc->trigger($order_id);

}
	$order->add_order_note(__('Payment completed successfully', 'wc-gateway-alt5-pay'));

		wc_reduce_stock_levels( $order_id );
	}
	else if($invoiceStatus == 'Partially Paid'){
		$order->update_status( 'partial-payment' );
		$order->add_order_note(__('Partially Paid', 'wc-gateway-alt5-pay'));
		$order->add_order_note(__('Amount Paid:'.$invoicePaidAmount, 'wc-gateway-alt5-pay'));

		$order->add_order_note(__('Amount Oustanding:'.$invoiceOustandingAmount, 'wc-gateway-alt5-pay'));
	

}else{
	
}


}

/**
 * Check payment details for valid format
 *
 * @return bool
 */

add_action('rest_api_init', function () {
	register_rest_route('alt5pay/ipn', '/status', array(
		'methods' => 'POST,GET',
		'callback' => 'alt5pay_check_ipn_response',
		'permission_callback' => '__return_true',
	));
	
});

add_action('woocommerce_thankyou', 'alt5pay_checkout_custom_message');
function alt5pay_checkout_custom_message($order_id)
{
    $order = new WC_Order($order_id);
    if ($order->get_payment_method() == 'alt5pay'):



	$checkout_message='We are still processing your order';

	
  
		if ($order->get_status() == "error") {    
			$params = new stdClass();
			
				$params->closeURL = $base_url . "/order-cancelled";
            	wp_redirect($params->closeURL);	
		
        }
       // if ($checkout_message != ''):
            echo '<hr><b>' . esc_html($checkout_message) . '</b><br><br><hr>';
       // endif;
    endif;
}