<?php
require 'config.inc.php';
require 'connect.php';
		$serial_no = $_POST['serial_no'];
		$cid = $_POST['id'];

		$query= "update product_inventory set serial_no='".$serial_no."' where id='".$cid."'";
		$sth = $dbh->prepare($query);
	    $res= $sth->execute();
	    if ($res) {
	    	echo "<strong>Successfuly Updated Barcode ".$serial_no."</strong>";
	    }


?>