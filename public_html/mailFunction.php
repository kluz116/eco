<?php
//if "email" variable is filled out, send email
  if (isset($_REQUEST['email'])|| isset($_REQUEST['comment']))  {
  
  //Email information
  $admin_email = "arktumwesigye@gmail.com";
  $email = $_REQUEST['email'];
  $name = $_REQUEST['name'];
  $subject = $_REQUEST['subject'];
  $comment = $_REQUEST['comment'];
  
  //send email
  mail($admin_email, "$subject",$name, $comment, "From:" . $email);
  
  //Email response
  echo "Thank you for contacting us!";
  }
  
  //if "email" variable is not filled out, display the form
  else  {
      echo ' <form method="post">
  Email: <input name="email" type="text" /><br />
  Subject: <input name="subject" type="text" /><br />
  Message:<br />
  <textarea name="comment" rows="15" cols="40"></textarea><br />
  <input type="submit" value="Submit" />
  </form>';
  }
?>

 
  
