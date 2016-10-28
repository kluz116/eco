<?php

include 'connections.php';

    $sqlz =$_POST['query'];
    
    $sql = str_replace("xzxq", " ", $sqlz);

    $connection = mysqli_connect($servername, $username, $password, $dbname) or die("Error " . mysqli_error($connection));

    if ($connection->query("SET @@session.time_zone='+03:00'") === TRUE) {
       
    }
    if ($connection->query($sql) === TRUE) {
        echo "1";
    } else {
        echo "0";
    }

    mysqli_close($connection);