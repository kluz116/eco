<?php

require 'config.inc.php';
require'connect.php';

$api = new Config();

try {
   
    $client = $_POST['client'];
    $date = $_POST['date'];
    $amt = $_POST['amount'];
    $remarks = $_POST['remarks'];
    $user = $_POST['user'];
    $payment_type='normal';
    $fund_source= 'cash';
    $status = 'processed';
    $order = $api->get_record("order","id","where customer='".$client."'");
  
  if (!empty($amt) ) {
   
       
       $data = $dbh->prepare("insert into payment (payment_type, customer, staff_associated, `order`, amt, fund_source,status, `date`, remarks) VALUES (:payment_type, :customer, :staff_associated, :orders, :amt, :fund_source,:status, :dates, :remarks)");
       $data->bindParam(':payment_type',$payment_type);
       $data->bindParam(':customer',$client);
       $data->bindParam(':staff_associated',$user);
       $data->bindParam(':orders',$order);
       $data->bindParam(':amt',$amt);
       $data->bindParam(':fund_source',$fund_source);
       $data->bindParam(':status',$status);
       $data->bindParam(':dates',$date);
       $data->bindParam(':remarks',$remarks);
       $res = $data->execute();
       if ($res) {
        $pid= $dbh->lastInsertId();
        // Update the order status to deposit receieved
       $order_status = $api->get_record("order","order_status","where id = '".$order."'");
       if($order_status=="pending_evaluation"){
       
       if($api->get_record("payment","count(*)","where `order` = '".$order."' and status = 'processed'") == 1){

        if ($api->totalAmtPaid($order) >= $api->upfrontRequired($order)) {
        $down_payment="update `order` set order_status = 'approved' where id = '".$order."'";
        $th = $dbh->prepare($down_payment);
        $down_pay=$th->execute();
        }
       
       }
      
       }//End of Updating the status


        echo "<div class='text-center'>Successfuly Made Cash Payment</div>";
       }else{
      echo "<div class='text-center'>Not Adding Anything</div>";
       }


  }else{
    echo "<div class='text-center'>Fill In All Fields</div>";
  }
} catch (PDOException $e) {
  
  echo "$e";
  
}