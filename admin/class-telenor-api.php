<?php

defined( 'ABSPATH' ) or exit;

/**
 * Telenor API
 */
if ( !class_exists( 'DS_TELENOR_API' ) ) {

	class DS_TELENOR_API {

		public static $customer_id;
		public static $message;
		public static $session_id;

		public static function send_sms( $customer_id, $message ){
			self::$customer_id = $customer_id;
			self::$message = $message;
			self::authenticate_request();
			return self::finally_call_api();
		}

		public static function get_api_username(){
			return 0987654321; // Update API mobile number
		}

		public static function get_api_password(){
			return 0000; // Update API passwird
		}

		public static function get_api_receiver(){
			return self::make_phone_formatted();
		}

		public static function get_api_message(){
			return self::make_sms_message();
		}

		public static function get_api_mask(){
			return 'PetsOne.pk';
		}

		public static function get_authentication_url(){
			return "https://telenorcsms.com.pk:27677/corporate_sms2/api/auth.jsp?msisdn=".self::get_api_username()."&password=".self::get_api_password();
		}

		public static function get_api_formatted_url(){

			$api_url = 'http://www.petsone.org/smsapi.php?api_username='. self::get_api_username() .'&api_password='. self::get_api_password() .'&to='. self::get_api_receiver() .'&message='. self::get_api_message() .'&mask='. self::get_api_mask();
			return "https://telenorcsms.com.pk:27677/corporate_sms2/api/sendsms.jsp?session_id=". self::$session_id ."&to=". self::get_api_receiver() ."&text=". self::get_api_message() ."&mask=".self::get_api_mask();

			return $api_url;
		}

		public static function make_phone_formatted() {

			$billing_phone = trim(DS_Customers_Table::get_order_data( self::$customer_id, '_billing_phone'));

			if (strlen($billing_phone) > 9) {

			    $billing_phone = substr($billing_phone, -10); //only keep last digits

			}

			return '92' . $billing_phone;
		}

		public static function get_customer_name() {

			return DS_Customers_Table::get_order_data( self::$customer_id, '_billing_first_name');

			// deprecated
			return DS_Customers_Table::get_user_data( self::$customer_id, 'first_name' );

		}

		public static function make_sms_message() {

			$message = '';
			$message .= 'Hey ';
			$message .= ucfirst( self::get_customer_name() );
			$message .= ', ';
			$message .= '<p>'.self::$message.'</p>';

			return urlencode( strip_tags( $message ) );
		}

		public static function finally_call_api() {

			try {

				// Initialization
				$ch = curl_init();

				// URL to send request
				curl_setopt($ch, CURLOPT_URL, self::get_api_formatted_url() );

				// Return instead outputting
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				// Execute the request and fetch the response
				$response = curl_exec($ch);
				// Close and free up the curl handler
				curl_close($ch);

				$responseArray = DS_Helpers::get_array_from_xml($response);

				if ( $responseArray['response'] == "OK" ) {

					return "Sent Successfully! Here is message ID:" . $responseArray['data'];

				}

				// Display raw output
				var_dump($response);
				die;

				return false;


			} catch (SoapFault $e) {

				print_r($e);

			}

		}


		public static function authenticate_request() {

			try {

				// Initialization
				$ch = curl_init();

				// URL to send request
				curl_setopt($ch, CURLOPT_URL, self::get_authentication_url() );

				// Return instead outputting
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				// Execute the request and fetch the response
				$response = curl_exec($ch);
				// Close and free up the curl handler
				curl_close($ch);

				$responseArray = DS_Helpers::get_array_from_xml($response);

				if ( $responseArray['response'] == "OK" ) {

					self::$session_id = $responseArray['data'];

					return true;

				}

				// Display raw output
				var_dump($response);


			} catch (SoapFault $e) {

				print_r($e);

			}

		}
	}

}
