<?php

include 'connections.php';

if (isset($_GET['submit'])) {

    $unit = json_decode($_GET['query'], true);

    $sqlz = $unit['query'];

    $sql = str_replace("xzxq", " ", $sqlz);

    $connection = mysqli_connect($servername, $username, $password, $dbname) or die("Error " . mysqli_error($connection));

    if ($connection->query("SET @@session.time_zone='+03:00'") === TRUE) {
       
    }
    
    if ($connection->query($sql) === TRUE) {
        echo "1";
    } else {
        echo mysqli_error($connection);
    }

    mysqli_close($connection);
}
   