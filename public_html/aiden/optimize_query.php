<?php
include 'connections.php';
if (isset($_GET['submit'])) {
    $shipment = $_GET['shipment'];
    $cargo = $_GET['cargo'];
    $item = $_GET['caargoitems'];

    ///cargo array
    $xx = "";
    $cargo_array = array();
    for ($j = 0; $j < strlen($cargo); $j++) {
        if ($cargo[$j] == "%" && $xx != "") {
            $cargo_array[$j] = $xx;
            $xx = "";
        } else {
            $xx = $xx . $cargo[$j];
        }
    }

    ///cargo items array
    $yy = "";
    $item_array = array();
    for ($i = 0; $i < strlen($item); $i++) {
        if ($item[$i] == "%" && $yy != "") {
            $item_array[$i] = $yy;
            $yy = "";
        } else {
            $yy = $yy . $item[$i];
        }
    }
//    foreach ($item_array as $value2) {
//        echo $value2;
//        echo "<br>";
//    }


    $connection = mysqli_connect($servername, $username, $password, $dbname) or die("Error " . mysqli_error($connection));

//    if ($connection->query("BEGIN") === TRUE) {
       echo "success";

        $position = strpos($shipment, "%");
        $shipment_query = substr($shipment, $position + 1, strlen($shipment));
        $shipment_invNo = substr($shipment, 0, $position);

        if ($connection->query($shipment_query) === TRUE) {
        echo "success1";

            $fetch_shipment_id = "SELECT shipmentId FROM shipment WHERE commercialInvNo='" . $shipment_invNo . "'";

            $query_run = $connection->query($fetch_shipment_id);
            if ($query_run) {
            echo "success2";
                $row = mysqli_fetch_array($query_run);
                $shipmentId = $row['shipmentId'];
                foreach ($cargo_array as $value) {
//                    echo $value;
                    $insert_cargo = "INSERT INTO cargo VALUES('','.$value.','.$shipmentId.')";
                    $insert_cargo_run = $connection->query($insert_cargo);
                    if ($insert_cargo_run) {
                    echo "success3";
                        $fetch_cargo_id = "SELECT cargoId FROM cargo WHERE containerNo=' . $value . '";
                        $fetch_cargo_id_run = $connection->query($fetch_cargo_id);
                        if ($fetch_cargo_id_run) {
                        echo "success4";
                            $row = mysqli_fetch_array($query_run);
                            $cargoId = $row['cargoId'];
                            foreach ($item_array as $value3) {
                                $find_position = strpos($value3, ":");
                                $received_container_no = substr($value3, 0, $find_position);
                                
                                $seperated = substr($value3, $find_position + 1, strlen($value3));
                                    echo $received_container_no;
                                    echo $value;
                                if ($received_container_no == $value) {
                                echo "success5";
                                    $insert_item = "INSERT INTO cargoItem VALUES('.$seperated.','.$cargoId.','.$shipmentId.')";
                                    $insert_item_run = $connection->query($insert_item);
                                    if ($insert_item_run) {
                                       echo "success6";
                                    } else {
                                        echo "failed to insert_item";
                                    }
                                } else {
                                    echo "received_container_no not equal cargoId";
                                }
                            }
                            //end seperate
                        }
                    } else {
                        echo "fetch_shipment_id_error";
                    }
                }
            } else {
               echo "save_shipment_error" . mysql_error($connection);
            }
        }else{
            echo "error";
        }
//    } else {
//        echo "transaction failed to start" . mysql_error($connection);
//    }
}