<?php require_once('Connections/vpDB.php'); ?>
<?php require_once('functions.php'); ?>
<?php $api = new vp_(); ?>
<?php
error_reporting(0);
 //date_time
 //amount
 //narative - reason for payment
 //network_ref
 //msisdn
 //Date: 2015-04-13 17:50:16 Amount: 500 Narrative: 258 Pay VILLAGE POWER UGANDA LIMITED/Reason: 258/Customer Name:KATUSABE TUMUHAIRWE Network Ref: 182041915 External Ref: VILLAGEPOWER Msidn: 256755062197
 //Signature: MTViNzlhYTBiYjA5ZGE2Y2IxY2Y3MGVmOTVlMGNmZDU1YTNjMGRiNw==



 $fp = fopen("sys.txt","a");
 fwrite($fp,"Date: ".$_REQUEST['date_time']." Amount: ".$_REQUEST['amount']." Narrative: ".$_REQUEST['narrative']." Network Ref: ".$_REQUEST['network_ref']." External Ref: ".$_REQUEST['external_ref']." Msidn: ".$_REQUEST['msisdn']."Stage: Start\r\nSignature: ".$signature."\r\n");
 fclose($fp);
 
 
 
 $phone = $_REQUEST['msisdn'];
 $stage = "Reason Processing";
 switch(substr($phone,0,5))
 {
		 case '25678':
		 case '25677': $cust_id = $api->get_record("customer","cid","where default_phone = '".$api->format_number(trim($_REQUEST['narrative']))."'");
		 			   break;
		 case '25675':
		 case '25670': $temp = substr($_REQUEST['narrative'],strpos($_REQUEST['narrative'],'Reason:'));
		 			   $n = str_replace("Reason:","",substr($temp,0,strpos($temp,"/")));
					   $cust_id = $api->get_record("customer","cid","where default_phone = '".$api->format_number(trim($n))."'");
		 			   break;
		 default: $cust_id = "";
 }
 
 if($cust_id == "")
 {
	$cust_id = $api->get_record("customer","cid","where default_phone = '".$phone."'");
	$stage = "Phone Processing";
 }
 
 if(trim($cust_id) == "")
 {
	 $stage = "No Customer ID";
	 //No Customer Information
	 //Enter null payment reference

	 $query_one = mysql_query("select * from payment where network_ref = '".$_REQUEST['network_ref']."'");
	 $check = mysql_fetch_row($query_one);
	 if (!$check) {
	 	$pay_one = mysql_query("insert into payment (payment_type, amt, fund_source, `status`, `date`, remarks, gateway_provider, network_ref) values ('unknown', '".preg_replace("/[^0-9]/","",$_REQUEST['amount'])."', 'mobile_money_', 'processed', '".date("Y-m-d H:i:s", strtotime($_REQUEST['date_time']))."', '".mysql_real_escape_string($_REQUEST['narrative']." Msidn".$_REQUEST['msisdn'])."', 'yo','".mysql_real_escape_string($_REQUEST['network_ref'])."')");
	 	if ($pay_one){
	 		$api->send_new_sms('VP UG Ltd',$phone,'Dear customer, we have received your payment of UGX '.$_REQUEST['amount'].'.Thank you for choosing Village Power.');	
	 	}
	 	}	 
 } 
 else
 {
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
		 	$query_two = mysql_query("select * from payment where network_ref='".$_REQUEST['network_ref']."'");
		 	 $check2 = mysql_fetch_row($query_two);
		 	if (!$check2) {
			$t = mysql_query("insert into payment (payment_type, customer, `order`, amt, fund_source, `status`, `date`, remarks, gateway_provider,network_ref) values ('".($order_status == 'pending_down_payment' ? 'normal' : 'follow-up')."', '".$cust_id."','".$order_id."', '".preg_replace("/[^0-9]/","",$_REQUEST['amount'])."', 'mobile_money_', 'processed', '".date("Y-m-d H:i:s", strtotime($_REQUEST['date_time']))."', '".mysql_real_escape_string($_REQUEST['narrative']." Msidn".$_REQUEST['msisdn'])."', 'yo','".mysql_real_escape_string($_REQUEST['network_ref'])."')");
			
			if($t)
			{
			$api->send_new_sms('VP UG Ltd',$phone,'Dear customer, we have received your payment of UGX '.$_REQUEST['amount'].'.Thank you for choosing Village Power.');
			$pid = mysql_insert_id();
				//if($api->get_record("payment","count(*)","where `order` = '".$order_id."' and status = 'processed'") == 1)
			//mysql_query("update `order` set order_status = 'approved' where id = '".$order_id."'");
			//begining of the payment notification.
			
			 $call_status = mysql_query("select max(date_called),amount,date,cid  from calls_payments where cid ='".$cust_id."' ");
			if (mysql_num_rows($call_status)==1) {
				$max_date = $api->get_record("calls_payments", "max(date_called)", "where cid = '".$cust_id."'");
				mysql_query("update calls_payments set date='".date("Y-m-d H:i:s", strtotime($_REQUEST['date_time']))."',amount='".preg_replace("/[^0-9]/","",$_REQUEST['amount'])."' where cid = '".$cust_id."' and date_called='".$max_date."'");
			}

			$sms_status = mysql_query("select max(date_sms),amount_paid,date,cid  from sms_payments where cid ='".$cust_id."' ");
			if (mysql_num_rows($sms_status)==1) {
				$max_date_sms = $api->get_record("sms_payments", "max(date_sms)", "where cid = '".$cust_id."'");
				mysql_query("update sms_payments set date='".date("Y-m-d H:i:s", strtotime($_REQUEST['date_time']))."',amount_paid='".preg_replace("/[^0-9]/","",$_REQUEST['amount'])."' where cid = '".$cust_id."' and date_sms='".$max_date_sms."'");
			}
			
			$order_status = $api->get_record("order","order_status","where id = '".$order_id."'");
			$payment_phone = $api->get_record("customer","default_phone","where cid = '".$cust_id."'"); 
            $payment_name = $api->get_record("customer","concat(fname,' ',lname)","where cid = '".$cust_id."'");
            $productvp=$api->get_record("product", "product_code", "where pid = '".$api->get_record("order_item", "item", "where `order` = '".$order_id."'")."'");

			 if($order_status=="pending_evaluation"){
		     	if($api->get_record("payment","count(*)","where `order` = '".$order_id."' and status = 'processed'") == 1){
		     		 if ($api->totalAmtPaid($order_id) >= $api->upfrontRequired($order_id)){
			 	   mysql_query("update `order` set order_status = 'approved' where id = '".$order_id."'");
			 	}
			 }
		     		
           }else{
			 $api->send_new_sms('VP UG Ltd',$payment_phone,'Dear '.$payment_name.', your solar payment for a '.$productvp.' of '.preg_replace("/[^0-9]/","",$_REQUEST['amount']).' UGX has been received by Village Power Uganda. Thanks for the payments');
            }
			

			//End of the payment notifications
				//Update payment schedule
				//$schedule_id = $api->get_record("payment_schedule","id","where `order` = '".$order_id."' and payment_ref is null order by id asc limit 1");
				//if($schedule_id != "")
				//{
					//$previous_payment_date = $api->get_record("payment_schedule","next_payment_date","where id = '".$schedule_id."'");
					//Update payment ref in schedule table
					//mysql_query("update payment_schedule set payment_ref = '".$payment_ref."' where id = '".$schedule_id."'");
					//Determine active period
					//$period_active = $api->period_active($order_id,preg_replace("/[^0-9]/","",$_REQUEST['amount']));
					//Update timelines
					
						
							//Next payment date
							
							//mysql_query("insert into payment_schedule (`order`, next_payment_date, pending_amt) values ('".$order_id."', DATE_ADD(DATE_ADD('".$previous_payment_date."', INTERVAL ".$period_active['days']." DAY), INTERVAL ".$period_active['months']." MONTH), '".$api->order_balance($order_id).
		 if($api->get_record("order", "payment_plan", "where id = '".$order_id."'")=="hire")    {											
						$stage = "Payment Plan is Hire";	
			                       $query_h = "select * from payment where `order` = '".$order_id."' and status='processed' order by pid asc";
						$payment_schedule = mysql_query($query_h);
						$totalRows_h = mysql_num_rows($payment_schedule);
					if($totalRows_h > 0){
						$amount=0;$validity_buffer=0;$transactionBal=0;
                                                                                         
                                              while($row_h = mysql_fetch_assoc($payment_schedule)){ 
                                               $payment_delay=$api->paymentDelay($row_h['order'],$row_h['pid'],$api->overDueValidity($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt'],"",$validity_buffer,$transactionBal),$validity_buffer,$row_h['date']);
                                                                                                 					 
                                             $next_payment_date=$api->scheduleValidity($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt'],$payment_delay,$validity_buffer,$transactionBal); 
                                             $pending_amt=$api->scheduleOutstanding($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt']);
                                             $active_days=$api->daysPaid($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt'],$transactionBal);
                                             
                                             $amount+=$row_h['amt'];
                                             $validity_buffer=$api->overDueValidity($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt'],$payment_delay,$validity_buffer,$transactionBal);
                                             $transactionBal=$api->transactionBal($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt']);
                                                 } 
                                                 }
                                                                                 
                                        if($active_days > 0)
					{
					    $api->toggle_order_device($order_id, 'turn_on');
				  if($pending_amt > 0){
                          $query = "select * from payment_schedule where `order`=".$order_id." and reminded='no' and device_toggle='no'";
                          $query_run = mysql_query($query);
		                    if ($query_run) {
		                     if (mysql_num_rows($query_run)==1) {
		                    $r=mysql_query("update payment_schedule set reminded='yes', device_toggle='yes' where `order`=".$order_id."");
		                 if ($r) {
		                   mysql_query("insert into payment_schedule (`order`, next_payment_date, pending_amt, payment_ref) values ('".$order_id."', '".$next_payment_date."', '".$pending_amt."','".$pid."')");
		                    }

				            }else{
				              mysql_query("insert into payment_schedule (`order`, next_payment_date, pending_amt, payment_ref) values ('".$order_id."', '".$next_payment_date."', '".$pending_amt."','".$pid."')");
				            }
		                 }
		              //mysql_query("insert into payment_schedule (`order`, next_payment_date, pending_amt, payment_ref) values ('".$order_id."', '".$next_payment_date."', '".$pending_amt."','".$payment_ref."')");         
		            
            }else //bal here
            {
              //Completion of process detected
              mysql_query("update `order` set completion_date = '".date("Y-m-d H:i:s")."' where id = '".$order_id."'");
            }
					}
					}
					else $stage = "Payment Plan is Full";
					
					//Modify Order Status (if necessary)
				//}
			 }
			else { $stage = mysql_error(); }
		 }
	     }
	     else
		 {
			 $stage = 'Order ID determined';
			 
			 //Pending payments present
			 $payment_id = $api->get_record("payment","pid","where `order` = '".$order_id."' and amt <= '".preg_replace("/[^0-9]/","",$_REQUEST['amount'])."' and `status` = 'pending'");
			 
			 $stage = "Attaching payment to pending order - payment ref: ".$payment_id." Order ref: ".$order_id;
			 $h = mysql_query("update payment set fund_source='mobile_money_', `status`='processed', `date`='".date("Y-m-d H:i:s", strtotime($_REQUEST['date_time']))."', remarks='".mysql_real_escape_string($_REQUEST['narrative']." Msidn".$_REQUEST['msisdn'])."', gateway_provider='yo' where pid = '".$payment_id."'");
			 
			 if($h && mysql_affected_rows() > 0)
			 {
				 $stage = $order_status;
				 switch($order_status)
				 {
					 case 'pending_down_payment': $t1 = mysql_query("update `order` set order_status = 'approved' where id = '".$order_id."'");
					 							  if($t1) { $stage = "Payment attached to order"; }
												  else { $stage = mysql_error(); }
					 							  break;
				 }
			 }
			 else { $stage = mysql_error(); }
		 }
	 } 
	 else
	 {
		 $stage = "No order determined";
	$query_three = mysql_query("select * from payment where network_ref='".$_REQUEST['network_ref']."'");
	$check3= mysql_fetch_row($query_three);
	if (!$check3) {
	$pay_two =mysql_query("insert into payment (payment_type, customer, amt, fund_source, `status`, `date`, remarks, gateway_provider,network_ref) values ('unknown', '".$cust_id."', '".preg_replace("/[^0-9]/","",$_REQUEST['amount'])."', 'mobile_money_', 'processed', '".date("Y-m-d H:i:s", strtotime($_REQUEST['date_time']))."', '".mysql_real_escape_string($_REQUEST['narrative']." Msidn".$_REQUEST['msisdn'])."', 'yo','".mysql_real_escape_string($_REQUEST['network_ref'])."')");
	if ($pay_two) {
			$call_status = mysql_query("select max(date_called),amount,date,cid  from calls_payments where cid ='".$cust_id."' ");
			if (mysql_num_rows($call_status)==1) {
				$max_date = $api->get_record("calls_payments", "max(date_called)", "where cid = '".$cust_id."'");
				mysql_query("update calls_payments set date='".date("Y-m-d H:i:s", strtotime($_REQUEST['date_time']))."',amount='".preg_replace("/[^0-9]/","",$_REQUEST['amount'])."' where cid = '".$cust_id."' and date_called='".$max_date."'");
			}
			$sms_status = mysql_query("select max(date_sms),amount_paid,date,cid  from sms_payments where cid ='".$cust_id."' ");
			if (mysql_num_rows($sms_status)==1) {
				$max_date_sms = $api->get_record("sms_payments", "max(date_sms)", "where cid = '".$cust_id."'");
				mysql_query("update sms_payments set date='".date("Y-m-d H:i:s", strtotime($_REQUEST['date_time']))."',amount_paid='".preg_replace("/[^0-9]/","",$_REQUEST['amount'])."' where cid = '".$cust_id."' and date_sms='".$max_date_sms."'");
			}
		$api->send_new_sms('VP UG Ltd',$phone,'Dear customer, we have received your payment of UGX '.$_REQUEST['amount'].'.Thank you for choosing Village Power.');
		if($api->get_record("payment","count(*)","where customer = '".$cust_id."' and status = 'processed'") == 1)
		mysql_query("update `order` set order_status = 'approved' where customer = '".$cust_id."'");
		

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

