
<?php
        require 'config.inc.php';
         require'connect.php';

       $id = $_GET['id'];
       
       	try{

		 $date =$dbh->prepare("delete from `order` where id =:id");
		 $date->bindParam(':id',$id);
		 $res=$date->execute();
		 if ($res) {
		 $dat =$dbh->prepare("delete from order_item where `order` =:id");
		 $dat->bindParam(':id',$id);
		 $re=$dat->execute();
		  header("Location: orders.php");
		 }

		}catch(PDOException $e){

		trigger_error('Errors :'.$e->getMessage());

	}

?>