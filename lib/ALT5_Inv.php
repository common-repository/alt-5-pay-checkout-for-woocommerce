<?php

class ALT5_Inv
{
    public function ALT5_createInvoice($payload,$authHeader,$mode,$apikey,$merchant_id,$finalBodyString)
{
   
if($mode=='live'){
    $url = "https://api.alt5pay.com";
}else{

    $url = "https://api.digitalpaydev.com";
}


$args = array(
	'body'        => $payload,
	'headers'     => array('Content-type'=>'application/json','apikey'=> $apikey,'merchant_id'=> $merchant_id,'authentication'=> $authHeader)
);


$wpresponse = wp_remote_post($url.'/usr/invoice/create', $args );
$json_response=wp_remote_retrieve_body( $wpresponse );

 
$status = wp_remote_retrieve_response_code($wpresponse);
 

if ( $status != 200 ) {
   die("Error: call to URL $url failed with status $status");
}
 

$response = $json_response;

$this->invoiceData = $response;



}
public function ALT5_getInvoiceData()
{
    return $this->invoiceData;
}




public function ALT5_checkInvoiceStatus($invoiceID)
{


    $alt5pay_checkout_options = get_option('woocommerce_alt5pay_settings');

    $mode =  $alt5pay_checkout_options['mode'];
    $merchantid =  $alt5pay_checkout_options['merchant_id'];


    if($mode=='live'){
        $url = "https://api.alt5pay.com";
    }else{
    
        $url = "https://api.digitalpaydev.com";
    }
    
    
    $args = array(
        'headers'     => array('Content-type'=>'application/json')
    );
    
    
    $wpresponse = wp_remote_post($url.'/invoice/'.$invoiceID, $args );
    $json_response=wp_remote_retrieve_body( $wpresponse );
    


  
   $status = wp_remote_retrieve_response_code($wpresponse);
     
    
    if ( $status != 200 ) {
        die("Error: call to URL $url failed with status $status");
    }
     
    
    
    $response = $json_response;

    $this->invoiceData = $response;
    
  
}

}