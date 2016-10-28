<?php
require 'config.inc.php';
require 'connect.php'; 



	try{

		$data= json_decode(file_get_contents("php://input")); 
        $firstname = $data->userfirstname; 
        $lastname = $data->userlastname;
        $email = $data->useremail;
        $category = $data->usercategory;
        $username = $data->userusername;
        $password = $data->userpassword;
 
        	$dataa = $dbh->prepare('select * from userregister where username=:username and password=:password');	
		    $dataa->bindParam(':username',$username);
		    $dataa->bindParam(':password',$password);
		    $dataa->execute();

		    $row = $dataa->fetch(PDO::FETCH_ASSOC);

		    if (!$row) {

			$dataa =$dbh-> prepare('insert into userregister(firstname,lastname,email,category,username,password)values(:firstname,:lastname,:email,:category,:username,:password)');
			$dataa->bindParam(':firstname',$firstname);
			$dataa->bindParam(':lastname',$lastname);
			$dataa->bindParam(':email',$email);
			$dataa->bindParam(':category',$category);
			$dataa->bindParam(':username',$username);
			$dataa->bindParam(':password',$password);


			$res = $dataa->execute();

			if ($res) {
				$arr = array('msg' => "Added New Administrator  ".$username, 'error' => '');
                $jsn = json_encode($arr);
                print_r($jsn);
			}else{

				   $arr = array('msg' => "", 'error' => "Failed To Add New User"  .$username);
                   $jsn = json_encode($arr);
                   print_r($jsn);
			}
		    }else{
				$arr = array('msg' => "", 'error' => "User Already Exists");
                $jsn = json_encode($arr);
                print_r($jsn);
		}
        	
      

	}catch(PDOException $e){


		trigger_error("error_msg".$e->getMessage());

	}
?>