<?php

require 'config.inc.php';
require'connect.php';
$api= new Config();

if(!isset($_SESSION['username']) && empty($_SESSION['username'])){

  header("Location:login.php");
  exit();
  }

    $name="";
    if($_SESSION['username']){

      $username = $_SESSION['username'];


    }     



  $result="SELECT ot.order as orderId from order_item ot join payment p on ot.order=p.order where p.order is not null group by p.order";
  $s = $dbh->prepare($result);
  $s->execute(); 
   $downPaymentReceived=array();
  $pendingDownPayment=array();
  $upfrontId=array();
  while ($data = $s->fetch(PDO::FETCH_ASSOC)) {
    if($api->totalAmtPaid($data['orderId']) >= $api->upfrontRequired($data['orderId'])){
          array_push($downPaymentReceived,$data['orderId']);
      }

   }  
foreach($downPaymentReceived as $order){
array_push($upfrontId,$api->firstInstalmentId($order));
}  


$paying_customers="select customer from `order` where id in (".implode(",", $downPaymentReceived).")";
$sth = $dbh->prepare($paying_customers);
$sth->execute();
$customers=array();
$x=0;
while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
$customers[$x]=$result["customer"];
$x++;
}
$overDue=0;
$notOverDue=0;
$overDueAmt=0;
$fullyPaid=0;
$full=0;
foreach($customers as $customer){
$order_id = $api->get_record("order", "id", "where customer = '".$customer."'");
$plan = $api->get_record("order", "payment_plan", "where id = '".$order_id."'");
if($plan=="hire"){
$query_h = "select * from payment where customer='".$customer."' and status='processed'";
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
if($api->getOverDueClient($validity)==1)

$overDue++;

elseif(($api->getOverDueClient($validity)==0) && $outstanding <=0)
$fullyPaid++;
else
$notOverDue++;
}
}else {
$query="select sum(amt) as total from payment where customer='".$customer."' and status='processed'";
$thh = $dbh->prepare($query);
$thh->execute();
while ($total = $thh->fetch(PDO::FETCH_ASSOC));
$total_paid=$total['total'];
$item_cost=$api->get_record("product_cost", "cost", "where product = '".$api->get_record("order_item", "item", "where `order` = '".$order_id."'")."'");
if($total_paid >= $item_cost){
$full++;
}
else
$notOverDue++;
}
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
    <!-- AdminLTE Skins. Choose a skin from the css/skins 
         folder instead of downloading all of them to reduce the load. -->
    <link href="dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
    <!-- iCheck -->
    <link href="plugins/iCheck/flat/blue.css" rel="stylesheet" type="text/css" />
   
    <link href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />

    <link rel="shortcut icon" href="images/eco.ico"> 

  </head>
  <body class="skin-green  fixed sidebar-mini">
    <div class="wrapper">
      
         <?php require 'header.php';?>

      <!-- Left side column. contains the logo and sidebar -->
      <?php require 'side_bar.php';?>

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Dashboard
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="box">
                <div class="box-header">
                  <h3 class="box-title"></h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    
            <!-- Small boxes (Stat box) -->
          <div class="row">
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-blue">
                <div class="inner">
                  <h3> <?php $api->GetNumberOfClient();?></h3>
                  <p>Clients</p>
                </div>
                <div class="icon">
                  <i class="ion ion-person"></i>
                </div>
                <a href="clients.php" class="small-box-footer">
                  Clients <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            </div><!-- ./col -->
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-green">
                <div class="inner">
                  <h3><?php $api->GetNumberOfOrders();?></h3>
                  <p>Orders</p>
                </div>
                <div class="icon">
                  <i class="fa fa-shopping-cart"></i>
                </div>
                <a href="orders.php" class="small-box-footer">
                  Orders <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            </div><!-- ./col -->
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-yellow">
                <div class="inner">
                  <h3><?php echo count($downPaymentReceived);?></h3>
                  <p>Sales </p>
                </div>
                <div class="icon">
                  <i class="ion ion-stats-bars"></i>
                </div>
                <a href="#" class="small-box-footer">
                  Sales <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            </div><!-- ./col -->

            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-red">
                <div class="inner">
                  <h3>
                     <?php
                          $total=0;
                          $result="select pl.cost as totalAmount from payment_plan pl join order_item t on pl.product=t.item where pl.payment_interval='full_payment' and t.order in (".implode(",", $downPaymentReceived).")";
                          $sth = $dbh->prepare($result);
                          $sth->execute();
                          while ($data = $sth->fetch(PDO::FETCH_ASSOC)) { 
                
                          $total+=$data['totalAmount'];
                          }
                          echo number_format(abs($total),0,".","'");
                        ?>

                  </h3>
                  <p>Value</p>
                </div>
                <div class="icon">
                  <i class="ion ion-cash"></i>
                </div>
                <a href="#" class="small-box-footer">
                  More info <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            </div><!-- ./col -->
          </div><!-- /.row -->
            <!-- Small boxes (Stat box) -->
          <div class="row">
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-blue">
                <div class="inner">
                  <h3> <?php echo $notOverDue;?></h3>
                  <p>With In Clients</p>
                </div>
                <div class="icon">
                  <i class="ion ion-person"></i>
                </div>
                <a href="clients.php" class="small-box-footer">
                  With In Clients<i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            </div><!-- ./col -->
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-blue">
                <div class="inner">
                  <h3><?php echo $overDue; ?></h3>
                  <p>Due Clients </p>
                </div>
                <div class="icon">
                  <i class="ion ion-person"></i>
                </div>
                <a href="due.php" class="small-box-footer">
                   Due Clients <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            </div><!-- ./col -->
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-blue">
                <div class="inner">
                  <h3><?php echo $fullyPaid+$full; ?></h3>
                  <p>Clients Paid Up</p>
                </div>
                <div class="icon">
                  <i class="ion ion-person"></i>
                </div>
                <a href="#" class="small-box-footer">
                  Clients Paid Up<i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            </div><!-- ./col -->

            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-red">
                <div class="inner">
                  <h3>
                     <?php
                                 $api->AmountCollected();
                      ?>

                  </h3>
                  <p>Collected Amount</p>
                </div>
                <div class="icon">
                  <i class="ion ion-cash"></i>
                </div>
                <a href="#" class="small-box-footer">
                  More info <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            </div><!-- ./col -->
          </div><!-- /.row -->
            <!-- Small boxes (Stat box) -->
          <div class="row">
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-green">
                <div class="inner">
                  <h3>
                           <?php
                                 
                                            try{

                                            
                                            $query = "select `order` from order_item where `order` in (".implode(",", $downPaymentReceived).") and disburse_date is not null";
                                            $sth = $dbh->prepare($query);
                                            $sth->execute();
                                            while ($result = $sth->fetchAll()) {
                                              
                                                echo $res = number_format(abs(count($result)));
                                               }
                                              

                                            }catch(PDOException $e){
                                                trigger_error("error_msg".$e->getMessage());

                                            }
                                          
                                 ?>
                                
                  </h3>
                  <p>Delivered</p>
                </div>
                <div class="icon">
                  <i class="ion ion-person-add"></i>
                </div>
                <a href="clients.php" class="small-box-footer">
                  Delivered <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            </div><!-- ./col -->
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-green">
                <div class="inner">
                  <h3><?php
                                 
                                            try{

                                            
                                            $query = "select `order` from order_item where `order` in (".implode(",", $downPaymentReceived).") and disburse_date is null";
                                            $sth = $dbh->prepare($query);
                                            $sth->execute();
                                            while ($result = $sth->fetchAll()) {
                                              
                                                echo $res = number_format(abs(count($result)));
                                            

                                               }
                                              

                                            }catch(PDOException $e){
                                              trigger_error("error_msg".$e->getMessage());


                                            }
                                          
                                 ?>
                                 </h3>
                  <p>Pending Delivery</p>
                </div>
                <div class="icon">
                  <i class="fa fa-shopping-cart"></i>
                </div>
                <a href="orders.php" class="small-box-footer">
                  Pending Delivery <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            </div><!-- ./col -->
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-green">
                <div class="inner">
                  <h3>
                           <?php
                                 try{

  
                                $query = "select `order` from order_item where `order` in (".implode(",", $downPaymentReceived).") and disburse_date is not null and installation_date is not null";
                                $sth = $dbh->prepare($query);
                                $sth->execute();
                                while ($result = $sth->fetchAll()) {
                                  
                                    echo $res = number_format(abs(count($result)));
                                   }
                                  

                                }catch(PDOException $e){
                                  trigger_error("error_msg".$e->getMessage());

                                }
                                ?>
                  </h3>
                  <p>Activated Systems </p>
                </div>
                <div class="icon">
                  <i class="fa fa-shopping-cart"></i>
                </div>
                <a href="activated.php" class="small-box-footer">
                  Activated Systems <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            </div><!-- ./col -->

            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-red">
                <div class="inner">
                  <h3>
                 <?php
                                      
                                $outstanding=0;
                                $outstand=0;
                                foreach($downPaymentReceived as $order){
                                if($api->get_record("order", "payment_plan", "where id = '".$order."'")=='hire'){
                                $qry="select pid,date,amt from payment where `order`='".$order."' order by date";   
                                $sth = $dbh->prepare($qry);
                                $sth->execute();
                                while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {     
    
                                $outstanding=$api->scheduleOutstanding($order,$row['pid'],$row['date'],$row['amt']);
                                
                                }
                                if($outstanding > 0)
                                $outstand+=$outstanding;
                                
                                }
                                }
                                echo number_format(abs($outstand),0,".","'").'<br>'; 
                                 
                                 ?>

                  </h3>
                  <p>Outstanding Amount</p>
                </div>
                <div class="icon">
                  <i class="ion ion-cash"></i>
                </div>
                <a href="#" class="small-box-footer">
                  More info <i class="fa fa-arrow-circle-right"></i>
                </a>
              </div>
            </div><!-- ./col -->
          </div><!-- /.row -->


                     
                       <div class="row">
                      <div class="col-md-12">
                        <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                      </div>
                <!--                        
                    <div class="row">
                      <div class="col-md-12">
                        <div id="payments" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                      </div>
                      
                    </div>
                    <div class="row">
                      <div class="col-md-12">
                        <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                      </div>
                      
                    </div>
                      <div class="row">
                      <div class="col-md-12">
                        <div id="revenue" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                      </div>
                      -->
                      

                </div>
          </div>
        </section>     
    </div><!-- ./wrapper -->
     <?php
        require 'footer.php';
        ?> 

    <!-- jQuery 2.1.4 -->
    <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
     <script src="plugins/jQueryUI/jquery-ui-1.10.3.js"></script>


    
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
      $.widget.bridge('uibutton', $.ui.button);
    </script>
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
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script type="text/javascript" src="charts/custom.js"></script>
    <script type="text/javascript" src="charts/revenua.js"></script>
    <script type="text/javascript" src="charts/payments.js"></script>


    
    
  </body>
</html>