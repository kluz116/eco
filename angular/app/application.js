var dispatch = angular.module('dispatch',[]);

     dispatch.controller('AdminController', function($scope,$http) {
			    $scope.errors = [];
                $scope.msgs = [];

	       $scope.submitForm =  function  (isValid) {

		if (isValid) {
              $scope.errors.splice(0, $scope.errors.length); // remove all error messages
              $scope.msgs.splice(0, $scope.msgs.length);
	    $http.post('./administrator.php',
		     {

		'userfirstname' : $scope.firstname,
		'userlastname'  :  $scope.lastname,
		'useremail':       $scope.email,
		'usercategory':    $scope.category,
		'userusername' : $scope.username,
		'userpassword' : $scope.password
	  }).success( function (data,status,headers,config){
		  if (data.msg != '')
              {
                $scope.msgs.push(data.msg);
              }
                else
              {
                $scope.errors.push(data.error);
              }
     })
	  .error(function  (data) {
			 $scope.errors.push(status);
			 console.log("Good Allan");
	   });

			};//end of if its true.
	}

  });
     dispatch.controller('PartnerController', function($scope,$http) {
			    $scope.errors = [];
                $scope.msgs = [];

	       $scope.submitForm =  function  (isValid) {

		if (isValid) {
              $scope.errors.splice(0, $scope.errors.length); // remove all error messages
              $scope.msgs.splice(0, $scope.msgs.length);
	    $http.post('./addPartners.php',
		     {

		'partnerfirstname' : $scope.firstname,
		'partnerphone'  :  $scope.phone,
		'partneremail':       $scope.email,
		'partnerusername' : $scope.username,
		'partnerpassword' : $scope.password
	  }).success( function (data,status,headers,config){
		  if (data.msg != '')
              {
                $scope.msgs.push(data.msg);
              }
                else
              {
                $scope.errors.push(data.error);
              }
     })
	  .error(function  (data) {
			 $scope.errors.push(status);
			 console.log("Good Allan");
	   });

			};
	}

  });
     dispatch.controller('ErandController', function($scope,$http) {
			    $scope.errors = [];
                $scope.msgs = [];

	       $scope.submitForm =  function  (isValid) {

		if (isValid) {
              $scope.errors.splice(0, $scope.errors.length); // remove all error messages
              $scope.msgs.splice(0, $scope.msgs.length);
	    $http.post('./addErand.php',
		     {

		'erandfirstname' : $scope.firstname,
		'erandlastname' : $scope.lastname,
		'erandphone'  :  $scope.phone,
		'erandemail':    $scope.email,
		'erandusername' : $scope.username,
		'erandpassword' : $scope.password
	  }).success( function (data,status,headers,config){
		  if (data.msg != '')
              {
                $scope.msgs.push(data.msg);
              }
                else
              {
                $scope.errors.push(data.error);
              }
     })
	  .error(function  (data) {
			 $scope.errors.push(status);
			 console.log("Good Allan");
	   });

			};
	}

  });
     dispatch.controller('ResaController', function($scope,$http) {
			    $scope.errors = [];
                $scope.msgs = [];

	       $scope.submitForm =  function  (isValid) {

		if (isValid) {
              $scope.errors.splice(0, $scope.errors.length); // remove all error messages
              $scope.msgs.splice(0, $scope.msgs.length);
	    $http.post('./addResa.php',
		     {

		'erandfirstname' : $scope.firstname,
		'erandlastname' : $scope.lastname,
		'erandphone'  :  $scope.phone,
		'erandemail':    $scope.email,
		'erandusername' : $scope.username,
		'erandpassword' : $scope.password
	  }).success( function (data,status,headers,config){
		  if (data.msg != '')
              {
                $scope.msgs.push(data.msg);
              }
                else
              {
                $scope.errors.push(data.error);
              }
     })
	  .error(function  (data) {
			 $scope.errors.push(status);
			 console.log("Good Allan");
	   });

			};
	}

  });
dispatch.controller('ClientController', function($scope, $http) {
		    $scope.errors = [];
            $scope.msgs = [];
	
	$scope.submitForm = function  (isValid) {
		if (isValid) {

			 $scope.errors.splice(0, $scope.errors.length); // remove all error messages
              $scope.msgs.splice(0, $scope.msgs.length);
	    $http.post('./clients.php',
		     {

		'clientphone' : $scope.phone,
		'clientname'  : $scope.name,
		'clientemail': $scope.email,
		'clientdistrict': $scope.district,
		'clientregion' : $scope.region,
		'clientresidance' : $scope.residance,
		'clientproductname' : $scope.productname,
		'clientproductprice' : $scope.productprice,
		'clientproductsize' : $scope.productsize,
		'clientproductcolor' : $scope.productcolor,
		'clientproducttype' : $scope.producttype
	  }).success( function (data,status,headers,config){
		  if (data.msg != '')
              {
                $scope.msgs.push(data.msg);
              }
                else
              {
                $scope.errors.push(data.error);
              }
     })
	  .error(function  (data) {
			 $scope.errors.push(status);
	   });


		};
	}
});

dispatch.controller('CustomerController', function($scope, $http) {
		    $scope.errors = [];
            $scope.msgs = [];
	
	$scope.submitForm = function  (isValid) {
		if (isValid) {

			 $scope.errors.splice(0, $scope.errors.length); //
              $scope.msgs.splice(0, $scope.msgs.length);
	    $http.post('./addCustomer.php',
		     {
        'customerphone' : $scope.phone,
		'customername'  : $scope.name,
		'customerdistrict': $scope.district,
		'customerregion' : $scope.region,
		'customerparish' : $scope.parish,
		'customerproductname' : $scope.productname,
		'customerproductprice' : $scope.productprice
	  }).success( function (data,status,headers,config){
	  	console.log(data);
	  	console.log(status);
		  if (data.msg != '')
              {
                $scope.msgs.push(data.msg);
              }
                else
              {
                $scope.errors.push(data.error);
              }
     })
	  .error(function  (data) {
			 $scope.errors.push(status);
	   });


		};
	}
});