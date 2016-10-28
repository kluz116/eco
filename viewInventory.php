<?php

require 'config.inc.php';
require 'connect.php';
$api= new Config();
if(!isset($_SESSION['username'])){

  header("Location:login");
  }

    $name="";
    if($_SESSION['username']){

      $username = $_SESSION['username'];


    }
      try{
    
     $date =$dbh->prepare("select * from product_inventory order by id desc");
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
          
         <i class="fa fa-shopping-cart"></i>  Inventory
          </h1>
          <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Inventory</li>
          </ol>
        </section>

                    <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                <div class="row">
                  <div class="col-md-11">
                     <h3 class="box-title">View All Inventory From Here.</h3>
                  </div>
                  <div class="col-md-1">
                      <a data-toggle="modal" href="#form-content" class="btn btn-primary btn-xs"><i class="fa fa-plus"></i><i class="fa fa-shopping-cart"></i></a>
                      <a data-toggle="modal" href="#form-codes" class="btn btn-success btn-xs"><i class="fa fa-key"></i></a>
                  </div>
                </div>
                  </div>
                  <div class="box-body">
                   <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                      <th style="width:18px"><input type="checkbox" /></th>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Product Code</th>
                        <th>Barcode</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Device</th>
                        <th>Edit</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php     

                    while ($row= $date->fetch(PDO::FETCH_ASSOC)) {
                      
                    ?>
                      <tr>
                      <td><input type="checkbox" /></td>
                        <td><?php echo $row['id'];?></td>
                        <td>
                        <?php

                          if ($row['order_id']=='') {
                              echo "--";
                          }else{

                            echo strtoupper($api->get_record("customer", "concat(fname,' ',lname)", "where cid = '".$api->get_record("order","customer","where id = '".$row['order_id']."'")."'"));
                          }
                        ?>
                       </td>
                         
                        <td><?php echo $api->get_record("product","product_code","where pid = '".$row['product_type']."'"); ?></td>
                        <td><?php echo $row['serial_no'];?></td>
                        <td><?php echo $row['when_added'];?></td>
                        <td><?php
                        if ($row['status']==0) {
                          echo "<span class='label label-success'>SOLD</span>";
                        }else{
                          echo "<span class='label label-success'>NOT SOLD</span>";
                        }
                         ?></td>
                        <td><?php
                        if ($row['device_status']=='off') {
                          echo "<span class='label label-danger'>OFF</span>";
                        }else{
                          echo "<span class='label label-success'>ON</span>";
                        }
                         ?></td>
                         <td><a class="btn btn-success btn-sm" href="edit_inventory.php?id=<?php echo $row['id'];?>"><i class="fa fa-edit"></i></a></td>
                        
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
                </div><!-- /.box-header -->

              </div><!-- /.box -->
            </div><!-- /.col -->
          </div><!-- /.row -->
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->
      <?php
        require 'footer.php';
      ?>
      
      
      <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
      <div class='control-sidebar-bg'></div>
    </div><!-- ./wrapper -->

        <div class="example-modal">
            <div id="form-content" class="modal modal-default  fade" style="display: none;">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-shopping-cart"></i> Add Item In Inventory</h4>
                  </div>
                  <div class="modal-body" id="content">
                  <div class="box-body">
                   <div id="response"></div>
                          <div class="form-group">
                    
                    
                        <?php
                        $api->GetProducts_two();
                            
                        ?>
                     
                    </div>
                  
                    <div class="form-group">
                        <label>Barcode</label>
                          <input type="text" class="form-control" id="serial_no" name="serial_no"  placeholder="Barcode">
                          
                     </div>
                      <div class="form-group">
                        <label>Serial Number</label>
                          <input type="number" class="form-control" id="item_number" placeholder="Item Serial Number">
                          
                     </div>
                    <div class="form-group">
                      <div class="col-md-4"></div>
                      <div class="col-md-4">
                        <button class="btn btn-primary" id="addInvent" type="submit"><i class="fa fa-plus"></i><i class="fa fa-shopping-cart"></i> Add Item In Inventory</button>
                      </div>
                      <div class="col-md-4"></div>
                          
                        </div>
                    </div>
                    
                  </div><!-- /.box-body -->
               
                  </div>
                 
                </div><!-- /.modal-content -->
              </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
          <!-- /.example-modal -->
          <div class="example-modal">
            <div id="form-codes" class="modal modal-default  fade" style="display: none;">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-key"></i> Generate Code</h4>
                  </div>
                  <div class="modal-body" id="content">
                  <div class="box-body">
                   <strong class="text-center"><div id="response_code"></div></strong>
                      <div class="form-group">
                       <label>Serial Number</label>
                        <input type="number" class="form-control" id="serialNo" placeholder="Serial Number" required>
                      </div>
                  
                    <div class="form-group">
                        <label>Minutes</label>
                          <input type="number" class="form-control" id="minutes" name=""  placeholder="Minutes" required>
                          
                     </div>
                    <div class="form-group">
                      <div class="col-md-4"></div>
                      <div class="col-md-4">
                        <button class="btn btn-primary" id="generate_code" type="submit"><i class="fa fa-key"></i> Generate Code</button>
                      </div>
                      <div class="col-md-4"></div>
                          
                        </div>
                    </div>
                    
                  </div><!-- /.box-body -->
               
                  </div>
                 
                </div><!-- /.modal-content -->
              </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
          <!-- /.example-modal -->

    <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="js/addInventory.js" type="text/javascript"></script>
    <script src="js/generate_codes.js" type="text/javascript"></script>
    <!-- DATA TABES SCRIPT -->
    <script src="plugins/datatables/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="plugins/datatables/dataTables.bootstrap.min.js" type="text/javascript"></script>
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


  </body>
</html>