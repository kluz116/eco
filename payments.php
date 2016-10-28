<?php

require 'config.inc.php';
require 'connect.php'; 
$api = new Config();

if(!isset($_SESSION['username']) && empty($_SESSION['username'])){

  header("Location:login.php");
  exit();
  }

    $name="";
    if($_SESSION['username']){

      $username = $_SESSION['username'];


    }   
 
  try{
    
     $date =$dbh->prepare("select py.* from payment py left join `order` o on py.order=o.id left join order_item t on o.id=t.order order by pid desc");
     $date->execute();
  
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
    <link rel="stylesheet" type="text/css" href="font-awesome/css/font-awesome.css">
    <!-- Ionicons 2.0.0 -->
    <link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />  
        <!-- DATA TABLES -->
    <link href="plugins/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />  
    <!-- Theme style -->
    <link href="dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins. Choose a skin from the css/skins 
         folder instead of downloading all of them to reduce the load. -->
    <link href="dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
    <!-- iCheck -->
    <link href="plugins/iCheck/flat/blue.css" rel="stylesheet" type="text/css" />
    <!-- Morris chart -->
    <link href="plugins/morris/morris.css" rel="stylesheet" type="text/css" />
    <!-- jvectormap -->
    <link href="plugins/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- Date Picker -->
    <link href="plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker -->
    <link href="plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
    <!-- bootstrap wysihtml5 - text editor -->
    <link href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />

  <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/css/bootstrap-select.min.css">
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
            Payments
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Payments</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
        <div class="box">
                <div class="box-header">
                  <div class="row">
                    <div class="col-md-11">
                       <h3 class="box-title">List Of All Payments</h3>
                    </div>
                    <div class="col-md-1">
                    </div>
                  </div>
                    
                
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table id="example2" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                      <th style="width:18px"><input type="checkbox" /></th>
                              <th> ID</th>
                              <th>Amount</th>
                              <th class="text-center">Mode</th>
                              <th class="text-center">Order</th>
                              <th class="text-center">Customer</th>  
                              <th>Date</th>
                              <th>Comments</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php     

                    while ($row= $date->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                      <tr>
                      <td><input type="checkbox" /></td>
                              <td>5000<?php echo $row['pid'];?></td>
                              <td><?php echo number_format(abs($row['amt'])); ?> <font color='green'>UGX</font></td>
                              <td class="text-center"><?php if ($row['fund_source']=='mobile_money_') { echo "MM"; }else if($row['fund_source']=='bank'){echo "Bank";}else{ echo "Cash";} ?></td>
                              <td class="text-center"><?php echo $row['order'] == "" ? "<a href=\"resolve_payment.php?payment_id=".$row['pid']."\">Look Up</a>" : "4000".$row['order']; ?></td>
                              <td class="text-center"><?php echo strtoupper($api->get_record("customer", "concat(fname,' ',lname)","where cid = '".$row['customer']."'")); ?></td>
                              <td><span title="<?php echo date("D jS M Y", strtotime($row['date'])); ?>"><?php echo $row['date']; ?></span></td>
                              <td><?php echo $row['remarks']; ?></td> 
                      </tr>
                          <?php

                        }
                      }catch(PDOException $e){

                       trigger_error('Errors :'.$e->getMessage());

                     }


                         ?>

                    </tbody>
                      
                    </tfoot>
                  </table>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
        </section>
        

        
    <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
      <div class='control-sidebar-bg'></div>
    
    </div><!-- ./wrapper -->
     <?php require 'footer.php'; ?> 



        
       



    <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <!-- DATA TABES SCRIPT -->
    <script src="plugins/datatables/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="plugins/datatables/dataTables.bootstrap.min.js" type="text/javascript"></script>
     <script src="plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
    <!-- datepicker -->
    <script src="plugins/datepicker/bootstrap-datepicker.js" type="text/javascript"></script>
    <!-- Bootstrap WYSIHTML5 -->
    <!-- SlimScroll -->
    <script src="plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <!-- FastClick -->
    <script src='plugins/fastclick/fastclick.min.js'></script>
    <!-- AdminLTE App -->
    <script src="dist/js/app.min.js" type="text/javascript"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="dist/js/demo.js" type="text/javascript"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script>

     <!-- (Optional) Latest compiled and minified JavaScript translation files -->
      <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/i18n/defaults-*.min.js"></script>




    <!-- page script -->
    <script type="text/javascript">
      $(function () {
        $("#example1").dataTable();
        $('#example2').dataTable({
          "bPaginate": true,
          "bLengthChange": true,
          "bFilter": false,
          "bSort": true,
          "bInfo": true,
          "bAutoWidth": false
        });
      });

 
    </script>

      <script type="text/javascript">
      //Date picker
    $('#datepicker').datepicker({
      autoclose: true
    });</script>  
   
  </body>
</html>