<?php

require 'config.inc.php';
require'connect.php';
$api= new Config();


if(!isset($_SESSION['username']) && empty($_SESSION['username'])){

  header("Location:login");
  exit();
  }

    $name="";
    if($_SESSION['username']){

      $username = $_SESSION['username'];


    }     


  try{
    if (isset($_GET['id'])) {
     $data = $dbh->prepare("select * from customer join `order` on customer.cid = order.customer where customer.cid ='".$_GET['id']."'");
     $data->execute();
     while ($roww= $data->fetch(PDO::FETCH_ASSOC)){
  $order_id= $roww['id'];

    $plan = $api->get_record("order", "payment_plan", "where id = '".$order_id."'");

$query_maxPaydate = "select max(pid) as maxpid,max(date) as maxdate from payment where customer='".$_GET['id']."' and `order`='".$order_id."'";
$sth = $dbh->prepare($query_maxPaydate);
$sth->execute();
$row = $sth->fetch(PDO::FETCH_ASSOC);
$maxDateId=$row['maxpid'];
$maxDate=$row['maxdate'];
$today=date('Y-m-d');

$query_hh = "select * from payment where customer='".$_GET['id']."' and status='processed' order by pid asc";
$thh = $dbh->prepare($query_hh);
$thh->execute();
$totall = $thh->fetchAll();

if(count($totall)===0)
$GenStatus="<font color='gray'>Prospect</font>";
elseif($plan=="hire"){ //begin plan

$query_h = "select * from payment where customer='".$_GET['id']."' and status='processed' order by pid asc";
$validity_buffer=0;$transactionBal=0;
$th = $dbh->prepare($query_h);
$th->execute();
while ($row = $th->fetch(PDO::FETCH_ASSOC)) {
$payment_delay=$api->paymentDelay($row['order'],$row['pid'],$api->overDueValidity($row['order'],$row['pid'],$row['date'],$row['amt'],"",$validity_buffer,$transactionBal),$validity_buffer,$row['date']);
$validity=$api->overDueValidity($row['order'],$row['pid'],$row['date'],$row['amt'],$payment_delay,$validity_buffer,$transactionBal);
$outstanding=$api->scheduleOutstanding($row['order'],$row['pid'],$row['date'],$row['amt']);
$validity_buffer=$api->overDueValidity($row['order'],$row['pid'],$row['date'],$row['amt'],$payment_delay,$validity_buffer,$transactionBal);$transactionBal=$api->transactionBal($row['order'],$row['pid'],$row['date'],$row['amt']);
}
if(isset($validity)){
if($api->getOverDueClient($validity)==1 && $outstanding >0){
$days=$api->paymentDelayToday($validity);
$GenStatus="<font color='red'>Due by&nbsp;".$days."&nbsp;days</font>";
}
elseif(($api->getOverDueClient($validity)==0) && $outstanding <=0){
$GenStatus="<font color='green'>Cleared Up</font>";
}elseif(($api->getOverDueClient($validity)==1) && $outstanding <=0){
  $GenStatus="<font color='green'>Cleared Up</font>";
  }
else{
$GenStatus="<font color='green'>With in</font>";
}
}
}
else{
$query = "select sum(amt) as total from payment where customer='".$_GET['id']."' and status='processed'";
$thh = $dbh->prepare($query);
$thh->execute();
while ($total = $thh->fetch(PDO::FETCH_ASSOC));
$total_paid=$total['total'];
$item_cost=$api->get_record("product_cost", "cost", "where product = '".$api->get_record("order_item", "item", "where `order` = '".$order_id."'")."'");
if($total_paid >= $item_cost){
$GenStatus="<font color='green'>Paid Up</font>";
}
else 
$GenStatus="<font color='green'>With in Plan</font>";
}


?>

<!DOCTYPE html>

<html>
  <head>
    <meta charset="UTF-8">
    <title>Eco Pre Pay</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.4 -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />    
    <!-- FontAwesome 4.3.0 -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons 2.0.0 -->
    <link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />    
    <!-- Theme style -->
    <link href="dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="font-awesome/css/font-awesome.css">
    <!-- DATA TABLES -->
    <link href="plugins/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins. Choose a skin from the css/skins 
         folder instead of downloading all of them to reduce the load. -->
    <link href="dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
    <!-- iCheck -->
    <link href="plugins/iCheck/flat/blue.css" rel="stylesheet" type="text/css" />
   
    <link href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />
     <link rel="shortcut icon" href="images/eco.ico"> 
     

  </head>
  <body class="skin-green fixed sidebar-mini">
    <div class="wrapper">
      
         <?php require 'header.php';?>

      <!-- Left side column. contains the logo and sidebar -->
      <?php require 'side_bar.php';?>

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Customer Information
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Customer Information</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="box">
                <div class="box-header">
                  <h3 class="box-title"><?php echo strtoupper($roww['fname']); ?>&nbsp;<?php echo strtoupper($roww['lname']);?></h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                <div class="row">
                  <div class="col-md-12">
                   <table class="table">
                 
                     <td>
                       <p><strong>Client ID :</strong> C00<?php echo $roww['cid']; ?></p>
                       <p><strong>Phone :</strong> <?php echo $roww['default_phone']; ?></p>
                       <p><strong>Alternate:</strong><span>&nbsp;<?php echo isset($roww['alt_phone']) ? $roww['alt_phone'] : "--" ; ?></span></p>
                       <p><strong>District:</strong><span>&nbsp;<?php echo isset($roww['region']) ? $api->get_record("region","region_name","where id = '".$roww['region']."'") : "--" ; ?></span></p>
                       <p><strong>Sub-County:</strong><span>&nbsp;<?php echo isset($roww['subcounty']) ? $api->get_record("subcounty","subcounty_name","where id = '".$roww['subcounty']."'") : "--" ; ?></span></p>
                       <p><strong>Parish:</strong><span>&nbsp;<?php echo isset($roww['parish']) ? $api->get_record("parish","parish_name","where id = '".$roww['parish']."'") : "--" ; ?></span></p>                                      
                       <p><strong>Next of Kin:</strong><span>&nbsp;<?php echo isset($roww['next_ov_keen']) ? $roww['next_ov_keen'] : "--" ; ?></span></p>
                       <p><strong>Next of Kin Phone No:</strong><span>&nbsp;<?php echo isset($roww['nok_phone']) ? $roww['nok_phone'] : "--" ; ?></span></p>

                     </td>
                     <td>
                       <p><strong>Order ID:</strong><span>&nbsp;D00<?php echo isset($roww['id']) ? $roww['id'] : "--"; ?></span></p>
                        <p><strong>Plan:</strong><span>&nbsp;<?php echo isset($roww['payment_plan']) ? $roww['payment_plan'] : "--" ; ?></span></p>
                       <p><strong>Status:</strong><span>&nbsp;<?php echo $GenStatus; ?></span></p> 
                        <p><strong>Product:</strong><span>&nbsp;<?php echo !empty($order_id) ? $api->get_record("product", "product_code", "where pid = '".$api->get_record("order_item", "item", "where `order` = '".$order_id."'")."'") : "--"; ?></span></p>                     
                      <p><strong>Delivered:</strong><span>&nbsp;<?php echo $api->disburseDate($order_id); ?></span></p> 
                       <p><strong>Initiated:</strong><span>&nbsp;<?php echo $api->installationDate($order_id); ?></span></p>
 
 
                     </td>
                          <td>
                  <img src="dist/img/avatar2.png" class="" alt="" />
                     </td>
                   </table>
      
                   <?php if($plan=="hire") { ?>
                                    <div class="table-responsive">
                                        <table  class="table table-striped table-bordered">
                                          
                                             <thead>
                                                <tr>
                                                                                       
                                                    <th>Payment Date</th>
                                                    <th>Amount</th>
                                                    <th>Installments</th>
                                                    <th>Active Minutes</th>
                                                    <th>(Days)</th>
                                                     <th>Valid Until</th>             
                                                    <th>Balance</th>
                                                                                                                                                                                                                              
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                   
                                            <?php $x=1;$amount=0;$validity_buffer=0;$transactionBal=0;
                                              //;$payment_delay=""
                                          $qry = "select * from payment where customer='".$roww['cid']."' and status='processed' order by pid asc";
                                          $t = $dbh->prepare($qry);
                                          $t->execute();
                                          while ($row_h = $t->fetch(PDO::FETCH_ASSOC)){
                                          $payment_delay=$api->paymentDelay($row_h['order'],$row_h['pid'],$api->overDueValidity($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt'],"",$validity_buffer,$transactionBal),$validity_buffer,$row_h['date']);?> 
                                       <tr>
                                                             
                                         <td><?php echo $row_h['date']; ?></td>
                                         <td><?php echo number_format(abs($row_h['amt'])); ?></td>
                                         <td><?php echo number_format(abs($api->instalment($row_h['order'],$row_h['pid']))); ?></td>                            
                                         <td><?php echo $api->minutesPaid($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt']); ?></td>
                                         <td><?php echo $api->daysPaid($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt']); ?></td> 
                                         <td><?php echo $api->scheduleValidity($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt'],$payment_delay,$validity_buffer,$transactionBal); ?></td>                                                                                  
                                 
                                      <td><?php echo number_format($api->scheduleOutstanding($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt']),0,".","'"); ?></td>  
                                       </tr>
                                         <?php
                                         $x++;$amount+=$row_h['amt'];$validity_buffer=$api->overDueValidity($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt'],$payment_delay,$validity_buffer,$transactionBal);$transactionBal=$api->transactionBal($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt']);//$payment_delay=$api->paymentDelay($row_h['order'],$row_h['pid'],$api->overDueValidity($row_h['order'],$row_h['pid'],$row_h['date'],$row_h['amt'],$payment_delay,$validity_buffer,$transactionBal),$validity_buffer,$row_h['date']);
                                         } ?>
                                         </tr>
                                         
                                          <tr><td><b>Total</b></td><td><font color='red'><strong><?php echo number_format(abs($amount)); ?></strong></font><strong>  UGX</strong></td><td  colspan='6'></td>
                            </tr> 
                                            </tbody>
                                        </table>
                                    </div>
                                  <?php }
                                  //else { }//if plan is full
                                  ?>
                  </div>
                </div>
                <div class="row"></div>
                <div class="row"></div>
                            <?php
                          }
                        }
                      }catch(PDOException $e){

                       trigger_error('Errors :'.$e->getMessage());

                     }


                         ?>
  
                </div>
          </div>
        </section>     
    </div><!-- ./wrapper -->
     <?php
        require 'footer.php';
        ?> 





    <!-- jQuery 2.1.4 -->
    <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>


    <!-- jQuery UI 1.11.2 -->
    <script src="http://code.jquery.com/ui/1.11.2/jquery-ui.min.js" type="text/javascript"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
       <script src="plugins/datatables/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="plugins/datatables/dataTables.bootstrap.min.js" type="text/javascript"></script>
    <!-- Sl   
 <!-- Bootstrap 3.3.2 JS -->
    <script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js" type="text/javascript"></script>
    <script src="plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
    <!-- datepicker -->
    <script src="plugins/datepicker/bootstrap-datepicker.js" type="text/javascript"></script>
    <!-- Bootstrap WYSIHTML5 -->
    <script src="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>
    <!-- Slimscroll -->
    <script src="plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <!-- FastClick -->
    <script src='plugins/fastclick/fastclick.min.js'></script>
    <!-- AdminLTE App -->
    <script src="dist/js/app.min.js" type="text/javascript"></script>   
    <script type="text/javascript">
      $(function () {
        $("#example1").dataTable();
        $('#example2').dataTable({
          "bPaginate": true,
          "bLengthChange": false,
          "bFilter": false,
          "bSort": true,
          "bInfo": true,
          "bAutoWidth": false
        });
      });
    </script>  
    
    
  </body>
</html>