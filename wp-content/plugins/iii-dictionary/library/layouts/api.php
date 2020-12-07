<?php
	$route = get_route();
	
	if( !isset($route[1]) ) : ?>
<!DOCTYPE html>
<html><head></head></html>
<?php endif ?>
<?php
	global $wpdb;
	$task = $route[1];
	if(isset($route[2])) {
		$do = $route[2];
	}

	if($task == 'activation')
	{
		if($do == 'register_id')
		{
			// $_POST['c']; // activation code, require sha1
			// $_POST['id']; // machine ID

			if(empty($_POST['c']) || empty($_POST['id'])) {
				die('0');
			}

			$code = $wpdb->get_row(
				$wpdb->prepare('SELECT c.*, us.activated_by, COUNT(activated_by) AS activated_times
								FROM ' . $wpdb->prefix . 'dict_credit_codes AS c
								LEFT JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.activation_code_id = c.id
								WHERE SHA1(encoded_code) = %s', $_POST['c'])
			);

			$json['status'] = 0;

			if(is_null($code->id))
			{
				// Invalid credit code number
				$json['status'] = 0;
			}
			else if($code->typeid == 1)
			{
				// invalid credit code
				$json['status'] = 0;
			}
			else if(!$code->active)
			{
				// inactive code
				$json['status'] = 0;
			}
			else if($code->activated_times == $code->no_of_students)
			{
				// exceeded maximum number of activation times
				$json['status'] = 0;
			}
			else
			{
				// add the machine id to user subscription table
				$json['status'] = 1;

				// check if user already add the code
				$check_result = $wpdb->get_row(
					'SELECT id FROM ' . $wpdb->prefix . 'dict_user_subscription 
					WHERE activation_code_id = ' . $code->id . ' AND activated_by = \'' . esc_sql($_POST['id']) . '\''
				);

				if(empty($check_result)) {
					// calculate expired date
					$m = $code->no_of_months_dictionary;
					$starting_date = date('Y-m-d', time());
					$expired_date = date('Y-m-d', strtotime('+' . $m . ' months', strtotime($starting_date)));

					$sub_data['activation_code_id'] = $code->id;
					$sub_data['user_id'] = $_POST['id'];
					$sub_data['starting_date'] = $starting_date;
					$sub_data['expired_date'] = $expired_date;
					$sub_data['code_typeid'] = $code->typeid;
					$sub_data['group_id'] = 0;
					$sub_data['sat_class_id'] = 0;
					$sub_data['number_of_students'] = $code->no_of_students;
					$sub_data['number_of_months'] = $m;
					$sub_data['dictionary_id'] = $code->dictionary_id;

					MWDB::add_user_subscription($sub_data);
				}
			}

			echo $json['status']; die;
		}

		if($do == 'init')
		{
			/*
			 * accepted params:
			 *	+ $_POST['c'] // sha1 activation code
			 *	+ $_POST['id'] // machine id
			 *	+ $_POST['sa'] // 15 characters salt string
			 * Response
			 *  + -1: Required parameter not found
			 * 	+ 0: subscription expired
			 *	+ 1: subscription valid
			 * 	+ <string>: new activation code
			 */

			$code = $_POST['c'];
			$id = $_POST['id'];
			$salt = $_POST['sa'];

			if(empty($code) || empty($id) || empty($salt))
			{
				echo '-1'; die;
			}

			$sub = $wpdb->get_row('SELECT id FROM ' . $wpdb->prefix . 'dict_user_subscription
									WHERE activated_by = \'' . esc_sql($id) . '\' AND expired_on >= \'' . date('Y-m-d', time()) . '\'');

			if(!empty($sub)) {
				// store salt string so we can check later
				$wpdb->update(
					$wpdb->prefix . 'dict_user_subscription',
					array('salt' => $salt),
					array('activated_by' => $id)
				);

				echo '1';
			}
			else {
				echo '0';
			}

			die;
		}

		if($do == 'subscribe')
		{
			// $_GET['c'] // sha1('Machine ID') + md5(sha1(<salt string>))
			// $_GET['ac'] // activation code
			$salted_key = $_GET['c'];
			$code = $_GET['ac'];

			$subs = $wpdb->get_results(
				'SELECT us.id, us.dictionary_id, d.name AS dictionary_name, us.number_of_students, expired_on
				FROM ' . $wpdb->prefix . 'dict_user_subscription AS us
				JOIN ' . $wpdb->prefix . 'dict_dictionaries AS d ON d.id = us.dictionary_id
				WHERE CONCAT(SHA1(activated_by), MD5(SHA1(salt))) = \'' . $salted_key . '\''
			);

			if(empty($subs)) {
				ik_enqueue_messages(__('Your Device hasn\'t subscribed any Dictionaries yet.', 'iii-dictionary'), 'error');
			}
			else {
				$subscribed_dictionaries = array();
				foreach($subs as $sub) {
					// store dictionary to avoid duplication
					$subscribed_dictionaries[$sub->dictionary_id] = $sub->dictionary_name;

					// delete salt key
					$wpdb->query('UPDATE ' . $wpdb->prefix . 'dict_user_subscription SET salt = NULL WHERE id = ' . $sub->id);

					// store subscription detail so we can retrieve it later
					$sub_item = new stdClass;
					$sub_item->id = $sub->id;
					$sub_item->typeid = 2;
					$sub_item->type = 'Dictionary (From Device)';
					$sub_item->number_of_students = $sub->number_of_students;
					$sub_item->expired_on = $sub->expired_on;
					$sub_item->dictionary = $sub->dictionary_name;
					$sub_item->group_name = null;
					set_device_sub_list($sub_item);
				}

				foreach($subscribed_dictionaries as $id => $name) {
					$subscribed_dictionary_names[] = $name;

					// set subscription status
					set_dictionary_subscription($id, true, true);
				}

				if(!empty($subscribed_dictionaries)) {
					$s = implode(', ', $subscribed_dictionary_names);
					ik_enqueue_messages(sprintf(__('Your Device has subscribed %s Dictionary', 'iii-dictionary'), $s), 'success');
				}
			}

			wp_redirect(locale_home_url());
			die;
		}

		/////////// FOR OFFLINE DICRIONARY PROGRAM ////////////////////
		// active a code
		if($do == 'activate') {
			// $_POST['c']; // activation code, require sha1
			// $_POST['id']; // machine ID, must be md5 before sending
			// $_POST['email']; //email user
	
			$json['status'] = 0;
			$json['msg'] = 'Invalid parameter';

			if(empty($_POST['c']) || empty($_POST['id']) || empty($_POST['email'])) {
				die(json_encode($json));
			}
			$email = $wpdb->get_row(
				$wpdb->prepare('SELECT u.ID AS id
								FROM '. $wpdb->prefix .'users AS u 
								WHERE u.user_email = %s', $_POST['email'])
			);
			$code = $wpdb->get_row(
				$wpdb->prepare('SELECT c.*, us.activated_by, us.activated_on, us.expired_on, COUNT(activated_by) AS activated_times, us.salt
								FROM ' . $wpdb->prefix . 'dict_credit_codes AS c
								LEFT JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.activation_code_id = c.id
								WHERE SHA1(encoded_code) = %s', $_POST['c'])
			);
			
			if(is_null($code->id)) {
				// Invalid credit code number
				$json['status'] = 0;
				$json['msg'] = 'Invalid activation code';
			}
			else if(in_array($code->typeid, array(SUB_TEACHER_TOOL, SUB_SAT_PREPARATION)) === true) {
				// invalid credit code. We only accept Dictioanry code
				$json['status'] = 0;
				$json['msg'] = 'Incorrect activation code';
			}
			else if(!$code->active) {
				// inactive code
				$json['status'] = 0;
				$json['msg'] = 'Incorrect activation code';
			} 
			else if(is_null($email->id)) {
				// Invalid email
				$json['status'] = 0;
				$json['msg'] = 'Incorrect email';
			}
			else {
				
				
				// add the id to user subscription table

				// check if user already activate this code
				$check_result = $wpdb->get_row(
					'SELECT id FROM ' . $wpdb->prefix . 'dict_user_subscription 
					WHERE activation_code_id = ' . $code->id . ' AND activated_by = \'' . esc_sql($email->id) . '\''
				);

				if(empty($check_result)) {
					// Number of license is used up for this activation code
					if($code->activated_times == $code->no_of_students) {
						$json['status'] = 0;
						$json['msg'] = 'Number of license is used up for this activation code. Please enter a different code';
					}
					else {
						// calculate expired date
						$m = $code->no_of_months_dictionary;
						$starting_date = date('Y-m-d', time());
						$expired_date = date('Y-m-d', strtotime('+' . $m . ' months', strtotime($starting_date)));

						$sub_data['activation_code_id'] = $code->id;
						$sub_data['user_id'] = $email->id;
						$sub_data['starting_date'] = $starting_date;
						$sub_data['expired_date'] = $expired_date;
						$sub_data['code_typeid'] = $code->typeid;
						$sub_data['group_id'] = 0;
						$sub_data['sat_class_id'] = 0;
						$sub_data['number_of_students'] = $code->no_of_students;
						$sub_data['number_of_months'] = $m;
						$sub_data['dictionary_id'] = $code->dictionary_id;
						$sub_data['salt'] = $_POST['id'];

						if(MWDB::add_user_subscription($sub_data)) {
							$json['status'] = 1;
							$json['expired_on'] = $expired_date;
							$json['dict_id'] = $code->dictionary_id;
							$json['msg'] = 'Activated';
						}
						else {
							$json['status'] = 0;
							$json['msg'] = 'An error occurred, cannot activate this code';
						}
					}
				}
				else {
					
					$bac = $wpdb->update($wpdb->prefix . 'dict_user_subscription', array('salt' => $_POST['id']), array('activation_code_id' => $code->id, 'activated_by' => $email->id ));
					$m = $code->no_of_months_dictionary;
					$starting_date = date('Y-m-d', time());
					$expired_date = date('Y-m-d', strtotime('+' . $m . ' months', strtotime($starting_date)));
					$json['status'] = 1;
					$json['expired_on'] = $expired_date;
					$json['dict_id'] = $code->dictionary_id;
					$json['msg'] = 'Activated another machine successfully';
					
				}
			}

			die(json_encode($json));
		}
		
		if($do == 'check_id') { 
		
			// $_POST['c']; // activation code, require sha1
			// $_POST['id']; // machine ID, must be md5 before sending
			// $_POST['email']; //email user
	
			$json['status'] = 0;
			$json['msg'] = 'Invalid parameter';
			if(empty($_POST['c']) || empty($_POST['id']) || empty($_POST['email'])) {
				die(json_encode($json));
			}
			$email = $wpdb->get_row(
				$wpdb->prepare('SELECT u.ID AS id
								FROM '. $wpdb->prefix .'users AS u 
								WHERE u.user_email = %s', $_POST['email'])
			);
			$code = $wpdb->get_row(
				$wpdb->prepare('SELECT c.*, us.activated_by, us.activated_on, us.expired_on, COUNT(activated_by) AS activated_times, us.salt
								FROM ' . $wpdb->prefix . 'dict_credit_codes AS c
								LEFT JOIN ' . $wpdb->prefix . 'dict_user_subscription AS us ON us.activation_code_id = c.id
								WHERE SHA1(encoded_code) = %s', $_POST['c'])
			);
			$check_result = $wpdb->get_row(
				'SELECT id FROM ' . $wpdb->prefix . 'dict_user_subscription 
				WHERE activation_code_id = ' . $code->id . ' AND activated_by = \'' . esc_sql($email->id) . '\' AND salt = \'' . esc_sql($_POST['id']) . '\''
			);
			if(!empty($check_result)) { 
				$json['status'] = 1;
				$json['msg'] 	= 'OK'; 
			}else {
				$json['status'] = 0;
				$json['msg'] = 'You have activated this activation code already';
			}
			die(json_encode($json));
		}
	}

	if($task == 'paypalipn')
	{
		define("DEBUG", 0);
		// Set to 0 once you're ready to go live
		define("USE_SANDBOX", 0);
		define("LOG_FILE", "./ipn.log");
		// Read POST data
		// reading posted data directly from $_POST causes serialization
		// issues with array data in POST. Reading raw POST data from input stream instead.
		$raw_post_data = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);
		$myPost = array();
		foreach ($raw_post_array as $keyval) {
			$keyval = explode ('=', $keyval);
			if (count($keyval) == 2)
				$myPost[$keyval[0]] = urldecode($keyval[1]);
		}
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		if(function_exists('get_magic_quotes_gpc')) {
			$get_magic_quotes_exists = true;
		}
		foreach ($myPost as $key => $value) {
			if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
				$value = urlencode(stripslashes($value));
			} else {
				$value = urlencode($value);
			}
			$req .= "&$key=$value";
		}
		// Post IPN data back to PayPal to validate the IPN data is genuine
		// Without this step anyone can fake IPN data
		if(USE_SANDBOX == true) {
			$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		} else {
			$paypal_url = "https://www.paypal.com/cgi-bin/webscr";
		}
		$ch = curl_init($paypal_url);
		if ($ch == FALSE) {
			return FALSE;
		}
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		if(DEBUG == true) {
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
		}
		// CONFIG: Optional proxy configuration
		//curl_setopt($ch, CURLOPT_PROXY, $proxy);
		//curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
		// Set TCP timeout to 30 seconds
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
		// CONFIG: Please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path
		// of the certificate as shown below. Ensure the file is readable by the webserver.
		// This is mandatory for some environments.
		//$cert = __DIR__ . "./cacert.pem";
		//curl_setopt($ch, CURLOPT_CAINFO, $cert);
		$res = curl_exec($ch);
		if (curl_errno($ch) != 0) // cURL error
			{
			if(DEBUG == true) {	
				error_log(date('[Y-m-d H:i e] '). "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 3, LOG_FILE);
			}
			curl_close($ch);
			exit;
		} else {
				// Log the entire HTTP response if debug is switched on.
				if(DEBUG == true) {
					error_log(date('[Y-m-d H:i e] '). "HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req" . PHP_EOL, 3, LOG_FILE);
					error_log(date('[Y-m-d H:i e] '). "HTTP response of validation request: $res" . PHP_EOL, 3, LOG_FILE);
				}
				curl_close($ch);
		}
		// Inspect IPN validation result and act accordingly
		// Split response headers and payload, a better way for strcmp
		$tokens = explode("\r\n\r\n", trim($res));
		$res = trim(end($tokens));
		if (strcmp ($res, "VERIFIED") == 0) {
			// check whether the payment_status is Completed
			// check that txn_id has not been previously processed
			// check that receiver_email is your PayPal email
			// process payment and mark item as paid.

			// assign posted variables to local variables
			//$item_name = $_POST['item_name'];
			//$item_number = $_POST['item_number'];
			$payment_status = $_POST['payment_status'];
			$payment_amount = $_POST['mc_gross'];
			$payment_currency = $_POST['mc_currency'];
			$txn_id = $_POST['txn_id'];
			//$receiver_email = $_POST['receiver_email'];
			$payer_email = $_POST['payer_email'];
			$payment_date = $_POST['payment_date'];
			$user_id = $_POST['custom'];

			global $wpdb;

			// check that payment_amount/payment_currency are correct
			$cart = get_user_cart($user_id);

			if($cart->total_amount == $payment_amount && $payment_currency == 'USD') {
				// store transaction history
				$wpdb->insert(
					$wpdb->prefix . 'dict_paypal_transaction_history',
					array(
						'user_id' => $user_id,
						'txn_id' => $txn_id,
						'payment_status' => $payment_status,
						'payer_email' => $payer_email,
						'payment_amount' => $payment_amount,
						'payment_date' => $payment_date
					)
				);

				// checkout cart items
				ik_checkout_cart_items(PAYMENT_METHOD_PAYPAL, $wpdb->insert_id, $user_id);
				// ugly hack to return success message back to the user
				update_user_meta($user_id, 'ik-messages', $_SESSION['message_queue']);
				update_user_meta($user_id, 'ik-paypal-refresh', 1);
			}
			else {
				$message = new stdClass;
				$message->type = 'message';
				$message->title = 'Messages';
				$message->msg = 'Paid amount is not enought';
				$message->label = 'Error';
				update_user_meta($user_id, 'ik-messages', array($message));
			}

			if(DEBUG == true) {
				error_log(date('[Y-m-d H:i e] '). "Verified IPN: $req ". PHP_EOL, 3, LOG_FILE);
			}
		} else if (strcmp ($res, "INVALID") == 0) {
			// log for manual investigation
			// Add business logic here which deals with invalid IPN messages
			if(DEBUG == true) {
				error_log(date('[Y-m-d H:i e] '). "Invalid IPN: $req" . PHP_EOL, 3, LOG_FILE);
			}
		}
	}
