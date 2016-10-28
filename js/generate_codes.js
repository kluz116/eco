$(document).ready(function() {


$('#generate_code').on('click', function(e){
    // We don't want this to act as a link so cancel the link action
    e.preventDefault();

    addInventory();
  });

function addInventory () {

	  var serialNo = $('#serialNo').val();
	  var minutes= $('#minutes').val();

       $.ajax({
        url: 'encoder.php',
       data: 'serialNo='+ serialNo+'&minutes='+minutes,
       type: "POST",
       cache: false,  
       success: function(data) {
        $('#response_code').html(data);
        //location.reload();
       }
    }); 
}

});