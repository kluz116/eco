<?php
require 'config.inc.php';
require'connect.php';
$api= new Config();

$num_rows=0;
$join="";
$sql="";


$paying_customers="select o.customer as customer from `order` o join order_item ot on o.id=ot.order join payment p on ot.order=p.order where ot.order is not null and p.order is not null group by p.order";
$sth = $dbh->prepare($paying_customers);
$sth->execute();
$customers=array();
$x=0;
while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
$customers[$x]=$result["customer"];
$x++;
}
$overDue=array();
$notOverDue=array();
$fullyPaid=array();

foreach($customers as $customer){
$order_id = $api->get_record("order", "id", "where customer = '".$customer."'");
$plan = $api->get_record("order", "payment_plan", "where id = '".$order_id."'");
if($plan=="hire"){
$query_h = "select * from payment where customer='".$customer."' and status='processed'";
$validity_buffer=0;$transactionBal=0;
$th = $dbh->prepare($query_h);
$th->execute();
while ($row = $th->fetch(PDO::FETCH_ASSOC)) {
$payment_delay=$api->paymentDelay($row['order'],$row['pid'],$api->overDueValidity($row['order'],$row['pid'],$row['date'],$row['amt'],"",$validity_buffer,$transactionBal),$validity_buffer,$row['date']);
$validity=$api->overDueValidity($row['order'],$row['pid'],$row['date'],$row['amt'],$payment_delay,$validity_buffer,$transactionBal);
$outstanding=$api->scheduleOutstanding($row['order'],$row['pid'],$row['date'],$row['amt']);
$validity_buffer=$api->overDueValidity($row['order'],$row['pid'],$row['date'],$row['amt'],$payment_delay,$validity_buffer,$transactionBal);$transactionBal=$api->transactionBal($row['order'],$row['pid'],$row['date'],$row['amt']);
}
if(isset($validity)){
if($api->getOverDueClient($validity)==1){
array_push($overDue,$customer);
}
elseif(($api->getOverDueClient($validity)==0) && $outstanding <=0){
array_push($fullyPaid,$customer);
}
else{
array_push($notOverDue,$customer);
}
}
}else {
$query_hh ="select sum(amt) as total from payment where customer='".$customer."' and status='processed'";
$thh = $dbh->prepare($query_hh);
$thh->execute();
while ($total = $thh->fetch(PDO::FETCH_ASSOC));
$total_paid=$total['total'];
$item_cost=$api->get_record("product_cost", "cost", "where product = '".$api->get_record("order_item", "item", "where `order` = '".$order_id."'")."'");
if($total_paid >= $item_cost){
array_push($fullyPaid,$customer);
}
else 
array_push($notOverDue,$customer);
}
}

if($fullyPaid){

$sql==""?$sql.="where c.cid in (".implode(",", $fullyPaid).")" : $sql.=" and c.cid in (".implode(",", $fullyPaid).")";

$query_clients="select c.* from customer c ".$sql." order by c.cid desc";

try{

    
   $date =$dbh->prepare($query_clients);
   $date->execute();
   while ($row= $date->fetch(PDO::FETCH_ASSOC)) {

   	$cid = $row['cid'];
   	$firstname = $row['fname']; 
   	$lastname = $row['lname']; 
   	$gender = $row['gender'];
   	$default_phone = $row['default_phone'];
   	$next_of_keen = $row['next_ov_keen'];
   	$next_ov_keen_phone =$row['nok_phone'];
   	$district= $api->get_record("region","region_name","where id = '".$row['region']."'"); 
    $subcounty= $api->get_record("subcounty","subcounty_name","where id = '".$row['subcounty']."'"); 
    $parish= $api->get_record("parish","parish_name","where id = '".$row['parish']."'"); 
    $village=$row['address_desc'];

    $dat =$dbh-> prepare('select * from customer_status where cid=:cid');
	$dat->bindParam(':cid',$cid);
	$dat->execute();
    $row = $dat->fetch(PDO::FETCH_ASSOC);
    if(!$row){
    		$pay="insert into customer_status (cid,fname, lname, gender, default_phone,next_ov_keen,nok_phone,address_desc, region,subcounty,parish,outstanding) VALUES (:cid,:fname, :lname, :gender, :default_phone,:next_ov_keen,:nok_phone,:address_desc,:region,:subcounty,:parish,:outstanding)";
			$data = $dbh->prepare($pay);
			$data->bindParam(':cid',$cid);
			$data->bindParam(':fname',$firstname);
			$data->bindParam(':lname',$lastname);
			$data->bindParam(':gender',$gender);
			$data->bindParam(':default_phone',$default_phone);
			$data->bindParam(':next_ov_keen',$next_of_keen);
			$data->bindParam(':nok_phone',$next_ov_keen_phone);
			$data->bindParam(':address_desc',$village);
			$data->bindParam(':region',$district);
			$data->bindParam(':subcounty',$subcounty);
			$data->bindParam(':parish',$parish);
			$data->bindParam(':outstanding',$outstanding);
			$res = $data->execute();

			if ($res) {
				echo "Saving clients With In Plan :".$lastname."\n";
			}
		}
    $dat =$dbh-> prepare('select * from customer_status where cid=:cid');
	$dat->bindParam(':cid',$cid);
	$dat->execute();
    $row = $dat->fetch(PDO::FETCH_ASSOC);
    if($row){
    $query= "update customer_status set status='paid_up' where cid='".$cid."'";
	$sth = $dbh->prepare($query);
	$res= $sth->execute();
	if ($res) {
		echo "client ".$lastname." Now Is Paid Up \n";
	}else{
		echo "Failed To Update client ".$lastname." \n";
	}


	}else{
		echo "Doest Not Exist:".$lastname."\n";
	}

   }//end of while loop


}catch(PDOException $e){


}
}

