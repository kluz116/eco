<?php
error_reporting(0);
require'connection.php';
ini_set('max_execution_time',10000);

session_start();


class Config extends Connection
{


public function loginUser()
{
	if(isset($_POST['username']) && isset($_POST['password'])){
		$username = $_POST['username'];
		$password = $_POST['password'];

		if (!empty($username) && !empty($password)) {
			try{

		    $data =$this->dbh-> prepare('select * from userregister where password=:password and username=:username');
			$data->bindParam(':username',$username);
			$data->bindParam(':password',$password);
			$data->execute();

			$row = $data->fetch(PDO::FETCH_ASSOC);

			if($row){
				         $_SESSION['username'] = $username;
						if(isset($_SESSION['username'])){
                            header("Location:index.php");
                             exit();
						}else{
							 header("Location:login.php");
						}
			}else{
				echo "You Not Found Here.";
			}

			}catch(PDOException $e){
				trigger_error('Error: ' .$e->getMessage());
			}

		}else{

			echo "Fill In All Fields To Login.";
		}
	}
}

  public function get_record ($table, $col, $conditions) {
  		try {
  				
		$result = '';
		$data =$this->dbh-> prepare("select ".$col." from `".$table."` ".$conditions);
		$start=$data->execute();
		if($start) {
			$result = $data->fetchColumn(); //or die(mysql_error());

			return $result;
		
		}
  			
  		} catch (Exception $e) {
  			
  	
	}
}
public function getSessionRole()
{
	try {
        if($_SESSION['username']){
        $username = $_SESSION['username'];

        $data =$this->dbh-> prepare('select * from userregister where username=:username');
        $data->bindParam(':username',$username);
        $results= $data->execute();

        while ($row= $data->fetch(PDO::FETCH_ASSOC)) {

            $firstname = $row['firstname'];
            $lastname = $row['lastname'];
		 	echo $category  = $firstname.' '.$lastname;
           }


          }



} catch (PDOException $e) {

     }
}
public function getSessionID()
{
	try {
        if($_SESSION['username']){
        $username = $_SESSION['username'];

        $data =$this->dbh-> prepare('select * from userregister where username=:username');
        $data->bindParam(':username',$username);
        $results= $data->execute();

        while ($row= $data->fetch(PDO::FETCH_ASSOC)) {

            $id = $row['id'];
            
		 	echo $id;
           }


          }



} catch (PDOException $e) {

     }
}
	public function order_total_cost($order)
	{
		$total = 0;
		$data =$this->dbh-> prepare("select * from order_item where `order` = '".$order."'");
		$data->execute();
		$row = $data->fetch(PDO::FETCH_ASSOC);
		do
		{
			$total += $row['quantity']*$row['unit_cost'];
		} while ($row = $data->fetch(PDO::FETCH_ASSOC));
		
		return $total;
	}
	public function order_status($status)
		  {
			  switch($status)
			  {
				  case 'installed': return 'Activated';
				  							 break;
				  case 'pending_down_payment': return 'Pending Deposit';
				  							 break;
				   case 'approved': return 'Deposit Received';
				  							 break;
				  							 							 
				  case 'items_disbursed': return 'Delivered';
				  							 break;
				   case 'pending_disbursement': return 'Pending Delivery';
				  							 break;
				  							 
				  case 'pending_evaluation': return 'Pending Deposit';
				  							 break;							 
				  default: return $status;
				  							 break;
				  
			  }
		  }

public function GetAllClients()
{
	try{

	$sth = $this->dbh->prepare("select * from customer join `order` on customer.cid = order.customer group by customer.cid order by customer.cid desc");
	$sth->execute();
	//$result = $sth->fetchAll(PDO::FETCH_ASSOC);
	echo "<select name ='attach' id='event_district' class='selectpicker' data-live-search='true' data-size='3' data-style='btn-success' data-width='100%'>";
	echo "<option  class='text-center'>Click Here To Choose Client To Attach Payments To</option>";
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$cid = $result['cid'];
		$name = $result['fname'];
		$lname = $result['lname'];
		$order = $result['id'];
	echo "<option value='$order'>$name $lname - OrderID 4000$order </option>";
	}

    
	echo "</select>";
	
	}catch(PDOException $e){


	}
}
public function GetAllClientsPayments()
{
	try{

	$sth = $this->dbh->prepare("select * from customer join `order` on customer.cid = order.customer group by customer.cid order by customer.cid desc");
	$sth->execute();
	
	echo "<select id='client' class='selectpicker' data-live-search='true' data-size='5' data-style='btn-success' data-width='100%'>";
	echo "<option  class='text-center'>Click Here To Choose Client To Make Payments For.</option>";
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$cid = $result['cid'];
		$name = $result['fname'];
		$lname = $result['lname'];
		$order = $result['id'];
	echo "<option value='$cid'>$name $lname - OrderID 4000$order </option>";
	}

    
	echo "</select>";
	
	}catch(PDOException $e){
		echo "$e";

	}
}
public function attachPayment()
{
	if (isset($_POST['payment'])) {
		$attach =$_POST['attach'];
		$pid = $_POST['payment_idd'];

		if (!empty($attach)) {
			$check_off= $this->dbh->prepare('SET FOREIGN_KEY_CHECKS=0');
	       if ($check_off->execute()) {
			$query= "update payment set `order`='".$attach."' where pid='".$pid."'";
			$sth = $this->dbh->prepare($query);
	        $res= $sth->execute();
	        if ($res) {
	        $customer = $this->get_record("order","customer","where id='".$attach."'");
	        $qy= "update payment set customer='".$customer ."' where pid='".$pid."'";
			$th = $this->dbh->prepare($qy);
	        $rs= $th->execute();
	        if ($rs) {
	        //$order_status = $api->get_record("order","order_status","where id = '".$attach."'");
			$payment_phone = $this->get_record("customer","default_phone","where cid = '".$customer."'"); 
			$payment_name = $this->get_record("customer","concat(fname,' ',lname)","where cid = '".$customer."'");
			$productvp=$this->get_record("product", "product_code", "where pid = '".$this->get_record("order_item", "item", "where `order` = '".$attach."'")."'");

	        $order_status = $this->get_record("order","order_status","where id = '".$attach."'");
			 if($order_status=="pending_evaluation"){
			 
			 if($this->get_record("payment","count(*)","where `order` = '".$attach."' and status = 'processed'") == 1){

				if ($this->totalAmtPaid($attach) >= $this->upfrontRequired($attach)) {
				$down_payment="update `order` set order_status = 'approved' where id = '".$attach."'";
				$th = $this->dbh->prepare($down_payment);
				$down_pay=$th->execute();
				}
			 
			 }
			
		   }
		   //Begin payment schedule
		   		 if($this->get_record("order", "payment_plan", "where id = '".$attach."'")=="hire")  
			 {	

			           $stage = "Payment Plan is Hire";	
					   $query_h = "select * from payment where `order` = '".$attach."' and status='processed' order by pid asc";
						$th = $this->dbh->prepare($query_h);
						$th->execute();
						$amount=0;$validity_buffer=0;$transactionBal=0;
					 while ($row_h = $th->fetch(PDO::FETCH_ASSOC)) {
						 $payment_delay=$this->paymentDelay($row_h['order'],$row_h['pid'],$this->overDueValidity($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt'],"",$validity_buffer,$transactionBal),$validity_buffer,$row_h['date']);
						  $next_payment_date=$this->scheduleValidity($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt'],$payment_delay,$validity_buffer,$transactionBal); 
						  $pending_amt=$this->scheduleOutstanding($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt']);
						  $active_days=$this->daysPaid($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt']);
						  $amount+=$row_h['amt'];
						  $validity_buffer=$this->overDueValidity($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt'],$payment_delay,$validity_buffer,$transactionBal);
						   $transactionBal=$this->transactionBal($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt']);
					 } 
												 
																				 
					if($active_days > 0)
					{
					 $serial_number = $this->get_record("product_inventory","item_no","where order_id='".$attach."'");
					 $code_of_pay= $this->generate($active_minutes, $serial_number);
					 $this->send_sms_notifications('Eco Stove',$phone,'Dear customer, we have received your payment of UGX '.$_REQUEST['amount'].'.Thank you for choosing Eco Stove Ltd Uganda.');
					 $this->send_sms_notifications('Eco Stove',$payment_phone,'Dear '.$payment_name.', Your Pay Go Code Is *'.$code_of_pay.'# . Thanks for the payments');

				      if($pending_amt > 0){
						  $query = "select * from payment_schedule where `order`=".$attach." and reminded='no' and device_toggle='no'";
						  $sth = $this->dbh->prepare($query);
						  $sth->execute();
						   $ro = $dat->fetch(PDO::FETCH_ASSOC);
						 if ($ro) {
						  
							    $r="update payment_schedule set reminded='yes', device_toggle='yes' where `order`=".$attach."";
							    $th = $this->dbh->prepare($r);
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
		   //End payment Schedule
				
	        	echo "Payment Successfuly Attached";
	        }
	        	$check_on= $this->dbh->prepare('SET FOREIGN_KEY_CHECKS=1');
	           $check_on->execute();
	        }
	    }
		}
	}
}


public function getFemaleClients()
{
	try{

	
	$query = "SELECT cid FROM   `customer` where gender='female'";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
		

	}catch(PDOException $e){


	}
}
public function getFemaleCharts()
{
	try{

	//$query = "SELECT SUM(revenue) as totalSales FROM sales WHERE YEAR(date) = '2016' GROUP BY MONTH(date)";
	$query = "SELECT COUNT(cid) As total FROM  `customer` where gender='female'";
	//$query = "SELECT MONTH(date) AS month, COUNT(date)  AS total FROM sales WHERE date >= NOW() - INTERVAL 1 YEAR GROUP BY MONTH(date)";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
	$rows[] = $result['total'];
      
    }
   //echo json_encode($return_arr);
   echo join($rows, ',');
    
	}catch(PDOException $e){


	}
}
public function getmaleCharts()
{
	try{

	//$query = "SELECT SUM(revenue) as totalSales FROM sales WHERE YEAR(date) = '2016' GROUP BY MONTH(date)";
	$query = "SELECT COUNT(cid) As total FROM  `customer` where gender='male'";
	//$query = "SELECT MONTH(date) AS month, COUNT(date)  AS total FROM sales WHERE date >= NOW() - INTERVAL 1 YEAR GROUP BY MONTH(date)";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
	$rows[] = $result['total'];
      
    }
   //echo json_encode($return_arr);
   echo join($rows, ',');
    
	}catch(PDOException $e){


	}
}
public function getmale()
{
	try{

	
	$query = "SELECT cid FROM   `customer` where gender='male'";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
		

	}catch(PDOException $e){


	}
}
public function getUrbanClient()
{
	try{

	
	$query = "SELECT cid FROM   `customer` where location='Urban'";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
		

	}catch(PDOException $e){


	}
}
public function getRuralCharts()
{
	try{

	//$query = "SELECT SUM(revenue) as totalSales FROM sales WHERE YEAR(date) = '2016' GROUP BY MONTH(date)";
	$query = "SELECT COUNT(cid) As total FROM  `customer` where location='Rural'";
	//$query = "SELECT MONTH(date) AS month, COUNT(date)  AS total FROM sales WHERE date >= NOW() - INTERVAL 1 YEAR GROUP BY MONTH(date)";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
	$rows[] = $result['total'];
      
    }
   //echo json_encode($return_arr);
   echo join($rows, ',');
    
	}catch(PDOException $e){


	}
}
public function getUrbanCharts()
{
	try{

	//$query = "SELECT SUM(revenue) as totalSales FROM sales WHERE YEAR(date) = '2016' GROUP BY MONTH(date)";
	$query = "SELECT COUNT(cid) As total FROM  `customer` where location='Urban'";
	//$query = "SELECT MONTH(date) AS month, COUNT(date)  AS total FROM sales WHERE date >= NOW() - INTERVAL 1 YEAR GROUP BY MONTH(date)";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
	$rows[] = $result['total'];
      
    }
   //echo json_encode($return_arr);
   echo join($rows, ',');
    
	}catch(PDOException $e){


	}
}
public function getRuralClient()
{
	try{

	
	$query = "SELECT cid FROM   `customer` where location='Rural'";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
		

	}catch(PDOException $e){


	}
}
public function getNormalClient()
{
	try{

	
	$query = "SELECT cid FROM   `customer` where condtion='Normal'";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
		

	}catch(PDOException $e){


	}
}
public function getDisableClient()
{
	try{

	
	$query = "SELECT cid FROM   `customer` where condtion='Disable'";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
		

	}catch(PDOException $e){


	}
}
public function GetNumberOfClient()
{
	try{

	
	$query = "SELECT cid FROM   `customer` ";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
		

	}catch(PDOException $e){


	}
}
public function GetNumberOfOrders()
{
	try{

	
	$query = "SELECT id FROM  `order` ";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
		

	}catch(PDOException $e){


	}
}
public function getMajorClient()
{
	try{

	
	$query = "SELECT cid FROM   `customer` where group_='Major'";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
		

	}catch(PDOException $e){


	}
}
public function getMinorClient()
{
	try{

	
	$query = "SELECT cid FROM   `customer` where group_='Minor'";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
		

	}catch(PDOException $e){


	}
}
public function getPro1()
{
	 try{

 
  $query = "SELECT COUNT(order_id)  AS totalSales FROM sales WHERE date >= NOW() - INTERVAL 1 YEAR AND kit_type='P000S' GROUP BY MONTH(date)";
  $sth = $this->dbh->prepare($query);
  $sth->execute();
  while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
  $rows[] = $result['totalSales'];
      
    }
   //echo json_encode($return_arr);
   echo join($rows, ',');
    
  }catch(PDOException $e){


  } 

}
public function getPro2()
{
	 try{

 
  $query = "SELECT COUNT(order_id)  AS totalSales FROM sales WHERE date >= NOW() - INTERVAL 1 YEAR AND kit_type='PD000D' GROUP BY MONTH(date)";
  $sth = $this->dbh->prepare($query);
  $sth->execute();
  while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
  $rows[] = $result['totalSales'];
      
    }
   //echo json_encode($return_arr);
   echo join($rows, ',');
    
  }catch(PDOException $e){


  } 

}
public function getPaidUp()
{
	try{

	
	$query = "SELECT * FROM `customer_status` WHERE status='paid_up'";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
		

	}catch(PDOException $e){


	}
}
public function getDue()
{
	try{

	
	$query = "SELECT * FROM `customer_status` WHERE status='due'";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
		

	}catch(PDOException $e){


	}
}
public function getWithIn()
{
	try{

	
	$query = "SELECT * FROM `customer_status` WHERE status='with_in'";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
		

	}catch(PDOException $e){


	}
}
public function getSales()
{
	try{
	$query = "SELECT * FROM sales";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
	}catch(PDOException $e){


	}
}
public function getValue()
{
	try{
	$query = "SELECT SUM(revenue) As value FROM sales";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		
		echo number_format($result['value']);
		
		 }
	}catch(PDOException $e){


	}
}
public function getOutstanding()
{
	try{
	$query = "SELECT SUM(outstanding) As total FROM customer_status where status='due' or status='with_in'";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {

		echo number_format($result['total']);
	   
		 }
	}catch(PDOException $e){


	}
}
public function getInitiated()
{
	try{
	$query = "select `order` from order_item where `order` in (select order_id as id from sales) and installation_date is not null";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
	}catch(PDOException $e){


	}
}
public function getNotInitiated()
{
	try{
	$query = "select `order` from order_item where `order` in (select order_id as id from sales) and installation_date is null";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
	}catch(PDOException $e){


	}
}
public function getPendingDeposit()
{
	try{
	$query = "select o.* from `order` o join order_item ot on o.id=ot.order left outer join payment p on ot.order=p.order where p.order is null order by o.when_placed desc";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
	}catch(PDOException $e){


	}
}
public function getPendingDelivery()
{
	try{
	$query = "select `order` from order_item where `order` in (select order_id as id from sales) and disburse_date is null";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
	}catch(PDOException $e){


	}
}
public function getDelivery()
{
	try{
	$query = "select `order` from order_item where `order` in (select order_id as id from sales) and disburse_date is not null";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		echo count($result);
		
		 }
	}catch(PDOException $e){


	}
}
public function GetDownPayment()
{
	try{

	
	$query = "SELECT  ( select count(id) from `order` where order_status='pending_evaluation') AS pending, (select count(id) from `order` where order_status!='pending_evaluation') AS recieved";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	echo "<table class='table table-bordered'>";
		 echo "<thead>";
		 echo"<tr>";
		 echo "<th>Recieved</th>";
		 echo "<th>Pending</th>";
	    echo "</tr>";
		 echo "</thead>";
	    echo "<tbody>";


	
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$recieved = number_format(abs($result['recieved']));
		$pending = number_format(abs($result['pending']));


	        echo "<tr>";
		 	echo "<th><a href='orders.php'>$recieved</a></th>";
		 	echo "<th><a href='prospect.php'>$pending</a></th>";
		 	echo "</tr>";

		 }
		 echo "</tbody>";
		 echo "</table>";

	}catch(PDOException $e){


	}
}
public function format_number($number)
	{
		$number = preg_replace("/[^0-9]/","",urldecode($number));
		$number = round($number);
		$country_code = 256;
		$number_len = 12;
		
		if(substr($number,0,strlen($country_code)) == $country_code && strlen($number) == $number_len) return $number;
		else if((strlen($number)+strlen($country_code)) == $number_len) return $country_code.$number;
		else return $number;
	}
public function sendRequest($query) {
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

public function trigger_SMS()
{
    //try{
    $sth = $this->dbh->prepare("select * from sms where status='pending'");
	$sth->execute();
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$recipient = $result['recipient'];
		$message = $result['message'];

		if ($recipient!='') {
			$host = 'http://smgw1.yo.co.ug/sendsms?ybsacctno=1000601891&password='.urlencode("5uMpEg").'&origin='.urlencode($result['sender']).'&sms_content='.urlencode($result['message']).'&destinations='.$this->format_number($result['recipient']).'&nostore=1';
            $feedback = $this->sendRequest($host);
	       $query= "update sms set `status` = 'sent', when_sent = '".date("Y-m-d H:i:s")."', more_info = '".$feedback."' where id = '".$result['id']."'";
	       $sth = $this->dbh->prepare($query);
	       $res= $sth->execute();
	       if ($res) {
	       	 echo "Succeffuly Sent SMS To ".$recipient." \n";

	        }
			
		}
	}
    //}catch(PDOException $e){
      //trigger_error("error_msg").$e;

    //}
}
public function send_sms_notifications($sender,$recipient,$message)
	{ 
	 $q= "insert into sms (sender, recipient, message) values (:sender, :recipient, :message)";
	 $b = $this->dbh->prepare($q);
	 $b->bindParam(':sender',$sender);
	 $b->bindParam(':recipient',$recipient);
	 $b->bindParam(':message',$message);
	 $tt = $b->execute();
	  if($tt) 
		{
		return true;
	} return false;
	}

public function GetSample()
{
	try{

	
	$query = "SELECT ot.order as orderId from order_item ot join payment p on ot.order=p.order where p.order is not null group by p.order";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		  echo $res = number_format(abs(count($result)));
	

		 }
		

	}catch(PDOException $e){


	}
}
public function ChartRevenua()
{
	try{

	//$query = "SELECT SUM(revenue) as totalSales FROM sales WHERE YEAR(date) = '2016' GROUP BY MONTH(date)";
	$query = "SELECT SUM(revenue) as total FROM sales WHERE YEAR(date) = '2016' GROUP BY MONTH(date)";
	//$query = "SELECT MONTH(date) AS month, COUNT(date)  AS total FROM sales WHERE date >= NOW() - INTERVAL 1 YEAR GROUP BY MONTH(date)";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
	$rows[] = $result['total'];
      
    }
   //echo json_encode($return_arr);
   echo join($rows, ',');
    
	}catch(PDOException $e){


	}
}
public function Get_Sample()
{
	try{

	
	$query = "SELECT ot.order as orderId from order_item ot join payment p on ot.order=p.order where p.order is not null group by p.order";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$order=$result['orderId'];
		
	    return $order;
	

		 }
		

	}catch(PDOException $e){


	}
}
public function GetSample2()
{
	try{

	
	$query = "SELECT o.* from `order` o left outer join order_item ot on o.id=ot.order left outer join payment p on ot.order=p.order where ot.order is null or p.order is null";
	$sth = $this->dbh->prepare($query);
	$sth->execute();


         $downPaymentReceived=array();
	     $pendingDownPayment=array();
	
	while ($result = $sth->fetchAll()) {
		
		  echo $res = number_format(abs(count($result)));
	

		 }
		

	}catch(PDOException $e){


	}
}
public function GetNumberDisbursed()
{
	try{

	
	$query = "SELECT ot.order as orderId from order_item ot join payment p on ot.order=p.order where p.order is not null  and ot.disburse_date is not null group by p.order";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		  echo $res = number_format(abs(count($result)));
	

		 }
		

	}catch(PDOException $e){


	}
}
public function GetNumberNotDisbursed()
{
	try{

	
	$query = "SELECT ot.order as orderId from order_item ot join payment p on ot.order=p.order where p.order is not null  and ot.disburse_date is  null group by p.order";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		  echo $res = number_format(abs(count($result)));
	

		 }
		

	}catch(PDOException $e){


	}
}
public function GetDisbursed()
{
	try{

	
	$query = "SELECT  ( SELECT COUNT(*) FROM order_item where disburse_date is not null  ) AS disbursed, ( SELECT COUNT(*) FROM   order_item where disburse_date is  null ) AS not_disbursed";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	echo "<table class='table table-bordered'>";
		 echo "<thead>";
		 echo"<tr>";
		 echo "<th>Disbursed</th>";
		 echo "<th>Not Disbursed</th>";
	    echo "</tr>";
		 echo "</thead>";
	    echo "<tbody>";


	
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$disbursed = number_format(abs($result['disbursed']));
		$not_disbursed = number_format(abs($result['not_disbursed']));


	        echo "<tr>";
		 	echo "<th><a href=''>$disbursed</a></th>";
		 	echo "<th><a href=''>$not_disbursed</a></th>";
		 	echo "</tr>";

		 }
		 echo "</tbody>";
		 echo "</table>";

	}catch(PDOException $e){


	}
}
public function GetNumberInstalled()
{
	try{

	
	$query = "SELECT ot.order as orderId from order_item ot join payment p on ot.order=p.order where p.order is not null  and ot.installation_date is not null and disburse_date is not null group by p.order";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		  echo $res = number_format(abs(count($result)));
	

		 }
		

	}catch(PDOException $e){


	}
}
public function GetNumberNotInstalled()
{
	try{

	
	$query = "SELECT ot.order as orderId from order_item ot join payment p on ot.order=p.order where p.order is not null  and ot.installation_date is null and disburse_date is not null group by p.order";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll()) {
		
		  echo $res = number_format(abs(count($result)));
	

		 }
		

	}catch(PDOException $e){


	}
}
public function AmountCollected()
{
	try{

	
	$query = "SELECT SUM(amt) as total FROM payment where customer is not null and `order` is not null and status='processed'";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		
		  echo $res = number_format(abs($result['total']));
	

		 }
		

	}catch(PDOException $e){


	}
}
public function GetInstalltion()
{
	try{

	
	$query = "SELECT  ( SELECT COUNT(*) FROM order_item where installation_date is not null and disburse_date is not null ) AS installed, ( SELECT COUNT(*) FROM   order_item where installation_date is  null and disburse_date is not null) AS not_installed";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	echo "<table class='table table-bordered'>";
		 echo "<thead>";
		 echo"<tr>";
		 echo "<th>Installed</th>";
		 echo "<th>Not Installed</th>";
	    echo "</tr>";
		 echo "</thead>";
	    echo "<tbody>";


	
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$installed = number_format(abs($result['installed']));
		$not_installed = number_format(abs($result['not_installed']));


	        echo "<tr>";
		 	echo "<th><a href=''>$installed</a></th>";
		 	echo "<th><a href=''>$not_installed</a></th>";
		 	echo "</tr>";

		 }
		 echo "</tbody>";
		 echo "</table>";

	}catch(PDOException $e){


	}
}
public function GetAmount()
{
	try{

	
	$query = "SELECT  (SELECT SUM(amt) FROM payment where customer is not null and `order` is not null and status='processed') AS totalAmount, ( SELECT COUNT(*) FROM   order_item where installation_date is  null ) AS not_installed";
	$sth = $this->dbh->prepare($query);
	$sth->execute();
	echo "<table class='table table-bordered'>";
		 echo "<thead>";
		 echo"<tr>";
		 echo "<th>Amount </th>";
		 echo "<th>Not Installed</th>";
	    echo "</tr>";
		 echo "</thead>";
	    echo "<tbody>";


	
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$totalAmount = number_format(abs($result['totalAmount']));
		$not_installed = number_format(abs($result['not_installed']));


	        echo "<tr>";
		 	echo "<th><a href=''>$totalAmount</a></th>";
		 	echo "<th><a href=''>$not_installed</a></th>";
		 	echo "</tr>";

		 }
		 echo "</tbody>";
		 echo "</table>";

	}catch(PDOException $e){


	}
}
public function GetDistrict()
{
	try{

	$sth = $this->dbh->prepare("select * from region");
	$sth->execute();
	echo "<select name='region' class='selectpicker' data-live-search='true' data-size='3' data-style='btn-success'   style='width: 100%'>";
	echo "<option >Choose District Please</option>";
	echo "<option >NULL</option>";
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$id = $result['id'];
		$name = $result['region_name'];

	echo "<option value='$id'>$name</option>";
	}

    
	echo "</select>";
	
	}catch(PDOException $e){


	}
}
public function GetInstitution()
{
	try{

	$sth = $this->dbh->prepare("SELECT * FROM `instituations`");
	$sth->execute();
	echo "<select name='institution' class='selectpicker' data-live-search='true' data-size='3' data-style='btn-success' data-width='50%'>";
	echo "<option >Choose Institution Please</option>";
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$id = $result['id'];
		$name = $result['institution_name'];

	echo "<option value='$id'>$name</option>";
	}

    
	echo "</select>";
	
	}catch(PDOException $e){


	}
}
public function GetPos()
{
	try{

	$sth = $this->dbh->prepare("select * from pos");
	$sth->execute();
	echo "<select name='pos' class='selectpicker' data-live-search='true' data-size='3' data-style='btn-success'   style='width: 100%'>";
	echo "<option >Choose Point Of Sale</option>";
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$id = $result['posid'];
		$name = $result['name'];

	echo "<option value='$id'>$name</option>";
	}

    
	echo "</select>";
	
	}catch(PDOException $e){


	}
}
public function GetLanguage()
{
	try{

	$sth = $this->dbh->prepare("select * from language");
	$sth->execute();
	echo "<select name='language' class='selectpicker' data-live-search='true' data-size='3' data-style='btn-success'   style='width: 100%'>";
	echo "<option >Choose Language Please</option>";
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$id = $result['id'];
		$name = $result['name'];

	echo "<option value='$id'>$name</option>";
	}

    
	echo "</select>";
	
	}catch(PDOException $e){


	}
}
public function SubCounty()
{
	try{

	$sth = $this->dbh->prepare("select * from subcounty");
	$sth->execute();
	echo "<select name='subcounty' class='selectpicker' data-live-search='true' data-size='3' data-style='btn-success' style='width: 100%'>";
	echo "<option >Choose  Sub-County</option>";
	echo "<option >NULL</option>";
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$id = $result['id'];
		$name = $result['subcounty_name'];

	echo "<option value='$id'>$name</option>";
	}

    
	echo "</select>";
	
	}catch(PDOException $e){


	}
}
public function Parish()
{
	try{

	$sth = $this->dbh->prepare("select * from parish");
	$sth->execute();
	echo "<select name='parish' class='selectpicker' data-live-search='true' data-size='3' data-style='btn-success'   style='width: 100%'>";
	echo "<option >Choose Parish Please</option>";
	echo "<option >NULL</option>";
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$id = $result['id'];
		$name = $result['parish_name'];

	echo "<option value='$id'>$name</option>";
	}

    
	echo "</select>";
	
	}catch(PDOException $e){


	}
}
public function GetClientsOrders()
{
	try{

	$sth = $this->dbh->prepare("select cid, default_phone, concat(fname,' ',lname) as name from customer where `status` = '1' and cid not in (select customer as cid from `order`) order by when_added desc");
	$sth->execute();
	echo "<select name='customer' class='selectpicker' data-live-search='true' data-size='3' data-style='btn-success'   data-width='100%'>";
	echo "<option >Choose Client To Make Order For</option>";
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$id = $result['cid'];
		$name = $result['name'];

	echo "<option value='$id'>$name</option>";
	}

    
	echo "</select>";
	
	}catch(PDOException $e){


	}
}
public function GetProducts()
{
	try{

	$sth = $this->dbh->prepare("select pid, name, product_code from `product` where `status` = '1' order by name asc");
	$sth->execute();
	echo "<select name='product_id' class='selectpicker' data-live-search='true' data-size='5' data-style='btn-success'   data-width='50%'>";
	echo "<option >Choose Product Type</option>";
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$id = $result['pid'];
		$name = $result['name'];
		$product_code = $result['product_code'];
		$cost = number_format($this->get_record("product_cost", "cost", "where product = '".$id."'"));

	echo "<option value='$id'>$product_code - $name- $cost</option>";
	}

    
	echo "</select>";
	
	}catch(PDOException $e){


	}
}
public function GetProducts_two()
{
	try{
	$sth = $this->dbh->prepare("select pid, name, product_code from `product` where `status` = '1' order by name asc");
	$sth->execute();
	echo "<select id='product' class='selectpicker' data-live-search='true' data-size='3' data-style='btn-success'   data-width='100%'>";
	echo "<option >Choose Product Type</option>";
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$pid = $result['pid'];
		$name = $result['name'];
		$product_code = $result['product_code'];

	echo "<option value='$pid'>$product_code $name</option>";
	}

    
	echo "</select>";
	
	}catch(PDOException $e){


	}
}
public function GetProductList()
{
	try{

	//$sth = $this->dbh->prepare("select * from product_inventory ");
	$sth = $this->dbh->prepare("select id, serial_no, product_type from product_inventory where id not in (select item_disbursed as id from order_item where item_disbursed is not null)");
	$sth->execute();
	echo "<select  name='item'  class='selectpicker' data-live-search='true' data-size='3' data-style='btn-success'   data-width='100%'>";
	echo "<option >Assign Barcode To The Client Before Delivering</option>";
	while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$serial_no = $result['serial_no'];
		$id= $result['id'];
		$serial_no = $result['serial_no'];
		$product_code= $this->get_record("product","product_code","where pid = '".$result['product_type']."'");
	echo "<option value='$id'>$product_code - $serial_no</option>";
	}

    
	echo "</select>";
	
	}catch(PDOException $e){
		echo "$e";

	}
}



public function deliverItem()
{
try{

	if (isset($_POST['deliver'])) {
		
	$delivery_date = $_POST['delivery_date'];
	$item_id = $_POST['item_id'];
	$item = $_POST['item'];
	$check_off= $this->dbh->prepare('SET FOREIGN_KEY_CHECKS=0');
	 if ($check_off->execute()) {

	$query= "update order_item set item_disbursed='".$item."',disburse_date='".$delivery_date."' where `order`='".$item_id."'";
	$sth = $this->dbh->prepare($query);
	$res= $sth->execute();
	if ($res) {
	$qry= "update `order` set order_status='items_disbursed' where id='".$item_id."'";
	$th = $this->dbh->prepare($qry);
	$rs= $th->execute();

	if ($rs) {
	$qy= "update product_inventory set status=0,order_id='".$item_id."' where id='".$item."'";
	$o = $this->dbh->prepare($qy);
	$ros= $o->execute();
	echo "Successfuly Delivered Item";
	}


	$check_on= $this->dbh->prepare('SET FOREIGN_KEY_CHECKS=1');
	$check_on->execute();
	}
  }

	}


}catch(PDOException $e){

trigger_error("error_msg".$e->getMessage());
echo "$e";	
}
}


public function initiateItem()
{
try{

	if (isset($_POST['initiate'])) {
		
	$initiate_date = $_POST['initiate_date'];
	$item_id = $_POST['item_id'];
	$check_off= $this->dbh->prepare('SET FOREIGN_KEY_CHECKS=0');
	 if ($check_off->execute()) {

	$query= "update order_item set installation_date='".$initiate_date."' where `order`='".$item_id."'";
	$sth = $this->dbh->prepare($query);
	$res= $sth->execute();
	if ($res) {
	$qry= "update `order` set order_status='installed' where id='".$item_id."'";
	$th = $this->dbh->prepare($qry);
	$rs= $th->execute();

	if ($rs) {
	/*	
		if($this->get_record("order", "payment_plan", "where id = '".$item_id."'")=="hire")  
			 {											
			           $stage = "Payment Plan is Hire";	
					   $query_h = "select * from payment where `order` = '".$item_id."' and status='processed' order by pid asc";
						$tho = $this->dbh->prepare($query_h);
						$tho->execute();
						$amount=0;$validity_buffer=0;$transactionBal=0;
					 while ($row = $tho->fetch(PDO::FETCH_ASSOC)) {
						 $payment_delay=$this->paymentDelay($row_h['order'],$row_h['pid'],$this->overDueValidity($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt'],"",$validity_buffer,$transactionBal),$validity_buffer,$row_h['date']);
						  $next_payment_date=$api->scheduleValidity($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt'],$payment_delay,$validity_buffer,$transactionBal); 
						  $pending_amt=$this->scheduleOutstanding($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt']);
						  $active_days=$this->daysPaid($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt']);
						  $amount+=$row_h['amt'];
						  $validity_buffer=$this->overDueValidity($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt'],$payment_delay,$validity_buffer,$transactionBal);
						   $transactionBal=$this->transactionBal($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt']);
						   $pid = $row_h['pid'];
					 } 
												 
																				 
					if($active_days > 0)
					{
				      if($pending_amt > 0){
					
							$qqq= "insert into payment_schedule (`order`, next_payment_date, pending_amt, payment_ref) values (:order, :next_payment_date, :pending_amt, :payment_ref)";
							$bbb = $this->dbh->prepare($qqq);
							$bbb->bindParam(':order',$item_id);
							$bbb->bindParam(':next_payment_date',$next_payment_date);
						    $bbb->bindParam(':pending_amt',$pending_amt);
							$bbb->bindParam(':payment_ref',$pid);
							$tt = $bbb->execute();
							
						 }        
					
			            }
				}*/
		   //End payment Schedule
		echo "Successfuly Item Initiated";	
	}//End Order status changed

	
	$check_on= $this->dbh->prepare('SET FOREIGN_KEY_CHECKS=1');
	$check_on->execute();
	}
  }

	}


}catch(PDOException $e){

trigger_error("error_msg".$e->getMessage());	
echo "$e";
}
}

public function UpdateClients()
{
	    $fname = $_POST['fname'];
		$lname = $_POST['lname'];
		$default_phone = $_POST['default_phone'];
		$alt_phone = $_POST['alt_phone'];
		$next_ov_keen = $_POST['next_ov_keen'];
		$nok_phone = $_POST['nok_phone'];
		$address_desc = $_POST['address_desc'];
		$location= $_POST['location'];
		$cid = $_POST['cid'];

		$query= "update customer set fname='".$fname."',lname='".$lname."',default_phone='".$default_phone."',alt_phone='".$alt_phone."',next_ov_keen='".$next_ov_keen."',nok_phone='".$nok_phone."',location='".$location."',address_desc='".$address_desc."' where cid='".$cid."'";
		$sth = $this->dbh->prepare($query);
	    $res= $sth->execute();
	    if ($res) {
	    	echo "Successfuly Updated ".$fname.' '.$lname;
	    }
	
}

public function UpdateOrders()
{
	    $institution = $_POST['institution'];
		$id = $_POST['id'];
		$user = $_POST['user'];
		if (!empty($institution)) {
					$query= "update `order` set institution='".$institution."',session='".$user."' where id='".$id."'";
		$sth = $this->dbh->prepare($query);
	    $res= $sth->execute();
	    if ($res) {
	    	echo "Successfuly Updated";
	    }
	
		}


}

public function addClient()
{ 

	try {
	if (isset($_POST['addClient'])) {

		$fname = $_POST['fname'];
		$lname = $_POST['lname'];
		$gender = $_POST['gender'];
		$language = $_POST['language'];
		$location = $_POST['location'];
		$condtion = $_POST['condtion'];
		$group = $_POST['group'];
		$default_phone = $_POST['default_phone'];
		$alt_phone = $_POST['alt_phone'];
		$next_ov_keen = $_POST['next_ov_keen'];
		$nok_phone = $_POST['nok_phone'];
		$region = $_POST['region'];
		$subcounty = $_POST['subcounty'];
		$parish = $_POST['parish'];
		$address_desc = $_POST['address_desc'];
		$targetDir = "uploads/";
	    $fileName = $_FILES['image']['name'];
	    $targetFile = $targetDir.$fileName;
		$status = '1';
		$when_added = date("Y-m-d");
		$user = $_POST['user'];

		if(empty($fname) && empty($lname) && empty($default_phone) && empty($next_ov_keen) && empty($nok_phone)) {
			echo "<div class='text-center'>Fill In All Fields</div>";
		}else{
			//if (move_uploaded_file($_FILES['image']['tmp_name'],$targetFile)) {
			$check_off= $this->dbh->prepare('SET FOREIGN_KEY_CHECKS=0');
	       if ($check_off->execute()) {
			$pay="insert into customer (fname, lname, gender, `language`, location, condtion, group_, default_phone,alt_phone,next_ov_keen,nok_phone,address_desc, region,subcounty,parish,when_added, `status`,file_name,session) VALUES (:fname, :lname, :gender, :language,:location, :condtion, :group,:default_phone,:alt_phone,:next_ov_keen,:nok_phone,:address_desc,:region,:subcounty,:parish,:when_added, :status,:file_name,:session)";
			$data = $this->dbh->prepare($pay);
			$data->bindParam(':fname',$fname);
			$data->bindParam(':lname',$lname);
			$data->bindParam(':gender',$gender);
			$data->bindParam(':language',$language);
			$data->bindParam(':location',$location);
			$data->bindParam(':condtion',$condtion);
			$data->bindParam(':group',$group);
			$data->bindParam(':default_phone',$default_phone);
			$data->bindParam(':alt_phone',$alt_phone);
			$data->bindParam(':next_ov_keen',$next_ov_keen);
			$data->bindParam(':nok_phone',$nok_phone);
			$data->bindParam(':address_desc',$address_desc);
			$data->bindParam(':region',$region);
			$data->bindParam(':subcounty',$subcounty);
			$data->bindParam(':parish',$parish);
			$data->bindParam(':when_added',$when_added);
			$data->bindParam(':status',$status);
			$data->bindParam(':file_name',$fileName);
			$data->bindParam(':session',$user);
			$res = $data->execute();
			if ($res) {
				echo "<div class='text-center'>Successfuly Added New Customer<div>";
			     $this->send_sms_notifications('Eco Stove',$default_phone,'Dear '.$fname.' '.$lname.', You have been registered with Eco Stove Uganda.Thank you for choosing Eco Stove Uganda.');	
				$check_on= $this->dbh->prepare('SET FOREIGN_KEY_CHECKS=1');
	           $check_on->execute();
			}else{
				echo "Not Adding";
			}
		}//End of foreign key check
	//}
		}//end Elsea



	}
	
} catch (Exception $e) {
	echo "$e";
}
	
}
public function addOrder()
{ 

	try {
	if (isset($_POST['addorder'])) {
		$customer = $_POST['customer'];
		$institution = $_POST['institution'];
		$payment_plan = $_POST['payment_plan'];
		$quantity = $_POST['quantity'];
		$item = $_POST['product_id'];
		$when_placed = date("Y-m-d");
		$pos= $_POST['pos'];
		$user= $_POST['user'];

		if(empty($customer)  && empty($payment_plan) && empty($when_placed)) {
			echo "Fill In All Fields";

        }else{
			$check_off= $this->dbh->prepare('SET FOREIGN_KEY_CHECKS=0');
	       if ($check_off->execute()) {
			$pay="INSERT INTO `order` (customer,institution, payment_plan, when_placed,pos,session) VALUES (:customer, :institution,:payment_plan, :when_placed,:pos,:session)";
			$data = $this->dbh->prepare($pay);
			$data->bindParam(':customer',$customer);
			$data->bindParam(':institution',$institution);
			$data->bindParam(':payment_plan',$payment_plan);
			$data->bindParam(':when_placed',$when_placed);
			$data->bindParam(':pos',$pos);
			$data->bindParam(':session',$user);
	
			$res = $data->execute();
			if ($res) {
			$order = $this->dbh->lastInsertId();
			$unit_cost=$this->get_record("product_cost","cost","where product = '".$item."'");
			$qury="insert into order_item (`order`, item, quantity, unit_cost) values (:order, :item, :quantity, :unit_cost)";
			$dat = $this->dbh->prepare($qury);
			$dat->bindParam(':order',$order);
			$dat->bindParam(':quantity',$quantity);
			$dat->bindParam(':unit_cost',$unit_cost);
			$dat->bindParam(':item',$item);

			$results = $dat->execute();

			if ($results) {
				echo "<div class='text-center'>Order Successfuly Generated</div>";
			}

				$check_on= $this->dbh->prepare('SET FOREIGN_KEY_CHECKS=1');
	           $check_on->execute();
			}else{
				echo "Not Adding";
			}
		}//End of foreign key check
		}



	}
	
} catch (Exception $e) {
	trigger_error("error_msg".$e->getMessage());
	echo "$e";
}
	
}

public function scheduleValidity($order,$paymentId,$payment_date,$amtPaid,$payment_delay,$validity_buffer,$transactionBal){
		
		$counterId=$this->firstInstalmentId($order);
		$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
        $gracePeriodEnd=date("Y-m-d H:i:s",strtotime("+30days",strtotime($installation_date)));
		
		$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
		
		$daily_pay_for_plan=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=6 and payment_interval='daily'");
		
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
		 
		   }   
		   elseif(($installments%$daily_pay_for_plan) > 0){
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
		}
		else{
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
		   if($payment_delay==0)
		   if($validity_buffer!=0){
		   $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($validity_buffer)));
		   return $valid_until;
		   }
		   //else	 
		  $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($payment_date)));
		  //$valid_until=$validity_buffer;
		  
		   }   
		   elseif(($installments%$daily_pay_for_plan) > 0){
		   $days=floor($installments/$daily_pay_for_plan);
		  if($payment_delay==0)
		   if($validity_buffer!=0){
		   $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($validity_buffer)));
		   return $valid_until;
		   }
		  // else	   
		  $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($payment_date)));
		 
		 //$valid_until=$days;	
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
				$valid_until=date("Y-m-d H:i:s",strtotime("+1day",strtotime($validity_buffer)));	
			}else{
				$valid_until=date("Y-m-d H:i:s",strtotime("+0day",strtotime($validity_buffer)));
			}
		}

		if($installments < $daily_pay_for_plan){//require attention		 
		   $valid_until="Less Than Minimum";		
		}		
		}
		else{
		$valid_until= "Pending Installation";		
		}
		}		
		}
		else{
		$valid_until= "Less than Upfront Required";		
		}
		
		return $valid_until; 
		}
public function instalment($order,$paymentId){		
		$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <= '".$paymentId."' and `order` = '".$order."' and status='processed'");
		$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
		if($totalAmtPaid > $upfront)		
		return $totalAmtPaid-$upfront;				 
		return 0;
		}
		
public function totalInstalments($order){		
		$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where `order` = '".$order."' and status='processed'");
		$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
		if($totalAmtPaid > $upfront)		
		return $totalAmtPaid-$upfront;				
		return 0;
		}
public function upfrontPaid($order){
	
		$arr = array();
		$counterId=$this->firstInstalmentId($order);
		               
		$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
						                 
		if($counterId > 0){
		$upfront=$upfront;		
		}
		else{		
		$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where `order` = '".$order."' and status='processed'");
		$upfront=$totalAmtPaid;	
		}		
		return $upfront; 
		}
public function paymentDelayToday($validity_buffer){
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
public function loanPeriod($order,$paymentId,$payment_date){
		
			$arr = array();
			$counterId=$this->firstInstalmentId($order);
			$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
	        $gracePeriodEnd=date("Y-m-d H:i:s",strtotime("+30days",strtotime($installation_date)));
			
			$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			
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
				if($paymentId < $counterId){
					//$period="3months";
					if($installation_date!=""){			
						if($payment_date <= $Q2endDate){		
							$period="6 Months";
						}	
					}else{
						$period="6 Months";
					}	
				}
			
				if($paymentId == $counterId){
					if($installation_date!=""){			
						if($payment_date <= $Q2endDate){		
							$period="6 Months";
						}		
					}else{
						$period="6 Months";
					}
				}
			
				if($paymentId > $counterId){
					if($installation_date!=""){
															
						if($payment_date <= $Q2endDate){		
							$period="3 Months";
						
						}else{
							$period="Above 6 Months";
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
		
		public function daysPaid($order,$paymentId,$payment_date,$amtPaid){
		$arr = array();
		$counterId=$this->firstInstalmentId($order);
		$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
          $gracePeriodEnd=date("Y-m-d H:i:s",strtotime("+30days",strtotime($installation_date)));
		
		$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
		
		$daily_pay_for_plan=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=6 and payment_interval='daily'");
		
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
		  
		   }   
		   elseif(($installments%$daily_pay_for_plan) > 0){
		   $days=floor($installments/$daily_pay_for_plan);		   
		   		 		
		   }
		}

		if($installments == $daily_pay_for_plan){
		 $days=1;	
		}

		if($installments < $daily_pay_for_plan){		 
		   $days=30;		
		}		
		}
		else{
		$days=0;				
		}
		}
		
		if($paymentId > $counterId){
			if($installation_date!=""){
		
		$installments=$amtPaid;
		//$installments=$totalAmtPaid-$upfront;		
												
		if($installments > $daily_pay_for_plan){
		   if(($installments%$daily_pay_for_plan)==0){
		   $days=$installments/$daily_pay_for_plan;		 
		   		 
		   }   
		   elseif(($installments%$daily_pay_for_plan) > 0){
		   $days=floor($installments/$daily_pay_for_plan);		   
		   		 		
		   }
		}

		if($installments == $daily_pay_for_plan){
		 $days=1;		
		}

		if($installments < $daily_pay_for_plan){		 
		   $days=0;		
		}		
		}
		else{
		$days=0;		
		}
		}		
		}
		else{
		$days=0;		
		}
		
		return $days; 
		}
	
	public function minutesPaid($order,$paymentId,$payment_date,$amtPaid){
		$arr = array();
		$mins = 60;
		$counterId=$this->firstInstalmentId($order);
		$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
         $gracePeriodEnd=date("Y-m-d H:i:s",strtotime("+30days",strtotime($installation_date)));
		
		$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
		
		$daily_pay_for_plan=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=6 and payment_interval='daily'");
		
		$Q1endDate=date("Y-m-d H:i:s",strtotime("+90days",strtotime($gracePeriodEnd))); 
		$Q2endDate=date("Y-m-d H:i:s",strtotime("+180days",strtotime($gracePeriodEnd))); 
		$Q3endDate=date("Y-m-d H:i:s",strtotime("+270days",strtotime($gracePeriodEnd))); 
		$Q4endDate=date("Y-m-d H:i:s",strtotime("+360days",strtotime($gracePeriodEnd)));
				                
		if($counterId > 0){
		//$payment_date=date("Y-m-d");
		if($paymentId < $counterId){		
		$days=0;
		$minutes = $days * $mins;		
		}
		
		if($paymentId == $counterId){
		if($installation_date!=""){
		$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
		
		$installments=$totalAmtPaid-$upfront;		
												
		if($installments > $daily_pay_for_plan){
		   if(($installments%$daily_pay_for_plan)==0){
		   $days=$installments/$daily_pay_for_plan;	
		   $minutes = $days * $mins;	 
		  
		   }   
		   elseif(($installments%$daily_pay_for_plan) > 0){
		   $days=floor($installments/$daily_pay_for_plan);	
		   $minutes = $days * $mins;	   
		   		 		
		   }
		}

		if($installments == $daily_pay_for_plan){
		 $days=1;
		 $minutes = $days * $mins;	
		}

		if($installments < $daily_pay_for_plan){		 
		   $days=30;
		   $minutes = $days * $mins;		
		}		
		}
		else{
		$days=0;
		$minutes = $days*$mins;				
		}
		}
		
		if($paymentId > $counterId){
			if($installation_date!=""){
		
		$installments=$amtPaid;
		//$installments=$totalAmtPaid-$upfront;		
												
		if($installments > $daily_pay_for_plan){
		   if(($installments%$daily_pay_for_plan)==0){
		   $days=$installments/$daily_pay_for_plan;	
		   $minutes = $days*$mins;		 
		   		 
		   }   
		   elseif(($installments%$daily_pay_for_plan) > 0){
		   $days=floor($installments/$daily_pay_for_plan);
		   $minutes = $days*$mins;			   
		   		 		
		   }
		}

		if($installments == $daily_pay_for_plan){
		 $days=1;
		 $minutes = $days * $mins;	
		}

		if($installments < $daily_pay_for_plan){		 
		   $days=0;
		   $minutes = $days * $mins;	
		}		
		}
		else{
		$days=0;
		$minutes = $days * $mins;		
		}
		}		
		}
		else{
		$days=0;
		$minutes = $days * $mins;		
		}
		
		return $minutes; 
		}
		
		
public function finalPrice($order,$paymentId,$payment_date){
	
			$arr = array();
			$counterId=$this->firstInstalmentId($order);
			$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
	        $gracePeriodEnd=date("Y-m-d H:i:s",strtotime("+30days",strtotime($installation_date)));
			
			$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			
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
				$today=$payment_date;
				if($paymentId < $counterId){
					$finalPrice=$finalPriceQ1;		
				}
			
				if($paymentId == $counterId){
					if($installation_date!=""){
												
						if($today <= $Q1endDate){
								$finalPrice=$finalPriceQ1;
						}elseif($today > $Q1endDate and $today<= $Q2endDate and $this->Q1Outstanding($order) > 0){
							$finalPrice=$finalPriceQ2;
						}elseif($today > $Q2endDate and $today <= $Q3endDate and $this->Q2Outstanding($order) > 0){
							$finalPrice=$finalPriceQ3;
						}elseif($today > $Q3endDate and $today <= $Q4endDate and $this->Q3Outstanding($order) > 0){
							$finalPrice=$finalPriceQ4;
						}						
					}else{
						$finalPrice=$finalPriceQ1;
					}
				}
			
				if($paymentId > $counterId){
					if($installation_date!=""){
														
						if($today <= $Q1endDate){
							$finalPrice=$finalPriceQ1;
						}elseif($today > $Q1endDate and $today<= $Q2endDate ){
							$finalPrice=$finalPriceQ2;
						}elseif($today > $Q2endDate and $today <= $Q3endDate){
							$finalPrice=$finalPriceQ3;
						}elseif($today > $Q3endDate and $today <= $Q4endDate){
							$finalPrice=$finalPriceQ4;
						}	
					}else{
						$finalPrice=$finalPriceQ1;
					}
				}
			}else{
				$finalPrice=$finalPriceQ1;
			}
			return $finalPrice; 
		}

			
public function installationDate($order){
	
		$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
		if($installation_date!="")
		return $installation_date;
		else
		return "Pending";		
		}

public function disburseDate($order){
	
		$disburse_date=$this->get_record("order_item","disburse_date","where `order` = '".$order."'");
		if($disburse_date!="")
		return $disburse_date;
		else
		return "Pending";		
		}
public	function getOverDueClient($valid_until){ // us on dashboard
	
		if($valid_until!=0){
		$validity = strtotime(date('Y-m-d', strtotime($valid_until)));
		$today = strtotime(date('Y-m-d'));		
		if($validity < $today)//if validity days have expired
		return 1;		
		}		
		return 0;		
		}
public function transactionBal($order,$paymentId,$payment_date,$amtPaid){
	
		$arr = array();
		$counterId=$this->firstInstalmentId($order);
		$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
         $gracePeriodEnd=date("Y-m-d H:i:s",strtotime("+30days",strtotime($installation_date)));
		
		$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
		
		$daily_pay_for_plan=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='daily'");
		
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
		 
		   }   
		   elseif(($installments%$daily_pay_for_plan) > 0){
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
		}
		else{
		$valid_until= "Pending Installation";
		$suspense=0;				
		}
		}
		
		if($paymentId > $counterId){
			if($installation_date!=""){
		
		$installments=$amtPaid;
		//$installments=$totalAmtPaid-$upfront;		
												
		if($installments > $daily_pay_for_plan){
		   if(($installments%$daily_pay_for_plan)==0){
		   $days=$installments/$daily_pay_for_plan;		 
		   $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($gracePeriodEnd)));
		   $suspense=0;
		 
		   }   
		   elseif(($installments%$daily_pay_for_plan) > 0){
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
		}
		else{
		$valid_until= "Pending Installation";
		$suspense=0;			
		}
		}		
		}
		else{
		$valid_until= "Pending Installation";
		$suspense=0;		
		}
		
		return $suspense; 
		}

public function overDueValidity($order,$paymentId,$payment_date,$amtPaid,$payment_delay,$validity_buffer,$transactionBal){
		
		$counterId=$this->firstInstalmentId($order);
		$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
        $gracePeriodEnd=date("Y-m-d H:i:s",strtotime("+30days",strtotime($installation_date)));
		
		$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
		
		$daily_pay_for_plan=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=6 and payment_interval='daily'");
		
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
		 
		   }   
		   elseif(($installments%$daily_pay_for_plan) > 0){
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
		}
		else{
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
		  $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($payment_date)));
		  //$valid_until=$validity_buffer;
		  
		   }   
		   elseif(($installments%$daily_pay_for_plan) > 0){
		   $days=floor($installments/$daily_pay_for_plan);
		  if($payment_delay==0)
		   if($validity_buffer!=0){
		   $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($validity_buffer)));
		   return $valid_until;
		   }
		  // else	   
		  $valid_until=date("Y-m-d H:i:s",strtotime("+".$days."day",strtotime($payment_date)));
		 
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
		   $valid_until=0;		
		}		
		}
		else{
		//$valid_until= "Pending Installation";
		$valid_until=0;		
		}
		}		
		}
		else{
		//$valid_until= "Pending Installation";
		$valid_until=0;		
		}		
		return $valid_until; 		
		}

		
public function paymentDelay($order,$paymentId,$validity,$validity_buffer,$payDate){
		$counterId=$this->firstInstalmentId($order);
		if($paymentId <= $counterId){
		$overDueDays ="";
		}
		else{
		$overDueDays="";
		if($validity!=0){
		$payDate = strtotime(date('Y-m-d',strtotime($payDate)));
		
		if($validity_buffer!=0){
		//$today = strtotime(date('Y-m-d'));
		//$validity_buffer=$today;//if no payment made				
		$validity_buffer = strtotime(date('Y-m-d', strtotime($validity_buffer)));
		if($validity_buffer < $payDate){ //if validity days have expired, calculate over due days		
		$secs=$payDate-$validity_buffer;
		
		$overDueDays = floor($secs / (24 * 60 * 60 ));
		}
		else
		$overDueDays=0;		 		
		}
		else			
		$overDueDays =0;		
		}
		}		
		return $overDueDays;
		}
public function totalAmtPaid($order){		
		$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where `order` = '".$order."' and status='processed'");
		
		if($totalAmtPaid > 0)				
		return $totalAmtPaid;
		return 0;
 }
 public function downPaymentMadeDate($order){
		
		$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
		$date="";
		if($upfront>0){		
		$data =$this->dbh-> prepare('select pid,amt,date from payment where `order` =:orders');
        $data->bindParam(':orders',$order);
        $data->execute();
        $sum=0;	
        while($row = $data->fetch(PDO::FETCH_ASSOC)){
        $sum+=$row['amt'];
		if($sum >= $upfront){
		$date=$row['date'];
		break;
		}
        }
			
		}
		return $date;
                }
public function startCounterOustanding($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $finalPriceQ1=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=3 and payment_interval='final'");
                $upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
                
                $totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$counterId."' and `order` = '".$order."' and status='processed'");
                $outstanding=($finalPriceQ1-($this->totalAmtPaid($order)-$upfront));
                return $outstanding;
                }
                else 
                return "Set Upfront Amount for Product";
                }
                
                public function Q1Outstanding($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $finalPriceQ1=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=3 and payment_interval='final'");
                $upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
                
                $outstanding=($finalPriceQ1-($this->totalAmtPaid($order)-$upfront));
                return $outstanding;
                }
               
                }
                
                public function Q2Outstanding($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $finalPriceQ2=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=6 and payment_interval='final'");
                $upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
                
                $outstanding=($finalPriceQ2-($this->totalAmtPaid($order)-$upfront));
                return $outstanding;
                }
               
                }
                
                 public function Q3Outstanding($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $finalPriceQ3=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=9 and payment_interval='final'" );
                $upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
                
                $outstanding=($finalPriceQ3-($this->totalAmtPaid($order)-$upfront));
                return $outstanding;
                }
               
                }
                
                 public function Q4Outstanding($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $finalPriceQ4=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=12 and payment_interval='final'"); 
                $upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
                
                $outstanding=($finalPriceQ4-($this->totalAmtPaid($order)-$upfront));
                return $outstanding;
                }
               
                }
                
                public function startCounterValidity($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
                $gracePeriodEnd=date("Y-m-d H:i:s",strtotime("+14days",strtotime($installation_date)));
                
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
                
                public function oustandingAfterStartCounter($order){
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
                
                public function validityAfterStartCounter($order){
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
                
               public function dateAfterStartCounter($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $payment_date=$this->get_record("payment","date","where pid > '".$counterId."' and `order` = '".$order."' and status='processed' limit 1");   
                                      
                return $payment_date;
                }
                }
                
               public  function amtAfterStartCounter($order){
                $counterId=$this->firstInstalmentId($order);
                if($counterId>0){
                $amtPaid=$this->get_record("payment","amt","where pid > '".$counterId."' and `order` = '".$order."' and status='processed' limit 1");                            
                return $amtPaid;
                }
                }
                
                public function dateFirstInstalment($order){		
		$date=$this->get_record("payment","date","where `order` = '".$order."' and status='processed' limit 1");
		
		return $date;
		}


 public function upfrontRequired($order){
		
		$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
		return $upfront;
}
public function firstInstalmentId($order){
		
		$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'");
		$id=0;
		if($upfront>0){		
		$query="select pid,amt from payment where `order` ='".$order."'";
		$sth = $this->dbh->prepare($query);
		$sth->execute();
		$sum=0;	
		while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
		$sum+=$result['amt'];
		if($sum >= $upfront){
		$id=$result['pid'];
		break;
		}
		   
		 }
		return $id;
		}
		return $id;
     }
public function scheduleOutstanding($order,$paymentId,$payment_date,$amtPaid){
			$arr = array();
			$counterId=$this->firstInstalmentId($order);
			$installation_date=$this->get_record("order_item","installation_date","where `order` = '".$order."'");
	        $gracePeriodEnd=date("Y-m-d H:i:s",strtotime("+30days",strtotime($installation_date)));
			
			$upfront=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_interval='upfront'"); 
			
			$daily_pay_for_plan=$this->get_record("payment_plan","cost","where product ='".$this->get_record("order_item","item","where `order` = '".$order."'")."' and payment_period=6 and payment_interval='daily'");
			
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
					$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid < '".$counterId."' and `order` = '".$order."' and status='processed'");

					//jj
					if($today <= $Q2endDate){
						$outstanding=$finalPriceQ2+$upfront-$totalAmtPaid;
					}
					//jj

					//$outstanding=$finalPriceQ1+$upfront-$totalAmtPaid;
					//$valid_until="Less Than Required Upfront";		
				}
			
				if($paymentId == $counterId){
					if($installation_date!=""){
						$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$counterId."' and `order` = '".$order."' and status='processed'");
						
						$installments=$totalAmtPaid-$upfront;		
												
						if($today <= $Q2endDate){
							$outstanding=$finalPriceQ2-$installments;
						}
					}else{
						$valid_until= "Pending Installation";		
						$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
						$outstanding=$finalPriceQ2+$upfront-$totalAmtPaid;
					}
				}

				if($paymentId > $counterId){
					
					if($installation_date!=""){
						$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
						//$installments=$totalAmtPaid;
						$installments=$totalAmtPaid-$upfront;

						if($today <= $Q2endDate){
							$outstanding=$finalPriceQ2-$installments;
						}
					}else{
						//$valid_until= "Pending Installation";
						$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
						$outstanding=$finalPriceQ2+$upfront-$totalAmtPaid;
					}
				}	
			
			}else{
				//$valid_until= "Pending Installation";
				$totalAmtPaid=$this->get_record("payment","sum(amt) as totalPaid","where pid <='".$paymentId."' and `order` = '".$order."' and status='processed'");
				$outstanding=$finalPriceQ2+$upfront-$totalAmtPaid;
			}	
			return $outstanding; 
		}
		




public function getSessionInfo()
{
	try {
        if($_SESSION['username']){
        $username = $_SESSION['username'];

        $data =$this->dbh-> prepare('select * from userregister where username=:username');
        $data->bindParam(':username',$username);
        $results= $data->execute();

        while ($row= $data->fetch(PDO::FETCH_ASSOC)) {

            $firstname = $row['firstname'];
		 	$lastname = $row['lastname'];
		 	$email = $row['email'];
		 	$password = $row['password'];
		 	$username = $row['username'];
		 	$category = $row['category'];

		 		echo "<div class='row'>";
		 		echo "<div class='col-md-5'>";
		 		echo "<img src='dist/img/user2-160x160.jpg' class='user-image image-circle image-rounded' />";
		 		echo "</div>";
		 		echo "<div class='col-md-7'>";
		 		echo "<h4>First Name : $firstname </h4>";
		 		echo "<h4>Last Name : $lastname </h4>";
		 		echo "<h4>Email : $email </h4>";
		 		echo "<h4>Username : $username </h4>";
		 		echo "<h4>Category : $category</h4>";
		 		echo "</div>";
		 		echo "</div>";


		 		echo "<div class='row'>";
		 		echo "<div class='col-md-12'>";
		 		//echo "<h4>Email : $email </h4>";
		 		echo "</div>";
		 		echo "</div>";



		 		echo "<div class='row'>";
		 		echo "<div class='col-md-6'>";
		 		//echo "<h4>Username : $username </h4>";
		 		echo "</div>";
		 		echo "<div class='col-md-6'>";
		 		//echo "<h4>Category : $category</h4>";
		 		echo "</div>";
		 		echo "</div>";


           }


          }



} catch (PDOException $e) {

     }
}
public function getSessionForm()
{
	try {
        if($_SESSION['username']){
        $username = $_SESSION['username'];

        $data =$this->dbh-> prepare('select * from userregister where username=:username');
        $data->bindParam(':username',$username);
        $results= $data->execute();

        while ($row= $data->fetch(PDO::FETCH_ASSOC)) {

            $firstname = $row['firstname'];
		 	$lastname = $row['lastname'];
		 	

		 		echo " $firstname $lastname ";
		
		 		
           }


          }



} catch (PDOException $e) {

     }
}




public function send_sms($recipients,$message)
{
//require('AfricasTalkingGateway.php');
$username   = "kluz116";
$apikey     = "448cdfda1c4065a0274f6561a794b833cc34b0e13e0e21532b7b5e831822e4b6";
// Specify the numbers that you want to send to in a comma-separated list
// Please ensure you include the country code (+254 for Kenya in this case)
//$recipients = "+254711XXXYYY,+254733YYYZZZ";
// And of course we want our recipients to know what we really do
//$message    = "I'm a lumberjack and its ok, I sleep all night and I work all day";
// Create a new instance of our awesome gateway class
$gateway    = new AfricasTalkingGateway($username, $apikey);
// Any gateway error will be captured by our custom Exception class below, 
// so wrap the call in a try-catch block
try 
{ 
  // Thats it, hit send and we'll take care of the rest. 
  $results = $gateway->sendMessage($recipients, $message);
            
  foreach($results as $result) {
    // status is either "Success" or "error message"
    //echo " Number: " .$result->number;
    //echo " Status: " .$result->status;
    //echo " MessageId: " .$result->messageId;
    //echo " Cost: "   .$result->cost."\n";
  }
}
catch ( AfricasTalkingGatewayException $e )
{
  echo "Encountered an error while sending: ".$e->getMessage();
}
}


public function add_Payments()
{
	try {
   
    $client = $_POST['client'];
    $date = $_POST['date'];
    $amt = $_POST['amount'];
    $receipt = $_POST['receipt'];
    $remarks = $_POST['remarks'];
    $user = $_POST['user'];
    $payment_type='normal';
    $fund_source= 'cash';
    $order = $this->get_record("order","id","where customer='".$client."'");
  
  if (!empty($amt) && !empty($receipt) ) {
   
       
       $data = $this->dbh->prepare("insert into payment (payment_type, customer, staff_associated, `order`, amt, fund_source,receipt_no, `date`, remarks) VALUES (:payment_type, :customer, :staff_associated, :orders, :amt, :fund_source,:receipt_no, :dates, :remarks)");
       $data->bindParam(':payment_type',$payment_type);
       $data->bindParam(':customer',$client);
       $data->bindParam(':staff_associated',$user);
       $data->bindParam(':orders',$order);
       $data->bindParam(':amt',$amt);
       $data->bindParam(':fund_source',$fund_source);
       $data->bindParam(':receipt_no',$receipt);
       $data->bindParam(':dates',$date);
       $data->bindParam(':remarks',$remarks);
       $res = $data->execute();
       if ($res) {
         echo "good";
       }else{
        echo "Not Adding Any Thing";
       }


  }else{
    echo "<div class='text-center'>Fill In All Fields</div>";
  }
} catch (PDOException $e) {
  
  echo "$e";
  
}

}

function generate($minutx, $serialx) {

    $min = $minutx;
    $sn = $serialx;
    $looptimes = mt_rand(1, 9);

    $primes = array("7", "11", "13", "17", "19");

    $codediviserAdjust = mt_rand(1, 9);
    $codedivider = $primes[mt_rand(0, 4)];
    $codedividerAdjusted = $codedivider + $codediviserAdjust;
    $snDigits = strlen(($sn . ""));

    $code = $sn . "" . $min;
    $code = $code * $codedivider;
    $code = $code . "";

//echo 'Looptimes   '.$looptimes;
//    echo '<br/>';
//    echo 'Original code   ' . $code;
//    echo '<br/>';
//    echo 'Original min   ' . $min;
//    echo '<br/>';
//    echo 'Original serial   ' . $sn;
//    echo '<br/>';
//    echo 'diviser adjust' . $codediviserAdjust;
//    echo '<br/>';
//    echo 'Original diviser' . $codedivider;
//    echo '<br/>';
//    echo $code;
//    echo '<br/>';


    for ($i = 0; $i < $looptimes; $i++) {

        $code = changa($code);
    }

//    echo $looptimes . $codedividerAdjusted . $codediviserAdjust . $snDigits . "-" . $code;

    return $looptimes . $codedividerAdjusted . $codediviserAdjust . $snDigits . "-" . $code;
}

function changa($code) {

    $final = "";

    $lastpositions = "";

    $temp = "";
    $temp = $code;

    for ($i = 0; $i < strlen($code); $i++) {

        $exchanger = "";
        for ($x = 0; $x < (strlen($temp)); $x++) {

            $exchanger = $temp[$x] . $exchanger;
        }

        $temp = $exchanger;
        if ($i == 0) {
            $lastpositions = $temp[(strlen($temp) - 1)];
        } else {

            $lastpositions = $temp[(strlen($temp) - 1)] . $lastpositions;
        }
        $temp = substr($temp, 0, -1);

        $final = "";
        $final = $temp . $lastpositions;
    }

    return $final;
}


}//End of the class.






?>
