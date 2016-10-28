<?php
//if "email" variable is filled out, send email
  if (isset($_REQUEST['email']))  {
  
  //Email information
  $admin_email = "tumwesigye.antony@gmail.com";
  $email = $_REQUEST['email'];
  $subject = $_REQUEST['subject'];
  $message = $_REQUEST['message'];
  
  //send email
  mail($admin_email, "$subject",$message, "From:" . $email);
  
  //Email response
  echo "Thank you for contacting us!";
  }
  
  //if "email" variable is not filled out, display the form
  else  {
?>

 <div class="col-md-4">
                <div style="text-align: center; font-size:15px">
                    <p style="color:#0e9ec7" class="text-uppercase">mail us</p>
                    
                    <div class="">
                    <div class="panel panel-default">
                        <div class="panel-heading" style="color:#1d4608" id="adcontact"> <strong>Send Us a Mail.</strong></div>
                        <div class="panel-body">
                            <form method="post" action="mailFunction.php" role="form">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" class="form-control" name="name" placeholder="Enter Name">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control" name="email" placeholder="Enter Email">
                                </div>
                                <div class="form-group">
                                    <label for="subject">Subject</label>
                                    <input type="text" class="form-control" name="subject" placeholder="Enter Subject">
                                </div>
                                <div class="form-group">
                                    <label for="message">Message</label>
                                    <textarea class="form-control" name="message" rows="7"></textarea>
                                </div>
                                <input type="submit" class="btn btn-default" value="Send Mail" id="cbtn" />
                            </form>
                        </div>
                    </div>
                </div>
                </div>
            </div>
  
  
  
<?php
  }
?>
