<?php

include 'connections.php';

if (isset($_GET['submit'])) {

    $unit = json_decode($_GET['query'], true);

    $sqlz = $unit['query'];

    $sql = str_replace("xzxq", " ", $sqlz);

    $connection = mysqli_connect($servername, $username, $password, $dbname) or die("Error " . mysqli_error($connection));

    if ($connection->query("BEGIN") === TRUE) {
    } else {
    }
    
    $query1 = "";
    for ($i = 0; $i < strlen($sql); $i++) {

        if ($sql[$i] != ';') {
            $query1 = $query1 . $sql[$i];
        } else {

            if ($connection->query($query1) === TRUE) {
                
            } else {
              
            }

            $query1 = "";
        }
    }
    
    if ($connection->query("COMMIT") === TRUE) {
        echo "1";
    } else {
        echo "0";
    }

    mysqli_close($connection);
}
