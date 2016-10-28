
$(document).ready(function() {


$('#editInvent').on('click', function(e){
    // We don't want this to act as a link so cancel the link action
    e.preventDefault();

    addInventory();
  });

function addInventory () {

	  var serial_no = $('#serial_no').val();
	  var id= $('#id').val();

       $.ajax({
        url: 'edit_mess.php',
       data: 'id='+ id+'&serial_no='+serial_no,
       type: "POST",
       cache: false,  
       success: function(data) {
        $('#response').html(data);
        //location.reload();
       }
    }); 
}

});