
<?php
        require 'config.inc.php';
         require'connect.php';

       $id = $_GET['id'];
       
       	try{

		 $date =$dbh->prepare("delete from customer where cid =:id");
		 $date->bindParam(':id',$id);
		 $res=$date->execute();
		 if ($res) {
		 	header("Location: clients.php");
		 }

		}catch(PDOException $e){

		trigger_error('Errors :'.$e->getMessage());

	}

?>