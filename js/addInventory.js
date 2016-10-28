
$(document).ready(function() {


$('#addInvent').on('click', function(e){
    // We don't want this to act as a link so cancel the link action
    e.preventDefault();

    addInventory();
  });

function addInventory () {

	  var serial_no = $('#serial_no').val();
    var item_number = $('#item_number').val();
	  var product= $('#product').val();

       $.ajax({
        url: 'addInventotry.php',
       data: 'product='+ product+'&serial_no='+serial_no+'&item_number='+item_number,
       type: "POST",
       cache: false,  
       success: function(data) {
        $('#response').html(data);
        //location.reload();
       }
    }); 
}

});