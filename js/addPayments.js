
$(document).ready(function() {


$('#addPayment').on('click', function(e){
    // We don't want this to act as a link so cancel the link action
    e.preventDefault();

    add_Payments();
  });

function add_Payments() {

	  var client = $('#client').val();
	  var date= $('#date').val();
    var amount= $('#amount').val();
    var mode= $('#mode').val();
    var remarks= $('#remarks').val();
    var user = $('#user').val();

       $.ajax({
       url: 'add_payments.php',
       data: 'client='+client+'&date='+date+'&amount='+amount+'&remarks='+remarks+'&user='+user,
       type: "POST",
       cache: false,  
       success: function(data,status,response) {
        console.log(data);
        console.log(status);
        console.log(response);
        $('#payment').html(data);
        //location.reload();
       },
       error: function (jqXHR, textStatus, errorThrown) {
        console.log(textStatus, errorThrown);
       }
    }); 
}

});