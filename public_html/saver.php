<?php

include 'connections.php';

if (isset($_GET['submit'])) {

    $unit = json_decode($_GET['query'], true);

    $sql = $unit['query'];

    $connection = mysqli_connect($servername, $username, $password, $dbname) or die("Error " . mysqli_error($connection));

    if ($connection->query($sql) === TRUE) {
        echo "1";
    } else {
        echo "0";
    }

    mysqli_close($connection);
}
   