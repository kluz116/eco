<?php 
require 'config.inc.php';
require'connect.php';
$api= new Config();
 try{

  $result="SELECT ot.order as orderId from order_item ot join payment p on ot.order=p.order where p.order is not null group by p.order";
  $s = $dbh->prepare($result);
  $s->execute(); 
   $downPaymentReceived=array();
  $pendingDownPayment=array();
  $upfrontId=array();
  while ($data = $s->fetch(PDO::FETCH_ASSOC)) {
    if($api->totalAmtPaid($data['orderId']) >= $api->upfrontRequired($data['orderId'])){
          //array_push($downPaymentReceived,$data['orderId']);
      }else
      array_push($pendingDownPayment,$data['orderId']);


   }

$query_orders = "select * from `order` where id in (".implode(",", $pendingDownPayment).") order by when_placed desc";
$sth = $dbh->prepare($query_orders);
$sth->execute();
while ($row= $sth->fetch(PDO::FETCH_ASSOC)) {

$customer_id = $row['customer'];
$order = $row['id'];
echo $barcode = $api->get_record("product_inventory","serial_no", "where id = '".$api->get_record("order_item","item_disbursed", "where `order` = '".$row['id']."'")."'");
$kit_type = $api->get_record("product","product_code", "where pid = '".$api->get_record("order_item","item", "where `order` = '".$row['id']."'")."'");
$name = $api->get_record("customer", "concat(fname,' ',lname)", "where cid = '".$row['customer']."'");
echo $phone = $api->get_record("customer", "default_phone", "where cid = '".$row['customer']."'");
$status = $row['order_status'];
$pos=$api->get_record("pos","name", "where posid = '".$row['pos']."'");
$revenue = number_format($api->order_total_cost($row['id']));
$sales_type = $row['payment_plan'];
$when_placed=$api->downPaymentMadeDate($row['id']);

 $data =$dbh-> prepare('select * from not_sales where customer_id=:customer');
      $data->bindParam(':customer',$customer_id);
      $data->execute();

  $r = $data->fetch(PDO::FETCH_ASSOC);
  if(!$r){
   
  $dat = $dbh->prepare("INSERT INTO not_sales (customer_id, order_id, kit_type,name,phone,status, pos, revenue,sales_type, barcode, date) VALUES(:customer_id, :order_id, :kit_type,:name, :phone, :status, :pos,:revenue, :sales_type, :barcode, :date)");
  $dat->bindParam(':customer_id',$customer_id);
  $dat->bindParam(':order_id',$order);
  $dat->bindParam(':kit_type',$kit_type);
  $dat->bindParam(':name',$name);
  $dat->bindParam(':phone',$phone);
  $dat->bindParam(':status',$status);
  $dat->bindParam(':pos',$pos);
  $dat->bindParam(':revenue',$revenue);
  $dat->bindParam(':sales_type',$sales_type);
  $dat->bindParam(':barcode',$barcode);
  $dat->bindParam(':date',$when_placed);
  $res = $dat->execute();
      if ($res) {
          echo "Saving Sales For Client ".$name."\n";
          }else{
          echo "Not Adding Anything\n";
         }     
  }

}

    }catch(Exception $e){
     echo "$e";

}


?>