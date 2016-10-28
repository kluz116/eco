<?php

include 'connections.php';

if (isset($_GET['submit'])) {
    
    $unit=json_decode($_GET['query'], true);
        
        $sqlz=$unit['query'];
        
        $sql=str_replace("xzxq"," ",$sqlz);
        
        $connection = mysqli_connect($servername, $username, $password, $dbname) or die("Error " . mysqli_error($connection));

        $result = mysqli_query($connection, $sql) or die("Error in Selecting " . mysqli_error($connection));

        //create an array
        //$emparray = array();
        $data= array();;
        while ($row = mysqli_fetch_assoc($result)) {
//            echo '<br/>';
            $i=0;
//            echo '***';
            $rowx= array();
            foreach ($row as $col => $val) {
                $i++;
                $rowx["".$i.""]= "".$val."";
//                echo $i;
//                echo '###';
//                echo $val;
                
            } // end foreach
            //echo '***';
            $data[]=$rowx;
        }
        
        echo json_encode($data);
        
        mysqli_close($connection);
    }

   