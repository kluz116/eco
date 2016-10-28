<?php
	try {
	  $dbh = new PDO("mysql:host=localhost;dbname=ecostove_eco-pay-go", 'root','jesus');
	  if ($dbh) {
	  	//echo "connected";
	  }
	}
	catch(PDOException $e) {
	    echo $e;
	}