<?php 
require 'config.inc.php';
require'connect.php';
$api= new Config();


try{

	$query = "SELECT SUM(revenue) as 'Winnie Bakowa' FROM sales WHERE YEAR(date) = '2016' GROUP BY MONTH(date)";
	//$query = "SELECT MONTH(date) AS month, COUNT(date)  AS totalSales FROM sales WHERE date >= NOW() - INTERVAL 1 YEAR GROUP BY MONTH(date)";
	$sth = $dbh->prepare($query);
	$sth->execute();
	while ($result = $sth->fetchAll(PDO::FETCH_ASSOC)) {
	echo json_encode($result);
      
    }

    
	}catch(PDOException $e){


	}