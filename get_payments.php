<?php 

 require_once('connect.php'); 
 require_once('config.inc.php'); 
 $api = new Config(); 
 error_reporting(0);

 
 $fp = fopen("sys.txt","a");
 fwrite($fp,"Date: ".$_REQUEST['date_time']." Amount: ".$_REQUEST['amount']." Narrative: ".$_REQUEST['narrative']." Network Ref: ".$_REQUEST['network_ref']." External Ref: ".$_REQUEST['external_ref']." Msidn: ".$_REQUEST['msisdn']."Stage: Start\r\nSignature: ".$signature."\r\n");
 fclose($fp);
 
 
 
 $phone = $_REQUEST['msisdn'];
 $stage = "Reason Processing";
 $network_ref= $_REQUEST['network_ref'];
 $amt = $_REQUEST['amount'];
 $remarks=$_REQUEST['narrative'];
 $status = 'processed';
 $fund_source = 'mobile_money_';
 $gateway_provider = 'Yo';
 $date = $_REQUEST['date_time'];
 $payment_type= 'unknown';
 switch(substr($phone,0,5))
 {
		 case '25678':
		 case '25677': $cust_id = $api->get_record("customer","id","where default_phone = '".$api->format_number(trim($_REQUEST['narrative']))."'");
					   break;
		 case '25670':
		 case '25675': $temp = substr($_REQUEST['narrative'],strpos($_REQUEST['narrative'],'Reason:'));
					   $n = str_replace("Reason:","",substr($temp,0,strpos($temp,"/")));
					   $cust_id = $api->get_record("customer","id","where default_phone = '".$api->format_number(trim($n))."'");
					   break;
		 default: $cust_id = "";
 }
 
 if($cust_id == "")
 {
	$cust_id = $api->get_record("customer","id","where default_phone = '".$phone."'");
	$stage = "Phone Processing";
 }
 
 if(trim($cust_id) == "")
 {
	     $stage = "No Customer ID";
		$data =$dbh-> prepare('select * from payment where network_ref=:network_ref');
		$data->bindParam(':network_ref',$network_ref);
		$data->execute();

		$row = $data->fetch(PDO::FETCH_ASSOC);
		if (!$row) {
			$pay_one="insert into payment (payment_type, amt, fund_source, `status`, `date`, remarks, gateway_provider, network_ref) values (:type,:amt,:fund_source,:status,:date,:remarks,:gateway_provider,:network_ref)";
			 $data = $dbh->prepare($pay_one);
			$data->bindParam(':type',$payment_type);
			$data->bindParam(':amt',$amt);
			$data->bindParam(':fund_source',$fund_source);
			$data->bindParam(':status',$status);
			$data->bindParam(':date',$date);
			$data->bindParam(':remarks',$remarks);
			$data->bindParam(':gateway_provider',$gateway_provider);
			$data->bindParam(':network_ref',$network_ref);
			$res = $data->execute();
			if ($res) {
				$api->send_sms_notifications('Eco Stove',$phone,'Dear customer, we have received your payment of UGX '.$_REQUEST['amount'].'.Thank you for choosing Eco Stove Ltd Uganda.');	
			}
		} 
 } 
 else
 {//no order defined
	 $stage = "Figuring out Order ID";
	 //Customer Information Entered
	 //Pending Orders?
	 $order_id = $api->get_record("order","id","where customer = '".$cust_id."' order by id asc limit 1");
	 
	 if($order_id != "")
	 {
		 $order_status = $api->get_record("order","order_status","where id = '".$order_id."'");
		 //Has pending payments ?
		 if($api->get_record("payment","count(*)","where `order` = '".$order_id."' /* and amt <= '".preg_replace("/[^0-9]/","",$_REQUEST['amount'])."' */ and `status` = 'pending'") == 0)
		 {
		$stage = "Order Determined";
		 $dat =$dbh-> prepare('select * from payment where network_ref=:network_ref');
		$dat->bindParam(':network_ref',$network_ref);
		$dat->execute();
		$ro = $dat->fetch(PDO::FETCH_ASSOC);
		if (!$ro) {
		
			$both_known = "insert into payment (payment_type, customer, `order`, amt, fund_source, `status`, `date`, remarks, gateway_provider,network_ref) values (:payment_type, :customer, :order, :amt, :fund_source, :status, :date, :remarks, :gateway_provider,:network_ref)";
			$da = $this->dbh->prepare($both_known);
			$da>bindParam(':payment_type',$payment_type);
			$da->bindParam(':amt',$amt);
			$da->bindParam(':customer',$cust_id);
			$da->bindParam(':order',$order_id);
			$da->bindParam(':fund_source',$fund_source);
			$da->bindParam(':status',$status);
			$da->bindParam(':date',$date);
			$da->bindParam(':remarks',$remarks);
			$da->bindParam(':gateway_provider',$gateway_provider);
			$da->bindParam(':network_ref',$network_ref);
			$t = $data->execute();
			
			if($t)
			{
			$api->send_sms_notifications('Eco Stove',$phone,'Dear customer, we have received your payment of UGX '.$_REQUEST['amount'].'.Thank you for choosing Eco Stove Uganda.');
			$pid = $dbh->lastInsertId();
				
			$order_status = $api->get_record("order","order_status","where id = '".$order_id."'");
			$payment_phone = $api->get_record("customer","default_phone","where cid = '".$cust_id."'"); 
			$payment_name = $api->get_record("customer","concat(fname,' ',lname)","where cid = '".$cust_id."'");
			$productvp=$api->get_record("product", "product_code", "where pid = '".$api->get_record("order_item", "item", "where `order` = '".$order_id."'")."'");

			 if($order_status=="pending_evaluation"){
			 
			 if($api->get_record("payment","count(*)","where `order` = '".$order_id."' and status = 'processed'") == 1){

				if ($api->totalAmtPaid($order_id) >= $api->upfrontRequired($order_id)) {
				$down_payment="update `order` set order_status = 'approved' where id = '".$order_id."'";
				$th = $dbh->prepare($down_payment);
				$down_pay=$th->execute();

				 if ($down_pay) {
					//$api->send_sms('VP UG Ltd',$payment_phone,'Dear '.$payment_name.', Your solar down payment for a '.$productvp.' of '.preg_replace("/[^0-9]/","",$_REQUEST['amount']).' UGX has been received by Village Power Uganda. Thanks for the payments');
				 }
				}
			 
			 }
			
		   }else{
			 //$api->send_sms('VP UG Ltd',$payment_phone,'Dear '.$payment_name.', your solar payment for a '.$productvp.' of '.preg_replace("/[^0-9]/","",$_REQUEST['amount']).' UGX has been received by Village Power Uganda. Thanks for the payments');
			}
										
		 if($api->get_record("order", "payment_plan", "where id = '".$order_id."'")=="hire")  
			 {											
			           $stage = "Payment Plan is Hire";	
					   $query_h = "select * from payment where `order` = '".$order_id."' and status='processed' order by pid asc";
						$th = $dbh->prepare($query_h);
						$th->execute();
						$amount=0;$validity_buffer=0;$transactionBal=0;
					 while ($row = $th->fetch(PDO::FETCH_ASSOC)) {
						 $payment_delay=$api->paymentDelay($row_h['order'],$row_h['pid'],$api->overDueValidity($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt'],"",$validity_buffer,$transactionBal),$validity_buffer,$row_h['date']);
						  $next_payment_date=$api->scheduleValidity($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt'],$payment_delay,$validity_buffer,$transactionBal); 
						  $pending_amt=$api->scheduleOutstanding($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt']);
						  $active_days=$api->daysPaid($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt']);
						  $active_minutes=$api->minutesPaid($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt']);
			
						  $amount+=$row_h['amt'];
						  $validity_buffer=$api->overDueValidity($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt'],$payment_delay,$validity_buffer,$transactionBal);
						   $transactionBal=$api->transactionBal($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt']);
					 } 
												 
																				 
					if($active_days > 0)
					{
					$serial_number = $api->get_record("product_inventory","item_no","where order_id='".$order_id."'");
					 $code_of_pay= $api->generate($active_minutes, $serial_number);
					 $api->send_sms_notifications('Eco Stove',$phone,'Dear customer, we have received your payment of UGX '.$_REQUEST['amount'].'.Thank you for choosing Eco Stove Ltd Uganda.');
					 $api->send_sms_notifications('Eco Stove',$payment_phone,'Dear '.$payment_name.', Your Pay Go Code Is *'.$code_of_pay.'# . Thanks for the payments');

				      if($pending_amt > 0){
						  $query = "select * from payment_schedule where `order`=".$order_id." and reminded='no' and device_toggle='no'";
						  $sth = $dbh->prepare($query);
						  $sth->execute();
						   $ro = $dat->fetch(PDO::FETCH_ASSOC);
						 if ($ro) {
						  
							    $r="update payment_schedule set reminded='yes', device_toggle='yes' where `order`=".$order_id."";
							    $th = $dbh->prepare($r);
							    $dy=$th->execute();
						          if ($dy) {
						              $q= "insert into payment_schedule (`order`, next_payment_date, pending_amt, payment_ref) values (:order, :next_payment_date, :pending_amt, :payment_ref)";
							          $a = $this->dbh->prepare($q);
							          $a->bindParam(':order',$order_id);
							          $a->bindParam(':next_payment_date',$next_payment_date);
							          $a->bindParam(':pending_amt',$pending_amt);
							          $a->bindParam(':payment_ref',$pid);
							          $tt = $a->execute();
			
							         }

							}else{
							         $q= "insert into payment_schedule (`order`, next_payment_date, pending_amt, payment_ref) values (:order, :next_payment_date, :pending_amt, :payment_ref)";
							         $b = $this->dbh->prepare($q);
							         $b->bindParam(':order',$order_id);
							         $b->bindParam(':next_payment_date',$next_payment_date);
							         $b->bindParam(':pending_amt',$pending_amt);
							         $b->bindParam(':payment_ref',$pid);
							         $tt = $b->execute();
							}
						 }        
					
			            }
				}
					
					else $stage = "Payment Plan is Full";
					
					//Modify Order Status (if necessary)
				//}
			 }
			 else { $stage = mysql_error(); }
		 }
		 }else{
			 $stage = 'Order ID determined';
			 
			 //Pending payments present
			 $payment_id = $api->get_record("payment","pid","where `order` = '".$order_id."' and amt <= '".preg_replace("/[^0-9]/","",$_REQUEST['amount'])."' and `status` = 'pending'");
			 
			 $stage = "Attaching payment to pending order - payment ref: ".$payment_id." Order ref: ".$order_id;
			 $h = "update payment set fund_source='mobile_money_', `status`='processed', `date`='".date("Y-m-d H:i:s", strtotime($_REQUEST['date_time']))."', remarks='".mysql_real_escape_string($_REQUEST['narrative']." Msidn".$_REQUEST['msisdn'])."', gateway_provider='yo' where pid = '".$payment_id."'";
			 $th = $dbh->prepare($h);
			 $y=$th->execute();
			 if($y)
			 {
				 $stage = $order_status;
				 switch($order_status)
				 {
					 case 'pending_down_payment': 
				if ($api->totalAmtPaid($order_id) >= $api->upfrontRequired($order_id)) {
				$down_payment="update `order` set order_status = 'approved' where id = '".$order_id."'";
				$th = $dbh->prepare($down_payment);
				$down_pay=$th->execute();

				 if ($down_pay) {
					//$api->send_sms('VP UG Ltd',$payment_phone,'Dear '.$payment_name.', Your solar down payment for a '.$productvp.' of '.preg_replace("/[^0-9]/","",$_REQUEST['amount']).' UGX has been received by Village Power Uganda. Thanks for the payments');
					   $stage = "Payment attached to order"; 
				 }
				}
					
					  break;
				 }
			 }
			
		 }
	}
	else
	 {
	   $stage = "No order determined";
		$dt =$dbh-> prepare('select * from payment where network_ref=:network_ref');
		$dt->bindParam(':network_ref',$network_ref);
		$dt->execute();

		$row = $dt->fetch(PDO::FETCH_ASSOC);
		if (!$row) {
			$pay_one="insert into payment (payment_type, customer, amt, fund_source, `status`, `date`, remarks, gateway_provider, network_ref) values (:type,:customer,:amt,:fund_source,:status,:date,:remarks,:gateway_provider,:network_ref)";
			$aa = $this->dbh->prepare($pay_one);
			$aa->bindParam(':type',$payment_type);
			$da->bindParam(':customer',$cust_id);
			$aa->bindParam(':amt',$amt);
			$aa->bindParam(':fund_source',$fund_source);
			$aa->bindParam(':status',$status);
			$aa->bindParam(':date',$date);
			$aa->bindParam(':remarks',$remarks);
			$aa->bindParam(':gateway_provider',$gateway_provider);
			$aa->bindParam(':network_ref',$network_ref);
			$res = $aa->execute();
			if ($res) {
				$api->send_sms_notifications('Eco Stove',$phone,'Dear customer, we have received your payment of UGX '.$_REQUEST['amount'].'.Thank you for choosing Eco Stove Ltd Uganda.');	
			if ($api->totalAmtPaid($order_id) >= $api->upfrontRequired($order_id)) {
				$down_pa="update `order` set order_status = 'approved' where id = '".$order_id."'";
				$tho = $dbh->prepare($down_pa);
				$down_payo=$tho->execute();

				 if ($down_payo) {
					//$api->send_sms('VP UG Ltd',$payment_phone,'Dear '.$payment_name.', Your solar down payment for a '.$productvp.' of '.preg_replace("/[^0-9]/","",$_REQUEST['amount']).' UGX has been received by Village Power Uganda. Thanks for the payments');
				 }
				}
			}
		} 
	 }
 }
 
 $signature = base64_encode(sha1($_REQUEST['date_time'].$_REQUEST['amount'].$_REQUEST['narrative'].$_REQUEST['network_ref'].$_REQUEST['external_ref'].$_REQUEST['msisdn']));
 $fp = fopen("sys.txt","a");
 fwrite($fp,"Date: ".$_REQUEST['date_time']." Amount: ".$_REQUEST['amount']." Narrative: ".$_REQUEST['narrative']." Network Ref: ".$_REQUEST['network_ref']." External Ref: ".$_REQUEST['external_ref']." Msidn: ".$_REQUEST['msisdn']."Stage: ".$stage."\r\nSignature: ".$signature."\r\n");
 fclose($fp);
 header("HTTP/1.1 200 Ok");
 exit;
?>
<?php
 //HTTP Response code: 200
 //triggering sms to user
 //narrative=urlencode('testing');
?>
