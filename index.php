<?php 
class Paipal {
   public $apiUrl    = "https://svcs.sandbox.paypal.com/AdaptivePayments/";
   public $paypalUrl = "https://www.sandbox.paypal.com/webscr?cmd=_ap-payment&paykey=";

   function __construct()
   {
       $this->headers = array(
        "X-PAYPAL-SECURITY-USERID: test_seller_api1.aazztech.com",
        "X-PAYPAL-SECURITY-PASSWORD: 9YL5XKPKEZB4WYVF",
        "X-PAYPAL-SECURITY-SIGNATURE: A5v94doyzkAL45GWSOpp3GzSpU1aA1JceAaFGdOySjnFO5DbLdDQXynD",
        "X-PAYPAL-REQUEST-DATA-FORMAT: JSON",
        "X-PAYPAL-RESPONSE-DATA-FORMAT: JSON",
        "X-PAYPAL-APPLICATION-ID: APP-80W284485P519543T",
       );
   }

   function getPaymentOptions( $paykey ){
        $packet = array(
            "requestEnvelope"    => array(
                "errorLanguage" =>  "en_US",
                "detailLevel"   => "ReturnAll"
            ),
            "payKey"    => $paykey
        );

        return $this->_paypalSend( $packet, "GetPaymentOptions" );
   }

   function setPaymentDetails() {

   }

   function _paypalSend( $data, $call ) {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $this->apiUrl . $call );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $this->headers );

        return json_decode( curl_exec( $ch ), TRUE );
   }

   function split_pay() {
       $create_packet = array(
           "actionType" => "PAY",
           "currencyCode" => "USD",
           "receiverList"   => array(
                "receiver"  => array(
                    array(
                        "amount"    => "3.0",
                        "email"     => "test_seller@aazztech.com"
                    ),
                    array(
                        "amount"    => "7.0",
                        "email"     => "sb-n476xd467761@business.example.com"
                    ),
                )
            ),
            "returnUrl" => "http://sovware.com",
            "cancelUrl" => "http://wpwax.com",
            "requestEnvelope"    => array(
                "errorLanguage" =>  "en_US",
                "detailLevel"   => "ReturnAll"
            )
       );

       $response = $this->_paypalSend( $create_packet, "Pay");
    //   var_dump( $response );
       $paykey = $response['payKey'];
       $detail_packet   = array(
            "requestEnvelope"    => array(
                "errorLanguage" =>  "en_US",
                "detailLevel"   => "ReturnAll"
            ), 
            "payKey"    =>  $response['payKey'],
            "receiverOptions"   => array(
                array(
                    "receiver" => array("email" => "test_seller@aazztech.com" ),
                    "invoiceData" => array(
                        "item" => array(
                            array(
                                "name" => "Booking commission",
                                "price" => "3.0",
                                "identifier" => "p1"
                            ),
                        )
                    )
                ),
                array(
                    "receiver" => array("email" => "sb-n476xd467761@business.example.com" ),
                    "invoiceData" => array(
                        "item" => array(
                            array(
                                "name" => "Booking",
                                "price" => "7.0",
                                "identifier" => "p1"
                            ),
                        )
                    )
                ),
            )
       );

       $response = $this->_paypalSend( $detail_packet, "SetPaymentOptions");
       //var_dump( $response );

       $dets = $this->getPaymentOptions( $paykey );

     //  var_dump( $dets );

       header("Location: ". $this->paypalUrl . $paykey );
   }
}

$paypal = new Paipal();

$paypal->split_pay();