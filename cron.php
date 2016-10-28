<?php

require 'config.inc.php';
require 'connect.php'; 
$api = new Config();

//while (true) {
  
 $api->trigger_SMS();
 //print("Sleeping for 60 seconds\n");
 //sleep(60);


//}


?>