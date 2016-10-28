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
    
     $date =$dbh->prepare("select * from userregister order by id desc");
     $date->execute();
     ?>


<!DOCTYPE html>
<html ng-app="dispatch">
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

    <style>
          
 
            .infon{
                margin: 10px 0px;
                padding:10px;
                color: #00529B;
                list-style: none;
                text-align: center;
            }
            .errn{
                margin: 10px 0px;
                padding:10px; 
                 color: #D8000C;
                 list-style: none;
                 text-align: center;
            }
        </style>

     
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
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
          
         <i class="fa fa-users"></i>  Users
          </h1>
          <ol class="breadcrumb">
            <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Users</li>
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
                
                         <h3 class="box-title">View All Users From Here.</h3>
                
                	</div>
                	<div class="col-md-1">

        <div class="example-modal">
            <div id="form-content" class="modal modal-default  fade" style="display: none;">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-user"></i> Add New Administrator</h4>
                  </div>
                  <div class="modal-body" id="content">
                   <form name="userForm" ng-submit="submitForm(userForm.$valid)" ng-controller="AdminController" novalidate>
             
                  <div class="box-body">
                    <div class="form-group">
                      <div class="col-lg-12">
                      <ul>
                          <li class="errn" ng-repeat="error in errors"> {{ error}} </li>
                      </ul>
                      <ul>
                          <li class="infon" ng-repeat="msg in msgs"> {{ msg}} </li>
                      </ul>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-lg-6" ng-class="{ 'has-error' : userForm.firstname.$invalid && !userForm.firstname.$pristine }">
                      		<input type="text" class="form-control" name="firstname"  ng-model="firstname" placeholder="First Name" required>
                          <p ng-show="userForm.firstname.$invalid && !userForm.firstname.$pristine" class="help-block">First Name is required.</p>
                      </div>
                      <div class="col-lg-6" ng-class="{ 'has-error' : userForm.lastname.$invalid && !userForm.lastname.$pristine }">
                      		<input type="text" class="form-control" name="lastname" ng-model="lastname"  placeholder="Last Name" required>
                          <p ng-show="userForm.lastname.$invalid && !userForm.lastname.$pristine" class="help-block">First Name is required.</p>
                      </div>
                    </div>
                    <div class="form-group"  ng-class="{ 'has-error' : userForm.email.$invalid && !userForm.email.$pristine }">
                    	<div class="col-lg-12"><br><br>
                      		<input type="email" class="form-control" name="email" ng-model="email"  placeholder="Email">
                          <h5>{{email}}<h5>
                          <p ng-show="userForm.email.$invalid && !userForm.email.$pristine" class="help-block">Enter a valid email.</p>
                        </div>
                    </div>
                    <div class="form-group">
                    	<div class="col-lg-12"><br><br>
                      		<select class="form-control" name="category" ng-model="category">
                      			<option>--Choose Category Of User--</option>
                      			<option>Admin</option>
                      			<option>Support</option>
                      			<option>Sales</option>
                      			<option>Others</option>
                      		</select>
                        </div>
                    </div>

                    <div class="form-group" ng-class="{ 'has-error' : userForm.username.$invalid && !userForm.username.$pristine }">
              			<div class="col-lg-6"><br><br>
                      		<input type="text" class="form-control" name="username" ng-model="username" ng-minlength="5" ng-maxlength="8"  placeholder="Username" required>
                          <p ng-show="userForm.username.$error.minlength" class="help-block">Username is too short.</p>
                          <p ng-show="userForm.username.$error.maxlength" class="help-block">Username is too long.</p>
                      </div>
                      <div class="col-lg-6" ng-class="{ 'has-error' : userForm.password.$invalid && !userForm.password.$pristine }"><br><br>
                      		<input type="password" class="form-control" name="password" ng-model="password" ng-minlength="5" ng-maxlength="8"  placeholder="Password" required>
                          <p ng-show="userForm.password.$error.minlength" class="help-block">Password is too short.</p>
                          <p ng-show="userForm.password.$error.maxlength" class="help-block">Password is too long.</p>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-lg-12"><br><br>
                      <div class="col-md-4"></div>
                      <div class="col-md-4">
                        <button class="btn btn-danger" ng-click='submitForm();' type="submit" ng-disabled="userForm.$invalid"><i class="fa fa-plus"></i><i class="fa fa-user"></i> Click To Add New User Please</button>
                      </div>
                      <div class="col-md-4"></div>
                          
                        </div>
                    </div>
                    
                  </div><!-- /.box-body -->
                </form>
                  </div>
                 
                </div><!-- /.modal-content -->
              </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
          </div><!-- /.example-modal -->

          <a data-toggle="modal" href="#form-content" class="btn btn-primary btn-xs"><i class="fa fa-plus"></i><i class="fa fa-user"></i></a>
                	</div>

                </div>
                  
                </div><!-- /.box-header -->
                <div class="box-body">
                     <div class="box-header">
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                      <th style="width:18px"><input type="checkbox" /></th>
                        <th>ID</th>
                        <th>Users</th>
                        <th>Email</th>
                        <th>Category</th>
                        <th>User Name</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php     

                    while ($row= $date->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                      <tr>
                      <td><input type="checkbox" /></td>
                        <td>USER0<?php echo $row['id'];?></td>
                        <td><a href=" "><?php echo strtoupper($row['firstname']); ?>&nbsp;<?php echo strtoupper($row['lastname']); ?></a></td>
                        <td><?php echo $row['email'];?></td>
                        <td><?php echo $row['category'];?></td>
                        <td><?php echo $row['username'];?></td> 
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

    <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.2 JS -->
    <script type="text/javascript" src="angular/angular/angular.js"></script>
    <script type="text/javascript" src="angular/app/application.js"></script>
    <script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
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