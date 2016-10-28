<?php

require 'config.inc.php';
require'connect.php';

try {
    $serial_no = $_POST['serial_no'];
    $item_number = $_POST['item_number'];
    $product = $_POST['product'];
    $date = date("Y-m-d h:i:s");
  if (!empty($serial_no)) {
      $check_off= $dbh->prepare('SET FOREIGN_KEY_CHECKS=0');
         if ($check_off->execute()) {
            $pay="insert into product_inventory (product_type, serial_no,item_no, when_added) VALUES (:product_type, :serial_no,:item_number,:when_added)";
           $data = $dbh->prepare($pay);
           $data->bindParam(':product_type',$product);
          $data->bindParam(':serial_no',$serial_no);
          $data->bindParam(':item_number',$item_number);
           $data->bindParam(':when_added',$date);
           $res = $data->execute();
         if ($res) {
        echo "<div class='text-center'>Successfuly Added New Product</div>";
        $check_on= $dbh->prepare('SET FOREIGN_KEY_CHECKS=1');
        $check_on->execute();
         }else{
        echo "<div class='text-center'>Not Adding</div>";
      }


         }
  }else{
    echo "<div class='text-center'>Fill In All Fields</div>";
  }
} catch (Exception $e) {
  trigger_error("error_msg".$e->getMessage());
  
}
