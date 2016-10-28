<?php date_default_timezone_set("Africa/Kampala"); ?>
<?php
	
	ini_set('max_execution_time',10000);
	
	/*
	$_SESSION['MM_Username'] = NULL;
	$_SESSION['MM_UserGroup'] = NULL;
	$_SESSION['PrevUrl'] = NULL;
	unset($_SESSION['MM_Username']);
	unset($_SESSION['MM_UserGroup']);
	unset($_SESSION['PrevUrl']);

	$logoutGoTo = "login.php";
	*/

class vp_{
	
	public $mm_api_url = "https://paymentsapi2.yo.co.ug/ybs/task.php";
	public $mm_api_key = "100462716541";
	public $mm_api_pass = "hYCt-38W3-CMtQ-OpHY-TmvM-CpZC-uyQc-Lea6";
	public $sent_amt = 0;
		
	function get_record ($table, $col, $conditions) {
		$data = '';
		$start = mysql_query("select ".$col." from `".$table."` ".$conditions);
		if($start) {
		if(mysql_num_rows($start) > 0) {
			$end = mysql_fetch_assoc($start); //or die(mysql_error());
			$data = $end[mysql_field_name($start,0)];
			mysql_free_result($start);
			return $data;
		} else return '';
		} else return mysql_error();
	}

	function customer_balance($cust)
	{
		$last_id = $this->get_record("payment","pid","where funds_source != 'transaction_bal' and `status` = 'processed' and gateway_provider != '-' and customer = '".$cust."' order by pid desc limit 1");
		
		return $this->get_record("payment","sum(amt)","where funds_source = 'transaction-bal' and `status` = 'processed' and gateway_provider = '-' and customer = '".$cust."' and pid > '".$last_id)."'";
	}
	
	function min_amt($order)
	{
		$total = 0;
		$period = $this->get_record("order","payment_period","where id = '".$order."'");
		$order_items = $this->get_records("order_item","item","where `order` = '".$order."'");
		foreach($order_items as $item)
		{
			//Daily Rate
			$total += $this->get_record("payment_plan","cost","where payment_interval = 'monthly' and payment_period = '".$period."' and product = '".$item."'");
		}
		
		return $total;
	}
	
	function period_active($order, $amt)
	{
		$days = 0;
		$months = 0;
		$balance_amt = $amt;
		
		//Applies to hire purchase orders only
		$u = mysql_query("select * from payment_plan where product in (select item from order_item where `order` = '".$order."') and cost <= '".$amt."' order by cost desc");
		if(mysql_num_rows($u) > 0)
		{
			$u1 = mysql_fetch_assoc($u);
			do
			{
				if($balance_amt >= $u1['cost'])
				{
					switch($u1['payment_interval'])
					{
						case 'daily': $days += 1;
									  $balance_amt -= $u1['cost'];
									  break;
						case 'weekly': 	$days += 7;
										$balance_amt -= $u1['cost'];
										break;
						case 'monthly': $months += 1;
										$balance_amt -= $u1['cost'];
										break;
					}
				}
			} while ($u1 = mysql_fetch_assoc($u));
		}
		
		return array("days"=>$days,"months"=>$months,"balance"=>$balance_amt);
	}
	
	function unpaid_days($order)
	{
		$total = 0;
		if($this->get_record("order","payment_plan","where id = '".$order."'") == "hire")
		{
			//all days should be catered for
			$next_pay_date = $this->get_record("payment_schedule","next_payment_date","where `order` = '".$order."'");
			$completion_date = $this->get_record("order","completion_date","where id = '".$order."'");
			
			$p = mysql_query("select datediff('".$completion_date."','".$next_pay_date."') as days");
			$p1 = mysql_fetch_assoc($p);
			$total = $p1['days'];
			mysql_free_result($p);
		}
		return $total;
	}
		
	function get_records ($table, $col, $conditions) {
		$data = array();
		$start = mysql_query("select ".$col." from `".$table."` ".$conditions);
		if($start) {
		if(mysql_num_rows($start) > 0) {
			$end = mysql_fetch_assoc($start); //or die(mysql_error());
			do 
			{
				$data[] = $end[mysql_field_name($start,0)];
			} while($end = mysql_fetch_assoc($start));
			mysql_free_result($start);
			return $data;
		} else return array();
		} else return array(mysql_error());
	}
	
	function random_password() {
	    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
	    srand((double)microtime()*1000000);
		$i = 0;
		$pass = '' ;
		while ($i <= 7) {
			$num = rand() % 33;
			$tmp = substr($chars, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}
		return $pass;
	}
	
	function format_number($number)
	{
		$number = preg_replace("/[^0-9]/","",urldecode($number));
		$number = round($number);
		$country_code = 256;
		$number_len = 12;
		
		if(substr($number,0,strlen($country_code)) == $country_code && strlen($number) == $number_len) return $number;
		else if((strlen($number)+strlen($country_code)) == $number_len) return $country_code.$number;
		else return $number;
	}
		
	function format_url($url, $to_remove)
	{
		$formatted = "";
		$k = explode("&", $url);
		foreach($k as $item)
		{
			if(substr($item,0,strlen($to_remove)) != $to_remove && trim($item) != "")
			{
				$formatted .= $item."&";
			}
		}
		return $formatted;
	}
	
	function order_total_cost($order)
	{
		$total = 0;
		$a = mysql_query("select * from order_item where `order` = '".$order."'");
		$b = mysql_fetch_assoc($a);
		do
		{
			$total += $b['quantity']*$b['unit_cost'];
		} while ($b = mysql_fetch_assoc($a));
		
		return $total;
	}
	
	function order_balance($order)
	{
		$paid = $this->get_record("payment","sum(amt)","where `order` = '".mysql_real_escape_string($order)."' and `status` = 'processed'");
		return $this->order_total_cost($order) - $paid;
	}
	
	function hire_purchase_order_total($order)
	{
		$total = 0; $item = "";
		$a = mysql_query("select * from order_item where `order` = '".$order."'");
		$b = mysql_fetch_assoc($a);
		do
		{
			$item = $b['item'];
			$total += ($b['quantity']*$b['unit_cost'])*((($this->get_record("payment_plan","annual_interest","where product = '".$item."'")/12)*$this->get_record("order","payment_period","where id = '".$order."'"))/100);
		} while ($b = mysql_fetch_assoc($a));
		
		return $this->order_total_cost($order) + $total;
	}
	
	function deposit($amount,$phoneNumber,$transactionID) {
		$act_provider_code = "MTN_UGANDA";
		$prefix = substr($phoneNumber,0,5);
		if($prefix == "25675" || $prefix == "25670")
		{
			$act_provider_code = "AIRTEL_UGANDA";
		}
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
					<AutoCreate>
					<Request>
					<APIUsername>'.$this->mm_api_key.'</APIUsername>
					<APIPassword>'.$this->mm_api_pass.'</APIPassword>
					<Method>acdepositfunds</Method>
					<NonBlocking>TRUE</NonBlocking>
					<Amount>'.$amount.'</Amount>
					<Account>'.$phoneNumber.'</Account>
					<AccountProviderCode>'.$act_provider_code.'</AccountProviderCode>
					<Narrative>'.$transactionID.'</Narrative>
					<ExternalReference>'.$transactionID.'</ExternalReference>
					</Request>
				</AutoCreate>';
				$sendToYo = $this->post($xml, $this->mm_api_url);
				$sxml = simplexml_load_string($sendToYo);
				$first = json_encode($sxml);
				$values = json_decode($first,true);
				if(isset($values['Response']['TransactionReference']))
				{
					return $values['Response']['TransactionReference'];
				} else
				{
					return "";
				}
	   }
	
	function post_request($url, $data, $referer='') {
			
    	$data = http_build_query($data);
 
		// parse the given URL
		$url = parse_url($url);
 
		/*if ($url['scheme'] != 'http') { 
			die('Error: Only HTTP request are supported !');
		}*/
 
		// extract host and path:
		$host = $url['host'];
		$path = $url['path'];
 
		// open a socket connection on port 80 - timeout: 30 sec
		$fp = fsockopen($host, 80, $errno, $errstr, 30);
 
    	if ($fp){
 
			// send the request headers:
			fputs($fp, "POST $path HTTP/1.1\r\n");
			fputs($fp, "Host: $host\r\n");
	 
			if ($referer != '')
				fputs($fp, "Referer: $referer\r\n");
	 
				fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
				fputs($fp, "Content-length: ". strlen($data) ."\r\n");
				fputs($fp, "Connection: close\r\n\r\n");
				fputs($fp, $data);
	 
				$result = ''; 
				while(!feof($fp)) {
					// receive the results of the request
					$result .= fgets($fp, 128);
				}
				return $result;
    		}
		else { 
			return array(
				'status' => 'err', 
				'error' => "$errstr ($errno)"
		);
    }
 
    // close the socket connection:
    fclose($fp);
 
    // split the result header from the content
    $result = explode("\r\n\r\n", $result, 2);
 
    $header = isset($result[0]) ? $result[0] : '';
    $content = isset($result[1]) ? $result[1] : '';
 
    // return as structured array:
    /*return array(
        'status' => 'ok',
        'header' => $header,
        'content' => $content
    );*/
	  $this->response = $content;
	  
		
    }
	
	function transaction_status($ref) {
		 $xml = '<?xml version="1.0" encoding="UTF-8"?>
					<AutoCreate>
						<Request>
						<APIUsername>'.$this->mm_api_key.'</APIUsername>
						<APIPassword>'.$this->mm_api_pass.'</APIPassword>
						<Method>actransactioncheckstatus</Method>
						<TransactionReference>'.$ref.'</TransactionReference>
						</Request>
					</AutoCreate>';
			 $checkStatus = $this->post($xml, $this->mm_api_url);
			 $sxml = simplexml_load_string($checkStatus);
			 $a = json_encode($sxml);
			 $b = json_decode($a,true);
			 //print_r($b);Amount
			 if($b['Response']['TransactionStatus'] == 'SUCCEEDED')
			 {
				 $this->sent_amt = $b['Response']['Amount'];
			 }
			 return $b['Response']['TransactionStatus'];
	   }
	
	function post($xml, $url) {
				$options = array  
				( 
					CURLOPT_URL            => $url,
					CURLOPT_HTTPHEADER => array("Content-Type: text/xml"),
					CURLOPT_POST           => 1, 
					CURLOPT_POSTFIELDS     => $xml,
					CURLOPT_SSL_VERIFYPEER => false, 
					CURLOPT_RETURNTRANSFER => 1 
				); 
				
				$curl = curl_init(); 
				curl_setopt_array($curl, $options); 
				$response = curl_exec($curl);
				 if(!$response) { $response = curl_error($curl); }
				curl_close($curl); 
				return $response;
	   }
	   
	function send_email($sender_address, $sender_name, $recipient_name, $recipient_address, $email_subject, $email_content)
	{
		require_once("mail/class.phpmailer.php");
		$mail = new PHPMailer();
		$mail->IsMail();             
		
		//Headers
		$mail->From = $sender_address;
		$mail->FromName = $sender_name;
		$mail->Sender = $sender_address;
		$mail->Subject = $email_subject;
		$mail->Body = $email_content;
		
		//Iage
		$logo = 'img/logo.png';
		if(file_exists($logo)) {
			$mail->AddEmbeddedImage($logo,'logo',$logo);
		} else {
			$mail->AddEmbeddedImage($logo,'logo',$logo);
		}
		
		$mail->IsHTML(true);
		$mail->AddAddress($recipient_address, $recipient_name);
		$mail->Send();
	}
	
	function generate_device_serial($product, $index,$type)
	{
		$serial = "";
		$serial .= $this->get_record("product","product_code","where pid = '".mysql_real_escape_string($product)."'");
				
		if(strlen($serial) < 4)
		{
			$i = 4-strlen($serial);
			for($i=0;$i <= (4-strlen($serial)); $i++)
			{
				$serial = "0".$serial;
			}
		}
		
		$serial .= substr(date("Y"),2);
		$phone = $this->get_record("product_inventory","model_phone","where id = '".mysql_real_escape_string($index)."'");
		if($type == "regular")
		{
			$serial .= "R";
		} else
		{
			$serial .= "P";
		}
		
		//$alphabet = range('A','Z');
		//$serial .= $alphabet[array_rand($alphabet)];
		
		if(strlen($index) <= 6)
		{
			for($i=strlen($index);$i<6;$i++)
			{
				$serial .= "0";
			}
			$serial .= $index;
		} else
		{
			$serial .= $index;
		}
	return $serial;	
	}
	
	function generate_device_serial_old_style($product, $index)
	{
		$serial = "";
		$serial .= $this->get_record("product","product_code","where pid = '".mysql_real_escape_string($product)."'");
		
		if(strlen($serial) < 4)
		{
			$i = 4-strlen($serial);
			for($i=0;$i < (4-strlen($serial)); $i++)
			{
				$serial .= "0";
			}
		}
		
		$serial .= substr(date("Y"),2);
		$alphabet = range('A','Z');
		$serial .= $alphabet[array_rand($alphabet)];
		
		if(strlen($index) < 6)
		{
			for($i=strlen($index);$i<6;$i++)
			{
				$serial .= "0";
			}
			$serial .= $index;
		} else
		{
			$serial .= $index;
		}
	return $serial;	
	}
	
	function pmt($apr, $term, $loan) {
	  $term = $term * 12;
	  $apr = $apr / 1200;
	  $amount = $apr * -$loan * pow((1 + $apr), $term) / (1 - pow((1 + $apr), $term));
	  return $amount;
	}
	
	
	function change_device_status($device_id,$to_do='turn_off',$print_out=false)
	{
		$phone = $this->get_record("product_inventory","model_phone","where id = '".$device_id."'");
		if($this->get_record("sms_out","count(*)","where recipient = '".$phone."' and `status` = 'pending' and sms_gateway = 'android_device'") == 0)
		{
			$device_status = $this->get_record("product_inventory","device_status","where id = '".$device_id."'");
			if("turn_".$device_status != $to_do)
			{
				switch($to_do)
				{
					case 'turn_on': $k = mysql_query("insert into sms_out (sender, recipient, message, sms_gateway) values ('VP', '".$phone."', 'START 123456', 'android_device')");
									if($k)
									{
										if($print_out) { print("Logged message to turn on device: ".$device_id."\n"); }
										//Update Device Status
										mysql_query("update product_inventory set device_status = 'on' where id = '".$device_id."'");
										return true;
									}
									break;
					case 'turn_off':  $k = mysql_query("insert into sms_out (sender, recipient, message, sms_gateway) values ('VP', '".$phone."', 'STOP 123456', 'android_device')");
									  if($k)
									  {
										if($print_out) { print("Logged message to turn off device: ".$device_id."\n"); }
										//Update Device Status
										mysql_query("update product_inventory set device_status = 'off' where id = '".$device_id."'");
										return true;
									  }
									break;
				}
			
			}	
		}
		
		return false;
	}
	
	
	function send_sms($sender,$recipient,$message)
	{
		 $a = mysql_query("insert into sms_out (sender, recipient, message, sms_gateway) values ('".mysql_real_escape_string($sender)."', '".mysql_real_escape_string($recipient)."', '".mysql_real_escape_string($message)."', 'other_gateway')");
		 if($a) 
		 {
			 return true;
		 } return false;
	}
	
	function send_json($url,$data)
	{
		$content = json_encode($data);
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER,
				array("Content-type: application/json",'Authorization: Basic '. base64_encode("VillagePO:Vp1sth3"))
		);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //curl error SSL certificate problem, verify that the CA cert is OK
		 
		$result     = curl_exec($curl);
		if(is_array($result))
		{
			$response   = json_decode($result);
		} else
		{
			$response = $result;
		}
		//var_dump($response);
		curl_close($curl);
		return $response;
	}
	
	function gateway_send()
	{
		 	/*Billing Account. This used to check balance, TopUp the account and obtain SMS CDRs
			url: http://switch1.yo.co.ug/ybs_p/portal
			username:  greenpaygo
			account :  1000501075
			password : 637938774823746
			
			
			SMS GATEWAY CREDENTIALS
			sms gateway api username/ybsacctno: 1000501075
			sms gateway api password: URUTEyMm*/
			
			//Infobip details
			//Username: ensibuuko
			//Password: Opio432Ens
		 $y1 = mysql_query("select * from sms_out where /* sms_gateway = 'other_gateway' and */ `status` = 'pending'");
		 //echo mysql_num_rows($y1); exit;
		 if(mysql_num_rows($y1) > 0)
		 {
			 $y2 = mysql_fetch_assoc($y1);
			 do
			 {
				 if(trim($y2['recipient']) != "")
				 {
					 if(strtoupper(substr($y2['message'],0,4)) == 'STOP' || strtoupper(substr($y2['message'],0,4)) == 'STAR')
					 {
						 //echo $testing
						 $host = 'http://smgw1.yo.co.ug/sendsms?ybsacctno=1000501075&password='.urlencode("URUTEyMm").'&origin='.urlencode($y2['sender']).'&sms_content='.urlencode($y2['message']).'&destinations='.$this->format_number($y2['recipient']).'&nostore=1';
					 	//file_put_contents('/tmp/test.log', $host."\n\n", FILE_APPEND);
					 	//$host = 'http://199.38.182.205/api.php?sender=&message=&destination=&message_type=normal';
					 	//$username= 'simon@village-power.ch';
					 	//$password = 'Power!23';
					 	//$url = $host;
					 	$feedback = $this->sendRequest($host);
					 	//if($feedback<>"false"){
					 	$jk = mysql_query("update sms_out set `status` = 'sent', when_sent = '".date("Y-m-d H:i:s")."', more_info = '".mysql_real_escape_string($feedback)."' where id = '".$y2['id']."'");
					 //}
					 } else
					 {
						 $host = 'https://api.infobip.com/sms/1/text/single';
						 $feedback = $this->send_json($host,array("from"=>$y2['sender'],"to"=>$this->format_number($y2['recipient']),"text"=>$y2['message']));

						 $jk = mysql_query("update sms_out set `status` = 'sent', when_sent = '".date("Y-m-d H:i:s")."', more_info = '".mysql_real_escape_string($feedback)."' where id = '".$y2['id']."'");
					 
					 }
					 
					 /*if($jk)
					 {
						//Update Kit Satus
						$todo = preg_replace("/[^a-zA-Z]/","",strtolower($y2['message']));
						$recipient = $y2['recipient'];
						switch($todo)
						{
									case 'stop': $r = mysql_query("update product_inventory set device_status = 'off' where model_phone = '".mysql_real_escape_string($recipient)."'") or die(mysql_error());
												 print(mysql_affected_rows()." devices updated. - ".date("Y-m-d H:i:s")."\n");
												 break;
									case 'start': $s = mysql_query("update product_inventory set device_status = 'on' where model_phone = '".mysql_real_escape_string($recipient)."'");
													print(mysql_affected_rows()." devices updated. - ".date("Y-m-d H:i:s")."\n");
												 break;
									default: print($todo."\n");
											 break;
					}
				}*/
				 } 
				 else
				 {
					 mysql_query("delete from sms_out where id = '".$y2['id']."' limit 1");
				 }
			 } while ($y2 = mysql_fetch_assoc($y1));
			 return mysql_num_rows($y1);
		 } else return "0";
	}
	
	   function sendRequest($query) {
		 $curl = curl_init();		
		 # Create Curl Object
		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);	
		 # Allow self-signed certs
		 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0); 	
		 # Allow certs that do not match the hostname
		 curl_setopt($curl, CURLOPT_HEADER,0);			
		 # Do not include header in output
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);	
		# set the username and password
		curl_setopt($curl, CURLOPT_URL, $query);			
		# execute the query
		$result = curl_exec($curl);
		if ($result == false) {
		error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");	
		#log error if curl exec fails
		}
		curl_close($curl);
		return $result;
	   }
	   
	   function pending_orders($customer)
	   {
		   $orders = array();
		   $b = mysql_query("select distinct(`order`) from payment_schedule where `order` in (select id from `order` where customer = '".$customer."') and payment_ref is null");
		   if(mysql_num_rows($b) > 0)
		   {
			   $b1 = mysql_fetch_assoc($b);
			   do
			   {
				   $orders[] = $b1['order'];
			   } while ($b1 = mysql_fetch_assoc($b));
		   }
		   
		   return $orders;
	   }
	   
	   function toggle_order_device($order, $command)
	   {
		   $n = mysql_query("select item_disbursed from order_item where `order` = '".mysql_real_escape_string($order)."'");
		   if(mysql_num_rows($n) > 0)
					{
									$n1 = mysql_fetch_assoc($n);
									//Unlock Devices
									do
									{
										if(trim($n1['item_disbursed']) != "")
										{
											$this->change_device_status($n1['item_disbursed'],$command);
										}
									} while ($n1 = mysql_fetch_assoc($n));
					}
	   }
	
	function send_push_instruction($recipient,$amount,$name)
	{
		if(substr($recipient,0,5) == '25675')
		{
			$msg = $this->get_record("system_setting","content","where setting_name = 'airtel_mm_instruction'");
			$msg = str_replace("{name}",$name,$msg);
			$msg = str_replace("{amt}",number_format($amount),$msg);
			$msg = str_replace("{reason}",$recipient,$msg);
			$this->send_sms('VP',$recipient,$msg);
		}
		
	}
	
	function generate_bar_code($text)
	{
		//if(!file_exists('img/'.md5($text).'.png'))
		//{
		require_once('inventory_barcode_generator/BCGFontFile.php');
		require_once('inventory_barcode_generator/BCGColor.php');
		require_once('inventory_barcode_generator/BCGDrawing.php');
		
		require_once('inventory_barcode_generator/BCGcode128.barcode.php');
		
		$colorFront = new BCGColor(0, 0, 0);
		$colorBack = new BCGColor(255, 255, 255);
		
		$font = new BCGFontFile('inventory_barcode_generator/font/Arial.ttf', 13);
		$code = new BCGcode128(); // Or another class name from the manual
		$code->setScale(1); // Resolution
		$code->setThickness(30); // Thickness
		$code->setForegroundColor($colorFront); // Color of bars
		$code->setBackgroundColor($colorBack); // Color of spaces
		$code->setFont($font); // Font (or 0)
		$code->parse($text); // Text
		
		$drawing = new BCGDrawing('img/'.md5($text).'.png', $colorBack);
		$drawing->setBarcode($code);
		$drawing->draw();
		$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
		return "img/".md5($text).".png";
		//header('Content-Type: image/png');
		//} else return "img/".md5($text).".png";
	}
	
	function disburseDate($order){
	
		$disburse_date=$this->get_record("order_item","disburse_date","where `order` = '".$order."'");
		if($disburse_date!=""){
			$disburse_date = date("Y-m-d",strtotime($disburse_date));
			return $disburse_date;
		}
		else
			return "Pending";		
		}
				
		function installationDate($order){
			$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
			if($installation_date!=""){
				$installation_date = date("Y-m-d",strtotime($installation_date));
				return $installation_date;
			}
			else
				return "Pending";		
		}
		function lastPaymentDate($order){
			$last_pay_date=$this->get_record("payment","max(date)","where `order` = '".$order."'");
			if($last_pay_date!="")
				return $last_pay_date;
			else
				return "";		
		}
		function installationDate_format($order){
			$install_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
			if($install_date!=""){
				$installation_date=date("d-m-Y",strtotime($install_date));
				return $installation_date;
			}else
				return "Pending";		
		}

		function GPDate_format($order){
			$install_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
			if($install_date!=""){
				$installation_date=date("d-m-Y",strtotime($install_date));
				$gracePeriodEnd=date("d-m-Y",strtotime("+14days",strtotime($installation_date)));
				return $gracePeriodEnd;
			}else
				return "Pending";		
		}

		function m3_format($order){
			$install_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
			if($install_date!=""){
				$installation_date=date("d-m-Y",strtotime($install_date));
				$gracePeriodEnd=date("d-m-Y",strtotime("+14days",strtotime($installation_date)));
				$period=date("d-m-Y",strtotime("+90days",strtotime($gracePeriodEnd)));
				return $period;
			}else
				return "--";	
		}

		function m6_format($order){
			$install_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
			if($install_date !=""){
				$installation_date=date("d-m-Y",strtotime($install_date));
				$gracePeriodEnd=date("d-m-Y",strtotime("+14days",strtotime($installation_date)));
				$period=date("d-m-Y",strtotime("+180days",strtotime($gracePeriodEnd)));
				return $period;
			}else
				return "--";		
		}

		function m9_format($order){
			$install_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
			if($install_date!=""){
				$installation_date=date("d-m-Y",strtotime($install_date));
				$gracePeriodEnd=date("d-m-Y",strtotime("+14days",strtotime($installation_date)));
				$period=date("d-m-Y",strtotime("+270days",strtotime($gracePeriodEnd)));
				return $period;
			}else
				return "--";		
		}

		function m12_format($order){
			$install_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
			if($install_date!=""){
				$installation_date=date("d-m-Y",strtotime($install_date));
				$gracePeriodEnd=date("d-m-Y",strtotime("+14days",strtotime($installation_date)));
				$period=date("d-m-Y",strtotime("+360days",strtotime($gracePeriodEnd)));
				return $period;
			}else
				return "--";		
		}

		function loanPeriodNew($order, $date){
			$install_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
			if($install_date != ""){
				$new_date = strtotime($date);
				$installation_date=strtotime($install_date);
				$gracePeriodEnd = strtotime("+14days",$installation_date);
				$m3 = strtotime("+90days", $gracePeriodEnd);
				$m6 = strtotime("+180days", $gracePeriodEnd);
				$m9 = strtotime("+270days", $gracePeriodEnd);
				$m12 = strtotime("+360days", $gracePeriodEnd);

				if ($new_date <= $m3) {
					$period = '3 months';
					return $period;
				}elseif ($new_date > $m3 && $new_date <= $m6) {
					$period = '6 months';
					return $period;
				}elseif ($new_date > $m6 && $new_date <= $m9) {
					$period = '9 months';
					return $period;
				}elseif ($new_date > $m9 && $new_date <= $m12) {
					$period = '12 months';
					return $period;
				}else{
					$period = 'Above 12 months';
					return $period;
				}
			}else{
				return "--";
			}
		}
		
		function loanPeriodInt($order, $pid, $date){
			$install_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
			if($install_date != ""){
				$new_date = strtotime($date);
				$installation_date=strtotime($install_date);
				$gracePeriodEnd = strtotime("+14days",$installation_date);
				$m3 = strtotime("+90days", $gracePeriodEnd);
				$m6 = strtotime("+180days", $gracePeriodEnd);
				$m9 = strtotime("+270days", $gracePeriodEnd);
				$m12 = strtotime("+360days", $gracePeriodEnd);

				if ($new_date <= $m3){
					$period = 3;
					$query=mysql_query("UPDATE payment SET loan_period =$period where pid =$pid AND `order` = $order") or die(mysql_error());
					return $period;
				}elseif($new_date > $m3 && $new_date <= $m6) {
					$period = 6;
					$query=mysql_query("UPDATE payment SET loan_period =$period where pid =$pid AND `order` = $order") or die(mysql_error());
					return $period;
				}elseif($new_date > $m6 && $new_date <= $m9) {
					$period = 9;
					$query=mysql_query("UPDATE payment SET loan_period =$period where pid =$pid AND `order` = $order") or die(mysql_error());
					return $period;
				}elseif($new_date > $m9 && $new_date <= $m12) {
					$period = 12;
					$query=mysql_query("UPDATE payment SET loan_period =$period where pid =$pid AND `order` = $order") or die(mysql_error());
					return $period;
				}else{
					$period = '>12';
					$query=mysql_query("UPDATE payment SET loan_period ='".$period."' where pid =$pid AND `order` = $order") or die(mysql_error());
					return $period;
				}
			}else{
				return "--";
			}
		}
		
		function upfrontRequired($order){
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? $old_upfront : ceil(($old_upfront + $daily_pay_for_plan*14)/100)*100;
			return $upfront;
		}
		
		function instalment($order,$paymentId){		
			$totalAmtPaid = $this->get_record("payment","sum(amt) as totalPaid","where pid <= '".$paymentId."' and `order` = '".$order."' and status='processed'");
			//$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? $old_upfront : ($old_upfront + $daily_pay_for_plan*14);
			if($totalAmtPaid > $upfront)
					
				return $totalAmtPaid-$upfront;				 
			return 0;
		}
		
		function totalInstalments($order){		
			$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where `order` = '".$order."' and status='processed'");
			//$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? $old_upfront : ($old_upfront + $daily_pay_for_plan*14);
			if($totalAmtPaid > $upfront)		
				return $totalAmtPaid-$upfront;				
			return 0;
		}
		
		function totalAmtPaid($order){
			$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where `order` = '".$order."' and status='processed'");
			
			if($totalAmtPaid > 0)				
				return $totalAmtPaid;
			return 0;
		}

		//Sum Down Payment Made
		function sumDownpaymentMade($order){
			//$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? $old_upfront : ($old_upfront + $daily_pay_for_plan*14);
			$total_upfront="";
			if($upfront>0){
				$query=mysql_query("select pid,amt,date from payment where `order` ='".$order."'");
				if(mysql_num_rows($query)>0){
					$sum=0;
					while($row=mysql_fetch_assoc($query)){
						$sum+=$row['amt'];
						if($sum >= $upfront){
							$total_upfront=$sum;
							break;
						}
					}
				}
			}
			return $total_upfront;
		}
		//Sum Down Payment Made
		
		function downPaymentMadeDate($order){
			//$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? $old_upfront : ($old_upfront + $daily_pay_for_plan*14);
			$date="";
			if($upfront>0){
				$query=mysql_query("select pid,amt,date from payment where `order` ='".$order."'");
				if(mysql_num_rows($query)>0){
					$sum=0;
					while($row=mysql_fetch_assoc($query)){
						$sum+=$row['amt'];
						if($sum >= $upfront){
							$date=$row['date'];
							break;
						}
					}
				}
			}
			if ($date != "") {
				$query=mysql_query("UPDATE `order` SET dP_Date ='".$date."' where id ='".$order."'") or die(mysql_error());
			}else{
				$query=mysql_query("UPDATE `order` SET dP_Date = NULL where id ='".$order."'") or die(mysql_error());
			}
			
			return $date;
		}
		
		function fullPaymentMadeDate($order){
			$plan = $this->get_record("order","payment_plan","where `order` = '".$order."'");
			//$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? $old_upfront : ($old_upfront + $daily_pay_for_plan*14);
			
			$date="";
			if ($plan == 'hire') {
				if($upfront>0){
					$query=mysql_query("SELECT pid,amt,date, loan_period FROM payment WHERE `order` ='".$order."'");
					if(mysql_num_rows($query)>0){
						$sum=0;
						while($row=mysql_fetch_assoc($query)){
							$sum+=$row['amt'];
							$fullPayment=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='final' AND payment_period ='".$row['loan_period']."' ");
							$total = $upfront + $fullPayment;
							if($sum >= $total){
								$date=$row['date'];
								
								//$query=mysql_query("UPDATE `order` SET fP_Date ='".$date."' where id ='".$order."'") or die(mysql_error());
								
								break;
							}
						}
					}
				}
			}else{
				$query=mysql_query("SELECT pid,amt,date, loan_period FROM payment WHERE `order` ='".$order."'");
				if(mysql_num_rows($query)>0){
					$sum=0;
					while($row=mysql_fetch_assoc($query)){
						$sum+=$row['amt'];
						$fullPayment = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='full_payment' AND payment_period = 0 ");
						$total = $fullPayment;
						if($sum >= $total){
							$date=$row['date'];
							break;
						}
					}
				}
			}
			//return date;
		}

 		function schedulepaidUp($order){
 			//$counterId=$this->firstInstalmentId($order);
        	$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where `order` = '".$order."' and status='processed'");
			$finalPrice=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=0 and payment_interval='full_payment'");

			$outstanding = $finalPrice - $totalAmtPaid;
			return $outstanding;
 		}

 		function no_discount($order){
 			//$counterId=$this->firstInstalmentId($order);
        	$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where `order` = '".$order."' and status='processed'");
			$finalPrice=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=0 and payment_interval='full_payment'");
			
			$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$final=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='final' and payment_period=12"); 

			$total_cost = $upfront + $final;

			$outstanding = $total_cost - $totalAmtPaid;
			return $outstanding;
 		}

 		function cur_outstanding($order){
 			$today= date('Y-m-d');
 			$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
	        $gracePeriodEnd = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? date("Y-m-d H:i:s",strtotime("+14days",strtotime($installation_date))) : $installation_date;
	        $upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");

	        $payment_plan = $this->get_record("order","payment_plan", "where id='".$order."'");
 			$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where `order` = '".$order."' and status='processed'");
 			
 			if ($installation_date != NULL){
 				if($payment_plan == 'hire'){
					$Q1endDate=date("Y-m-d H:i:s",strtotime("+90days",strtotime($gracePeriodEnd))); 
					$Q2endDate=date("Y-m-d H:i:s",strtotime("+180days",strtotime($gracePeriodEnd))); 
					$Q3endDate=date("Y-m-d H:i:s",strtotime("+270days",strtotime($gracePeriodEnd))); 
					$Q4endDate=date("Y-m-d H:i:s",strtotime("+360days",strtotime($gracePeriodEnd)));

					if($today <= $Q1endDate || $this->Q1Outstanding($order) >= 0){
						$finalPrice=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=3 and payment_interval='final'");
					}elseif($today > $Q1endDate and $today <= $Q2endDate || $this->Q2Outstanding($order) >= 0){
						$finalPrice=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=6 and payment_interval='final'");
					}elseif($today > $Q2endDate and $today <= $Q3endDate || $this->Q3Outstanding($order) >= 0){
						$finalPrice=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=9 and payment_interval='final'");
					}elseif($today > $Q3endDate and $today <= $Q4endDate || $this->Q4Outstanding($order) >= 0){
						$finalPrice=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='final'");
					}else{
						$finalPrice=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='final'");
					}
					$required_amt = $finalPrice + $upfront;
				}else{
					$finalPrice=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=0 and payment_interval='full_payment'");
					$required_amt = $finalPrice;
				}

				$outstanding = $required_amt -$totalAmtPaid;
			}else{
				$outstanding=NULL;
			}
			
			return $outstanding;
 		}
		
		function downPayment($order){
        	$counterId=$this->firstInstalmentId($order);
        	$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$counterId."' and `order` = '".$order."' and status='processed'");
        	//$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? $old_upfront : ($old_upfront + $daily_pay_for_plan*14);
        	if ($totalAmtPaid >= $upfront) {
        		return $upfront;
        	}else{
        		return 0;
        	}
        }

		function firstInstalmentId($order){
			//$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? $old_upfront : ($old_upfront + $daily_pay_for_plan*14);
			$id=0;
			if($upfront>0){
				$query=mysql_query("select pid,amt from payment where `order` ='".$order."'");
				if(mysql_num_rows($query)>0){ 
					$sum=0;		
					while($row=mysql_fetch_assoc($query)){
						$sum+=$row['amt'];
						if($sum >= $upfront){
							$id=$row['pid'];
							break;
						}
					}
				}
				return $id;
			}
			return $id;
		}
                
                function startCounterOustanding($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $finalPriceQ1=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=3 and payment_interval='final'");
                //$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? $old_upfront : ($old_upfront + $daily_pay_for_plan*14);
                
                $totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$counterId."' and `order` = '".$order."' and status='processed'");
                $outstanding=($finalPriceQ1-($this->totalAmtPaid($order)-$upfront));
                return $outstanding;
                }
                else 
                return "Set Upfront Amount for Product";
                }
                
                function Q1Outstanding($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $finalPriceQ1=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=3 and payment_interval='final'");
                //$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? $old_upfront : ($old_upfront + $daily_pay_for_plan*14);
                
                $outstanding=($finalPriceQ1-($this->totalAmtPaid($order)-$upfront));
                return $outstanding;
                }
               
                }
                
                function Q2Outstanding($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $finalPriceQ2=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=6 and payment_interval='final'");
                //$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? $old_upfront : ($old_upfront + $daily_pay_for_plan*14);
                
                $outstanding=($finalPriceQ2-($this->totalAmtPaid($order)-$upfront));
                return $outstanding;
                }
               
                }
                
                 function Q3Outstanding($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $finalPriceQ3=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=9 and payment_interval='final'" );
                //$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? $old_upfront : ($old_upfront + $daily_pay_for_plan*14);
                
                $outstanding=($finalPriceQ3-($this->totalAmtPaid($order)-$upfront));
                return $outstanding;
                }
               
                }
                
                 function Q4Outstanding($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $finalPriceQ4=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='final'"); 
                //$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? $old_upfront : ($old_upfront + $daily_pay_for_plan*14);
                
                $outstanding=($finalPriceQ4-($this->totalAmtPaid($order)-$upfront));
                return $outstanding;
                }
               
                }
                
                function startCounterValidity($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
                $gracePeriodEnd = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? date("Y-m-d H:i:s",strtotime("+14days",strtotime($installation_date))) : $installation_date;
                
                $totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$counterId."' and `order` = '".$order."' and status='processed'");
                $upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
                $daily_pay_for_plan=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='daily'");
                $installments=$totalAmtPaid-$upfront;
                
                if($installments > $daily_pay_for_plan){
		   if(($installments%$daily_pay_for_plan)==0){
		   $days=$installments/$daily_pay_for_plan;
		   //$days=$days+14;
		   $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($gracePeriodEnd)));
		   return $valid_until;
		   }   
		   elseif(($installments%$daily_pay_for_plan) > 0){
		   $days=floor($installments/$daily_pay_for_plan);
		   
		   $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($gracePeriodEnd)));
		 
		  return $valid_until;
		   }
		}

		if($installments == $daily_pay_for_plan){
		 $valid_until=date("Y-m-d H:i:s",strtotime("+1day",strtotime($gracePeriodEnd)));
		 return $valid_until;
		}

		if($installments < $daily_pay_for_plan){
		 
		   $valid_until="Minimum Amount Required";
		   return $valid_until;
		}
		              
                }
                else 
                return "Set Upfront Amount for Product";
                }
                
                function oustandingAfterStartCounter($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
               // $finalPriceQ1=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=3 and payment_interval='final'");
                $amtPaid=$this->get_record("payment","amt","where pid > '".$counterId."' and `order` = '".$order."' and status='processed' limit 1");
                $payment_date=$this->get_record("payment","date","where pid > '".$counterId."' and `order` = '".$order."' and status='processed' limit 1");
                $outstanding=$this->outstanding($order,$payment_date,$amtPaid);
                return $outstanding;
                }
                else 
                return "Set Upfront Amount for Product";
                }
                
                function validityAfterStartCounter($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $payment_date=$this->get_record("payment","date","where pid > '".$counterId."' and `order` = '".$order."' and status='processed' limit 1");
               
                $amtPaid=$this->get_record("payment","amt","where pid > '".$counterId."' and `order` = '".$order."' and status='processed' limit 1");
                $daily_pay_for_plan=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='daily'");
                $installments=$amtPaid;
                
                if($installments > $daily_pay_for_plan){
		   if(($installments%$daily_pay_for_plan)==0){
		   $days=$installments/$daily_pay_for_plan;
		   //$days=$days+14;
		   $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($payment_date)));
		   return $valid_until;
		   }   
		   elseif(($installments%$daily_pay_for_plan) > 0){
		   $days=floor($installments/$daily_pay_for_plan);
		   
		   $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($payment_date)));
		 
		  return $valid_until;
		   }
		}

		if($installments == $daily_pay_for_plan){
		 $valid_until=date("Y-m-d H:i:s",strtotime("+1day",strtotime($payment_date)));
		 return $valid_until;
		}

		if($installments < $daily_pay_for_plan){
		 
		   $valid_until="Minimum Amount Required";
		   return $valid_until;
		}
		              
                }
                else 
                return "Set Upfront Amount for Product";
                }
                
                
               /* function oustandingAfterStartCounter($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $finalPriceQ1=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=3 and payment_interval='final'");
                $amtPaid=$this->get_record("payment","amt","where pid > '".$counterId."' and `order` = '".$order."' and status='processed' limit 1");
                $payment_date=$this->get_record("payment","date","where pid > '".$counterId."' and `order` = '".$order."' and status='processed' limit 1");
                $outstanding=$this->outstanding($order,$payment_date,$amtPaid);
                return $outstanding;
                }
                else 
                return "Set Upfront Amount for Product";
                }*/
                
                function dateAfterStartCounter($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $payment_date=$this->get_record("payment","date","where pid > '".$counterId."' and `order` = '".$order."' and status='processed' limit 1");   
                                      
                return $payment_date;
                }
                }
                
                function amtAfterStartCounter($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $amtPaid=$this->get_record("payment","amt","where pid > '".$counterId."' and `order` = '".$order."' and status='processed' limit 1");                            
                return $amtPaid;
                }
                }
                
                function dateFirstInstalment($order){		
		$date=$this->get_record("payment","date","where `order` = '".$order."' and status='processed' limit 1");
		
		return $date;
		}
		
		function scheduleOutstanding($order,$paymentId,$payment_date,$amtPaid){
			$arr = array();
			$counterId=$this->firstInstalmentId($order);
			$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
	        $gracePeriodEnd = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? date("Y-m-d H:i:s",strtotime("+14days",strtotime($installation_date))) : $installation_date;
			
			//$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $old_upfront;

			//$daily_pay_for_plan=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='daily'");
			
			$Q1endDate=date("Y-m-d H:i:s",strtotime("+90days",strtotime($gracePeriodEnd))); 
			$Q2endDate=date("Y-m-d H:i:s",strtotime("+180days",strtotime($gracePeriodEnd))); 
			$Q3endDate=date("Y-m-d H:i:s",strtotime("+270days",strtotime($gracePeriodEnd))); 
			$Q4endDate=date("Y-m-d H:i:s",strtotime("+360days",strtotime($gracePeriodEnd)));
			
			$finalPriceQ1=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=3 and payment_interval='final'");
			
			$finalPriceQ2=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=6 and payment_interval='final'");
			
			$finalPriceQ3=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=9 and payment_interval='final'" );
			
			$finalPriceQ4=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='final'"); 
	                 
			if($counterId > 0){
				//$today=date("Y-m-d");
				$today = $payment_date;
				if($paymentId < $counterId){
					$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <= '".$paymentId."' and `order` = '".$order."' and status='processed'");

					//jj
					if($today <= $Q1endDate){
						$outstanding=$finalPriceQ1+$upfront-$totalAmtPaid;
					}elseif($today > $Q1endDate and $today <= $Q2endDate and $this->Q1Outstanding($order) > 0){
						$outstanding=$finalPriceQ2+$upfront-$totalAmtPaid;
					}elseif($today > $Q2endDate and $today <= $Q3endDate and $this->Q2Outstanding($order) > 0){
						$outstanding=$finalPriceQ3+$upfront-$totalAmtPaid;
					}elseif($today > $Q3endDate and $today <= $Q4endDate and $this->Q3Outstanding($order) > 0){
						$outstanding=$finalPriceQ4+$upfront-$totalAmtPaid;
					}else{
						$outstanding=$finalPriceQ1+$upfront-$totalAmtPaid;	
					}
					//jj

					//$outstanding=$finalPriceQ1+$upfront-$totalAmtPaid;
					//$valid_until="Less Than Required Upfront";		
				}
			
				if($paymentId == $counterId){
					if($installation_date!=""){
						$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$counterId."' and `order` = '".$order."' and status='processed'");
						
						$installments=$totalAmtPaid-$upfront;		
												
						if($today <= $Q1endDate){
							$outstanding=$finalPriceQ1-$installments;
						}elseif($today > $Q1endDate and $today <= $Q2endDate and $this->Q1Outstanding($order) > 0){
							$outstanding=$finalPriceQ2-$installments;
						}elseif($today > $Q2endDate and $today <= $Q3endDate and $this->Q2Outstanding($order) > 0){
							$outstanding=$finalPriceQ3-$installments;
						}elseif($today > $Q3endDate and $today <= $Q4endDate and $this->Q3Outstanding($order) > 0){
							$outstanding=$finalPriceQ4-$installments;
						}else{
							$outstanding=$finalPriceQ1-$installments;
						}
					}else{
						$valid_until= "Pending Installation";		
						$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
						$outstanding=$finalPriceQ1+$upfront-$totalAmtPaid;
					}
				}

				if($paymentId > $counterId){
					
					if($installation_date!=""){
						$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
						//$installments=$totalAmtPaid;
						$installments=$totalAmtPaid-$upfront;

						if($today <= $Q1endDate){
							$outstanding=$finalPriceQ1-$installments;
						}elseif($today > $Q1endDate and $today <= $Q2endDate ){
							//$outstanding=$finalPriceQ2-$installments;
							$outstanding=$finalPriceQ2-$installments;
						}elseif($today > $Q2endDate and $today <= $Q3endDate ){
							$outstanding=$finalPriceQ3-$installments;
						}elseif($today > $Q3endDate and $today <= $Q4endDate ){
							$outstanding=$finalPriceQ4-$installments;
						}else{
							$outstanding=$finalPriceQ4-$installments;
						}
					}else{
						//$valid_until= "Pending Installation";
						$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
						$outstanding=$finalPriceQ1+$upfront-$totalAmtPaid;
					}
				}	
			
			}else{
				//$valid_until= "Pending Installation";
				$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
				$outstanding=$finalPriceQ1+$upfront-$totalAmtPaid;
			}	
			return $outstanding; 
		}
		
		// Joshua Schedule today
		function scheduleOutstanding_now($order,$paymentId,$payment_date,$amtPaid){
			$arr = array();
			$counterId=$this->firstInstalmentId($order);
			$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
	        $gracePeriodEnd = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? date("Y-m-d H:i:s",strtotime("+14days",strtotime($installation_date))) : $installation_date;
			
			//$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $old_upfront;

			$daily_pay_for_plan=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='daily'");
			
			$Q1endDate=date("Y-m-d H:i:s",strtotime("+90days",strtotime($gracePeriodEnd))); 
			$Q2endDate=date("Y-m-d H:i:s",strtotime("+180days",strtotime($gracePeriodEnd))); 
			$Q3endDate=date("Y-m-d H:i:s",strtotime("+270days",strtotime($gracePeriodEnd))); 
			$Q4endDate=date("Y-m-d H:i:s",strtotime("+360days",strtotime($gracePeriodEnd)));
			
			$finalPriceQ1=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=3 and payment_interval='final'");
			
			$finalPriceQ2=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=6 and payment_interval='final'");
			
			$finalPriceQ3=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=9 and payment_interval='final'" );
			
			$finalPriceQ4=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='final'"); 
	                 
			if($counterId > 0){
				//$today=date("Y-m-d");
				$today = date("Y-m-d");
				if($paymentId < $counterId){
					$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid < '".$paymentId."' and `order` = '".$order."' and status='processed'");

					//jj
					if($today <= $Q1endDate){
						$outstanding=$finalPriceQ1+$upfront-$totalAmtPaid;
					}elseif($today > $Q1endDate and $today <= $Q2endDate and $this->Q1Outstanding($order) > 0){
						$outstanding=$finalPriceQ2+$upfront-$totalAmtPaid;
					}elseif($today > $Q2endDate and $today <= $Q3endDate and $this->Q2Outstanding($order) > 0){
						$outstanding=$finalPriceQ3+$upfront-$totalAmtPaid;
					}elseif($today > $Q3endDate and $today <= $Q4endDate and $this->Q3Outstanding($order) > 0){
						$outstanding=$finalPriceQ4+$upfront-$totalAmtPaid;
					}else{
						$outstanding=$finalPriceQ1+$upfront-$totalAmtPaid;	
					}
					//jj

					//$outstanding=$finalPriceQ1+$upfront-$totalAmtPaid;
					//$valid_until="Less Than Required Upfront";		
				}
			
				if($paymentId == $counterId){
					if($installation_date!=""){
						$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$counterId."' and `order` = '".$order."' and status='processed'");
						
						$installments=$totalAmtPaid-$upfront;		
												
						if($today <= $Q1endDate){
							$outstanding=$finalPriceQ1-$installments;
						}elseif($today > $Q1endDate and $today <= $Q2endDate and $this->Q1Outstanding($order) > 0){
							$outstanding=$finalPriceQ2-$installments;
						}elseif($today > $Q2endDate and $today <= $Q3endDate and $this->Q2Outstanding($order) > 0){
							$outstanding=$finalPriceQ3-$installments;
						}elseif($today > $Q3endDate and $today <= $Q4endDate and $this->Q3Outstanding($order) > 0){
							$outstanding=$finalPriceQ4-$installments;
						}else{
							$outstanding=$finalPriceQ1-$installments;
						}
					}else{
						$valid_until= "Pending Installation";		
						$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
						$outstanding=$finalPriceQ1+$upfront-$totalAmtPaid;
					}
				}

				if($paymentId > $counterId){
					
					if($installation_date!=""){
						$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
						//$installments=$totalAmtPaid;
						$installments=$totalAmtPaid-$upfront;

						if($today <= $Q1endDate){
							$outstanding=$finalPriceQ1-$installments;
						}elseif($today > $Q1endDate and $today <= $Q2endDate ){
							//$outstanding=$finalPriceQ2-$installments;
							$outstanding=$finalPriceQ2-$installments;
						}elseif($today > $Q2endDate and $today <= $Q3endDate ){
							$outstanding=$finalPriceQ3-$installments;
						}elseif($today > $Q3endDate and $today <= $Q4endDate ){
							$outstanding=$finalPriceQ4-$installments;
						}else{
							//Above 12 months
							$outstanding=$finalPriceQ4-$installments;
						}
					}else{
						//$valid_until= "Pending Installation";
						$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
						$outstanding=$finalPriceQ1+$upfront-$totalAmtPaid;
					}
				}	
			
			}else{
				//$valid_until= "Pending Installation";
				$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
				$outstanding=$finalPriceQ1+$upfront-$totalAmtPaid;
			}	
			return $outstanding; 
		}
		// End of Schedule today	
		
		function scheduleValidity($order,$paymentId,$payment_date,$amtPaid,$payment_delay,$validity_buffer,$transactionBal){
			$counterId=$this->firstInstalmentId($order);
			$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
			$gracePeriodEnd = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? date("Y-m-d H:i:s",strtotime("+14days",strtotime($installation_date))) : $installation_date;
			$outstanding = $this->scheduleOutstanding($order,$paymentId,$payment_date,$amtPaid);
			
			//$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $old_upfront;

			//$daily_pay_for_plan=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='daily'");

			$Q1endDate=date("Y-m-d H:i:s",strtotime("+90days",strtotime($gracePeriodEnd))); 
			$Q2endDate=date("Y-m-d H:i:s",strtotime("+180days",strtotime($gracePeriodEnd))); 
			$Q3endDate=date("Y-m-d H:i:s",strtotime("+270days",strtotime($gracePeriodEnd))); 
			$Q4endDate=date("Y-m-d H:i:s",strtotime("+360days",strtotime($gracePeriodEnd)));
					                
			if($counterId > 0){
				//$payment_date=date("Y-m-d");
				if($paymentId < $counterId){		
					$valid_until="Less Than Required Upfront";		
				}
			
				if($paymentId == $counterId){
					if($installation_date!=""){
						$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
			
						$installments=$totalAmtPaid-$upfront;		
													
						if($installments > $daily_pay_for_plan){
						   if(($installments%$daily_pay_for_plan)==0){
							   $days=$installments/$daily_pay_for_plan;		 
							   $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($gracePeriodEnd)));
						   }elseif(($installments%$daily_pay_for_plan) > 0){
							   $days=floor($installments/$daily_pay_for_plan);		   
							   $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($gracePeriodEnd)));	
						   }
						}

						if($installments == $daily_pay_for_plan){
							$valid_until=date("Y-m-d H:i:s",strtotime("+1day",strtotime($gracePeriodEnd)));		
						}

						if($installments < $daily_pay_for_plan){		 
						   $valid_until=$gracePeriodEnd;		
						}
					}else{
						$valid_until= "Pending Installation";				
					}
				}
			
				if($paymentId > $counterId){
					if($installation_date!=""){
						$installments=$amtPaid+$transactionBal;
						//$installments=$amtPaid;

						if($installments > $daily_pay_for_plan){
				   			if(($installments%$daily_pay_for_plan)==0){
				   				$days=$installments/$daily_pay_for_plan;
				   				if($payment_delay==0){
				   					if($validity_buffer!=0){
				   						if ($outstanding > 0){
				   							$valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($validity_buffer)));
				   						}else{
				   							$valid_until = '<span class="label label-success">Permanent</span>';
				   						}
				   						return $valid_until;
				   					}
				   				}else{
				   					//else
				  					//The tricky forgotten side :-)
				  					if ($outstanding > 0){
										$valid_until = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($payment_date))) : date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($validity_buffer)));
				  					}else{
				  						$valid_until = '<span class="label label-success">Permanent</span>';
				  					}
				  				}
				   			}elseif(($installments%$daily_pay_for_plan) > 0){
								$days=floor($installments/$daily_pay_for_plan);
				  				if($payment_delay==0){
				   					if($validity_buffer!=0){
				   						if ($outstanding > 0){
									   		$valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($validity_buffer)));
									   	}else{
									   		$valid_until = '<span class="label label-success">Permanent</span>';
									   	}
									   	return $valid_until;
				   					}
				   				}else{
				  					// else
				  					if ($outstanding > 0){
				  						$valid_until = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($payment_date))) : date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($validity_buffer)));
				 					}else{
				 						$valid_until = '<span class="label label-success">Permanent</span>';
				 					}
				 					return $valid_until;
				 				}
				   			}
						}

						if($installments == $daily_pay_for_plan){
							$days=floor($installments/$daily_pay_for_plan);
							if($payment_delay==0){
								if($validity_buffer!=0){
									$valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($validity_buffer)));
								}
							}
							if ($days > 1) {
								$valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($validity_buffer)));
							}else{
								$valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($validity_buffer)));
							}
						}

						if($installments < $daily_pay_for_plan){//require attention		 
						   $valid_until="Less Than Minimum";		
						}
					}else{
						$valid_until= "Pending Installation";		
					}
				}
			}else{
				$valid_until= "Less than Upfront Required";		
			}
			
			return $valid_until; 
		}
		
		function send_json_new($url,$data){
			$content = json_encode($data);
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER,
				array("Content-type: application/json",'Authorization: Basic '. base64_encode("VillagePO:Vp1sth3"))
			);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //curl error SSL certificate problem, verify that the CA cert is OK
			 
			$result = curl_exec($curl);
			if(is_array($result)){
				$response = json_decode($result);
			}else{
				$response = $result;
			}
			//var_dump($response);
			curl_close($curl);
			return $response;
		}
		
		function send_new_sms($sender,$reciepient,$message){
			$host = 'https://api.infobip.com/sms/1/text/single';
			$feed = $this->send_json_new($host,array("from"=>$sender,"to"=>$reciepient,"text"=>$message));
			
			$feedb = json_decode($feed, true);
			$messageId = $feedb['messages'][0]['messageId'];
			
			$feedc = json_decode($feed, true);
			$status = $feedc['messages'][0]['status']['groupName'];
			
			$insert_message = "INSERT INTO sms_out_new(sender,reciepient,message,date, status, messageId ,feedback) VALUES('$sender','$reciepient','$message',CURRENT_TIMESTAMP(), '$status', '$messageId', '$feed')";
			$query_insert = mysql_query($insert_message) or die(mysql_error());
			
			return $feed;
		}

		function check_json($url){
			//$content = json_encode($data);
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER,
				array("Content-type: application/json",'Authorization: Basic '. base64_encode("VillagePO:Vp1sth3"))
			);
			 
			$result = curl_exec($curl);
			if(is_array($result)){
				$response = json_decode($result);
			}else{
				$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				$response = $result;
			}
			
			//var_dump($response);
			curl_close($curl);
			return $response;
		}

		function check_logs($messageId){
			//$from = 'VP UG Ltd';
			$host = 'https://api.infobip.com/sms/1/logs?messageId='.$messageId;
			$feed = $this->check_json($host);
			$feedb = json_decode($feed, true);
			$feedback = $feedb['results'][0]['status']['groupName'];
			$description = $feedb['results'][0]['status']['description'];
			
			$update_message = "UPDATE sms_out_new SET status = '".$feedback."', description = '".$description."' WHERE messageId = '".$messageId."'";
			$query_update = mysql_query($update_message) or die(mysql_error());			
		}
		function check_logs2($messageId){
			//$from = 'VP UG Ltd';
			$host = 'https://api.infobip.com/sms/1/logs?messageId='.$messageId;
			$feed = $this->check_json($host);
			$feedb = json_decode($feed, true);
			$feedback = $feedb['results'][0]['status']['groupName'];

			echo $feed;
		}
		
		function transactionBal($order,$paymentId,$payment_date,$amtPaid){
			$arr = array();
			$counterId=$this->firstInstalmentId($order);
			$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
	        $gracePeriodEnd = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? date("Y-m-d H:i:s",strtotime("+14days",strtotime($installation_date))) : $installation_date;
			
			//$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? $old_upfront : ($old_upfront + $daily_pay_for_plan*14);

			//$daily_pay_for_plan=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='daily'");
			
			$Q1endDate=date("Y-m-d H:i:s",strtotime("+90days",strtotime($gracePeriodEnd))); 
			$Q2endDate=date("Y-m-d H:i:s",strtotime("+180days",strtotime($gracePeriodEnd))); 
			$Q3endDate=date("Y-m-d H:i:s",strtotime("+270days",strtotime($gracePeriodEnd))); 
			$Q4endDate=date("Y-m-d H:i:s",strtotime("+360days",strtotime($gracePeriodEnd)));
					                
			if($counterId > 0){
				//$payment_date=date("Y-m-d");
				if($paymentId < $counterId){		
					//transactions not yet started
					//$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
					$suspense=0;		
				}
			
				if($paymentId == $counterId){
					if($installation_date!=""){
						$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
					
						$installments=$totalAmtPaid-$upfront;		
														
						if($installments > $daily_pay_for_plan){
				   			if(($installments%$daily_pay_for_plan)==0){
				   				$days=$installments/$daily_pay_for_plan;		 
				   				$valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($gracePeriodEnd)));
				   				$suspense=0;
				   			}elseif(($installments%$daily_pay_for_plan) > 0){
				   				$days=floor($installments/$daily_pay_for_plan);		   
				   				$valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($gracePeriodEnd)));
				   				$suspense=$installments-($days*$daily_pay_for_plan);
				   			}
						}

						if($installments == $daily_pay_for_plan){
							$valid_until=date("Y-m-d H:i:s",strtotime("+1day",strtotime($gracePeriodEnd)));
							$suspense=0;		
						}

						if($installments < $daily_pay_for_plan){		 
						   $valid_until="Less Than Minimum Amount Required";
						   
						   $suspense=$installments;		
						}	
					}else{
						$valid_until= "Pending Installation";
						$suspense=0;				
					}
				}
			
				if($paymentId > $counterId){
					if($installation_date!=""){
				
						//$installments=$amtPaid;
						//$installments=$totalAmtPaid-$upfront;		

						$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
					
						$installments=$totalAmtPaid-$upfront;
															
						if($installments > $daily_pay_for_plan){
						   if(($installments%$daily_pay_for_plan)==0){
							   $days=$installments/$daily_pay_for_plan;		 
							   $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($gracePeriodEnd)));
							   $suspense=0;
						   }elseif(($installments%$daily_pay_for_plan) > 0){
							   $days=floor($installments/$daily_pay_for_plan);		   
							   $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($gracePeriodEnd)));
							   $suspense=$installments-($days*$daily_pay_for_plan);	
						   }
						}

						if($installments == $daily_pay_for_plan){
						$valid_until=date("Y-m-d H:i:s",strtotime("+1day",strtotime($gracePeriodEnd)));
						$suspense=0;			
						}

						if($installments < $daily_pay_for_plan){		 
						   $valid_until="Less Than Minimum Amount Required";
						   $suspense=$amtPaid;			
						}		
					}else{
						$valid_until= "Pending Installation";
						$suspense=0;			
					}
				}
			}else{
				$valid_until= "Pending Installation";
				$suspense=0;		
			}
			
			return $suspense; 
		}
		
		function loanPeriod($order,$paymentId,$payment_date){
		
			$arr = array();
			$counterId=$this->firstInstalmentId($order);
			$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
	        $gracePeriodEnd = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? date("Y-m-d H:i:s",strtotime("+14days",strtotime($installation_date))) : $installation_date;
			
			//$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? $old_upfront : ($old_upfront + $daily_pay_for_plan*14); 
			
			$daily_pay_for_plan=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='daily'");
			
			$Q1endDate=date("Y-m-d H:i:s",strtotime("+90days",strtotime($gracePeriodEnd))); 
			$Q2endDate=date("Y-m-d H:i:s",strtotime("+180days",strtotime($gracePeriodEnd))); 
			$Q3endDate=date("Y-m-d H:i:s",strtotime("+270days",strtotime($gracePeriodEnd))); 
			$Q4endDate=date("Y-m-d H:i:s",strtotime("+360days",strtotime($gracePeriodEnd)));
			
			$finalPriceQ1=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=3 and payment_interval='final'");
			
			$finalPriceQ2=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=6 and payment_interval='final'");
			
			$finalPriceQ3=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=9 and payment_interval='final'" );
			
			$finalPriceQ4=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='final'"); 
	                 
			if($counterId > 0){
				//$payment_date = strtotime($payment_date);
				if($paymentId < $counterId){
					//$period="3months";
					if($installation_date!=""){			
						if($payment_date <= $Q1endDate){		
							$period="3 Months";
						}elseif($payment_date > $Q1endDate and $payment_date <= $Q2endDate and $this->Q1Outstanding($order) > 0){		
							$period="6 Months";
						}elseif($payment_date > $Q2endDate and $payment_date <= $Q3endDate and $this->Q2Outstanding($order) > 0){		
							$period="9 Months";
						}elseif($payment_date > $Q3endDate and $payment_date <= $Q4endDate and $this->Q3Outstanding($order) > 0){		
							$period="12 Months";
						}		
					}else{
						$period="3 Months";
					}	
				}
			
				if($paymentId == $counterId){
					if($installation_date!=""){			
						if($payment_date <= $Q1endDate){		
							$period="3 Months";
						}elseif($payment_date > $Q1endDate and $payment_date <= $Q2endDate and $this->Q1Outstanding($order) > 0){		
							$period="6 Months";
						}elseif($payment_date > $Q2endDate and $payment_date <= $Q3endDate and $this->Q2Outstanding($order) > 0){		
							$period="9 Months";
						}elseif($payment_date > $Q3endDate and $payment_date <= $Q4endDate and $this->Q3Outstanding($order) > 0){		
							$period="12 Months";
						}			
					}else{
						$period="3 Months";
					}
				}
			
				if($paymentId > $counterId){
					if($installation_date!=""){
															
						if($payment_date <= $Q1endDate){		
							$period="3 Months";
						}elseif($payment_date > $Q1endDate and $payment_date <= $Q2endDate){		
							$period="6 Months";
						}elseif($payment_date > $Q2endDate and $payment_date <= $Q3endDate ){		
							$period="9 Months";
						}elseif($payment_date > $Q3endDate and $payment_date <= $Q4endDate ){		
							$period="12 Months";
						}else{
							$period="Above 12 Months";
						}
					}else{
						$period="3 Months";
					}
				}
			}else{
				$period="3 Months";
			}
			return $period; 
		}
		
		function daysPaid($order,$paymentId,$payment_date,$amtPaid,$transactionBal){
			$arr = array();
			$counterId=$this->firstInstalmentId($order);
			$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
	        $gracePeriodEnd = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? date("Y-m-d H:i:s",strtotime("+14days",strtotime($installation_date))) : $installation_date;
		
			//$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $old_upfront; 
		
			//$daily_pay_for_plan=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='daily'");
		
			$Q1endDate=date("Y-m-d H:i:s",strtotime("+90days",strtotime($gracePeriodEnd))); 
			$Q2endDate=date("Y-m-d H:i:s",strtotime("+180days",strtotime($gracePeriodEnd))); 
			$Q3endDate=date("Y-m-d H:i:s",strtotime("+270days",strtotime($gracePeriodEnd))); 
			$Q4endDate=date("Y-m-d H:i:s",strtotime("+360days",strtotime($gracePeriodEnd)));
				                
			if($counterId > 0){
				//$payment_date=date("Y-m-d");
				if($paymentId < $counterId){		
					$days=0;		
				}
				
				if($paymentId == $counterId){
					if($installation_date!=""){
						$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
						
						$installments=$totalAmtPaid-$upfront;		
																
						if($installments > $daily_pay_for_plan){
							if(($installments%$daily_pay_for_plan)==0){
								$days=$installments/$daily_pay_for_plan;
							}elseif(($installments%$daily_pay_for_plan) > 0){
								$days=floor($installments/$daily_pay_for_plan);
							}
						}

						if($installments == $daily_pay_for_plan){
						 $days=1;	
						}

						if($installments < $daily_pay_for_plan){		 
						   $days=14;		
						}
					}else{
						$days=0;				
					}
				}
			
				if($paymentId > $counterId){
					if($installation_date!=""){
				
						$installments=$amtPaid+$transactionBal;
						//$installments=$totalAmtPaid-$upfront;		
																
						if($installments > $daily_pay_for_plan){
							if(($installments%$daily_pay_for_plan)==0){
								$days=$installments/$daily_pay_for_plan;		 
						   }elseif(($installments%$daily_pay_for_plan) > 0){
								$days=floor($installments/$daily_pay_for_plan);		   
						   }
						}

						if($installments == $daily_pay_for_plan){
						 $days=1;		
						}

						if($installments < $daily_pay_for_plan){		 
						   $days=0;		
						}
					}else{
						$days=0;		
					}
				}
			}else{
				$days=0;		
			}
			
			return $days; 
		}
		
		function finalPrice($order,$paymentId,$payment_date){
	
			$arr = array();
			$counterId=$this->firstInstalmentId($order);
			$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
	        $gracePeriodEnd = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? date("Y-m-d H:i:s",strtotime("+14days",strtotime($installation_date))) : $installation_date;
			
			//$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			//$daily_pay_for_plan=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='daily'");

			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? $old_upfront : ($old_upfront + $daily_pay_for_plan*14);
			
			$Q1endDate=date("Y-m-d H:i:s",strtotime("+90days",strtotime($gracePeriodEnd))); 
			$Q2endDate=date("Y-m-d H:i:s",strtotime("+180days",strtotime($gracePeriodEnd))); 
			$Q3endDate=date("Y-m-d H:i:s",strtotime("+270days",strtotime($gracePeriodEnd))); 
			$Q4endDate=date("Y-m-d H:i:s",strtotime("+360days",strtotime($gracePeriodEnd)));
			
			$finalPriceQ1=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=3 and payment_interval='final'");
			
			$finalPriceQ2=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=6 and payment_interval='final'");
			
			$finalPriceQ3=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=9 and payment_interval='final'" );
			
			$finalPriceQ4=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='final'"); 
	                 
			if($counterId > 0){
				$today=$payment_date;
				if($paymentId < $counterId){
					$finalPrice = $finalPriceQ4 + $old_upfront;
				}

				if($paymentId == $counterId){
					if($installation_date!=""){
												
						if($today <= $Q1endDate){
								$finalPrice=$finalPriceQ4 + $old_upfront;
						}elseif($today > $Q1endDate and $today<= $Q2endDate and $this->Q1Outstanding($order) > 0){
							$finalPrice=$finalPriceQ4 + $old_upfront;
						}elseif($today > $Q2endDate and $today <= $Q3endDate and $this->Q2Outstanding($order) > 0){
							$finalPrice=$finalPriceQ4 + $old_upfront;
						}elseif($today > $Q3endDate and $today <= $Q4endDate and $this->Q3Outstanding($order) > 0){
							$finalPrice=$finalPriceQ4 + $old_upfront;
						}						
					}else{
						$finalPrice=$finalPriceQ4 + $old_upfront;
					}
				}
			
				if($paymentId > $counterId){
					if($installation_date!=""){

						if($today <= $Q1endDate){
							$finalPrice=$finalPriceQ4 + $old_upfront;
						}elseif($today > $Q1endDate and $today<= $Q2endDate ){
							$finalPrice=$finalPriceQ4 + $old_upfront;
						}elseif($today > $Q2endDate and $today <= $Q3endDate){
							$finalPrice=$finalPriceQ4 + $old_upfront;
						}elseif($today > $Q3endDate and $today <= $Q4endDate){
							$finalPrice=$finalPriceQ4 + $old_upfront;
						}else{
							$finalPrice=$finalPriceQ4 + $old_upfront;
						}
					}else{
						$finalPrice=$finalPriceQ4 + $old_upfront;
					}
				}
			}else{
				$finalPrice=$finalPriceQ4 + $old_upfront;
			}
			return $finalPrice; 
		}

		function upfrontPaid($order){
			$arr = array();
			$counterId=$this->firstInstalmentId($order);

			//$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? $old_upfront : ($old_upfront + $daily_pay_for_plan*14);

			if($counterId > 0){
				$upfront=$upfront;		
			}else{		
				$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where `order` = '".$order."' and status='processed'");
				$upfront=$totalAmtPaid;	
			}
			return $upfront;
		}		
		
		function overDueValidity($order,$paymentId,$payment_date,$amtPaid,$payment_delay,$validity_buffer,$transactionBal){
		
			$counterId=$this->firstInstalmentId($order);
			$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
	        $gracePeriodEnd = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? date("Y-m-d H:i:s",strtotime("+14days",strtotime($installation_date))) : $installation_date;
			
			//$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			$old_upfront = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
			$daily_pay_for_plan = $this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='daily' and payment_period='12'");
			$upfront = $old_upfront;
			
			//$daily_pay_for_plan=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='daily'");
			
			$Q1endDate=date("Y-m-d H:i:s",strtotime("+90days",strtotime($gracePeriodEnd))); 
			$Q2endDate=date("Y-m-d H:i:s",strtotime("+180days",strtotime($gracePeriodEnd))); 
			$Q3endDate=date("Y-m-d H:i:s",strtotime("+270days",strtotime($gracePeriodEnd))); 
			$Q4endDate=date("Y-m-d H:i:s",strtotime("+360days",strtotime($gracePeriodEnd)));
				                
			if($counterId > 0){
				//$payment_date=date("Y-m-d");
				if($paymentId < $counterId){		
					//$valid_until="Less Than Required Upfront";
					$valid_until=0;		
				}
			
				if($paymentId == $counterId){
					if($installation_date!=""){
						$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
			
						$installments=$totalAmtPaid-$upfront;	
													
						if($installments > $daily_pay_for_plan){
			   				if(($installments%$daily_pay_for_plan)==0){
							   $days=$installments/$daily_pay_for_plan;		 
							   $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($gracePeriodEnd)));
						   	}elseif(($installments%$daily_pay_for_plan) > 0){
							   $days=floor($installments/$daily_pay_for_plan);		   
							   $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($gracePeriodEnd)));
			   				}
						}

						if($installments == $daily_pay_for_plan){
							$valid_until=date("Y-m-d H:i:s",strtotime("+1day",strtotime($gracePeriodEnd)));		
						}

						if($installments < $daily_pay_for_plan){		 
							// $valid_until="Less Than Minimum Amount Required";	
							//$valid_until=0;	
							$valid_until=$gracePeriodEnd;
						}
					}else{
						//$valid_until= "Pending Installation";	
						$valid_until=0;			
					}
				}

			
				if($paymentId > $counterId){
					if($installation_date!=""){
						$installments=$amtPaid+$transactionBal;
						//$installments=$amtPaid;
																
						if($installments > $daily_pay_for_plan){
				   			if(($installments%$daily_pay_for_plan)==0){
				   				$days=$installments/$daily_pay_for_plan;
				   				if($payment_delay==0)
				   					if($validity_buffer!=0){
				   						$valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($validity_buffer)));
								   		return $valid_until;
									}
									//else
									$valid_until = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($payment_date))) : date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($validity_buffer)));
									//$valid_until=$validity_buffer;
				   			}elseif(($installments%$daily_pay_for_plan) > 0){
								$days=floor($installments/$daily_pay_for_plan);
								if($payment_delay==0)
									if($validity_buffer!=0){
				   						$valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($validity_buffer)));
				   						return $valid_until;
				   					}
				  					// else
				  					$valid_until = $this->get_record("order","penguin","where id = '".$order."'") == NULL ? date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($payment_date))) : date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($validity_buffer)));
				 					//$valid_until=$days;
				   			}
						}

						if($installments == $daily_pay_for_plan){
							if($payment_delay==0)
								if($validity_buffer<=0){
									$days = 0;
									$valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($validity_buffer)));
					   				return $valid_until;
				   				}
				   				//else
				 				$valid_until=date("Y-m-d H:i:s",strtotime("+1day",strtotime($validity_buffer)));		
						}

						if($installments < $daily_pay_for_plan){		 
						   //$valid_until="Less Than Minimum Amount Required";
						   $valid_until=$validity_buffer;		
						}		
					}else{
						//$valid_until= "Pending Installation";
						$valid_until=0;		
					}
				}
			}else{
				//$valid_until= "Pending Installation";
				$valid_until=0;		
			}	
			return $valid_until; 		
		}
				
		function getOverDueClient($valid_until){ // us on dashboard
		
			if($valid_until!=0){
			$validity = strtotime(date('Y-m-d', strtotime($valid_until)));
			$today = strtotime(date('Y-m-d'));		
			if($validity < $today)//if validity days have expired
			return 1;		
			}		
			return 0;		
		}
			
		function paymentDelay($order,$paymentId,$validity,$validity_buffer,$payDate){
			$counterId=$this->firstInstalmentId($order);
			if($paymentId <= $counterId){
				$overDueDays ="";
			}else{
				$overDueDays="";
				if($validity!=0){
					if ($this->get_record("order","penguin","where id = '".$order."'") == NULL) {
						$payDate = strtotime(date('Y-m-d',strtotime($payDate)));

						if($validity_buffer!=0){
						$validity_buffer = strtotime(date('Y-m-d', strtotime($validity_buffer)));
						if($validity_buffer < $payDate){ //if validity days have expired, calculate over due days		
							$secs=$payDate-$validity_buffer;
			
							$overDueDays = floor($secs / (24 * 60 * 60 ));
						}else
							$overDueDays=0;		 		
					}else
						$overDueDays =0;
					}else{
						if($validity_buffer!=0){
						//$validity = date('Y-m-d', strtotime($validity));
						if($validity_buffer < $payDate){ //if validity days have expired, calculate over due days
							
							$new_pay_date = date_create($payDate);
							$new_validity_buffer = date_create($validity_buffer);
							$new_validity = date_create($validity);
							
							//Valid Days
							$valid_days = date_diff($new_validity_buffer,$new_validity);
							$valid_days->format("%a");							
							$real_valid_days = $valid_days->format("%a");
							
							$old_pd = date_diff($new_pay_date,$new_validity_buffer);	
							$paid_days = date_diff($new_pay_date,$new_validity);	
							
							$old_pd->format("%a");							
							$real_old_pd = $old_pd->format("%a");	//Last Valid Until - Current Payment Date

							$paid_days->format("%a");
							$real_paid_days = $paid_days->format("%a");
							
							//$pd = $new_validity_buffer - $real_valid_days;
							
							$overDueDays = $real_old_pd;
							
							//$overDueDays = $real_old_pd - $real_valid_days;
						}else
							$overDueDays=0;		 		
					}else
						$overDueDays =0;
					}
				}
			}	
			return $overDueDays;
		}
		
		function paymentDelayToday($validity_buffer){
		//select pid from payment
		if($validity_buffer!=0){
		$validity = strtotime(date('Y-m-d', strtotime($validity_buffer)));
		$today = strtotime(date('Y-m-d'));
		
		if($validity < $today){ //if validity days have expired, calculate over due days
		
		$secs=$today-$validity;
		
		$overDueDays = floor($secs / (24 * 60 * 60 ));
		return $overDueDays;
		}
		}		
		return 0;
		}

		function sum_arrays($array1, $array2){
			$array = array();
			foreach($array1 as $index => $value){
				$array[$index] = isset($array2[$index]) ? $array2[$index] + $value : $value;
			}
			return $array;
		}

		function getFiveDaysBefore(){

		$query = "select * from payment_schedule";
		if ($query_run = mysql_query($query)) {
			if (mysql_num_rows($query_run)> 0) {
				while($nine = mysql_fetch_assoc($query_run))
				{
					$compare_date= date("Y-m-d");
					$nine_days_before = date('Y-m-d', strtotime($nine['next_payment_date'].' -5 day'));
					$timestamp1 = strtotime($compare_date);
                    $timestamp2 = strtotime($nine_days_before);
                    $compare_date= date("Y-m-d 09:50");
					$today = date("Y-m-d H:i");
					if ($nine['reminded']=='no') {
						if ($nine['pending_amt'] > 0) {

					if ($timestamp1 == $timestamp2) {
						if ($compare_date==$today) {
							
					$phone = $this->get_record("customer","default_phone","where cid = '".$this->get_record("order","customer","where id = '".$nine['order']."'")."'");
					$name = $this->get_record("customer","concat(fname,' ',lname)","where cid = '".$this->get_record("order","customer","where id = '".$nine['order']."'")."'");
					$msg="Dear ".$name.",kindly be reminded that the next payment  for your Village Power solar system is due on ".$nine['next_payment_date'].". For assistance on how to make payment, please call our customer care helpline on 0755064040 0r 0777455266";
					//Send out payment reminders
					//echo $msg;
					$this->send_sms('Vp UG Ltd',$phone,$msg);
					print($msg."\nDate/Time: ".date("Y-m-d H:i:s")."\n");

					}

				}else{
					//echo "$nine_days_before is not equal to $compare_date\n";
				}
				}
				}
				
				}
			}else{
				print("No payment reminders to send out nine days before due date- ".date("Y-m-d H:i:s")."\n");
			}
		}
	}
	function getSevenDaysBefore(){

		$query = "select * from payment_schedule";
		if ($query_run = mysql_query($query)) {
			if (mysql_num_rows($query_run)> 0) {
				while($nine = mysql_fetch_assoc($query_run))
				{
					$compare_date= date("Y-m-d");
					$seven_days_before = date('Y-m-d', strtotime($nine['next_payment_date'].' -4 day'));
					$timestamp1 = strtotime($compare_date);
                    $timestamp2 = strtotime($seven_days_before);
					if ($nine['reminded']=='no') {
						if ($nine['pending_amt'] > 0) {

					if ($timestamp1 == $timestamp2) {
		
							
					$phone = $this->get_record("customer","default_phone","where cid = '".$this->get_record("order","customer","where id = '".$nine['order']."'")."'");
					$name = $this->get_record("customer","concat(fname,' ',lname)","where cid = '".$this->get_record("order","customer","where id = '".$nine['order']."'")."'");
					$msg="Dear ".$name.",kindly be reminded that the next payment  for your Village Power solar system is due on ".$nine['next_payment_date'].". For assistance on how to make payment, please call our customer care helpline on 0755064040 0r 0777455266";
					//Send out payment reminders
					//echo $msg;
					$this->send_new_sms('Vp UG Ltd',$phone,$msg);
					print($msg."\nDate/Time: ".date("Y-m-d H:i:s")."\n");

					

				}else{
					//echo "$nine_days_before is not equal to $compare_date\n";
				}
				}
				}
				
				}
			}else{
				print("No payment reminders to send out nine days before due date- ".date("Y-m-d H:i:s")."\n");
			}
		}
	}
		function getthreeDaysBefore(){

		$query = "select * from payment_schedule";
		if ($query_run = mysql_query($query)) {
			if (mysql_num_rows($query_run)> 0) {
				while($three = mysql_fetch_assoc($query_run))
				{
					$compare_date= date("Y-m-d");
					$three_days_before = date('Y-m-d', strtotime($three['next_payment_date'].' -3 day'));
					$timestamp1 = strtotime($compare_date);
                    $timestamp2 = strtotime($three_days_before);
					if ($three['reminded']=='no') {
						if ($three['pending_amt'] > 0) {

					if ($timestamp1 == $timestamp2) {
							
					$phone = $this->get_record("customer","default_phone","where cid = '".$this->get_record("order","customer","where id = '".$three['order']."'")."'");
					$name = $this->get_record("customer","concat(fname,' ',lname)","where cid = '".$this->get_record("order","customer","where id = '".$three['order']."'")."'");
					//$msg="Dear ".$name.",kindly be reminded that the next payment  for your Village Power solar system is due on ".$three['next_payment_date'].". For assistance on how to make payment, please call our customer care helpline on 0755064040 0r 0777455266";
					$msg = "Dear ".$name.", we hope that you are enjoying your Village Power solar system. Kindly be reminded that your next payment is due on ".$three['next_payment_date'].". Please make your payment in order to avoid undesirable consequences. For assistance on how to make payment, please call our customer care helpline on 0755064040 0r 0777455266";
	
					//Send out payment reminders
					//echo $msg;
					$this->send_sms('Vp UG Ltd',$phone,$msg);
					print($msg."\nDate/Time: ".date("Y-m-d H:i:s")."\n");
				}else{
					//echo "$nine_days_before is not equal to $compare_date\n";
				}
				}
				}
				
				}
			}else{
				print("No payment reminders to send out three days before due date- ".date("Y-m-d H:i:s")."\n");
			}
		}
	}
	public function AutomaticDefault()
          {
               $query = "select * from overdue_timestamp where status='off' and days >= 90 order by id desc";
            $query_run= mysql_query($query);
               while($row = mysql_fetch_assoc($query_run)){
               $id = $row['id'];
               $customer_id = $row['customer_id'];
               $default_status = $row['defaulter_status'];
               $recovery_status = $row['recovery_status'];
               $ots2 = $row['ots2'];
               $ots3 = $row['ots3'];
               $compare_date= date("Y-m-d");
               $default_date = $row['default_date'];

               $paymentId= $this->get_record("payment","max(pid)","where customer =".$customer_id." and status='processed'");
               $payment_date= $this->get_record("payment","max(date)","where customer =".$customer_id." and status='processed'");
               $amtPaid= $this->get_record("payment","max(amt)","where customer =".$customer_id." and status='processed'");
               $orders= $this->get_record("order","id","where customer =".$customer_id."");
               $outstanding = $this->scheduleOutstanding_now($orders,$paymentId,$default_date,$amtPaid);
               
               $name = $this->get_record("customer", "concat(fname,' ',lname)", "where cid = '".$customer_id."'");

               if ($default_status == 'visit' && $default_date!='') {


                  $timestamp1 = strtotime($compare_date);
                   $timestamp2 = strtotime($default_date);

                  if ($timestamp2 == $timestamp1) {
                    $default_date_today = date("Y-m-d H:i");
                     $d = mysql_query("update overdue_timestamp set defaulter_status='defaulted', outstanding='".$outstanding."',status='on' where id='".$id."'");
                         if ($d) {
                         $order= $this->get_record("overdue_timestamp","customer_id","where id =".$id."");
                           $r = mysql_query("update `order` set status='Defaulter' where customer=".$order."");
                           if ($r) {
                              echo "Defaulting Client ".$name."\n";
                           }

                         }
                  }else{

                     echo "Time Not Reached To Default Client ".$name." With Outstanding ".$outstanding."\n";
                  }
               
               }else{
                    echo "Not yet Visited Client ".$name." outstanding ".$outstanding."\n";
               }

          }

          }

		function getTommorrow(){

		$query = "select * from payment_schedule";
		if ($query_run = mysql_query($query)) {
			if (mysql_num_rows($query_run)> 0) {
				while($nine = mysql_fetch_assoc($query_run))
				{
					$compare_date= date("Y-m-d");
					$nine_days_before = date('Y-m-d', strtotime($nine['next_payment_date'].' -1 day'));
					$timestamp1 = strtotime($compare_date);
                    $timestamp2 = strtotime($nine_days_before);
                    $compare_date= date("Y-m-d 09:50");
					$today = date("Y-m-d H:i");

					if ($nine['reminded']=='no') {
						if ($nine['pending_amt'] > 0) {

					if ($timestamp1 == $timestamp2) {

						if ($compare_date==$today) {
							
					$phone = $this->get_record("customer","default_phone","where cid = '".$this->get_record("order","customer","where id = '".$nine['order']."'")."'");
					$name = $this->get_record("customer","concat(fname,' ',lname)","where cid = '".$this->get_record("order","customer","where id = '".$nine['order']."'")."'");
					$msg="Dear ".$name.",kindly be reminded that the next payment  for your Village Power solar system is due tommorrow. Please make payment in order to avoid undesirable consequences. For assistance on how to make payment, please call our customer care helpline on 0755064040 0r 0777455266";
	
					//Send out payment reminders
					//echo $msg;
					$this->send_sms('Vp UG Ltd',$phone,$msg);
					print($msg."\nDate/Time: ".date("Y-m-d H:i:s")."\n");
				}
				}else{
					//echo "$nine_days_before is not equal to $compare_date\n";
				}
				}
				}
				
				}
			}else{
				print("No payment reminders to send out nine days before due date- ".date("Y-m-d H:i:s")."\n");
			}
		}
	}
	
	function getToday(){

		$query = "select * from payment_schedule where (next_payment_date like '".date("Y-m-d")."%' or next_payment_date <= '".date("Y-m-d")." 00:00:00') /* and (payment_ref is null or payment_ref = '') */ and `order` in (select id from `order` where order_status not in ('pending_down_payment', 'approved', 'cancelled', 'pending_evaluation'))";
		if ($query_run = mysql_query($query)) {
			if (mysql_num_rows($query_run)> 0) {
				while($nine = mysql_fetch_assoc($query_run))
				{
					
					if ($nine['reminded']=='no') {
						if ($nine['pending_amt'] > 0) {
					$compare_date= date("Y-m-d 09:48");
					$today = date("Y-m-d H:i");
					if ($compare_date==$today) {
					$phone = $this->get_record("customer","default_phone","where cid = '".$this->get_record("order","customer","where id = '".$nine['order']."'")."'");
					$name = $this->get_record("customer","concat(fname,' ',lname)","where cid = '".$this->get_record("order","customer","where id = '".$nine['order']."'")."'");
					$msg="Dear ".$name.",kindly be reminded that the next payment  for your Village Power solar system is due today. Please make payment in order to avoid undesirable consequences. For assistance on how to make payment, please call our customer care helpline on 0755064040 0r 0777455266";
	
					//Send out payment reminders
					echo $msg;
					$this->send_sms('VP UG Ltd',$phone,$msg);
					print($msg."\nDate/Time: ".date("Y-m-d H:i:s")."\n");
					mysql_query("update payment_schedule set reminded = 'yes' where id = '".$nine['id']."'");
						}else{
							echo "Sending Message Reminder At ".$compare_date."\n";
						}
					}
				}
					
				}
			}else{
				print("No payment reminders to send out for today - ".date("Y-m-d H:i:s")."\n");
			}
		}
	}
	function insertLogs($sesionId,$action,$message,$date)
		{
			$result ='';
			$query = "insert into dashboardlog values('','".mysql_real_escape_string($sesionId)."','".mysql_real_escape_string($action)."','".mysql_real_escape_string($message)."','".mysql_real_escape_string($date)."')";
			if ($query_run = mysql_query($query)) {
			}else{


           echo "Failed To Add Please";
			} 
		}

		function resetPassword()
		{
			if (isset($_POST['email'])) {
				$email = $_POST['email'];

				if (empty($email)) {
					echo "<p class='text-center'>Fill In Your Email Please</p>";
					
				}else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					echo "<p class='text-center'>Incorrect Email Please, Try Again</p>";
	
				}else{

					$query = "select uid,name from user where email_address = '$email'";
					$query_run = mysql_query($query);
					$row = mysql_fetch_assoc($query_run);
					$name = $row['name'];

					if ($row) {
						$encrypt = md5(1290*3+$row['uid']);
                        require 'mail/PHPMailerAutoload.php';
                        require_once("mail/class.phpmailer.php");//PHPMailer Object
                         $mail = new PHPMailer;
                         $mail->isSMTP();
                         $mail->Host = 'smtp.udag.de';
                         $mail->SMTPSecure = 'ssl';
                         $mail->Port = 465;
                         $mail->SMTPAuth = true;
                         $mail->Username='test@village-power.ug';
                         $mail->Password = 'vpug2013';
                        
 
                           //From email address and name
                          $mail->From = "test@village-power.ug";
                          $mail->FromName = "Village Power";
 
                          $mail->addAddress($email,$name); //Recipient name is optional
                       $mail->Subject = "Reset Password";
                       $mail->Body = 'Hi '.$name.', Click here to reset your password http://localhost/GreenPortal/reset.php?encrypt='.$encrypt.'';
 
                       if(!$mail->send()) 
                        {
                          echo "Mailer Error: " . $mail->ErrorInfo;
                         } 
                         else
                          {
                         echo "Your password reset link has been sent to your e-mail address.";
                           }																	  	
						
					}else{
						echo "Email Address Not Found";
					}

				}
				
			}
		}
		function getInterest($order,$outstanding)
 		{
 		$plan = $this->get_record("order", "payment_plan", "where id = '".$order."'");
 		if ($plan=='hire') {
 			if ($outstanding<=0) {
 				$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where `order` = '".$order."' and status='processed'");
 				$finalPrice=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=0 and payment_interval='full_payment'");
 				$interest = $totalAmtPaid-$finalPrice;
 				return $interest;
 			}else{
 				
 				return ;
 			}
 		}else{
 			return ;
 		}
 		}

		function ResetNewPassword()
 {
     $encrypt = mysql_real_escape_string($_GET['encrypt']);
        $query = "SELECT uid FROM user WHERE md5(1290*3+uid)='".$encrypt."'";
        $result = mysql_query($query);
        $Results = mysql_fetch_assoc($result);

        if ($Results) {

            if (isset($_POST['password']) && isset($_POST['confirmpassword'])) {
             $password = $_POST['password'];
             $confirmpassword = $_POST['confirmpassword'];

             if (empty($password) && empty($confirmpassword)) {
             	echo "Fill In All Fields";
             } else if($password!=$confirmpassword){
             	echo "Password Dont Macth";
             }else{
             	 $query = "update user set password='".sha1(md5($password))."' where uid ='".$Results['uid']."'";
                 mysql_query($query);
                 echo 'Your password changed sucessfully <a href="http://localhost/GreenPortal/login.php">click here to login</a>';
             }
           
    
            }
                }else{
                    echo "No User Found";
            }
 }
 
        function seeMore($mytext,$link,$var,$id)
   {
   	$chars = 0;  
    $mytext = substr($mytext,0,$chars);  
    $mytext = substr($mytext,0,strrpos($mytext,' '));  
    $mytext = $mytext."<p class='readmore'> <a href='$link?$var=$id' class='fancybox viewComment'>View Comment</a></p>";  
     return $mytext; 
   }
   function accessUsers()
  {  
    $username = '';
    $yax= mysql_query("select email_address from user where access=1");
    while ($row = mysql_fetch_assoc($yax)) {
      $username = $row['email_address'];
      
      return $username;
    }
    
  }

  function Un_paid_clients($amount,$date_called_clients)
  {
  	
  	 $compare_date= date("Y-m-d");
     $date_called= date('Y-m-d', strtotime($date_called_clients.' +7 day'));
     $timestamp1 = strtotime($compare_date);
     $timestamp2 = strtotime($date_called);
     if ($timestamp1>=$timestamp2) {
        if ($amount!='') {
        	echo "<span class='label label-success'><strong>Paid</strong></span>";
        }else{
        	echo "<span class='label label-important'><strong>Past</strong></span>";
        }
      }else{ 
      	echo "<span class='label label-warning'><strong>WithIn 7 Days</strong></span>"; 

      }
 
  }
 function un_reached($cid)
  {
    $query_two = "select cid from unreachable_customers where cid='".$cid."'";
    $status = "<span class='label label-success'><i class='fa fa-check' aria-hidden='true'></i></span>";
      $lol=mysql_query($query_two);
      while($row2 = mysql_fetch_assoc($lol)){     
      if ($row2['cid']!='' ) {        
          return $status;      
      }
    }
  }
     function un_reached_status($cid)
  {
    $query_two = "select count(cid) as times,cid from unreachable_customers where cid='".$cid."'";
    $status = '<i class="fa fa-check" aria-hidden="true"></i>';
    $statuss = '--';
      $lol=mysql_query($query_two);
      while($row2 = mysql_fetch_assoc($lol)){
      
      if ($row2['times']>=5 ) {   
         echo "<span class='label label-success'>$status</span>"; 
       }else{
        echo '--';
       }

    }
  }
 	 public function send_email_kluz($email,$pass)
             {  require_once'PHPMailer/PHPMailerAutoload.php';
                    require_once("PHPMailer/class.phpmailer.php");//PHPMailer Object
                         $mail = new PHPMailer;
                         $mail->isSMTP();
                         $mail->Host = 'smtp.udag.de';
                         $mail->SMTPSecure = 'ssl';
                         $mail->Port = 465;
                         $mail->SMTPAuth = true;
                         $mail->Username='test@village-power.ug';
                         $mail->Password = 'vpug2013';
                        
 
                           //From email address and name
                          $mail->From = "test@village-power.ug";
                          $mail->FromName = "Village Power";
 
                          $mail->addAddress($email); //Recipient name is optional
                          $mail->Subject = "Vpc Password";
                          $mail->Body = 'Your Village Power System account has succesfully been created, And Your One Time Password Is '.$pass.' Click here to http://villagepower.info/vpc/login.php login now and change your password .';
 
                       if(!$mail->send()) 
                        {
                          echo "Mailer Error: " . $mail->ErrorInfo;
                         }
                         else
                          {
                         //echo "Password reset link has been sent to your e-mail.";
                           } 
                        
      }

      public function getVpC()
      {
        $query_vpc = "select * from sales_agent order by cid desc";
        $vpc = mysql_query($query_vpc) or die(mysql_error());
        
        echo "<select name='cid' class='form-control'>";
          echo "<option >Choose Vpc</option>";
        while ($row = mysql_fetch_assoc($vpc)) {
          $id = $row['cid'];
          $fname = $row['fname'];
          $lname = $row['lname'];
          echo "<option value='$id'>$fname $lname </option>";
         }

         echo "</select>";
      }
				
}

?>
