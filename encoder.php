<?php

//make function--------
$serial_no = $_POST['serialNo'];
$minutes = $_POST['minutes'];

echo generate($minutes,$serial_no);

function generate($minutx, $serialx) {

    $min = $minutx;
    $sn = $serialx;
    $looptimes = mt_rand(1, 9);

    $primes = array("7", "11", "13", "17", "19");

    $codediviserAdjust = mt_rand(1, 9);
    $codedivider = $primes[mt_rand(0, 4)];
    $codedividerAdjusted = $codedivider + $codediviserAdjust;
    $snDigits = strlen(($sn . ""));

    $code = $sn . "" . $min;
    $code = $code * $codedivider;
    $code = $code . "";

//echo 'Looptimes   '.$looptimes;
//    echo '<br/>';
//    echo 'Original code   ' . $code;
//    echo '<br/>';
//    echo 'Original min   ' . $min;
//    echo '<br/>';
//    echo 'Original serial   ' . $sn;
//    echo '<br/>';
//    echo 'diviser adjust' . $codediviserAdjust;
//    echo '<br/>';
//    echo 'Original diviser' . $codedivider;
//    echo '<br/>';
//    echo $code;
//    echo '<br/>';


    for ($i = 0; $i < $looptimes; $i++) {

        $code = changa($code);
    }

//    echo $looptimes . $codedividerAdjusted . $codediviserAdjust . $snDigits . "-" . $code;

    return $looptimes . $codedividerAdjusted . $codediviserAdjust . $snDigits . "-" . $code;
}

function changa($code) {

    $final = "";

    $lastpositions = "";

    $temp = "";
    $temp = $code;

    for ($i = 0; $i < strlen($code); $i++) {

        $exchanger = "";
        for ($x = 0; $x < (strlen($temp)); $x++) {

            $exchanger = $temp[$x] . $exchanger;
        }

        $temp = $exchanger;
        if ($i == 0) {
            $lastpositions = $temp[(strlen($temp) - 1)];
        } else {

            $lastpositions = $temp[(strlen($temp) - 1)] . $lastpositions;
        }
        $temp = substr($temp, 0, -1);

        $final = "";
        $final = $temp . $lastpositions;
    }

    return $final;
}