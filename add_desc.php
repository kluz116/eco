<?php

require 'config.inc.php';
require'connect.php';

try {
    $editor1 = $_POST['editor1'];
    $product = $_POST['product'];
  

        $pay="update product set description = '".$editor1."' where pid ='".$product."'";
        $data = $dbh->prepare($pay);
        $res = $data->execute();
         if ($res) {
        echo "<strong><div class='text-center'>Successfuly Added Item Description</div></strong>";
      
         }else{
        echo "<div class='text-center'>Not Adding</div>";
      }


         
} catch (Exception $e) {
  echo "$e";
  
}
