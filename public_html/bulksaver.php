<?php

include 'connections.php';

    $sql =$_POST['query'];

    $connection = mysqli_connect($servername, $username, $password, $dbname) or die("Error " . mysqli_error($connection));

    if ($connection->query($sql) === TRUE) {
        echo "1";
    } else {
        echo "0";
    }

    mysqli_close($connection);