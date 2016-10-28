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

?>
<?php

try{

     $id = $_GET['id'];
     $data = $dbh->prepare("select * from customer  where cid =:id");
    $data->bindParam(':id',$id);
     $data->execute();
     while ($roww = $data->fetch(PDO::FETCH_ASSOC)){

     echo $roww['fname'];




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
            <i class="fa fa-edit"></i> Edit Customer
          </h1>
          <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Edit Customer</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="box">
                <div class="box-header">
                  <h3 class="box-title"></h3>
                </div><!-- /.box-header -->
                <div class="box-body">
              <div class="box box-primary">
                <div class="box-header">
                  <h3 class="box-title"></h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form role="form" action="" method="post" enctype="multipart/form-data">
                  <div class="box-body">
                    <div class="form-group">
                      <div class="col-sm-6">
                           <input type="text" value="<?php echo $roww['fname'];?>" class="form-control" name="fname" placeholder="Enter Firstname">
                      </div>
                        <div class="col-sm-6">
                           <input type="text" value="<?php echo $roww['lname'];?>" class="form-control" name="lname" placeholder="Enter Lastname">
                         </div>
                    </div><br><br>
                     <div class="form-group">
                      <div class="col-sm-4">
                           <select class="form-control" name="gender" value="<?php echo $roww['gender'];?>">
                                <option>Choose Sex</option>
                                <option>Male</option>
                                <option>Female</option>
                              </select>
                      </div>
                        <div class="col-sm-8">
                        <label>Language</label>
                          <?php
                          $api->GetLanguage();
                            
                          ?>
                         </div>
                    </div><br><br>
                      <div class="form-group">
                      <div class="col-sm-6">
                      <label>Phone</label>
                    <div class="input-group">
                      <div class="input-group-addon">
                        <i class="fa fa-phone"></i>
                      </div>
                      <input type="text" name="default_phone" value="<?php echo $roww['default_phone'];?>" class="form-control" data-inputmask="'mask': ['999-999-9999 [x99999]', '+099 99 99 9999[9]-9999']" data-mask/>
                    </div><!-- /.input group -->
                      </div>

                        <div class="col-sm-6">
                      <label>Alternate Phone:</label>
                    <div class="input-group">
                      <div class="input-group-addon">
                        <i class="fa fa-phone"></i>
                      </div>
                      <input type="text" name="alt_phone" value="<?php echo $roww['alt_phone'];?>" class="form-control" data-inputmask="'mask': ['999-999-9999 [x99999]', '+099 99 99 9999[9]-9999']" data-mask/>
                    </div><!-- /.input group -->
                      </div>
           
                      </div><br><br><br><br>
                      <div class="form-group">
                      <div class="col-sm-6">
                   <input type="text" class="form-control" value="<?php echo $roww['next_ov_keen'];?>" name="next_ov_keen" placeholder="Enter Next Of Keen Names">
                      </div>

                        <div class="col-sm-6">
                      <label> Next Of Keen Phone:</label>
                    <div class="input-group">
                      <div class="input-group-addon">
                        <i class="fa fa-phone"></i>
                      </div>
                      <input type="text" name="nok_phone" value="<?php echo $roww['nok_phone'];?>" class="form-control" data-inputmask="'mask': ['999-999-9999 [x99999]', '+099 99 99 9999[9]-9999']" data-mask/>
                    </div><!-- /.input group -->
                      </div>
           
                      </div><br><br>
                     <div class="form-group">
                      <div class="col-sm-6">
                      <label> District:</label><br>
                          <?php
                          $api->GetDistrict();
                            
                          ?>
                      </div>
                        <div class="col-sm-6">
                        <label> Subcounty:</label><br>
                              <?php
                          $api->SubCounty();
                            
                          ?>
                         </div>
                    </div><br><br>
                     <div class="form-group">
                      <div class="col-sm-6">
                      <label> Parish:</label><br>
                           <?php
                          $api->Parish();
                            
                          ?>
                      </div>
                        <div class="col-sm-6">
                         <label> Village:</label><br>
                              <input type="text" class="form-control" value="<?php echo $roww['address_desc'];?>" name="address_desc" placeholder="Village"> 
                         </div>
                    </div><br><br>
                       <div class="form-group">
                      <label for="exampleInputFile">Upload ID</label>
                      <input type="file" class="form-control" value="<?php echo $roww['image'];?>" name="image">
                    </div>
                      <div class="form-group">
                        <div class="col-sm-12">
                           <input type="hidden" value="<?php $api->getSessionRole();?>" class="form-control" name="user" >
                         </div>
                    </div><br><br>
                    
                  
                  </div><!-- /.box-body -->

                  <div class="box-footer">
                    <button type="submit" name="addClient" class="btn btn-primary"><i class="fa fa-plus"></i><i class="fa fa-user"></i> Add Customer</button>
                  </div>
                </form>
                <?php

                     }
               }catch(PDOExcption $e){
            
                    echo "$e";

                }  
                ?>
             
              </div><!-- /.box -->
                    
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
         <!-- Latest compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/bootstrap-select.min.js"></script>

     <!-- (Optional) Latest compiled and minified JavaScript translation files -->
      <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.10.0/js/i18n/defaults-*.min.js"></script>

  </body>
</html>