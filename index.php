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
  try{

  //$query = "SELECT COUNT(*) as totalSales FROM sales WHERE YEAR(date) = '2016' GROUP BY MONTH(date)";
  $query = "SELECT MONTH(date) AS month, COUNT(date)  AS totalSales FROM sales WHERE date >= NOW() - INTERVAL 1 YEAR GROUP BY MONTH(date)";
  $sth = $dbh->prepare($query);
  $sth->execute();
  while ($result = $sth->fetch(PDO::FETCH_ASSOC)) {
  $rows[] = $result['totalSales'];
      
    }
   //echo json_encode($return_arr);
   //echo join($rows, ',');
    
  }catch(PDOException $e){


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
    <style type="text/css">
 

       #exTab1 .nav-pills > li > a {
       border-radius: 2;
     }

    </style>

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
                    <div class="row">
                     <div class="col-md-3">
                        <table class='table table-bordered'>
                           <thead>
                              <tr>
                                 <th><font color='green'>Clients</font></th>
                                 <th><font color='green'>Female</font></th>
                                 <th><font color='green'>Male</font></th>
                             </tr>
                            </thead>
                            <tbody>
                            <tr>
                              <th>
                                 <a href="clients.php"><?php $api->GetNumberOfClient();?></a>
                              </th>
                              <th>
                                 <a href="female_clients.php"><?php $api->getFemaleClients(); ?> </a>
                              </th>
                               <th>
                                 <a href="male_clients.php"><?php $api->getmale(); ?> </a>
                              </th>
                            </tr>
                              
                            </tbody>
                        </table>
                        </div>
                        <div class="col-md-3">
                        <table class='table table-bordered'>
                           <thead>
                              <tr>
                                 <th><font color='green'>Rural</font></th>
                                 <th><font color='green'>Urban</font></th>
                             </tr>
                            </thead>
                            <tbody>
                            <tr>
                              <th>
                                 <a href="rural_clients.php"><?php $api->getRuralClient();?></a>
                              </th>
                              <th>
                                 <a href="urban_clients.php"><?php $api->getUrbanClient();?> </a>
                              </th>
                            </tr>
                              
                            </tbody>
                        </table> 
                      </div>

                      <div class="col-md-3">
                        <table class='table table-bordered'>
                           <thead>
                              <tr>
                                 <th><font color='green'>Able</font></th>
                                 <th><font color='green'>Disabled</font></th>
                             </tr>
                            </thead>
                            <tbody>
                            <tr>
                              <th>
                                 <a href="normal_clients.php"><?php $api->getNormalClient();?></a>
                              </th>
                              <th>
                                 <a href="disabled_clients.php"><?php $api->getDisableClient();?> </a>
                              </th>
                            </tr>
                              
                            </tbody>
                        </table> 
                      </div>
                      <div class="col-md-3">
                        <table class='table table-bordered'>
                           <thead>
                              <tr>
                                 <th><font color='green'>Elderly</font></th>
                                 <th><font color='green'>Child Headed </font></th>
                             </tr>
                            </thead>
                            <tbody>
                            <tr>
                              <th>
                                 <a href="major.php"><?php $api->getMajorClient();?></a>
                              </th>
                              <th>
                                 <a href="minor_clients.php"><?php $api->getMinorClient();?> </a>
                              </th>
                            </tr>
                              
                            </tbody>
                        </table> 
                      </div>
                      </div>
                      <div class="row">
                        <div class="col-md-3">
                        <table class='table table-bordered'>
                           <thead>
                              <tr>
                                 <th><font color='green'>Approved</font></th>
                                 <th><font color='green'>Active</font></th>
                             </tr>
                            </thead>
                            <tbody>
                            <tr>
                              <th>
                                 <a href="orders.php"><?php $api->GetNumberOfOrders();?></a>
                              </th>
                              <th>
                                 <a href="deposit_payment_recieved.php"><?php $api->getSales();?> </a>
                              </th>
                            </tr>
                              
                            </tbody>
                        </table>
                      
                      </div>
                      <div class="col-md-3">
                        <table class='table table-bordered'>
                           <thead>
                              <tr>
                                 <th><font color='green'>Recieved</font></th>
                                 <th><font color='green'>Pending</font></th>
                             </tr>
                            </thead>
                            <tbody>
                            <tr>
                              <th>
                                 <a href="deposit_payment_recieved.php"><?php $api->getSales();?> </a>
                              </th>
                              <th>
                                 <a href="pending_deposit.php"><?php $api->getPendingDeposit();?></a>
                              </th>
                            </tr>
                            </tbody>
                        </table>
                      
                      </div>
                      <div class="col-md-3">
                           <table class='table table-bordered'>
                           <thead>
                              <tr>
                                 <th><font color='green'>Delivered</font></th>
                                 <th><font color='green'>Pending</font></th>
                             </tr>
                            </thead>
                            <tbody>
                            <tr>
                              <th><a href="delivered.php">
                                <?php $api->getDelivery(); ?>
                                 </a>
                              </th>
                              <th>
                              <a href="pending_delivererly.php">
                                <?php $api->getPendingDelivery(); ?>
                                 </a>
                              </th>
                            </tr>
                              
                            </tbody>
                        </table>
                      </div>
                      <div class="col-md-3">
                       <table class='table table-bordered'>
                           <thead>
                              <tr>
                                 <th><font color='green'>Activated</font></th>
                                 <th><font color='green'>Pending</font></th>
                             </tr>
                            </thead>
                            <tbody>
                            <tr>
                              <th>
                             <a href="activated.php">
                                 <?php $api->getInitiated(); ?>
                                </a>

                              </th>
                              <th>
                              <a href="pending_activation.php">
                                 <?php $api->getNotInitiated(); ?>
                                 </a>
                              </th>
                            </tr>
                              
                            </tbody>
                        </table>
                      </div>
                    </div>
                      <div class="row">
                      
                      <!--<div class="col-md-3">
                                 <table class='table table-bordered'>
                           <thead>
                              <tr>
                                 <th><font color='green'>With In</font></th>
                                 <th><font color='green'>Due</font></th>
                                 <th><font color='green'>Cleared</font></th>
                             </tr>
                            </thead>
                            <tbody>
                            <tr>
                              <th>
                                 <a href="within.php"><?php
                                 $api->getWithIn();
                                 ?>
                                 </a>
                              </th>
                              <th>
                                 <a href="due.php"><?php
                                 $api->getDue();
                                 ?>
                                 </a>
                              </th>
                               <th>
                               <a href="paidup.php">
                                 <?php
                                 $api->getPaidUp();
                                 ?>
                                 </a>
                              </th>
                            </tr>
                              
                            </tbody>
                        </table>
                        
                      </div>-->
                      <div class="col-md-3">
                        <table class='table table-bordered'>
                           <thead>
                              <tr>
                                 <th><font color='green'>Total Eco Solars</font></th>
                                 <th><font color='green'>Value</font></th>
                             </tr>
                            </thead>
                            <tbody>
                            <tr>
                              <th>
                                 <?php $api->getSales();?>
                              </th>
                              <th>
                                 <?php $api->getValue(); ?>
                              </th>
                            </tr>
                              
                            </tbody>
                        </table>
                      </div>
                      <div class="col-md-3">
                        <table class='table table-bordered'>
                           <thead>
                              <tr>
                                 <th><font color='green'>Colllected</font></th>
                                 <th><font color='green'>Outstanding</font></th>
                             </tr>
                            </thead>
                            <tbody>
                            <tr>
                              <th>
                                 <?php $api->AmountCollected(); ?>
                              </th>
                              <th>
                                 <?php $api->getOutstanding(); ?>
                              </th>
                            </tr>
                              
                            </tbody>
                        </table>
                      </div>
                    </div>
                      </div>

                            
                    <div class="row">
                     
                      </div>
                      
              
                      <div class="row">
                    <div id="exTab1" class="container"> 
                        <ul  class="nav nav-pills">
                              <li class="active">
                                <a  href="#1a" data-toggle="tab">Overview</a>
                              </li>
                              <li><a href="#2a" data-toggle="tab">Monthly Revenue</a>
                              </li>
                              <li><a href="#3a" data-toggle="tab">Monthly Sales</a>
                              </li>
                        </ul>

                          <div class="tab-content clearfix">
                            <div class="tab-pane active" id="1a">
                                <div class="row">
                                    <div class="col-md-6">
                                      <div id="chart_gender" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                                    </div>
                                    <div class="col-md-6">
                                      <div id="chart_location" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                                    </div>
                               </div>
                            </div>
                            <div class="tab-pane" id="2a">
                               <div id="revenua" style="min-width: 1100px; height: 400px; margin: 0 auto"></div>
                            </div>
                            <div class="tab-pane" id="3a">
                            <div class="row">
                               <div class="col-md-12">
                                  <div id="container" style="min-width: 1100px; height: 400px; margin: 0 auto"></div>
                               </div>
                          </div>
                              
                            </div>
                          </div>
                        </div>
                      </div>
                      

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
    <script type="text/javascript">
         $(function () {
    $('#container').highcharts({
        chart: {
            type: 'line'

        },
        title: {
            text: 'Monthly Sales'
        },
        xAxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        },
        yAxis: {
            title: {
                text: 'Sales'
            }
        },
        plotOptions: {
            line: {
                dataLabels: {
                    enabled: true
                },
                enableMouseTracking: false
            }
        },
        series: [{
            name: 'Monthly Sales',
            data: [0,0,<?php echo join($rows, ',');?>]
        }]
    });
});
      $(function () {
    $('#revenua').highcharts({
        chart: {
            type: 'line'

        },
        title: {
            text: 'Monthly Revenue'
        },
        xAxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        },
        yAxis: {
            title: {
                text: 'Revenue'
            }
        },
        plotOptions: {
            line: {
                dataLabels: {
                    enabled: true
                },
                enableMouseTracking: false
            }
        },
        series: [{
            name: 'Monthly Revenue',
            data: [0,0,<?php $api->ChartRevenua(); ?>]
        }]
    });
});


$(function () {
    $('#chart_gender').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: 'Male And Female Gender Percentage'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'red'
                    }
                }
            }
        },
        series: [{
            name: 'Brands',
            colorByPoint: true,
            data: [{
                name: 'Female',
                y: <?php $api->getFemaleCharts();?>
            }, {
                name: 'Male',
                y: <?php $api->getmaleCharts();?>,
                sliced: true,
                selected: true
            }]
        }]
    });
});

$(function () {
    $('#chart_location').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: 'Rural And Urban Clients In Percentage'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'red'
                    }
                }
            }
        },
        series: [{
            name: 'Brands',
            colorByPoint: true,
            data: [{
                name: 'Rural',
                y: <?php $api->getRuralCharts();?>
            }, {
                name: 'Urban',
                y: <?php $api->getUrbanCharts();?>,
                sliced: true,
                selected: true
            }]
        }]
    });
});



    </script>

 
    
  </body>
</html>