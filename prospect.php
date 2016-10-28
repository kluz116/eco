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
    
     $date =$dbh->prepare("select c.* from customer c left join `order` o on c.cid=o.customer left join order_item t on o.id=t.order left join payment p on t.order=p.order where o.customer is null or p.order is null order by c.cid desc");
     $date->execute();
  
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>PROSPECTS</title>
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
            <i class="fa fa-users"></i> Prospect Clients
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Prospect Clients</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
        <div class="box">
                <div class="box-header">
                  <h3 class="box-title">List Of All Prospect Clients</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                      <th style="width:18px"><input type="checkbox" /></th>
                        <th>ID</th>
                        <th>Clients Name</th>
                        <th>Phone</th>
                        <th>Sex</th>
                        <th>Next Of Keen</th>
                        <th>Phone</th>
                        <th>District</th>
                        <th>Subcounty</th>
                        <th>Parish</th>
                        <th>Village</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php     

                    while ($row= $date->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                      <tr>
                      <td><input type="checkbox" /></td>
                        <td><?php echo $row['cid'];?></td>
                        <td><a href=''><?php echo $row['fname']; ?>&nbsp;<?php echo $row['lname']; ?></a></td>
                        <td><?php echo $row['default_phone'];?></td>
                        <td><?php echo $row['gender'];?></td>
                        <td><?php echo $row['next_ov_keen'];?></td>
                        <td><?php echo $row['nok_phone'];?></td>
                        <td><?php echo $api->get_record("region","region_name","where id = '".$row['region']."'"); ?></td>
                        <td><?php echo $api->get_record("subcounty","subcounty_name","where id = '".$row['subcounty']."'"); ?></td>
                        <td><?php echo $api->get_record("parish","parish_name","where id = '".$row['parish']."'"); ?></td>
                        <td><?php echo $row['address_desc']; ?></td>  
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
     <?php
        require 'footer.php';
        ?> 

          <div class="example-modal">
            <div id="profile" class="modal modal-primary  fade in" style="display: none;">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"> <i class="fa fa-user"></i> View User Profile</h4>
                  </div>
                  <div class="modal-body">
                      <?php

                      $dbo = new Config();
                      $dbo->getSessionInfo();
                      ?>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-dismiss="modal">Close</button>
                  </div>
                </div><!-- /.modal-content -->
              </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
          </div><!-- /.example-modal -->



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