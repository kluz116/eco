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
    
     $date =$dbh->prepare("select * from customer join `order` on customer.cid = order.customer order by order.id desc");
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
    <!-- Ionicons 2.0.0 -->
    <link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />  
        <!-- DATA TABLES -->
    <link href="plugins/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />  
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
            <i class="fa fa-shopping-cart"></i> Orders
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Orders</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
        <div class="box">
                <div class="box-header">
                  <h3 class="box-title">List Of All Orders</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                          <th style="width:18px"><input type="checkbox" /></th>
                          
                          <th>Order</th>
                          <th>Product code</th>
                          <th>Customer Name</th>
                          <th>Group</th>
                          <th>Pos</th>
                          <th>Status</th>
                          <th>Purchase Plan</th>
                           <th>Trash</th>
                           <th>Edit</th>

                      
                      </tr>
                    </thead>
                    <tbody>
                    <?php     

                    while ($row= $date->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                      <tr>    <td><input type="checkbox" /></td>
                            
                              <td><a href="edit_order.php?order=<?php echo $row['id']; ?>" >4000<?php echo $row['id']; ?></a></td>
                              <td><?php echo $api->get_record("product","product_code", "where pid = '".$api->get_record("order_item","item", "where `order` = '".$row['id']."'")."'"); ?></td>
                              <td><?php echo strtoupper($api->get_record("customer", "concat(fname,' ',lname)", "where cid = '".$row['customer']."'")); ?></td> 
                              <td><?php echo strtoupper($api->get_record("instituations", "institution_name", "where id = '".$row['institution']."'")); ?></td>
                               <td><?php echo strtoupper($api->get_record("pos", "name", "where posid = '".$row['pos']."'")); ?></td> 
                               <td><span id="order_status<?php echo $row['id'] ?>"><a href="<?php if($row['order_status'] == 'pending_evaluation') { ?>approve_order.php?order=<?php echo $row['id']; ?><?php } else if($row['order_status'] == 'approved') { ?>delivery_item.php?order=<?php echo $row['id']; ?><?php } else if($row['order_status'] == 'items_disbursed') { ?>activate.php?order=<?php echo $row['id']; ?><?php } else { echo 'close_box.html';} ?>" title="Modify Status" class="fancybox modify_status"><?php echo $api->order_status($row['order_status']); ?></a></span></td>
                              <td><?php echo strtoupper($row['payment_plan']); ?></td>
                              <td><a class="btn btn-success btn-sm" href="delete_orders.php?id=<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a></td>
                               <td><a class="btn btn-success btn-sm" href="edit_orders.php?id=<?php echo $row['id'];?>"><i class="fa fa-edit"></i></a></td>

  
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
    
    </div><!-- ./wrapper -->
     <?php require 'footer.php'; ?> 

    <!-- jQuery 2.1.4 -->
    <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>


    <!-- jQuery UI 1.11.2 -->
    <script src="http://code.jquery.com/ui/1.11.2/jquery-ui.min.js" type="text/javascript"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
      $.widget.bridge('uibutton', $.ui.button);
    </script>
    <!-- Bootstrap 3.3.2 JS -->
    <script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script> 
    <!-- DATA TABES SCRIPT -->
    <script src="plugins/datatables/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="plugins/datatables/dataTables.bootstrap.min.js" type="text/javascript"></script>
    <!-- Sl   

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